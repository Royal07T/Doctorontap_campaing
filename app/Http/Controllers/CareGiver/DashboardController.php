<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MedicationLog;
use App\Models\Observation;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the care giver dashboard with rich stats and patient cards.
     */
    public function index()
    {
        $careGiver = Auth::guard('care_giver')->user();

        // ── Assigned patients (eager-load latest vital + active care plan) ──
        $assignedPatients = $careGiver->assignedPatients()
            ->with(['latestVitalSigns', 'activeCarePlan'])
            ->get();

        $patientIds = $assignedPatients->pluck('id')->toArray();

        // ── Stat counters ────────────────────────────────────────────────
        $assignedPatientsCount = $assignedPatients->count();

        $activeCarePlansCount = DB::table('caregiver_patient_assignments')
            ->where('caregiver_id', $careGiver->id)
            ->where('status', 'active')
            ->whereNotNull('care_plan_id')
            ->distinct('care_plan_id')
            ->count('care_plan_id');

        $todayVitals = VitalSign::where('caregiver_id', $careGiver->id)
            ->whereDate('created_at', today())
            ->count();

        $todayObservations = Observation::where('caregiver_id', $careGiver->id)
            ->whereDate('created_at', today())
            ->count();

        $criticalVitals = VitalSign::where('caregiver_id', $careGiver->id)
            ->where('flag_status', 'critical')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // 7-day medication compliance across this caregiver's patients
        $medCompliance = 0;
        if (!empty($patientIds)) {
            $totalMeds = MedicationLog::whereIn('patient_id', $patientIds)
                ->where('scheduled_time', '>=', now()->subDays(7))
                ->count();
            $givenMeds = MedicationLog::whereIn('patient_id', $patientIds)
                ->where('scheduled_time', '>=', now()->subDays(7))
                ->where('status', MedicationLog::STATUS_GIVEN)
                ->count();
            $medCompliance = $totalMeds > 0 ? round(($givenMeds / $totalMeds) * 100, 0) : 100;
        }

        $stats = [
            'assigned_patients'  => $assignedPatientsCount,
            'active_care_plans'  => $activeCarePlansCount,
            'today_vitals'       => $todayVitals,
            'today_observations' => $todayObservations,
            'critical_vitals'    => $criticalVitals,
            'med_compliance'     => $medCompliance,
        ];

        // ── Shift task checklist (client-side only, not persisted) ───────
        $shiftTasks = [
            ['id' => 1, 'label' => 'Morning vitals check',        'time' => '06:00 – 08:00', 'done' => false],
            ['id' => 2, 'label' => 'Administer morning meds',     'time' => '08:00 – 09:00', 'done' => false],
            ['id' => 3, 'label' => 'Record mood & observation',   'time' => '09:00 – 10:00', 'done' => false],
            ['id' => 4, 'label' => 'Mobility / physio exercises', 'time' => '10:00 – 11:00', 'done' => false],
            ['id' => 5, 'label' => 'Nutrition & hydration log',   'time' => '12:00 – 13:00', 'done' => false],
            ['id' => 6, 'label' => 'Afternoon vitals check',      'time' => '14:00 – 15:00', 'done' => false],
            ['id' => 7, 'label' => 'Administer evening meds',     'time' => '18:00 – 19:00', 'done' => false],
            ['id' => 8, 'label' => 'End-of-shift summary',        'time' => '20:00 – 21:00', 'done' => false],
        ];

        // ── Recent audit activity ────────────────────────────────────────
        $recentActivity = AuditLog::where('user_id', $careGiver->id)
            ->where('user_type', 'care_giver')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('care-giver.dashboard', compact(
            'careGiver',
            'stats',
            'shiftTasks',
            'assignedPatients',
            'recentActivity',
        ));
    }
}

