<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\CarePlanScopingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Display a listing of assigned patients
     */
    public function index()
    {
        $careGiver = Auth::guard('care_giver')->user();

        // Get only assigned patients (active assignments)
        $patients = $careGiver->assignedPatients()
            ->with(['latestVitalSigns', 'activeCarePlan', 'caregiverAssignments' => function ($query) use ($careGiver) {
                $query->where('caregiver_id', $careGiver->id)
                      ->where('status', 'active');
            }])
            ->paginate(15);

        return view('care-giver.patients.index', compact('patients'));
    }

    /**
     * Display the specified patient profile with Livewire-powered
     * vitals entry, observations, medication tracker and vitals chart.
     */
    public function show(Patient $patient)
    {
        // Authorization check - caregiver can ONLY view assigned patients
        $this->authorize('view', $patient);

        $careGiver = Auth::guard('care_giver')->user();

        // Assignment details
        $assignment = $careGiver->patientAssignments()
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with('assignedBy')
            ->first();

        // Care plan + feature flags
        $carePlan = $patient->activeCarePlan;
        $scopingService = new CarePlanScopingService();
        $features = $scopingService->featureMap($patient);

        // Last 7 days vital signs (still used for the static table fallback)
        $vitalSigns = $patient->vitalSigns()
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('care-giver.patients.show', compact(
            'patient',
            'assignment',
            'vitalSigns',
            'carePlan',
            'features',
        ));
    }
}
