<?php

namespace App\Http\Controllers\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\Patient;
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
            ->with(['latestVitalSigns', 'caregiverAssignments' => function($query) use ($careGiver) {
                $query->where('caregiver_id', $careGiver->id)
                      ->where('status', 'active');
            }])
            ->paginate(15);
        
        return view('care-giver.patients.index', compact('patients'));
    }

    /**
     * Display the specified patient profile (read-only)
     */
    public function show(Patient $patient)
    {
        // Authorization check - caregiver can ONLY view assigned patients
        $this->authorize('view', $patient);
        
        $careGiver = Auth::guard('care_giver')->user();
        
        // Get assignment details
        $assignment = $careGiver->patientAssignments()
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with('assignedBy')
            ->first();
        
        // Get last 7 days vital signs
        $vitalSigns = $patient->vitalSigns()
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('care-giver.patients.show', compact('patient', 'assignment', 'vitalSigns'));
    }
}
