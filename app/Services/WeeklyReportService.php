<?php

namespace App\Services;

use App\Models\CarePlan;
use App\Models\MedicationLog;
use App\Models\Observation;
use App\Models\Patient;
use App\Models\VitalSign;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Generates weekly health‑summary PDFs for patients
 * enrolled in Executive or Sovereign care plans.
 */
class WeeklyReportService
{
    /**
     * Generate report data for a single patient covering the last 7 days.
     */
    public function gatherData(Patient $patient): array
    {
        $from = now()->subDays(7)->startOfDay();
        $to   = now()->endOfDay();

        // Vitals (ordered chronologically for charting)
        $vitals = VitalSign::where('patient_id', $patient->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        // Observations / mood entries
        $observations = Observation::where('patient_id', $patient->id)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        // Medication compliance
        $medications = MedicationLog::where('patient_id', $patient->id)
            ->whereBetween('scheduled_time', [$from, $to])
            ->get();

        $totalMeds    = $medications->count();
        $givenMeds    = $medications->where('status', MedicationLog::STATUS_GIVEN)->count();
        $compliance   = $totalMeds > 0 ? round(($givenMeds / $totalMeds) * 100, 1) : 100;

        // Vitals summaries
        $bpData    = $this->extractBPSeries($vitals);
        $hrData    = $vitals->pluck('heart_rate')->filter()->values();
        $spo2Data  = $vitals->pluck('oxygen_saturation')->filter()->values();
        $tempData  = $vitals->pluck('temperature')->filter()->values();
        $sugarData = $vitals->pluck('blood_sugar')->filter()->values();
        $dates     = $vitals->pluck('created_at')->map(fn ($d) => $d->format('M d'))->values();

        $criticalCount = $vitals->where('flag_status', 'critical')->count();
        $warningCount  = $vitals->where('flag_status', 'warning')->count();

        // Mood summary
        $moodCounts = $observations->groupBy('emoji_code')
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc();

        $avgPain = $observations->avg('pain_level');

        $carePlan = $patient->activeCarePlan;

        return [
            'patient'         => $patient,
            'carePlan'        => $carePlan,
            'from'            => $from,
            'to'              => $to,
            'vitals'          => $vitals,
            'dates'           => $dates,
            'bpSystolic'      => $bpData['systolic'],
            'bpDiastolic'     => $bpData['diastolic'],
            'heartRates'      => $hrData,
            'spo2'            => $spo2Data,
            'temperatures'    => $tempData,
            'bloodSugar'      => $sugarData,
            'criticalCount'   => $criticalCount,
            'warningCount'    => $warningCount,
            'observations'    => $observations,
            'moodCounts'      => $moodCounts,
            'avgPain'         => round($avgPain ?? 0, 1),
            'medications'     => $medications,
            'totalMeds'       => $totalMeds,
            'givenMeds'       => $givenMeds,
            'compliance'      => $compliance,
            'generatedAt'     => now(),
        ];
    }

    /**
     * Render and return a PDF string for one patient's weekly report.
     */
    public function generatePdf(Patient $patient): string
    {
        $data = $this->gatherData($patient);

        $pdf = Pdf::loadView('reports.weekly-health', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Get all patients eligible for a weekly report
     * (active Executive or Sovereign plan with weekly_reports feature).
     */
    public function eligiblePatients(): Collection
    {
        return Patient::whereHas('activeCarePlan', function ($q) {
            $q->whereIn('plan_type', ['executive', 'sovereign']);
        })->with('activeCarePlan')->get();
    }

    // ─── helpers ──────────────────────────────────

    private function extractBPSeries(Collection $vitals): array
    {
        $systolic  = [];
        $diastolic = [];

        foreach ($vitals as $v) {
            if (!$v->blood_pressure) {
                continue;
            }
            $parts = explode('/', $v->blood_pressure);
            if (count($parts) === 2) {
                $systolic[]  = (int) $parts[0];
                $diastolic[] = (int) $parts[1];
            }
        }

        return [
            'systolic'  => collect($systolic),
            'diastolic' => collect($diastolic),
        ];
    }
}
