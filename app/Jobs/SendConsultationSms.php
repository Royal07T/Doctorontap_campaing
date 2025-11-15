<?php

namespace App\Jobs;

use App\Notifications\ConsultationSmsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendConsultationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $type;
    protected array $data;
    protected mixed $model;

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
    public $backoff = [30, 60, 120]; // 30s, 1min, 2min

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @param string $type Type of SMS (confirmation, payment_request, etc.)
     * @param array $data Data for the SMS
     * @param mixed $model Optional model (Consultation, Doctor, etc.)
     */
    public function __construct(string $type, array $data, mixed $model = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->model = $model;
    }

    /**8b
     * Execute the job.
     */
    public function handle(ConsultationSmsNotification $smsNotification): void
    {
        try {
            $result = match ($this->type) {
                'consultation_confirmation' => $smsNotification->sendConsultationConfirmation($this->data),
                'payment_request' => $smsNotification->sendPaymentRequest($this->model),
                'treatment_plan_ready' => $smsNotification->sendTreatmentPlanReady($this->model),
                'status_change' => $smsNotification->sendStatusChange($this->model, $this->data['status']),
                'doctor_consultation' => $smsNotification->sendDoctorNewConsultation($this->model, $this->data),
                default => throw new \InvalidArgumentException("Unknown SMS type: {$this->type}"),
            };

            if (!$result['success']) {
                Log::warning("Queued SMS job completed with failure", [
                    'type' => $this->type,
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Queued SMS job exception", [
                'type' => $this->type,
                'error' => $e->getMessage()
            ]);
            
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendConsultationSms job failed after all retries', [
            'type' => $this->type,
            'data' => $this->data,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['sms', $this->type, 'consultation'];
    }
}

