<?php

namespace App\Jobs;

use App\Models\MedicationLog;
use App\Models\Patient;
use App\Notifications\MissedMedicationAlert;
use App\Services\VonageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs hourly. Checks for medications that were scheduled
 * in the past 2 hours but still have status = pending.
 * Marks them as missed and sends an alert.
 */
class MissedMedicationAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $backoff = 30;

    public function handle(): void
    {
        $cutoff = now()->subHours(2);

        // Find pending medications that are overdue
        $overdueMeds = MedicationLog::where('status', MedicationLog::STATUS_PENDING)
            ->where('scheduled_time', '<=', $cutoff)
            ->get();

        if ($overdueMeds->isEmpty()) {
            return;
        }

        // Mark as missed
        MedicationLog::whereIn('id', $overdueMeds->pluck('id'))
            ->update(['status' => MedicationLog::STATUS_MISSED]);

        // Group by patient and send alerts
        $grouped = $overdueMeds->groupBy('patient_id');

        foreach ($grouped as $patientId => $meds) {
            try {
                $patient     = Patient::find($patientId);
                $missedCount = $meds->count();

                if (!$patient) {
                    continue;
                }

                // Email notification
                $patient->notify(new MissedMedicationAlert($patient, $missedCount));

                // SMS via Vonage to emergency contact
                if ($patient->emergency_contact_phone) {
                    try {
                        $vonage  = app(VonageService::class);
                        $message = "DoctorOnTap Alert: {$patient->name} has {$missedCount} missed medication dose(s). Please check with the assigned caregiver.";
                        $vonage->sendSMS($patient->emergency_contact_phone, $message);
                    } catch (\Throwable $e) {
                        Log::warning("[MissedMedicationAlertJob] SMS failed for patient #{$patientId}: {$e->getMessage()}");
                    }
                }

                Log::info("[MissedMedicationAlertJob] {$missedCount} missed meds for patient #{$patientId}");
            } catch (\Throwable $e) {
                Log::error("[MissedMedicationAlertJob] Error for patient #{$patientId}: {$e->getMessage()}");
            }
        }
    }
}
