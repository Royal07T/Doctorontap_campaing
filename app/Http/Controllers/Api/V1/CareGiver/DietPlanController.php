<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\DietPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DietPlanController extends Controller
{
    /**
     * List diet plans for a patient.
     */
    public function index(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $plans = DietPlan::where('patient_id', $patientId)
            ->with('creator:id,first_name,last_name')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Store a new diet plan.
     */
    public function store(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'meals' => 'nullable|array',
            'meals.*.name' => 'required|string|max:100',
            'meals.*.time' => 'required|string|max:20',
            'meals.*.items' => 'nullable|array',
            'meals.*.calories' => 'nullable|integer|min:0',
            'meals.*.notes' => 'nullable|string|max:500',
            'restrictions' => 'nullable|array',
            'supplements' => 'nullable|array',
            'target_calories' => 'nullable|integer|min:0|max:10000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'dietician_notes' => 'nullable|string|max:2000',
        ]);

        $validated['patient_id'] = $patientId;
        $validated['created_by'] = $caregiver->id;
        $validated['status'] = 'active';

        $plan = DietPlan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diet plan created',
            'data' => $plan,
        ], 201);
    }

    /**
     * Show a specific diet plan.
     */
    public function show($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $plan = DietPlan::where('patient_id', $patientId)
            ->with('creator:id,first_name,last_name')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Update a diet plan.
     */
    public function update(Request $request, $patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $plan = DietPlan::where('patient_id', $patientId)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'meals' => 'nullable|array',
            'restrictions' => 'nullable|array',
            'supplements' => 'nullable|array',
            'target_calories' => 'nullable|integer|min:0|max:10000',
            'end_date' => 'nullable|date',
            'status' => 'sometimes|string|in:active,paused,completed',
            'dietician_notes' => 'nullable|string|max:2000',
        ]);

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Diet plan updated',
            'data' => $plan->fresh(),
        ]);
    }
}
