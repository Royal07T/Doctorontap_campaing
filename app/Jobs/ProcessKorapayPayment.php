<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OPTIMIZATION: Queued job for Korapay payment initialization
 * Prevents blocking HTTP requests while waiting for external API response
 * Includes retry logic and timeout handling for better reliability
 */
class ProcessKorapayPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [60, 180, 300]; // Exponential backoff: 1min, 3min, 5min

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Payment $payment,
        public array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $apiUrl = config('services.korapay.api_url');
            $secretKey = config('services.korapay.secret_key');
            $fullUrl = $apiUrl . '/charges/initialize';
            
            Log::info('Processing Korapay payment initialization', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference,
                'api_url' => $apiUrl,
            ]);
            
            // OPTIMIZATION: Added timeout and retry logic for external API calls
            $response = Http::timeout(30)
                ->retry(2, 100, function ($exception, $request) {
                    // Only retry on connection exceptions, not on 4xx errors
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $secretKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($fullUrl, $this->payload);

            $responseData = $response->json();

            if ($response->successful() && ($responseData['status'] ?? false) === true) {
                // Update payment with checkout URL
                $this->payment->update([
                    'checkout_url' => $responseData['data']['checkout_url'] ?? null,
                    'korapay_response' => json_encode($responseData),
                ]);

                Log::info('Korapay payment initialized successfully', [
                    'payment_id' => $this->payment->id,
                    'reference' => $this->payment->reference,
                ]);
            } else {
                Log::error('Korapay initialization failed', [
                    'payment_id' => $this->payment->id,
                    'reference' => $this->payment->reference,
                    'response' => $responseData,
                    'status_code' => $response->status(),
                ]);

                // Update payment status to failed
                $this->payment->update([
                    'status' => 'failed',
                    'korapay_response' => json_encode($responseData),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Korapay payment job exception', [
                'payment_id' => $this->payment->id,
                'reference' => $this->payment->reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark payment as failed
            $this->payment->update([
                'status' => 'failed',
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Korapay payment job failed after all retries', [
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference,
            'error' => $exception->getMessage(),
        ]);

        // Update payment status to failed
        $this->payment->update([
            'status' => 'failed',
        ]);
    }
}
