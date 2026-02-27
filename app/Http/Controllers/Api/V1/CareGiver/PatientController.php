<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\CarePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * List patients assigned to the authenticated caregiver.
     */
    public function index(Request $request)
    {
        $caregiver = Auth::user();

        $patients = $caregiver->assignedPatients()
            ->with(['activeCarePlan'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('name', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%")
                       ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    /**
     * Show a single patient with care plan details.
     */
    public function show($id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $patient = Patient::with([
            'activeCarePlan',
            'observations' => fn($q) => $q->latest()->limit(10),
            'medicationLogs' => fn($q) => $q->today(),
            'vitalSigns' => fn($q) => $q->latest()->limit(10),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $patient,
        ]);
    }

    /**
     * Get dashboard stats for the caregiver's patients.
     */
    public function dashboard()
    {
        $caregiver = Auth::user();
        $patientIds = $caregiver->assignedPatients()->pluck('patients.id');

        $activeCarePlans = CarePlan::whereIn('patient_id', $patientIds)->active()->count();

        $todayObservations = \App\Models\Observation::whereIn('patient_id', $patientIds)
            ->today()->count();

        $pendingMeds = \App\Models\MedicationLog::whereIn('patient_id', $patientIds)
            ->today()->pending()->count();

        $missedMeds = \App\Models\MedicationLog::whereIn('patient_id', $patientIds)
            ->today()->missed()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_patients' => $patientIds->count(),
                'active_care_plans' => $activeCarePlans,
                'today_observations' => $todayObservations,
                'pending_medications' => $pendingMeds,
                'missed_medications' => $missedMeds,
            ],
        ]);
    }
}
