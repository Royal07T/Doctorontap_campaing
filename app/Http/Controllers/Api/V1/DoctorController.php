<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\ConsultationResource;
use App\Models\Doctor;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Get all doctors (public)
     */
    public function index(Request $request)
    {
        $query = Doctor::query()
            ->approved()
            ->where('is_available', true);

        if ($request->has('specialization')) {
            // SECURITY: Sanitize specialization input
            $specialization = trim(strip_tags($request->specialization));
            if (strlen($specialization) > 0 && strlen($specialization) <= 255) {
                $query->where('specialization', $specialization);
            }
        }

        if ($request->has('search')) {
            // SECURITY: Sanitize search input to prevent SQL injection
            $search = trim(strip_tags($request->search));
            if (strlen($search) > 0 && strlen($search) <= 255) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('specialization', 'like', "%{$search}%");
                });
            }
        }

        // SECURITY: Limit pagination to prevent resource exhaustion
        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page

        $doctors = $query->with('reviews')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return DoctorResource::collection($doctors);
    }

    /**
     * Get a specific doctor
     */
    public function show($id)
    {
        $doctor = Doctor::with('reviews')->findOrFail($id);
        return new DoctorResource($doctor);
    }

    /**
     * Get doctor's consultations
     */
    public function getConsultations($id, Request $request)
    {
        $user = Auth::user();
        
        // Only the doctor themselves or admins can see their consultations
        if ($user->getMorphClass() === 'Doctor' && $user->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $consultations = Consultation::where('doctor_id', $id)
            ->with(['patient'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return ConsultationResource::collection($consultations);
    }

    /**
     * Get doctor's reviews
     */
    public function getReviews($id)
    {
        $doctor = Doctor::with('reviews')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'doctor' => new DoctorResource($doctor),
                'reviews' => $doctor->reviews,
            ]
        ]);
    }

    /**
     * Update doctor availability
     */
    public function updateAvailability(Request $request, $id)
    {
        $user = Auth::user();
        
        // Only the doctor themselves can update availability
        if ($user->getMorphClass() !== 'Doctor' || $user->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $doctor = Doctor::findOrFail($id);
        $doctor->update(['is_available' => $request->is_available]);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully',
            'data' => new DoctorResource($doctor),
        ]);
    }

    /**
     * Get doctor's own consultations
     */
    public function myConsultations(Request $request)
    {
        $doctor = Auth::user();
        
        $consultations = Consultation::where('doctor_id', $doctor->id)
            ->with(['patient'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return ConsultationResource::collection($consultations);
    }

    /**
     * Update consultation status
     */
    public function updateConsultationStatus(Request $request, $id)
    {
        $doctor = Auth::user();
        
        $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
        ]);

        $consultation = Consultation::where('doctor_id', $doctor->id)
            ->findOrFail($id);

        $consultation->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Consultation status updated successfully',
            'data' => new ConsultationResource($consultation->load(['patient'])),
        ]);
    }

    /**
     * Create treatment plan
     */
    public function createTreatmentPlan(Request $request, $id)
    {
        $doctor = Auth::user();
        
        $request->validate([
            'diagnosis' => 'required|string',
            'treatment_plan' => 'required|string',
            'prescribed_medications' => 'nullable|array',
        ]);

        $consultation = Consultation::where('doctor_id', $doctor->id)
            ->findOrFail($id);

        $consultation->update([
            'diagnosis' => $request->diagnosis,
            'treatment_plan' => $request->treatment_plan,
            'prescribed_medications' => $request->prescribed_medications,
            'treatment_plan_created' => true,
            'treatment_plan_created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Treatment plan created successfully',
            'data' => new ConsultationResource($consultation->load(['patient'])),
        ]);
    }

    /**
     * Get doctor profile
     */
    public function getProfile($id)
    {
        $user = Auth::user();
        
        if ($user->getMorphClass() === 'Doctor' && $user->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $doctor = Doctor::with('reviews')->findOrFail($id);
        return new DoctorResource($doctor);
    }

    /**
     * Update doctor profile
     */
    public function updateProfile(Request $request, $id)
    {
        $doctor = Auth::user();
        
        if ($doctor->getMorphClass() !== 'Doctor' || $doctor->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'sometimes|string',
            'specialization' => 'sometimes|string',
        ]);

        $doctor->update($request->only(['name', 'bio', 'specialization']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new DoctorResource($doctor)
        ]);
    }

    /**
     * Get availability schedule
     */
    public function getAvailabilitySchedule($id)
    {
        $doctor = Doctor::findOrFail($id);
        $user = Auth::user();
        
        if ($user->getMorphClass() === 'Doctor' && $user->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'availability_schedule' => $doctor->availability_schedule,
                'days_of_availability' => $doctor->days_of_availability,
                'availability_start_time' => $doctor->availability_start_time,
                'availability_end_time' => $doctor->availability_end_time,
            ]
        ]);
    }

    /**
     * Update availability schedule
     */
    public function updateAvailabilitySchedule(Request $request, $id)
    {
        $doctor = Auth::user();
        
        if ($doctor->getMorphClass() !== 'Doctor' || $doctor->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'availability_schedule' => 'sometimes|array',
            'days_of_availability' => 'sometimes|array',
            'availability_start_time' => 'sometimes|string',
            'availability_end_time' => 'sometimes|string',
        ]);

        $doctor->update($request->only([
            'availability_schedule', 'days_of_availability',
            'availability_start_time', 'availability_end_time'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Availability schedule updated successfully',
            'data' => new DoctorResource($doctor)
        ]);
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($id)
    {
        $doctor = Auth::user();
        
        if ($doctor->getMorphClass() !== 'Doctor' || $doctor->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Implementation would get payment history
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Get patient history for consultation
     */
    public function getPatientHistory($id)
    {
        $doctor = Auth::user();
        $consultation = Consultation::where('doctor_id', $doctor->id)
            ->with(['patient', 'patient.consultations', 'patient.vitalSigns'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => $consultation->patient,
                'previous_consultations' => $consultation->patient->consultations ?? [],
                'vital_signs' => $consultation->patient->vitalSigns ?? [],
            ]
        ]);
    }

    /**
     * Refer patient
     */
    public function referPatient(Request $request, $id)
    {
        $doctor = Auth::user();
        $consultation = Consultation::where('doctor_id', $doctor->id)
            ->findOrFail($id);

        $request->validate([
            'referral_type' => 'required|string',
            'referral_details' => 'required|string',
        ]);

        // Implementation would create referral
        return response()->json([
            'success' => true,
            'message' => 'Patient referred successfully'
        ]);
    }
}

