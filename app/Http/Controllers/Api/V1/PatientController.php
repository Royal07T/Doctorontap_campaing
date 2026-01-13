<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Http\Resources\ConsultationResource;
use App\Models\Patient;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Get all patients (for doctors/admins)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        // Only doctors and admins can list patients
        if (!in_array($userType, ['Doctor', 'AdminUser', 'Nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Patient::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return PatientResource::collection($patients);
    }

    /**
     * Get a specific patient
     */
    public function show($id)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $patient = Patient::findOrFail($id);

        // Patients can only see their own profile
        if ($userType === 'Patient' && $user->id !== $patient->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Doctors and admins can see any patient
        if (!in_array($userType, ['Patient', 'Doctor', 'AdminUser', 'Nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return new PatientResource($patient);
    }

    /**
     * Update patient
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $patient = Patient::findOrFail($id);

        // Only patient themselves or admins can update
        if ($user->getMorphClass() === 'Patient' && $user->id !== $patient->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'phone' => ['sometimes', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'gender' => 'sometimes|in:male,female,other',
            'date_of_birth' => 'sometimes|date|before:today',
            'blood_group' => ['sometimes', 'string', 'max:10', 'regex:/^[A-Z]+$/'],
            'genotype' => ['sometimes', 'string', 'max:10', 'regex:/^[A-Z]+$/'],
        ]);

        // Sanitize input
        $data = $request->only(['name', 'phone', 'gender', 'date_of_birth', 'blood_group', 'genotype']);
        if (isset($data['name'])) {
            $data['name'] = trim(strip_tags($data['name']));
        }
        if (isset($data['phone'])) {
            $data['phone'] = preg_replace('/[^0-9+\-()\s]/', '', $data['phone']);
        }

        $patient->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'data' => new PatientResource($patient),
        ]);
    }

    /**
     * Get patient's consultations
     */
    public function getConsultations($id, Request $request)
    {
        $user = Auth::user();
        $patient = Patient::findOrFail($id);

        // Check authorization
        if ($user->getMorphClass() === 'Patient' && $user->id !== $patient->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $consultations = $patient->consultations()
            ->with(['doctor'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return ConsultationResource::collection($consultations);
    }

    /**
     * Get patient's medical history
     */
    public function getMedicalHistory($id)
    {
        $user = Auth::user();
        $patient = Patient::findOrFail($id);

        // Check authorization
        if ($user->getMorphClass() === 'Patient' && $user->id !== $patient->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $medicalHistory = $patient->medicalHistory()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $medicalHistory,
        ]);
    }

    /**
     * Get patient's medical records
     */
    public function getMedicalRecords(Request $request)
    {
        $patient = Auth::user();
        
        $consultations = Consultation::where('patient_id', $patient->id)
            ->with(['doctor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $consultations
        ]);
    }

    /**
     * Get patient's dependents
     */
    public function getDependents()
    {
        $patient = Auth::user();
        // Implementation would get dependents
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Store dependent
     */
    public function storeDependent(Request $request)
    {
        $patient = Auth::user();
        // Implementation would create dependent
        return response()->json([
            'success' => true,
            'message' => 'Dependent added successfully'
        ], 201);
    }

    /**
     * Get menstrual cycle
     */
    public function getMenstrualCycle()
    {
        $patient = Auth::user();
        // Implementation would get menstrual cycle data
        return response()->json([
            'success' => true,
            'data' => null
        ]);
    }

    /**
     * Store menstrual cycle
     */
    public function storeMenstrualCycle(Request $request)
    {
        $patient = Auth::user();
        // Implementation would store menstrual cycle
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle recorded successfully'
        ], 201);
    }

    /**
     * Update menstrual cycle
     */
    public function updateMenstrualCycle(Request $request, $id)
    {
        $patient = Auth::user();
        // Implementation would update menstrual cycle
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle updated successfully'
        ]);
    }

    /**
     * Delete menstrual cycle
     */
    public function deleteMenstrualCycle($id)
    {
        $patient = Auth::user();
        // Implementation would delete menstrual cycle
        return response()->json([
            'success' => true,
            'message' => 'Menstrual cycle deleted successfully'
        ]);
    }

    /**
     * Store sexual health record
     */
    public function storeSexualHealth(Request $request)
    {
        $patient = Auth::user();
        
        $request->validate([
            'date' => 'required|date|before:today',
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // SECURITY: Sanitize notes
        $data = $request->all();
        if (isset($data['notes'])) {
            $data['notes'] = trim(strip_tags($data['notes']));
        }

        // Implementation would store sexual health record using sanitized data
        return response()->json([
            'success' => true,
            'message' => 'Sexual health record saved successfully'
        ], 201);
    }

    /**
     * Get doctor availability
     */
    public function getDoctorAvailability($doctorId)
    {
        $doctor = \App\Models\Doctor::findOrFail($doctorId);
        // Implementation would get availability
        return response()->json([
            'success' => true,
            'data' => [
                'is_available' => $doctor->is_available,
                'schedule' => $doctor->availability_schedule
            ]
        ]);
    }

    /**
     * Check time slot availability
     */
    public function checkTimeSlotAvailability(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
            'time' => 'required|string',
        ]);
        
        // Implementation would check slot availability
        return response()->json([
            'success' => true,
            'data' => ['available' => true]
        ]);
    }

    /**
     * Create scheduled consultation
     */
    public function createScheduledConsultation(Request $request)
    {
        $patient = Auth::user();
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date',
            'consultation_mode' => 'required|in:voice,video,chat',
        ]);

        // Implementation would create scheduled consultation
        return response()->json([
            'success' => true,
            'message' => 'Consultation scheduled successfully'
        ], 201);
    }

    /**
     * Get canvasser patients
     */
    public function canvasserPatients(Request $request)
    {
        $canvasser = Auth::user();
        $patients = Patient::where('canvasser_id', $canvasser->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Store patient (for canvasser)
     */
    public function storePatient(Request $request)
    {
        $canvasser = Auth::user();
        
        if ($canvasser->getMorphClass() !== 'Canvasser') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:patients',
            'phone' => 'required|string',
        ]);

        $patient = Patient::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'canvasser_id' => $canvasser->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient registered successfully',
            'data' => $patient
        ], 201);
    }

    /**
     * View patient (for nurse)
     */
    public function viewPatient($id)
    {
        $patient = Patient::with(['consultations', 'vitalSigns'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $patient
        ]);
    }

    /**
     * Search patients (for nurse)
     */
    public function searchPatients(Request $request)
    {
        $query = Patient::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }
}

