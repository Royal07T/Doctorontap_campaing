<?php

namespace App\Http\Controllers\Api\V1\CareGiver;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Services\VitalsEscalationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VitalsController extends Controller
{
    /**
     * List vitals for a patient.
     */
    public function index(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $vitals = VitalSign::where('patient_id', $patientId)
            ->with('caregiver:id,first_name,last_name')
            ->when($request->days, function ($q, $days) {
                $q->where('created_at', '>=', now()->subDays($days));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $vitals,
        ]);
    }

    /**
     * Store new vital signs for a patient.
     */
    public function store(Request $request, $patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'blood_pressure' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\/]+$/'],
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_sugar' => 'nullable|numeric|min:0|max:1000',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'heart_rate' => 'nullable|integer|min:0|max:300',
            'respiratory_rate' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['patient_id'] = $patientId;
        $validated['caregiver_id'] = $caregiver->id;

        if (isset($validated['notes'])) {
            $validated['notes'] = trim(strip_tags($validated['notes']));
        }

        $vital = VitalSign::create($validated);

        // Run escalation check
        try {
            $escalationService = app(VitalsEscalationService::class);
            $escalationService->evaluate($vital);
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Log::warning('Vitals escalation check failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vital signs recorded successfully',
            'data' => $vital->load('patient:id,name'),
        ], 201);
    }

    /**
     * Get latest vitals for a patient.
     */
    public function latest($patientId)
    {
        $caregiver = Auth::user();

        if (!$caregiver->isAssignedToPatient($patientId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $latest = VitalSign::where('patient_id', $patientId)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $latest,
        ]);
    }
}
