<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConsultationStatusChange;

class DashboardController extends Controller
{
    /**
     * Display doctor dashboard
     */
    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        
        $stats = [
            'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
            'pending_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                    ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'completed')->count(),
            'paid_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                ->where('payment_status', 'paid')->count(),
            'pending_payments' => Consultation::where('doctor_id', $doctor->id)
                                              ->where('payment_status', 'pending')->count(),
            'total_earnings' => Consultation::where('doctor_id', $doctor->id)
                                            ->where('payment_status', 'paid')
                                            ->with('payment')
                                            ->get()
                                            ->sum(function($consultation) {
                                                return $consultation->payment ? $consultation->payment->amount : 0;
                                            }),
        ];

        // Get recent consultations with payment information
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
                                           ->with('payment')
                                           ->latest()
                                           ->limit(10)
                                           ->get();

        return view('doctor.dashboard', compact('stats', 'recentConsultations'));
    }

    /**
     * Display all consultations for the doctor
     */
    public function consultations(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $query = Consultation::where('doctor_id', $doctor->id);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search by patient name, email, or reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                
                // If search contains a space, also try searching first and last name separately
                if (strpos($search, ' ') !== false) {
                    $parts = explode(' ', trim($search), 2);
                    if (count($parts) == 2) {
                        $q->orWhere(function($subQ) use ($parts) {
                            $subQ->where('first_name', 'like', "%{$parts[0]}%")
                                 ->where('last_name', 'like', "%{$parts[1]}%");
                        });
                        // Also try reversed in case user typed "last first"
                        $q->orWhere(function($subQ) use ($parts) {
                            $subQ->where('first_name', 'like', "%{$parts[1]}%")
                                 ->where('last_name', 'like', "%{$parts[0]}%");
                        });
                    }
                }
            });
        }
        
        $consultations = $query->latest()->paginate(15);
        
        return view('doctor.consultations', compact('consultations'));
    }

    /**
     * Update consultation status
     */
    public function updateConsultationStatus(Request $request, $id)
    {
        try {
            \Log::info('Consultation status update request', [
                'id' => $id,
                'request_data' => $request->all(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            
            $doctor = Auth::guard('doctor')->user();
            
            if (!$doctor) {
                \Log::error('No authenticated doctor found');
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $validated = $request->validate([
                'status' => 'required|in:pending,scheduled,completed,cancelled',
                'notes' => 'nullable|string|max:1000',
            ]);
            
            \Log::info('Validation passed', ['validated_data' => $validated]);
            
            // Store old status for notification
            $oldStatus = $consultation->status;
            $newStatus = $validated['status'];
            
            $consultation->update([
                'status' => $newStatus,
                'doctor_notes' => $validated['notes'] ?? $consultation->doctor_notes,
                'consultation_completed_at' => $newStatus === 'completed' ? now() : $consultation->consultation_completed_at,
            ]);
            
            // Send notification to admin if status changed
            if ($oldStatus !== $newStatus) {
                $adminEmail = config('mail.admin_email');
                
                Mail::to($adminEmail)->send(new ConsultationStatusChange(
                    $consultation,
                    $doctor,
                    $oldStatus,
                    $newStatus
                ));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Consultation status updated successfully! Admin has been notified.',
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Consultation status update failed', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View single consultation details
     */
    public function viewConsultation($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->with(['doctor', 'payment', 'canvasser', 'nurse'])
                                       ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'consultation' => [
                    'id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'patient_name' => $consultation->first_name . ' ' . $consultation->last_name,
                    'email' => $consultation->email,
                    'mobile' => $consultation->mobile,
                    'age' => $consultation->age,
                    'gender' => ucfirst($consultation->gender),
                    'symptoms' => $consultation->problem,
                    'status' => ucfirst($consultation->status),
                    'payment_status' => $consultation->payment ? ucfirst($consultation->payment->status) : 'Pending',
                    'created_at' => $consultation->created_at->format('M d, Y h:i A'),
                    'medical_documents' => $consultation->medical_documents,
                    'doctor_notes' => $consultation->doctor_notes,
                    'diagnosis' => $consultation->diagnosis,
                    'treatment_plan' => $consultation->treatment_plan,
                    'prescribed_medications' => $consultation->prescribed_medications,
                    'follow_up_instructions' => $consultation->follow_up_instructions,
                    'lifestyle_recommendations' => $consultation->lifestyle_recommendations,
                    'referrals' => $consultation->referrals,
                    'next_appointment_date' => $consultation->next_appointment_date ? $consultation->next_appointment_date->format('Y-m-d') : null,
                    'additional_notes' => $consultation->additional_notes,
                    'has_treatment_plan' => $consultation->hasTreatmentPlan(),
                    'treatment_plan_accessible' => $consultation->isTreatmentPlanAccessible(),
                    'requires_payment' => $consultation->requiresPaymentForTreatmentPlan(),
                    'canvasser' => $consultation->canvasser ? $consultation->canvasser->name : 'N/A',
                    'nurse' => $consultation->nurse ? $consultation->nurse->name : 'Not Assigned',
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load consultation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update treatment plan
     */
    public function updateTreatmentPlan(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $validated = $request->validate([
                // Medical Format Fields
                'presenting_complaint' => 'required|string|max:2000',
                'history_of_complaint' => 'required|string|max:5000',
                'past_medical_history' => 'nullable|string|max:2000',
                'family_history' => 'nullable|string|max:2000',
                'drug_history' => 'nullable|string|max:2000',
                'social_history' => 'nullable|string|max:2000',
                'diagnosis' => 'required|string|max:2000',
                'investigation' => 'nullable|string|max:5000',
                'treatment_plan' => 'required|string|max:5000',
                // Additional fields
                'prescribed_medications' => 'nullable|array',
                'prescribed_medications.*.name' => 'required_with:prescribed_medications|string|max:255',
                'prescribed_medications.*.dosage' => 'required_with:prescribed_medications|string|max:255',
                'prescribed_medications.*.frequency' => 'required_with:prescribed_medications|string|max:255',
                'prescribed_medications.*.duration' => 'required_with:prescribed_medications|string|max:255',
                'follow_up_instructions' => 'nullable|string|max:2000',
                'lifestyle_recommendations' => 'nullable|string|max:2000',
                'referrals' => 'nullable|array',
                'referrals.*.specialist' => 'required_with:referrals|string|max:255',
                'referrals.*.reason' => 'required_with:referrals|string|max:500',
                'referrals.*.urgency' => 'required_with:referrals|in:routine,urgent,emergency',
                'next_appointment_date' => 'nullable|date|after:today',
                'additional_notes' => 'nullable|string|max:2000',
            ]);
            
            // Update consultation with treatment plan
            $consultation->update([
                // Medical Format Fields
                'presenting_complaint' => $validated['presenting_complaint'],
                'history_of_complaint' => $validated['history_of_complaint'],
                'past_medical_history' => $validated['past_medical_history'] ?? null,
                'family_history' => $validated['family_history'] ?? null,
                'drug_history' => $validated['drug_history'] ?? null,
                'social_history' => $validated['social_history'] ?? null,
                'diagnosis' => $validated['diagnosis'],
                'investigation' => $validated['investigation'] ?? null,
                'treatment_plan' => $validated['treatment_plan'],
                // Additional fields
                'prescribed_medications' => $validated['prescribed_medications'] ?? null,
                'follow_up_instructions' => $validated['follow_up_instructions'] ?? null,
                'lifestyle_recommendations' => $validated['lifestyle_recommendations'] ?? null,
                'referrals' => $validated['referrals'] ?? null,
                'next_appointment_date' => $validated['next_appointment_date'] ?? null,
                'additional_notes' => $validated['additional_notes'] ?? null,
                'treatment_plan_created' => true,
                'treatment_plan_created_at' => now(),
                'status' => 'completed',
                'consultation_completed_at' => now(),
            ]);

            // Send treatment plan ready email (before payment)
            try {
                Mail::to($consultation->email)->send(new \App\Mail\TreatmentPlanReadyNotification($consultation));
                \Illuminate\Support\Facades\Log::info('Treatment plan ready email sent', [
                    'consultation_id' => $consultation->id,
                    'email' => $consultation->email
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send treatment plan ready email', [
                    'consultation_id' => $consultation->id,
                    'email' => $consultation->email,
                    'error' => $e->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan created successfully! Patient will need to pay to access it.',
                'treatment_plan_created' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }
}

