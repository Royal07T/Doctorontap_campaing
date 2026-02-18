<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\MedicationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicationController extends Controller
{
    /**
     * List medication logs for a patient.
     */
    public function index(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $medications = MedicationLog::where('patient_id', $patientId)
            ->with('caregiver:id,first_name,last_name')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->boolean('today'), fn($q) => $q->today())
            ->orderBy('scheduled_time', 'asc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $medications,
        ]);
    }

    /**
     * Store a new medication log entry.
     */
    public function store(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'medication_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'scheduled_time' => 'required|date',
            'route' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['patient_id'] = $patientId;
        $validated['caregiver_id'] = $caregiver->id;
        $validated['status'] = 'pending';

        if (isset($validated['notes'])) {
            $validated['notes'] = trim(strip_tags($validated['notes']));
        }

        $log = MedicationLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medication log created',
            'data' => $log,
        ], 201);
    }

    /**
     * Mark a medication as given.
     */
    public function markGiven($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $log = MedicationLog::where('patient_id', $patientId)->findOrFail($id);
        $log->markAsGiven();

        return response()->json([
            'success' => true,
            'message' => 'Medication marked as given',
            'data' => $log->fresh(),
        ]);
    }

    /**
     * Mark a medication as missed.
     */
    public function markMissed($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $log = MedicationLog::where('patient_id', $patientId)->findOrFail($id);
        $log->markAsMissed();

        return response()->json([
            'success' => true,
            'message' => 'Medication marked as missed',
            'data' => $log->fresh(),
        ]);
    }

    /**
     * Get compliance rate for a patient.
     */
    public function compliance($patientId, Request $request)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $days = $request->get('days', 7);
        $rate = MedicationLog::complianceRate($patientId, $days);

        return response()->json([
            'success' => true,
            'data' => [
                'compliance_rate' => round($rate, 1),
                'period_days' => $days,
            ],
        ]);
    }
}
