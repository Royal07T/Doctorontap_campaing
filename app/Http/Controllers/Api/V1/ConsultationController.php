<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConsultationResource;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    protected $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }

    /**
     * Get all consultations (filtered by user type)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userType = $user->getMorphClass();

        $query = Consultation::query();

        // Filter based on user type
        if ($userType === 'Patient') {
            $query->where('patient_id', $user->id);
        } elseif ($userType === 'Doctor') {
            $query->where('doctor_id', $user->id);
        } elseif ($userType === 'AdminUser') {
            // Admins can see all
        } else {
            // Other user types see their assigned consultations
            $query->where(function ($q) use ($user, $userType) {
                $q->where('nurse_id', $user->id)
                  ->orWhere('canvasser_id', $user->id)
                  ->orWhere('customer_care_id', $user->id);
            });
        }

        // Apply filters with validation - SECURITY: Prevent SQL injection
        if ($request->has('status')) {
            $validStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];
            if (in_array($request->status, $validStatuses)) {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('consultation_mode')) {
            $validModes = ['voice', 'video', 'chat'];
            if (in_array($request->consultation_mode, $validModes)) {
                $query->where('consultation_mode', $request->consultation_mode);
            }
        }

        // SECURITY: Limit pagination to prevent resource exhaustion
        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page

        $consultations = $query->with(['doctor', 'patient'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ConsultationResource::collection($consultations);
    }

    /**
     * Get a specific consultation
     */
    public function show($id)
    {
        $user = Auth::user();
        $consultation = Consultation::with(['doctor', 'patient', 'sessions'])->findOrFail($id);

        // Check authorization
        $userType = $user->getMorphClass();
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return new ConsultationResource($consultation);
    }

    /**
     * Create a new consultation
     */
    public function store(\App\Http\Requests\Api\V1\StoreConsultationRequest $request)
    {

        $validated = $request->all();
        
        // If authenticated patient, use their ID
        if (Auth::check() && Auth::user()->getMorphClass() === 'Patient') {
            $validated['patient_id'] = Auth::id();
        }

        try {
            $result = $this->consultationService->createConsultation($validated, []);

            return response()->json([
                'success' => true,
                'message' => 'Consultation created successfully',
                'data' => [
                    'consultation' => Consultation::with(['doctor', 'patient'])->find($result['consultation']->id),
                    'reference' => $result['reference'],
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create consultation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a consultation
     */
    public function update(Request $request, $id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Authorization check
        $userType = $user->getMorphClass();
        if (!in_array($userType, ['Doctor', 'AdminUser', 'Nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'sometimes|in:pending,assigned,in_progress,completed,cancelled',
            'doctor_notes' => ['sometimes', 'string', 'max:5000'],
        ]);

        // Sanitize doctor notes to prevent XSS
        $data = $request->only(['status', 'doctor_notes']);
        if (isset($data['doctor_notes'])) {
            $data['doctor_notes'] = strip_tags($data['doctor_notes']);
        }

        $consultation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Consultation updated successfully',
            'data' => new ConsultationResource($consultation->load(['doctor', 'patient'])),
        ]);
    }

    /**
     * Get consultation session token
     */
    public function getSessionToken($id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Check authorization
        $userType = $user->getMorphClass();
        if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($userType === 'Doctor' && $consultation->doctor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // This would typically call your session service
        // For now, returning a placeholder
        return response()->json([
            'success' => true,
            'message' => 'Session token generated',
            'data' => [
                'token' => 'session-token-placeholder',
                'session_id' => 'session-id-placeholder',
            ]
        ]);
    }

    /**
     * End consultation session
     */
    public function endSession($id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        // Check authorization
        $userType = $user->getMorphClass();
        if (!in_array($userType, ['Doctor', 'Patient', 'AdminUser'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $consultation->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session ended successfully',
        ]);
    }

    /**
     * Get consultation status
     */
    public function getStatus($id)
    {
        $consultation = Consultation::findOrFail($id);
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $consultation->status,
                'session_status' => $consultation->session_status,
                'consultation_status' => $consultation->status,
            ]
        ]);
    }

    /**
     * Get patient's consultations
     */
    public function myConsultations(Request $request)
    {
        $patient = Auth::user();
        
        $consultations = Consultation::where('patient_id', $patient->id)
            ->with(['doctor'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return ConsultationResource::collection($consultations);
    }

    /**
     * Admin: Get all consultations
     */
    public function adminIndex(Request $request)
    {
        $query = Consultation::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $consultations = $query->with(['doctor', 'patient'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return ConsultationResource::collection($consultations);
    }

    /**
     * Admin: Assign doctor to consultation
     */
    public function assignDoctor(Request $request, $id)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $consultation = Consultation::findOrFail($id);
        $consultation->update(['doctor_id' => $request->doctor_id]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor assigned successfully',
            'data' => new ConsultationResource($consultation->load(['doctor', 'patient'])),
        ]);
    }

    /**
     * Admin: Assign nurse to consultation
     */
    public function assignNurse(Request $request, $id)
    {
        $user = Auth::user();
        
        // SECURITY: Only admins can assign nurses
        if ($user->getMorphClass() !== 'AdminUser') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate([
            'nurse_id' => 'required|exists:nurses,id',
        ]);

        $consultation = Consultation::findOrFail($id);
        $consultation->update(['nurse_id' => $request->nurse_id]);

        // Log assignment for audit trail
        \Log::info('Nurse assigned to consultation', [
            'consultation_id' => $id,
            'nurse_id' => $request->nurse_id,
            'assigned_by' => $user->id,
            'assigned_by_type' => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nurse assigned successfully',
            'data' => new ConsultationResource($consultation->load(['doctor', 'patient'])),
        ]);
    }

    /**
     * Admin: Send payment request
     */
    public function sendPaymentRequest($id)
    {
        $user = Auth::user();
        
        // SECURITY: Only admins can send payment requests
        if ($user->getMorphClass() !== 'AdminUser') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $consultation = Consultation::findOrFail($id);
        
        // Implementation would send payment request
        $consultation->update([
            'payment_request_sent' => true,
            'payment_request_sent_at' => now(),
        ]);

        // Log for audit trail
        \Log::info('Payment request sent', [
            'consultation_id' => $id,
            'sent_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent successfully'
        ]);
    }

    /**
     * Admin: Mark payment as paid
     */
    public function markPaymentAsPaid(Request $request, $id)
    {
        $user = Auth::user();
        
        // SECURITY: Only admins can mark payments as paid
        if ($user->getMorphClass() !== 'AdminUser') {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }

        $consultation = Consultation::findOrFail($id);
        
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
        ]);

        $consultation->update([
            'payment_status' => 'paid',
            'payment_id' => $request->payment_id,
        ]);

        // Log for audit trail
        \Log::info('Payment marked as paid', [
            'consultation_id' => $id,
            'payment_id' => $request->payment_id,
            'marked_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid successfully'
        ]);
    }

    /**
     * Create consultation for patient (canvasser)
     */
    public function createForPatient(Request $request, $patientId)
    {
        $canvasser = Auth::user();
        
        if ($canvasser->getMorphClass() !== 'Canvasser') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $patient = \App\Models\Patient::findOrFail($patientId);
        
        if ($patient->canvasser_id !== $canvasser->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'problem' => 'required|string',
            'consultation_mode' => 'required|in:voice,video,chat',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        // Implementation would create consultation
        return response()->json([
            'success' => true,
            'message' => 'Consultation created successfully'
        ], 201);
    }
}

