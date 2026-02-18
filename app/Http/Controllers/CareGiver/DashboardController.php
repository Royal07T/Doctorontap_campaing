<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\VitalSign;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the care giver dashboard — "Today's Shift" view.
     */
    public function index()
    {
        $careGiver = Auth::guard('care_giver')->user();

        // ── Assigned patients (eager-load latest vital + active care plan) ──
        $assignedPatients = $careGiver->assignedPatients()
            ->with(['latestVitalSigns', 'activeCarePlan'])
            ->get();

        $patientIds = $assignedPatients->pluck('id')->toArray();

        // ── Active shift patient ─────────────────────────────────────
        $activeShiftPatient = $assignedPatients->first();
        $shiftStart = now()->startOfDay()->addHours(8); // default 08:00 AM
        if ($shiftStart->gt(now())) {
            $shiftStart = now()->subHours(2); // fallback
        }
        $shiftDurationHours = $shiftStart->diffInHours(now());
        $shiftDurationMinutes = $shiftStart->diffInMinutes(now()) - ($shiftDurationHours * 60);
        $planType = optional(optional($activeShiftPatient)->activeCarePlan)->plan_type;

        $activeShift = [
            'patient_id' => $activeShiftPatient?->id,
            'patient'    => $activeShiftPatient
                ? ($activeShiftPatient->title ? $activeShiftPatient->title . ' ' : '') . $activeShiftPatient->name
                : 'No patient assigned yet',
            'location'   => optional($activeShiftPatient)->address
                ?? optional($activeShiftPatient)->home_address
                ?? optional($activeShiftPatient)->city
                ?? 'Patient home',
            'plan'       => $planType ? ucfirst($planType) . ' Plan' : 'Sovereign Plan',
            'started_at' => $shiftStart,
            'duration'   => sprintf('%dh %02dm', $shiftDurationHours, $shiftDurationMinutes),
        ];

        // ── Shift tasks with AM / Midday / PM periods ────────────────
        $shiftTasks = [
            // AM Tasks
            ['id' => 1,  'label' => 'Morning Medication (Pack A)',        'time' => '08:15 AM', 'period' => 'am',     'done' => true],
            ['id' => 2,  'label' => 'Assisted Shower & Grooming',        'time' => '09:30 AM', 'period' => 'am',     'done' => true],
            ['id' => 3,  'label' => 'Light Physiotherapy (Leg Exercises)','time' => '10:30 AM', 'period' => 'am',     'done' => false],
            ['id' => 4,  'label' => 'Prepare Morning Snack (Low Sugar)', 'time' => '11:00 AM', 'period' => 'am',     'done' => false],
            // Midday Tasks
            ['id' => 5,  'label' => 'Lunch Preparation & Hydration',     'time' => '12:00 PM', 'period' => 'midday', 'done' => false],
            ['id' => 6,  'label' => 'Midday Vitals Check',               'time' => '12:30 PM', 'period' => 'midday', 'done' => false],
            ['id' => 7,  'label' => 'Afternoon Walk / Mobility Aid',     'time' => '01:00 PM', 'period' => 'midday', 'done' => false],
            ['id' => 8,  'label' => 'Record Mood & Observation',         'time' => '02:00 PM', 'period' => 'midday', 'done' => false],
            // PM Tasks
            ['id' => 9,  'label' => 'Evening Medication Round',          'time' => '06:00 PM', 'period' => 'pm',     'done' => false],
            ['id' => 10, 'label' => 'Dinner Prep & Nutrition Log',       'time' => '06:30 PM', 'period' => 'pm',     'done' => false],
            ['id' => 11, 'label' => 'Night-time Vitals & Comfort Check', 'time' => '08:00 PM', 'period' => 'pm',     'done' => false],
            ['id' => 12, 'label' => 'End-of-Shift Summary',              'time' => '09:00 PM', 'period' => 'pm',     'done' => false],
        ];

        // ── Last recorded vitals for the active patient ──────────────
        $lastVital = $activeShiftPatient
            ? VitalSign::where('patient_id', $activeShiftPatient->id)
                ->orderByDesc('created_at')
                ->first()
            : null;

        $lastRecordedVitals = [
            'time'        => $lastVital ? $lastVital->created_at->format('h:i A') : null,
            'heart_rate'  => $lastVital?->heart_rate,
            'bp'          => $lastVital?->blood_pressure,
            'spo2'        => $lastVital?->oxygen_saturation,
            'temperature' => $lastVital?->temperature,
        ];

        // ── Handover notes (from latest observation or empty) ────────
        $latestObservation = $activeShiftPatient
            ? Observation::where('patient_id', $activeShiftPatient->id)
                ->where('caregiver_id', $careGiver->id)
                ->whereDate('created_at', today())
                ->orderByDesc('created_at')
                ->first()
            : null;

        $handoverNotes    = $latestObservation?->behavior_notes ?? '';
        $handoverLastSaved = $latestObservation
            ? $latestObservation->created_at->format('h:i A')
            : now()->format('h:i A');

        return view('care-giver.dashboard', compact(
            'careGiver',
            'assignedPatients',
            'activeShift',
            'shiftTasks',
            'lastRecordedVitals',
            'handoverNotes',
            'handoverLastSaved',
        ));
    }
}

