<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\CarePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarePlanController extends Controller
{
    /**
     * List care plans for a specific patient.
     */
    public function index(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $plans = CarePlan::where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get the active care plan for a patient.
     */
    public function active($patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $plan = CarePlan::where('patient_id', $patientId)->active()->first();

        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'No active care plan found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Show a specific care plan.
     */
    public function show($patientId, $id)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $plan = CarePlan::where('patient_id', $patientId)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }
}
