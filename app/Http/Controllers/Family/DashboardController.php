<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\MedicationLog;
use App\Models\Observation;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $member  = Auth::guard('family')->user();
        $patient = $member->patient;

        // Today's stats
        $todayVitals   = VitalSign::where('patient_id', $patient->id)->whereDate('created_at', today())->count();
        $criticalCount = VitalSign::where('patient_id', $patient->id)->whereDate('created_at', today())->where('flag_status', 'critical')->count();

        $latestMood = Observation::where('patient_id', $patient->id)
            ->latest()
            ->first();

        $medsTotal = MedicationLog::where('patient_id', $patient->id)->whereDate('scheduled_time', today())->count();
        $medsGiven = MedicationLog::where('patient_id', $patient->id)->whereDate('scheduled_time', today())->where('status', MedicationLog::STATUS_GIVEN)->count();

        // Last 7 days vitals for mini chart
        $recentVitals = VitalSign::where('patient_id', $patient->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at')
            ->get();

        // Recent observations
        $recentObservations = Observation::where('patient_id', $patient->id)
            ->latest()
            ->limit(5)
            ->get();

        $carePlan = $patient->activeCarePlan;

        return view('family.dashboard', compact(
            'member',
            'patient',
            'todayVitals',
            'criticalCount',
            'latestMood',
            'medsTotal',
            'medsGiven',
            'recentVitals',
            'recentObservations',
            'carePlan',
        ));
    }

    public function reports()
    {
        $member  = Auth::guard('family')->user();
        $patient = $member->patient;

        // List available PDF reports
        $reportDir = "weekly-reports/{$patient->id}";
        $files     = [];

        if (Storage::disk('local')->exists($reportDir)) {
            $rawFiles = Storage::disk('local')->files($reportDir);
            foreach ($rawFiles as $file) {
                $files[] = [
                    'name' => basename($file, '.pdf'),
                    'path' => $file,
                    'date' => basename($file, '.pdf'),
                ];
            }
            // Sort newest first
            usort($files, fn ($a, $b) => strcmp($b['date'], $a['date']));
        }

        return view('family.reports', compact('member', 'patient', 'files'));
    }

    public function downloadReport(string $date)
    {
        $member  = Auth::guard('family')->user();
        $patient = $member->patient;
        $path    = "weekly-reports/{$patient->id}/{$date}.pdf";

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Report not found.');
        }

        return Storage::disk('local')->download($path, "health-report-{$date}.pdf");
    }
}
