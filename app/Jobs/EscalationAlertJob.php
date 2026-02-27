<?php

namespace App\Jobs;

use App\Models\VitalSign;
use App\Services\VitalsEscalationService;
use App\Services\VonageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EscalationAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public VitalSign $vitalSign,
    ) {}

    public function handle(VitalsEscalationService $escalation): void
    {
        $this->vitalSign->loadMissing(['patient', 'caregiver']);
        $evaluation = $escalation->evaluate($this->vitalSign);

        if (!$evaluation['should_escalate']) {
            return;
        }

        $message = $escalation->buildAlertMessage($this->vitalSign, $evaluation);
        $patient = $this->vitalSign->patient;

        // Collect recipients: emergency contact, primary caregiver phone, admin
        $recipients = [];

        // Patient's emergency contact
        if ($patient->emergency_contact_phone) {
            $recipients[] = $patient->emergency_contact_phone;
        }

        // Caregiver's own phone (they may not be the one on-site)
        if ($this->vitalSign->caregiver?->phone) {
            $recipients[] = $this->vitalSign->caregiver->phone;
        }

        $recipients = array_unique(array_filter($recipients));

        if (empty($recipients)) {
            Log::warning('EscalationAlertJob: No recipients for critical vital sign', [
                'vital_sign_id' => $this->vitalSign->id,
                'patient_id' => $patient->id,
            ]);
            return;
        }

        try {
            $vonage = app(VonageService::class);

            foreach ($recipients as $phone) {
                $vonage->sendSMS($phone, $message);
            }

            Log::info('EscalationAlertJob: Alerts sent', [
                'vital_sign_id' => $this->vitalSign->id,
                'patient_id' => $patient->id,
                'recipient_count' => count($recipients),
                'level' => $evaluation['level'],
            ]);
        } catch (\Throwable $e) {
            Log::error('EscalationAlertJob: Failed to send alerts', [
                'vital_sign_id' => $this->vitalSign->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Let queue retry
        }
    }
}
