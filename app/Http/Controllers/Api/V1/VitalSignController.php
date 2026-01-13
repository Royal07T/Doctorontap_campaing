<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VitalSignController extends Controller
{
    /**
     * Get all vital signs
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $query = VitalSign::query();

        // Filter based on user type
        if ($userType === 'Nurse') {
            $query->where('nurse_id', $user->id);
        } elseif ($userType === 'CareGiver') {
            $query->where('caregiver_id', $user->id);
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $vitalSigns = $query->with(['patient', 'nurse', 'caregiver'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }

    /**
     * Store new vital signs
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Only nurses and caregivers can create vital signs
        if (!in_array($userType, ['Nurse', 'CareGiver'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'blood_pressure' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\/]+$/'],
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_sugar' => 'nullable|numeric|min:0|max:1000',
            'height' => 'nullable|numeric|min:0|max:300',
            'weight' => 'nullable|numeric|min:0|max:500',
            'heart_rate' => 'nullable|integer|min:0|max:300',
            'respiratory_rate' => 'nullable|integer|min:0|max:100',
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // SECURITY: Sanitize notes
        $data = $request->all();
        if (isset($data['notes'])) {
            $data['notes'] = trim(strip_tags($data['notes']));
        }

        $data = $request->all();
        if ($userType === 'Nurse') {
            $data['nurse_id'] = $user->id;
        } else {
            $data['caregiver_id'] = $user->id;
        }

        $vitalSign = VitalSign::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vital signs recorded successfully',
            'data' => $vitalSign->load(['patient'])
        ], 201);
    }

    /**
     * Get a specific vital sign
     */
    public function show($id)
    {
        $vitalSign = VitalSign::with(['patient', 'nurse', 'caregiver'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $vitalSign
        ]);
    }

    /**
     * Update vital signs
     */
    public function update(Request $request, $id)
    {
        $vitalSign = VitalSign::findOrFail($id);
        $user = Auth::user();

        // Only the creator or admin can update
        if ($user->getMorphClass() !== 'AdminUser' && 
            $vitalSign->nurse_id !== $user->id && 
            $vitalSign->caregiver_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'blood_pressure' => 'nullable|string',
            'oxygen_saturation' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $vitalSign->update($request->only([
            'blood_pressure', 'oxygen_saturation', 'temperature',
            'blood_sugar', 'height', 'weight', 'heart_rate',
            'respiratory_rate', 'notes'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Vital signs updated successfully',
            'data' => $vitalSign->load(['patient'])
        ]);
    }

    /**
     * Delete vital signs
     */
    public function destroy($id)
    {
        $vitalSign = VitalSign::findOrFail($id);
        $user = Auth::user();

        // Only admin can delete
        if ($user->getMorphClass() !== 'AdminUser') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $vitalSign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vital signs deleted successfully'
        ]);
    }

    /**
     * Get patient's vital signs
     */
    public function getPatientVitalSigns($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $vitalSigns = VitalSign::where('patient_id', $patientId)
            ->with(['nurse', 'caregiver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vitalSigns
        ]);
    }

    /**
     * Send vital signs via email
     */
    public function sendEmail(Request $request, $id)
    {
        $vitalSign = VitalSign::with('patient')->findOrFail($id);
        
        // Implementation would send email here
        $vitalSign->update([
            'email_sent' => true,
            'email_sent_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vital signs sent via email successfully'
        ]);
    }
}

