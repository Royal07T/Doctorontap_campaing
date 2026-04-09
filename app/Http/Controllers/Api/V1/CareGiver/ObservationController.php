<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservationController extends Controller
{
    /**
     * List observations for a patient.
     */
    public function index(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $observations = Observation::where('patient_id', $patientId)
            ->with('caregiver:id,first_name,last_name')
            ->when($request->days, fn($q, $days) => $q->lastDays($days))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $observations,
        ]);
    }

    /**
     * Store a new observation.
     */
    public function store(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'type' => 'required|string|in:general,wound,mobility,cognitive,respiratory,nutrition,sleep,pain,other',
            'description' => 'required|string|max:2000',
            'mood_score' => 'nullable|integer|min:1|max:5',
            'pain_level' => 'nullable|integer|min:0|max:10',
            'mobility_score' => 'nullable|integer|min:1|max:5',
            'appetite' => 'nullable|string|in:poor,fair,good,excellent',
            'sleep_quality' => 'nullable|string|in:poor,fair,good,excellent',
            'alert_triggered' => 'nullable|boolean',
        ]);

        $validated['patient_id'] = $patientId;
        $validated['caregiver_id'] = $caregiver->id;

        // Sanitize description
        $validated['description'] = trim(strip_tags($validated['description']));

        $observation = Observation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Observation recorded successfully',
            'data' => $observation->load('caregiver:id,first_name,last_name'),
        ], 201);
    }

    /**
     * Show a specific observation.
     */
    public function show($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $observation = Observation::where('patient_id', $patientId)
            ->with('caregiver:id,first_name,last_name')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $observation,
        ]);
    }
}
