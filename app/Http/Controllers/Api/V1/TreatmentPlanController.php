<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreatmentPlanController extends Controller
{
    /**
     * Get treatment plan for consultation
     */
    public function show($id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Check authorization
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'diagnosis' => $consultation->diagnosis,
                'treatment_plan' => $consultation->treatment_plan,
                'prescribed_medications' => $consultation->prescribed_medications,
                'follow_up_instructions' => $consultation->follow_up_instructions,
                'created_at' => $consultation->treatment_plan_created_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Create treatment plan
     */
    public function create(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Only assigned doctor can create treatment plan
        if ($user->getMorphClass() !== 'Doctor' || $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'diagnosis' => ['required', 'string', 'max:5000'],
            'treatment_plan' => ['required', 'string', 'max:10000'],
            'prescribed_medications' => 'nullable|array',
            'follow_up_instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        // SECURITY: Sanitize medical data to prevent XSS while preserving medical formatting
        $data = [
            'diagnosis' => trim(strip_tags($request->diagnosis)),
            'treatment_plan' => trim(strip_tags($request->treatment_plan)),
            'prescribed_medications' => $request->prescribed_medications,
            'follow_up_instructions' => $request->follow_up_instructions ? trim(strip_tags($request->follow_up_instructions)) : null,
            'treatment_plan_created' => true,
            'treatment_plan_created_at' => now(),
        ];

        $consultation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan created successfully',
            'data' => $consultation->fresh()
        ]);
    }

    /**
     * Update treatment plan
     */
    public function update(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Only assigned doctor can update
        if ($user->getMorphClass() !== 'Doctor' || $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'diagnosis' => 'sometimes|string',
            'treatment_plan' => 'sometimes|string',
            'prescribed_medications' => 'nullable|array',
        ]);

        $consultation->update($request->only([
            'diagnosis', 'treatment_plan', 'prescribed_medications',
            'follow_up_instructions'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan updated successfully',
            'data' => $consultation->fresh()
        ]);
    }

    /**
     * Unlock treatment plan (for payment)
     */
    public function unlock(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Only patient can unlock
        if ($user->getMorphClass() !== 'Patient' || $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Implementation would handle payment and unlock
        $consultation->update([
            'treatment_plan_unlocked' => true,
            'treatment_plan_unlocked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan unlocked successfully'
        ]);
    }
}

