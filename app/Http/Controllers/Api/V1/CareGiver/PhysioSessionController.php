<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\PhysioSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhysioSessionController extends Controller
{
    /**
     * List physio sessions for a patient.
     */
    public function index(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $sessions = PhysioSession::where('patient_id', $patientId)
            ->with('creator:id,first_name,last_name')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('scheduled_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Store a new physio session.
     */
    public function store(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'session_type' => 'required|string|in:assessment,treatment,follow_up,exercise_session,manual_therapy,electrotherapy',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:180',
            'exercises' => 'nullable|array',
            'exercises.*.name' => 'required|string|max:100',
            'exercises.*.sets' => 'nullable|integer|min:1',
            'exercises.*.reps' => 'nullable|integer|min:1',
            'exercises.*.duration' => 'nullable|string|max:50',
            'exercises.*.notes' => 'nullable|string|max:500',
            'findings' => 'nullable|string|max:2000',
            'treatment_notes' => 'nullable|string|max:2000',
            'pain_level_before' => 'nullable|integer|min:0|max:10',
            'pain_level_after' => 'nullable|integer|min:0|max:10',
            'mobility_score' => 'nullable|integer|min:1|max:10',
            'next_session_plan' => 'nullable|string|max:1000',
        ]);

        $validated['patient_id'] = $patientId;
        $validated['created_by'] = $caregiver->id;
        $validated['status'] = 'scheduled';

        $session = PhysioSession::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Physio session created',
            'data' => $session,
        ], 201);
    }

    /**
     * Show a specific physio session.
     */
    public function show($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $session = PhysioSession::where('patient_id', $patientId)
            ->with('creator:id,first_name,last_name')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * Update a physio session.
     */
    public function update(Request $request, $patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $session = PhysioSession::where('patient_id', $patientId)->findOrFail($id);

        $validated = $request->validate([
            'session_type' => 'sometimes|string|in:assessment,treatment,follow_up,exercise_session,manual_therapy,electrotherapy',
            'scheduled_at' => 'sometimes|date',
            'duration_minutes' => 'sometimes|integer|min:15|max:180',
            'exercises' => 'nullable|array',
            'findings' => 'nullable|string|max:2000',
            'treatment_notes' => 'nullable|string|max:2000',
            'pain_level_before' => 'nullable|integer|min:0|max:10',
            'pain_level_after' => 'nullable|integer|min:0|max:10',
            'mobility_score' => 'nullable|integer|min:1|max:10',
            'next_session_plan' => 'nullable|string|max:1000',
        ]);

        $session->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Physio session updated',
            'data' => $session->fresh(),
        ]);
    }

    /**
     * Mark a session as completed.
     */
    public function complete(Request $request, $patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $session = PhysioSession::where('patient_id', $patientId)->findOrFail($id);

        $validated = $request->validate([
            'findings' => 'nullable|string|max:2000',
            'treatment_notes' => 'nullable|string|max:2000',
            'pain_level_after' => 'nullable|integer|min:0|max:10',
            'mobility_score' => 'nullable|integer|min:1|max:10',
            'next_session_plan' => 'nullable|string|max:1000',
        ]);

        $session->update(array_merge($validated, [
            'status' => 'completed',
            'completed_at' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Session marked as completed',
            'data' => $session->fresh(),
        ]);
    }

    /**
     * Cancel a session.
     */
    public function cancel($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $session = PhysioSession::where('patient_id', $patientId)->findOrFail($id);
        $session->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Session cancelled',
            'data' => $session->fresh(),
        ]);
    }
}
