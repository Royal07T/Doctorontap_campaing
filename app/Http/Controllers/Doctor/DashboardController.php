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
        ];

        // Get recent consultations
        $recentConsultations = Consultation::where('doctor_id', $doctor->id)
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
                  ->orWhere('reference_number', 'like', "%{$search}%");
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
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $validated = $request->validate([
                'status' => 'required|in:pending,scheduled,completed,cancelled',
                'notes' => 'nullable|string|max:1000',
            ]);
            
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
                $adminEmail = env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng');
                
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
                'diagnosis' => 'required|string|max:2000',
                'treatment_plan' => 'required|string|max:5000',
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
                'diagnosis' => $validated['diagnosis'],
                'treatment_plan' => $validated['treatment_plan'],
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

