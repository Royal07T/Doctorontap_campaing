<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Notifications\WeeklyHealthReport;
use App\Services\WeeklyReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Runs every Sunday at 08:00.
 * Generates a weekly health PDF for every patient on an
 * Executive or Sovereign plan and emails it to the patient's
 * emergency contact (family) and the assigned physician.
 */
class WeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function handle(WeeklyReportService $service): void
    {
        $patients = $service->eligiblePatients();

        Log::info("[WeeklyReportJob] Generating reports for {$patients->count()} patients.");

        foreach ($patients as $patient) {
            try {
                $pdfContent = $service->generatePdf($patient);

                // Store to disk (can be served later in family portal)
                $filename = "weekly-reports/{$patient->id}/" . now()->format('Y-m-d') . '.pdf';
                Storage::disk('local')->put($filename, $pdfContent);

                // Send notification with PDF attached
                $patient->notify(new WeeklyHealthReport($filename, $patient));

                Log::info("[WeeklyReportJob] Report generated for patient #{$patient->id}");
            } catch (\Throwable $e) {
                Log::error("[WeeklyReportJob] Failed for patient #{$patient->id}: {$e->getMessage()}");
            }
        }
    }
}
