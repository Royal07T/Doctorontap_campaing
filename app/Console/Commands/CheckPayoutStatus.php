<?php

namespace App\Console\Commands;

use App\Models\DoctorPayment;
use App\Services\KorapayPayoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPayoutStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Korapay status for processing payouts (webhook fallback)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Korapay status for processing payouts...');

        // Get payouts that are processing and have a Korapay reference
        $processingPayouts = DoctorPayment::where('status', 'processing')
            ->whereNotNull('korapay_reference')
            ->where('payout_initiated_at', '>=', now()->subHours(24)) // Only check recent payouts
            ->get();

        $this->info("Found {$processingPayouts->count()} processing payouts to check.");

        $korapayService = new KorapayPayoutService();
        $updated = 0;
        $failed = 0;
        $stillProcessing = 0;

        foreach ($processingPayouts as $payment) {
            $this->line("Checking payout {$payment->reference} (Korapay: {$payment->korapay_reference})...");

            $verification = $korapayService->verifyPayoutStatus($payment->korapay_reference);

            if (!$verification['success']) {
                $this->error("  Failed to verify: {$verification['message']}");
                $failed++;
                continue;
            }

            $data = $verification['data'] ?? [];
            $korapayStatus = $data['status'] ?? 'processing';

            $this->line("  Korapay status: {$korapayStatus}");

            // Map Korapay status to our status
            if ($korapayStatus === 'success' || $korapayStatus === 'successful') {
                $payment->update([
                    'status' => 'completed',
                    'korapay_status' => 'success',
                    'payout_completed_at' => now(),
                    'korapay_response' => array_merge($payment->korapay_response ?? [], [
                        'verified_at' => now()->toIso8601String(),
                        'verification_data' => $data,
                    ]),
                ]);

                // Send notification to doctor
                if ($payment->doctor && $payment->doctor->email) {
                    \Mail::to($payment->doctor->email)->send(new \App\Mail\DoctorPayoutCompletedNotification($payment));
                }

                $this->info("  ✅ Updated to completed");
                $updated++;
            } elseif ($korapayStatus === 'failed') {
                $payment->update([
                    'status' => 'failed',
                    'korapay_status' => 'failed',
                    'korapay_response' => array_merge($payment->korapay_response ?? [], [
                        'verified_at' => now()->toIso8601String(),
                        'verification_data' => $data,
                    ]),
                ]);

                $this->warn("  ⚠️ Updated to failed");
                $updated++;
            } else {
                $stillProcessing++;
                $this->line("  Still processing");
            }
        }

        $this->info("\nSummary:");
        $this->line("  Updated: {$updated}");
        $this->line("  Still processing: {$stillProcessing}");
        $this->line("  Failed to verify: {$failed}");

        Log::info('Payout status check completed', [
            'total_checked' => $processingPayouts->count(),
            'updated' => $updated,
            'still_processing' => $stillProcessing,
            'failed' => $failed,
        ]);

        return Command::SUCCESS;
    }
}
