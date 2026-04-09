<?php

namespace App\Jobs;

use App\Models\CarePlan;
use App\Models\MedicationLog;
use App\Models\Observation;
use App\Models\Patient;
use App\Models\VitalSign;
use App\Notifications\DailyFamilySummary;
use App\Services\VonageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs daily at 20:00. Sends a summary to each patient's
 * emergency contact (family) via email + WhatsApp.
 */
class DailyFamilySummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $backoff = 60;

    public function handle(): void
    {
        // All patients with an active care plan
        $patients = Patient::whereHas('activeCarePlan')->with('activeCarePlan')->get();

        foreach ($patients as $patient) {
            try {
                $summary = $this->buildSummary($patient);

                // Email notification (sent to patient, routed to family via toMail)
                $patient->notify(new DailyFamilySummary($patient, $summary));

                // WhatsApp to emergency contact
                if ($patient->emergency_contact_phone) {
                    $this->sendWhatsApp($patient, $summary);
                }

                Log::info("[DailyFamilySummaryJob] Summary sent for patient #{$patient->id}");
            } catch (\Throwable $e) {
                Log::error("[DailyFamilySummaryJob] Failed for patient #{$patient->id}: {$e->getMessage()}");
            }
        }
    }

    protected function buildSummary(Patient $patient): array
    {
        $today = today();

        $vitalsCount   = VitalSign::where('patient_id', $patient->id)->whereDate('created_at', $today)->count();
        $criticalCount = VitalSign::where('patient_id', $patient->id)->whereDate('created_at', $today)->where('flag_status', 'critical')->count();

        $latestMood = Observation::where('patient_id', $patient->id)
            ->whereDate('created_at', $today)
            ->latest()
            ->value('emoji_code') ?? 'No observation';

        $medsTotal = MedicationLog::where('patient_id', $patient->id)->whereDate('scheduled_time', $today)->count();
        $medsGiven = MedicationLog::where('patient_id', $patient->id)->whereDate('scheduled_time', $today)->where('status', MedicationLog::STATUS_GIVEN)->count();

        return [
            'vitals_count'   => $vitalsCount,
            'critical_count' => $criticalCount,
            'latest_mood'    => $latestMood,
            'meds_given'     => $medsGiven,
            'meds_total'     => $medsTotal,
        ];
    }

    protected function sendWhatsApp(Patient $patient, array $summary): void
    {
        try {
            $vonage  = app(VonageService::class);
            $message = "DoctorOnTap Daily Update for {$patient->name}:\n"
                . "Vitals: {$summary['vitals_count']} recorded\n"
                . "Mood: {$summary['latest_mood']}\n"
                . "Meds: {$summary['meds_given']}/{$summary['meds_total']} given\n"
                . ($summary['critical_count'] > 0 ? "⚠ {$summary['critical_count']} critical flag(s) — please contact the care team." : "All readings within normal range.");

            $vonage->sendWhatsAppMessage($patient->emergency_contact_phone, $message);
        } catch (\Throwable $e) {
            Log::warning("[DailyFamilySummaryJob] WhatsApp failed for patient #{$patient->id}: {$e->getMessage()}");
        }
    }
}
