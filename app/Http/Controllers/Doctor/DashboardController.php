<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ConsultationStatusChange;

class DashboardController extends Controller
{
    /**
     * Display doctor dashboard
     */
    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        
        // Get doctor payment percentage from settings
        $doctorPercentage = \App\Models\Setting::get('doctor_payment_percentage', 70);
        
        // Get all paid consultations
        $paidConsultations = Consultation::where('doctor_id', $doctor->id)
                                         ->where('payment_status', 'paid')
                                         ->get();
        
        // Calculate total earnings from paid consultations
        $totalEarnings = $paidConsultations->sum(function($consultation) use ($doctor, $doctorPercentage) {
            $consultationFee = $doctor->effective_consultation_fee ?? 0;
            return ($consultationFee * $doctorPercentage) / 100;
        });
        
        // Calculate pending earnings (completed but not yet paid out to doctor)
        $completedPaidConsultations = Consultation::where('doctor_id', $doctor->id)
                                                   ->where('status', 'completed')
                                                   ->where('payment_status', 'paid')
                                                   ->get();
        
        $pendingEarnings = $completedPaidConsultations->sum(function($consultation) use ($doctor, $doctorPercentage) {
            $consultationFee = $doctor->effective_consultation_fee ?? 0;
            return ($consultationFee * $doctorPercentage) / 100;
        });
        
        // Calculate growth percentages (comparing last 30 days to previous 30 days)
        $currentMonthConsultations = Consultation::where('doctor_id', $doctor->id)
                                                 ->where('created_at', '>=', now()->subDays(30))
                                                 ->count();
        
        $previousMonthConsultations = Consultation::where('doctor_id', $doctor->id)
                                                  ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
                                                  ->count();
        
        $consultationsGrowth = $previousMonthConsultations > 0 
            ? round((($currentMonthConsultations - $previousMonthConsultations) / $previousMonthConsultations) * 100) 
            : 0;
        
        // Calculate earnings growth
        $currentMonthEarnings = Consultation::where('doctor_id', $doctor->id)
                                            ->where('payment_status', 'paid')
                                            ->where('created_at', '>=', now()->subDays(30))
                                            ->count() * ($doctor->effective_consultation_fee * $doctorPercentage / 100);
        
        $previousMonthEarnings = Consultation::where('doctor_id', $doctor->id)
                                             ->where('payment_status', 'paid')
                                             ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
                                             ->count() * ($doctor->effective_consultation_fee * $doctorPercentage / 100);
        
        $earningsGrowth = $previousMonthEarnings > 0 
            ? round((($currentMonthEarnings - $previousMonthEarnings) / $previousMonthEarnings) * 100) 
            : 0;
        
        $stats = [
            'total_consultations' => Consultation::where('doctor_id', $doctor->id)->count(),
            'pending_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                    ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('doctor_id', $doctor->id)
                                                     ->where('status', 'completed')->count(),
            'paid_consultations' => $paidConsultations->count(),
            'pending_payments' => Consultation::where('doctor_id', $doctor->id)
                                              ->where('payment_status', 'pending')->count(),
            'total_earnings' => $totalEarnings,
            'pending_earnings' => $pendingEarnings,
            'consultations_growth' => $consultationsGrowth,
            'earnings_growth' => $earningsGrowth,
            // Stats for consultations page style cards
            'total' => Consultation::where('doctor_id', $doctor->id)->count(),
            'paid' => Consultation::where('doctor_id', $doctor->id)->where('payment_status', 'paid')->count(),
            'unpaid' => Consultation::where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')->count(),
            'pending' => Consultation::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
            'completed' => Consultation::where('doctor_id', $doctor->id)->where('status', 'completed')->count(),
        ];

        // Get upcoming priority consultations (pending, scheduled, or within next 2 hours)
        $priorityConsultations = Consultation::where('doctor_id', $doctor->id)
                                             ->whereIn('status', ['pending', 'scheduled'])
                                             ->with(['patient'])
                                             ->orderByRaw("CASE 
                                                 WHEN status = 'pending' THEN 1 
                                                 WHEN status = 'scheduled' THEN 2 
                                                 ELSE 3 
                                             END")
                                             ->orderBy('created_at', 'desc')
                                             ->limit(5)
                                             ->get();

        // Get recent forum posts for sidebar
        $recentForumPosts = \App\Models\ForumPost::with(['doctor', 'category'])
                                                 ->published()
                                                 ->recent()
                                                 ->limit(2)
                                                 ->get();

        return view('doctor.dashboard', compact('stats', 'priorityConsultations', 'recentForumPosts'));
    }

    /**
     * Display all consultations for the doctor
     */
    public function consultations(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $query = Consultation::where('doctor_id', $doctor->id)->with(['payment', 'booking']);
        
        // Filter by consultation status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Search by patient name, email, or reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
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
        
        // Get doctor payment percentage from settings
        $doctorPercentage = \App\Models\Setting::get('doctor_payment_percentage', 70);
        
        // Get all paid consultations
        $paidConsultations = Consultation::where('doctor_id', $doctor->id)
                                         ->where('payment_status', 'paid')
                                         ->get();
        
        // Calculate total earnings from paid consultations
        $totalEarnings = $paidConsultations->sum(function($consultation) use ($doctor, $doctorPercentage) {
            $consultationFee = $doctor->effective_consultation_fee ?? 0;
            return ($consultationFee * $doctorPercentage) / 100;
        });
        
        // Calculate statistics
        $stats = [
            'total' => Consultation::where('doctor_id', $doctor->id)->count(),
            'paid' => Consultation::where('doctor_id', $doctor->id)->where('payment_status', 'paid')->count(),
            'unpaid' => Consultation::where('doctor_id', $doctor->id)
                ->where('status', 'completed')
                ->where('payment_status', '!=', 'paid')->count(),
            'pending' => Consultation::where('doctor_id', $doctor->id)->where('status', 'pending')->count(),
            'completed' => Consultation::where('doctor_id', $doctor->id)->where('status', 'completed')->count(),
            'total_earnings' => $totalEarnings,
        ];
        
        $consultations = $query->latest()->paginate(20);
        
        return view('doctor.consultations', compact('consultations', 'stats'));
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
                // NOTE: consultation_mode is NOT in validation - it is set by patient and cannot be changed by doctors
            ]);
            
            \Log::info('Validation passed', ['validated_data' => $validated]);
            
            $newStatus = $validated['status'];
            
            // PAYMENT CHECK: Before allowing consultation to start (status change to in_progress/scheduled), verify payment
            if (in_array($newStatus, ['scheduled', 'in_progress']) && $consultation->requiresPaymentBeforeStart()) {
                \Log::warning('Consultation status update blocked: payment required', [
                    'consultation_id' => $consultation->id,
                    'consultation_reference' => $consultation->reference,
                    'attempted_status' => $newStatus,
                    'payment_status' => $consultation->payment_status,
                    'doctor_id' => $doctor->id,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is required before this consultation can proceed. Please wait for the patient to complete payment.',
                    'payment_required' => true,
                ], 400);
            }
            
            // IMPORTANT: Only update status and notes - consultation_mode is set by patient and cannot be changed
            $consultation->update([
                'status' => $newStatus,
                'doctor_notes' => $validated['notes'] ?? $consultation->doctor_notes,
                'consultation_completed_at' => $newStatus === 'completed' ? now() : $consultation->consultation_completed_at,
                // NOTE: consultation_mode is NOT included - it is set by patient during booking and cannot be changed
            ]);
            
            // If consultation is marked as completed, check for missed consultations
            // (This ensures doctors who complete consultations don't get penalized)
            if ($newStatus === 'completed') {
                try {
                    $penaltyService = app(\App\Services\DoctorPenaltyService::class);
                    $penaltyService->checkMissedConsultations($doctor);
                } catch (\Exception $e) {
                    \Log::warning('Failed to check missed consultations after status update', [
                        'consultation_id' => $consultation->id,
                        'doctor_id' => $doctor->id,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the request if penalty check fails
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Consultation status updated successfully!',
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
    public function viewConsultation(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->with(['doctor', 'payment', 'canvasser', 'nurse', 'booking', 'patient'])
                                       ->firstOrFail();
            
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
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
            }
            
            // Get available doctors for referral (excluding current doctor)
            $availableDoctors = \App\Models\Doctor::where('id', '!=', $doctor->id)
                ->where('is_available', true)
                ->where('is_approved', true)
                ->orderBy('specialization')
                ->orderBy('name')
                ->get(['id', 'name', 'first_name', 'last_name', 'specialization', 'email'])
                ->map(function($doctor) {
                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name ?: trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')),
                        'first_name' => $doctor->first_name,
                        'last_name' => $doctor->last_name,
                        'specialization' => $doctor->specialization ?? 'General Practice',
                        'email' => $doctor->email,
                    ];
                })
                ->values();
            
            // Load patient medical information if consultation is not completed and patient is authenticated
            $patientMedicalInfo = null;
            if ($consultation->status !== 'completed' && $consultation->patient_id) {
                $patientMedicalInfo = \App\Models\Patient::find($consultation->patient_id);
            }
            
            // Return view for regular HTTP requests
            return view('doctor.consultation-details', compact('consultation', 'availableDoctors', 'patientMedicalInfo'));
            
        } catch (\Exception $e) {
            // For AJAX requests, return JSON error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load consultation: ' . $e->getMessage()
                ], 500);
            }
            
            // For regular requests, redirect with error
            return redirect()->route('doctor.consultations')
                ->with('error', 'Failed to load consultation: ' . $e->getMessage());
        }
    }

    /**
     * Create or update treatment plan
     */
    public function updateTreatmentPlan(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('updateTreatmentPlan called', [
            'consultation_id' => $id,
            'doctor_id' => Auth::guard('doctor')->id(),
            'request_data_keys' => array_keys($request->all())
        ]);
        
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::with(['patient', 'booking'])
                                       ->where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            \Illuminate\Support\Facades\Log::info('Consultation found for treatment plan update', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference,
                'email' => $consultation->email,
                'patient_id' => $consultation->patient_id,
                'patient_email' => $consultation->patient->email ?? 'N/A',
                'booking_id' => $consultation->booking_id,
                'payer_email' => $consultation->booking->payer_email ?? 'N/A',
                'payment_status' => $consultation->payment_status,
                'is_paid' => $consultation->isPaid(),
                'treatment_plan_created' => $consultation->treatment_plan_created
            ]);
            
            // Check if it's an update or create
            $isUpdate = $consultation->treatment_plan_created;
            
            // Prevent editing if treatment plan has already been created/saved
            // Once saved, treatment plan becomes a permanent medical record and cannot be edited
            if ($isUpdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Treatment plan cannot be edited once it has been saved. The treatment plan has been finalized and cannot be modified.'
                ], 403);
            }
            
            // Handle JSON fields that come as strings from FormData
            $requestData = $request->all();
            if (isset($requestData['prescribed_medications']) && is_string($requestData['prescribed_medications'])) {
                $requestData['prescribed_medications'] = json_decode($requestData['prescribed_medications'], true);
            }
            if (isset($requestData['referrals']) && is_string($requestData['referrals'])) {
                $requestData['referrals'] = json_decode($requestData['referrals'], true);
            }
            $request->merge($requestData);
            
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
                'treatment_plan_attachments' => 'nullable|array',
                'treatment_plan_attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240',
            ]);
            
            // Handle treatment plan attachments
            $attachments = $consultation->treatment_plan_attachments ?? [];
            
            if ($request->hasFile('treatment_plan_attachments')) {
                try {
                    foreach ($request->file('treatment_plan_attachments') as $file) {
                        $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                        // Store in private storage (storage/app/treatment_plan_attachments)
                        $filePath = $file->storeAs('treatment_plan_attachments', $fileName);
                        
                        $attachments[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => $fileName,
                            'path' => $filePath,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'uploaded_at' => now()->toDateTimeString(),
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to upload treatment plan attachments: ' . $e->getMessage(), [
                        'consultation_id' => $consultation->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue without attachments rather than failing completely
                }
            }
            
            // Update consultation with treatment plan
            // IMPORTANT: consultation_mode is set by the patient during booking and cannot be changed by doctors
            // It is intentionally NOT included in the update data to prevent modification
            $updateData = [
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
                'treatment_plan_attachments' => !empty($attachments) ? $attachments : null,
                'treatment_plan_created' => true,
                // NOTE: consultation_mode is NOT included - it is set by patient and cannot be changed
            ];
            
            // Only set these on first create, not on update
            if (!$isUpdate) {
                $updateData['treatment_plan_created_at'] = now();
                $updateData['status'] = 'completed';
                $updateData['consultation_completed_at'] = now();
            }
            
            $consultation->update($updateData);

            // Sync to patient medical history
            $historyService = app(\App\Services\PatientMedicalHistoryService::class);
            $historyService->syncConsultationToHistory($consultation);
            
            \Illuminate\Support\Facades\Log::info($isUpdate ? 'Treatment plan updated' : 'Treatment plan created', [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'is_update' => $isUpdate
            ]);

            // Send payment request email when treatment plan is created/updated and payment hasn't been made
            \Illuminate\Support\Facades\Log::info('Checking if payment request email should be sent', [
                'consultation_id' => $consultation->id,
                'is_paid' => $consultation->isPaid(),
                'payment_status' => $consultation->payment_status
            ]);
            
            if (!$consultation->isPaid()) {
                \Illuminate\Support\Facades\Log::info('Consultation is not paid, proceeding to send payment request email', [
                    'consultation_id' => $consultation->id
                ]);
                
                try {
                    // Determine recipient email: check multiple sources
                    $recipientEmail = null;
                    
                    // 1. First try consultation email field
                    if (!empty($consultation->email)) {
                        $recipientEmail = $consultation->email;
                        \Illuminate\Support\Facades\Log::info('Using consultation email for payment request', [
                            'consultation_id' => $consultation->id,
                            'email' => $recipientEmail
                        ]);
                    }
                    // 2. Try patient relationship email (prefer user email as source of truth)
                    elseif ($consultation->patient) {
                        $patientEmail = $consultation->patient->user?->email ?? $consultation->patient->email;
                        if (!empty($patientEmail)) {
                            $recipientEmail = $patientEmail;
                            \Illuminate\Support\Facades\Log::info('Using patient email for payment request', [
                                'consultation_id' => $consultation->id,
                                'patient_id' => $consultation->patient_id,
                                'email' => $recipientEmail
                            ]);
                        }
                    }
                    // 3. Try booking payer email (for multi-patient bookings)
                    elseif ($consultation->booking && !empty($consultation->booking->payer_email)) {
                        $recipientEmail = $consultation->booking->payer_email;
                        \Illuminate\Support\Facades\Log::info('Using payer email for payment request', [
                            'consultation_id' => $consultation->id,
                            'booking_id' => $consultation->booking_id,
                            'payer_email' => $recipientEmail
                        ]);
                    }
                    
                    if ($recipientEmail) {
                        \Illuminate\Support\Facades\Log::info('Attempting to send payment request email', [
                            'consultation_id' => $consultation->id,
                            'email' => $recipientEmail,
                            'mail_driver' => config('mail.default'),
                            'mail_host' => config('mail.mailers.smtp.host')
                        ]);
                        
                        \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\PaymentRequest($consultation));
                        
                        \Illuminate\Support\Facades\Log::info('Payment request email sent successfully after treatment plan ' . ($isUpdate ? 'update' : 'creation'), [
                            'consultation_id' => $consultation->id,
                            'reference' => $consultation->reference,
                            'email' => $recipientEmail,
                            'is_update' => $isUpdate
                        ]);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('No email available to send payment request', [
                            'consultation_id' => $consultation->id,
                            'reference' => $consultation->reference,
                            'consultation_email' => $consultation->email ?? 'null',
                            'patient_id' => $consultation->patient_id,
                            'patient_email' => $consultation->patient->email ?? 'null',
                            'booking_id' => $consultation->booking_id,
                            'payer_email' => $consultation->booking->payer_email ?? 'null'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send payment request email after treatment plan ' . ($isUpdate ? 'update' : 'creation'), [
                        'consultation_id' => $consultation->id,
                        'email' => $consultation->email ?? 'N/A',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    // Don't fail the request if email fails
                }
            } else {
                \Illuminate\Support\Facades\Log::info('Skipping payment request email - consultation already paid', [
                    'consultation_id' => $consultation->id,
                    'payment_status' => $consultation->payment_status,
                    'is_paid' => $consultation->isPaid()
                ]);
                
                // If payment is already made and treatment plan was just created, send treatment plan email
                if (!$isUpdate && $consultation->isPaid() && $consultation->hasTreatmentPlan()) {
                    try {
                        // Determine recipient email: check multiple sources
                        $recipientEmail = null;
                        
                        // 1. First try consultation email field
                        if (!empty($consultation->email)) {
                            $recipientEmail = $consultation->email;
                        }
                        // 2. Try patient relationship email
                        elseif ($consultation->patient && !empty($consultation->patient->email)) {
                            $recipientEmail = $consultation->patient->email;
                        }
                        // 3. Try booking payer email (for multi-patient bookings)
                        elseif ($consultation->booking && !empty($consultation->booking->payer_email)) {
                            $recipientEmail = $consultation->booking->payer_email;
                        }
                        
                        if ($recipientEmail) {
                            // Ensure treatment plan is unlocked
                            if (!$consultation->treatment_plan_unlocked) {
                                $consultation->unlockTreatmentPlan();
                            }
                            
                            \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\TreatmentPlanNotification($consultation));
                            
                            \Illuminate\Support\Facades\Log::info('Treatment plan email sent immediately after creation (payment already made)', [
                                'consultation_id' => $consultation->id,
                                'reference' => $consultation->reference,
                                'email' => $recipientEmail
                            ]);
                            
                            // Create in-app notification for patient
                            if ($consultation->patient_id) {
                                try {
                                    \App\Models\Notification::create([
                                        'user_type' => 'patient',
                                        'user_id' => $consultation->patient_id,
                                        'title' => 'Treatment Plan Ready',
                                        'message' => "Your treatment plan for consultation (Ref: {$consultation->reference}) is ready! You can view it now.",
                                        'type' => 'success',
                                        'action_url' => patient_url('consultations/' . $consultation->id),
                                        'data' => [
                                            'consultation_id' => $consultation->id,
                                            'consultation_reference' => $consultation->reference,
                                            'type' => 'treatment_plan_ready',
                                            'email' => $recipientEmail
                                        ]
                                    ]);
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Failed to create treatment plan ready notification', [
                                        'consultation_id' => $consultation->id,
                                        'patient_id' => $consultation->patient_id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            // Send Review Request email
                            try {
                                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\ReviewRequest($consultation));
                                \Illuminate\Support\Facades\Log::info('Review request email sent after treatment plan', [
                                    'consultation_id' => $consultation->id,
                                    'email' => $recipientEmail
                                ]);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Failed to send review request email', [
                                    'consultation_id' => $consultation->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        } else {
                            \Illuminate\Support\Facades\Log::warning('No email available to send treatment plan (payment already made)', [
                                'consultation_id' => $consultation->id,
                                'reference' => $consultation->reference
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send treatment plan email after creation (payment already made)', [
                            'consultation_id' => $consultation->id,
                            'email' => $consultation->email ?? 'N/A',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Don't fail the request if email fails
                    }
                }
            }
            
            \Illuminate\Support\Facades\Log::info('Treatment plan update completed successfully', [
                'consultation_id' => $consultation->id,
                'is_update' => $isUpdate
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $isUpdate 
                    ? 'Treatment plan updated successfully! Changes saved to patient medical history.' 
                    : 'Treatment plan created successfully! Patient will need to pay to access it.',
                'treatment_plan_created' => true,
                'is_update' => $isUpdate
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Validation failed for treatment plan update', [
                'consultation_id' => $id,
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save treatment plan', [
                'consultation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a treatment plan attachment
     * 
     * @param Request $request
     * @param int $id Consultation ID
     * @param string $file Stored filename
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTreatmentPlanAttachment(Request $request, $id, $file)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            // Prevent deletion if treatment plan is locked
            if ($consultation->treatment_plan_created) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete attachments from a finalized treatment plan.'
                ], 403);
            }
            
            $attachments = $consultation->treatment_plan_attachments ?? [];
            $attachmentIndex = null;
            $attachmentToDelete = null;
            
            // Find the attachment to delete
            foreach ($attachments as $index => $attachment) {
                $storedName = $attachment['stored_name'] ?? basename($attachment['path'] ?? '');
                if ($storedName === $file) {
                    $attachmentIndex = $index;
                    $attachmentToDelete = $attachment;
                    break;
                }
            }
            
            if ($attachmentIndex === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment not found.'
                ], 404);
            }
            
            // Delete the file from storage
            if (isset($attachmentToDelete['path'])) {
                try {
                    \Illuminate\Support\Facades\Storage::delete($attachmentToDelete['path']);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete treatment plan attachment file from storage', [
                        'consultation_id' => $consultation->id,
                        'file' => $file,
                        'path' => $attachmentToDelete['path'],
                        'error' => $e->getMessage()
                    ]);
                    // Continue with database update even if file deletion fails
                }
            }
            
            // Remove from attachments array
            unset($attachments[$attachmentIndex]);
            $attachments = array_values($attachments); // Re-index array
            
            // Update consultation
            $consultation->update([
                'treatment_plan_attachments' => !empty($attachments) ? $attachments : null
            ]);
            
            \Log::info('Treatment plan attachment deleted', [
                'consultation_id' => $consultation->id,
                'doctor_id' => $doctor->id,
                'file' => $file,
                'original_name' => $attachmentToDelete['original_name'] ?? 'N/A'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
                'remaining_attachments' => count($attachments)
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found or you do not have permission to modify it.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to delete treatment plan attachment', [
                'consultation_id' => $id,
                'file' => $file,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the attachment. Please try again.'
            ], 500);
        }
    }

    /**
     * Auto-save treatment plan (draft mode)
     */
    public function autoSaveTreatmentPlan(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            // Prevent editing if treatment plan has already been created/saved
            // Once saved, treatment plan becomes a permanent medical record and cannot be edited
            if ($consultation->treatment_plan_created) {
                return response()->json([
                    'success' => false,
                    'message' => 'Treatment plan cannot be edited once it has been saved. The treatment plan has been finalized and cannot be modified.'
                ], 403);
            }
            
            // Save whatever data is provided (no validation for drafts)
            // IMPORTANT: consultation_mode is set by the patient during booking and cannot be changed by doctors
            // It is intentionally NOT included in the allowed fields to prevent modification
            $data = $request->only([
                'presenting_complaint',
                'history_of_complaint',
                'past_medical_history',
                'family_history',
                'drug_history',
                'social_history',
                'diagnosis',
                'investigation',
                'treatment_plan',
                'prescribed_medications',
                'follow_up_instructions',
                'lifestyle_recommendations',
                'referrals',
                'next_appointment_date',
                'additional_notes',
                // NOTE: consultation_mode is NOT included - it is set by patient and cannot be changed
            ]);
            
            $consultation->update($data);
            
            // Sync medical information to patient medical history (even for drafts)
            // This ensures family history, social history, etc. are always up-to-date
            try {
                $historyService = app(\App\Services\PatientMedicalHistoryService::class);
                $historyService->syncConsultationToHistory($consultation);
            } catch (\Exception $e) {
                // Log but don't fail the auto-save if history sync fails
                \Illuminate\Support\Facades\Log::warning('Failed to sync medical history during auto-save', [
                    'consultation_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Draft saved',
                'timestamp' => now()->format('H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to auto-save treatment plan', [
                'consultation_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed'
            ], 500);
        }
    }

    /**
     * Get patient's previous medical history for pre-filling
     */
    public function getPatientHistory($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            
            $consultation = Consultation::where('id', $id)
                                       ->where('doctor_id', $doctor->id)
                                       ->firstOrFail();
            
            $historyService = app(\App\Services\PatientMedicalHistoryService::class);
            $previousHistory = $historyService->getPreviousHistoryForConsultation($consultation);
            
            return response()->json([
                'success' => true,
                'has_history' => $previousHistory !== null,
                'history' => $previousHistory ? [
                    'past_medical_history' => $previousHistory->past_medical_history,
                    'family_history' => $previousHistory->family_history,
                    'drug_history' => $previousHistory->drug_history,
                    'social_history' => $previousHistory->social_history,
                    'last_consultation_date' => $previousHistory->consultation_date->format('Y-m-d'),
                ] : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load patient history'
            ], 500);
        }
    }

    /**
     * Display bank account management page
     */
    public function bankAccounts()
    {
        $doctor = Auth::guard('doctor')->user();
        $bankAccounts = $doctor->bankAccounts()->latest()->get();
        $defaultAccount = $doctor->defaultBankAccount;
        $banks = \App\Models\Bank::getActiveBanks();

        return view('doctor.bank-accounts', compact('bankAccounts', 'defaultAccount', 'banks'));
    }

    /**
     * Verify bank account with KoraPay (AJAX endpoint)
     */
    public function verifyBankAccount(Request $request)
    {
        try {
            $validated = $request->validate([
                'bank_code' => 'required|string|max:10',
                'account_number' => 'required|string|max:20',
            ]);

            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $verification = $payoutService->verifyBankAccount(
                $validated['bank_code'],
                $validated['account_number']
            );

            if ($verification['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $verification['data'],
                    'message' => $verification['message'] ?? 'Bank account verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $verification['message'] ?? 'Bank account verification failed'
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages = array_merge($errorMessages, $messages);
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages)
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Bank account verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new bank account
     */
    public function storeBankAccount(Request $request)
    {
        try {
            $doctor = Auth::guard('doctor')->user();

            $validated = $request->validate([
                'bank_id' => 'required|exists:banks,id',
                'account_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'account_type' => 'nullable|string|max:50',
                'swift_code' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            // Get bank details
            $bank = \App\Models\Bank::findOrFail($validated['bank_id']);

            // Check if this bank account already exists for this doctor (including soft-deleted)
            $existingAccount = \App\Models\DoctorBankAccount::withTrashed()
                ->where('doctor_id', $doctor->id)
                ->where('account_number', $validated['account_number'])
                ->where('bank_code', $bank->code)
                ->first();

            if ($existingAccount) {
                if ($existingAccount->trashed()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'This bank account was previously added and deleted. Please contact support if you need to restore it.');
                }
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This bank account has already been added to your account. Please use a different account number or edit the existing account.');
            }

            // Verify account with KoraPay before saving
            $payoutService = app(\App\Services\KoraPayPayoutService::class);
            $verification = $payoutService->verifyBankAccount(
                $bank->code,
                $validated['account_number']
            );

            if (!$verification['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Bank account verification failed: ' . $verification['message'] . '. Please check your account number and try again.');
            }

            // Extract account name from verification if available
            $verifiedAccountName = $verification['data']['account_name'] ?? $validated['account_name'];

            // Check if this is the first bank account (excluding soft-deleted)
            $isFirstAccount = !$doctor->bankAccounts()->exists();
            
            // Use database transaction to ensure atomicity
            \DB::beginTransaction();
            try {
                // Always unset any existing default accounts first (including soft-deleted)
                // Since we've removed the problematic unique constraint, we can safely update all defaults
                \DB::table('doctor_bank_accounts')
                    ->where('doctor_id', $doctor->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                $bankAccount = $doctor->bankAccounts()->create([
                    'bank_name' => $bank->name,
                    'bank_code' => $bank->code,
                    'account_name' => $verifiedAccountName,
                    'account_number' => $validated['account_number'],
                    'account_type' => $validated['account_type'] ?? null,
                    'swift_code' => $validated['swift_code'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'is_default' => $isFirstAccount,
                    'is_verified' => true, // Auto-verified since KoraPay verified it
                    'verified_at' => now(),
                ]);

                \DB::commit();
            } catch (QueryException $e) {
                \DB::rollBack();
                
                // Handle specific database constraint violations
                if ($e->getCode() === '23000') {
                    $errorMessage = $e->getMessage();
                    
                    // Check for unique constraint violations
                    if (str_contains($errorMessage, 'unique_default_bank_per_doctor')) {
                        \Log::error('Bank account unique constraint violation', [
                            'doctor_id' => $doctor->id,
                            'error' => $errorMessage
                        ]);
                        
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Unable to add bank account. There was a conflict with your existing accounts. Please try again or contact support if the issue persists.');
                    }
                    
                    // Generic duplicate entry error
                    if (str_contains($errorMessage, 'Duplicate entry')) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'This bank account already exists in your account. Please use a different account number or edit the existing account.');
                    }
                }
                
                // Re-throw if it's not a constraint violation we can handle
                throw $e;
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

            return redirect()->back()->with('success', 'Bank account added and verified successfully! You can now receive payments.');

        } catch (QueryException $e) {
            \Log::error('Failed to add bank account - Database error', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            // Handle database constraint violations with friendly messages
            if ($e->getCode() === '23000') {
                $errorMessage = $e->getMessage();
                
                if (str_contains($errorMessage, 'unique_default_bank_per_doctor')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Unable to add bank account. There was a conflict with your existing accounts. Please try again or contact support if the issue persists.');
                }
                
                if (str_contains($errorMessage, 'Duplicate entry')) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'This bank account already exists in your account. Please use a different account number or edit the existing account.');
                }
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to add bank account at this time. Please try again later or contact support if the problem continues.');
                
        } catch (\Exception $e) {
            \Log::error('Failed to add bank account', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to add bank account at this time. Please try again later or contact support if the problem continues.');
        }
    }

    /**
     * Update bank account
     */
    public function updateBankAccount(Request $request, $id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();

            $bankAccount = $doctor->bankAccounts()->findOrFail($id);

            $validated = $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'account_type' => 'nullable|string|max:50',
                'bank_code' => 'nullable|string|max:10',
                'swift_code' => 'nullable|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            $bankAccount->update($validated);

            return redirect()->back()->with('success', 'Bank account updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to update bank account', [
                'doctor_id' => Auth::guard('doctor')->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to update bank account: ' . $e->getMessage());
        }
    }

    /**
     * Set bank account as default
     */
    public function setDefaultBankAccount($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            $bankAccount = $doctor->bankAccounts()->findOrFail($id);
            
            $bankAccount->setAsDefault();

            return redirect()->back()->with('success', 'Default bank account updated!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to set default account: ' . $e->getMessage());
        }
    }

    /**
     * Delete bank account
     */
    public function deleteBankAccount($id)
    {
        try {
            $doctor = Auth::guard('doctor')->user();
            $bankAccount = $doctor->bankAccounts()->findOrFail($id);

            // Don't allow deletion of default account if there are others
            if ($bankAccount->is_default && $doctor->bankAccounts()->count() > 1) {
                return redirect()->back()->with('error', 'Cannot delete default account. Set another account as default first.');
            }

            $bankAccount->delete();

            return redirect()->back()->with('success', 'Bank account deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete bank account: ' . $e->getMessage());
        }
    }

    /**
     * Display payment history
     */
    public function paymentHistory()
    {
        $doctor = Auth::guard('doctor')->user();
        
        $payments = $doctor->payments()
            ->with(['bankAccount', 'paidBy'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_paid' => $doctor->payments()->where('status', 'completed')->sum('doctor_amount'),
            'pending_amount' => $doctor->pending_earnings,
            'paid_consultations' => $doctor->paid_consultations_count,
            'unpaid_consultations' => $doctor->unpaid_consultations_count,
        ];

        return view('doctor.payment-history', compact('payments', 'stats'));
    }

    /**
     * Display doctor profile page
     */
    public function profile()
    {
        $doctor = Auth::guard('doctor')->user();
        return view('doctor.profile', compact('doctor'));
    }

    /**
     * Update doctor profile
     */
    public function updateProfile(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:doctors,email,' . $doctor->id,
            'gender' => 'nullable|in:Male,Female,Other,male,female,other',
            'specialization' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'languages' => 'nullable|string|max:255',
            'place_of_work' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'insurance_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'can_provide_second_opinion' => 'nullable|boolean',
            'is_international' => 'nullable|boolean',
            'country_of_practice' => 'nullable|required_if:is_international,1|string|max:255',
        ]);
        
        // Convert checkbox values to boolean
        $validated['can_provide_second_opinion'] = $request->has('can_provide_second_opinion');
        $validated['is_international'] = $request->has('is_international');

        // Auto-generate full name
        $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            try {
                // Delete old photo if exists
                if ($doctor->photo && Storage::disk('public')->exists($doctor->photo)) {
                    Storage::disk('public')->delete($doctor->photo);
                }

                // Store new photo
                $photo = $request->file('photo');
                $fileName = Str::slug($doctor->name) . '-' . time() . '.' . $photo->getClientOriginalExtension();
                
                // Use putFileAs which properly handles the file stream
                // This stores the file at storage/app/public/doctors/filename.jpg
                // and returns the path 'doctors/filename.jpg'
                $path = Storage::disk('public')->putFileAs('doctors', $photo, $fileName);
                
                // Verify the file was stored
                if ($path && Storage::disk('public')->exists($path)) {
                    $validated['photo'] = $path;
                    
                    \Log::info('Photo uploaded successfully', [
                        'doctor_id' => $doctor->id,
                        'photo_path' => $path,
                        'url' => Storage::url($path)
                    ]);
                } else {
                    \Log::error('Photo upload failed - file not found after storage', [
                        'doctor_id' => $doctor->id,
                        'photo_name' => $photoName,
                        'path' => $path
                    ]);
                    
                    return redirect()->back()->with('error', 'Failed to upload photo. Please try again.');
                }
            } catch (\Exception $e) {
                \Log::error('Photo upload exception', [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'Failed to upload photo: ' . $e->getMessage());
            }
        }

        // Handle insurance document upload
        if ($request->hasFile('insurance_document')) {
            try {
                // Delete old document if exists
                if ($doctor->insurance_document && Storage::disk('public')->exists($doctor->insurance_document)) {
                    Storage::disk('public')->delete($doctor->insurance_document);
                }

                $file = $request->file('insurance_document');
                $fileName = 'insurance-' . Str::slug($doctor->name) . '-' . time() . '.' . $file->getClientOriginalExtension();
                
                // Store in public disk under 'insurance_documents' folder
                $path = Storage::disk('public')->putFileAs('insurance_documents', $file, $fileName);
                
                if ($path) {
                    $validated['insurance_document'] = $path;
                }
            } catch (\Exception $e) {
                \Log::error('Insurance document upload exception', [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage()
                ]);
                // Log error but continue with other updates
            }
        }

        // Update doctor
        $doctor->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Display availability settings page
     */
    public function availability()
    {
        $doctor = Auth::guard('doctor')->user();
        
        // Parse availability schedule if exists
        $schedule = $doctor->availability_schedule ?? [];
        $defaultSchedule = [
            'monday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'tuesday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'wednesday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'thursday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'friday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'saturday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
            'sunday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
        ];
        
        // Merge with existing schedule
        $schedule = array_merge($defaultSchedule, $schedule);
        
        return view('doctor.availability', compact('doctor', 'schedule'));
    }

    /**
     * Update availability settings
     */
    public function updateAvailability(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();

        // SECURITY: Prevent doctors from setting themselves to available if they are auto-unavailable due to penalties
        // Only admins can reset penalties and set doctors back to available
        if ($doctor->is_auto_unavailable) {
            $requestedAvailable = $request->has('is_available') ? true : false;
            
            // If doctor is trying to set themselves to available, reject it
            if ($requestedAvailable) {
                return redirect()->back()->with('error', 'You cannot set yourself to available. You have been automatically set to unavailable due to missed consultations. Please contact an administrator to resolve this issue.');
            }
            
            // Allow doctors to update schedule even when auto-unavailable, but keep them unavailable
            // Process availability schedule
            $schedule = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            foreach ($days as $day) {
                $enabled = $request->has("availability_schedule.{$day}.enabled");
                $start = $request->input("availability_schedule.{$day}.start", '09:00');
                $end = $request->input("availability_schedule.{$day}.end", '17:00');
                
                // Validate time format
                if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $start)) {
                    $start = '09:00';
                }
                if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $end)) {
                    $end = '17:00';
                }
                
                // Ensure end time is after start time
                if (strtotime($end) <= strtotime($start)) {
                    $end = date('H:i', strtotime($start . ' +8 hours'));
                }
                
                $schedule[$day] = [
                    'enabled' => $enabled,
                    'start' => $start,
                    'end' => $end,
                ];
            }

            // Update only schedule, keep is_available as false
            $doctor->update([
                'availability_schedule' => $schedule,
                // is_available remains false - cannot be changed by doctor
            ]);

            return redirect()->back()->with('success', 'Schedule updated. Note: Your availability status cannot be changed due to missed consultations. Please contact an administrator.');
        }

        // Normal flow for doctors who are not auto-unavailable
        // Handle is_available checkbox
        $isAvailable = $request->has('is_available') ? true : false;

        // Process availability schedule
        $schedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        foreach ($days as $day) {
            $enabled = $request->has("availability_schedule.{$day}.enabled");
            $start = $request->input("availability_schedule.{$day}.start", '00:00');
            $end = $request->input("availability_schedule.{$day}.end", '23:59');
            
            // Validate time format (24-hour format: 00:00 to 23:59)
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $start)) {
                $start = '00:00';
            }
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $end)) {
                $end = '23:59';
            }
            
            // Handle end time that wraps to next day (e.g., 22:00 to 02:00)
            // For now, ensure end time is after start time within same day
            // If end is before start, it means availability spans midnight - we'll allow this
            $startTime = strtotime($start);
            $endTime = strtotime($end);
            
            // If end time is before start time, it means availability spans midnight
            // We'll keep it as is - the booking system should handle this
            // But for validation, we'll ensure at least 1 hour difference
            if ($endTime <= $startTime && $endTime < strtotime('23:59')) {
                // Same day, end must be after start
                $endTime = $startTime + 3600; // Add 1 hour minimum
                $end = date('H:i', $endTime);
            }
            
            $schedule[$day] = [
                'enabled' => $enabled,
                'start' => $start,
                'end' => $end,
            ];
        }

        // Update doctor
        $doctor->update([
            'is_available' => $isAvailable,
            'availability_schedule' => $schedule,
        ]);

        return redirect()->back()->with('success', 'Availability settings updated successfully!');
    }

    /**
     * Refer patient to another doctor
     */
    public function referPatient(Request $request, $id)
    {
        $doctor = Auth::guard('doctor')->user();
        
        $consultation = Consultation::where('id', $id)
                                   ->where('doctor_id', $doctor->id)
                                   ->with(['patient', 'doctor'])
                                   ->firstOrFail();

        $validated = $request->validate([
            'referred_to_doctor_id' => 'required|exists:doctors,id',
            'reason' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Check if trying to refer to self
        if ($validated['referred_to_doctor_id'] == $doctor->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot refer a patient to yourself.'
            ], 400);
        }

        // Check if already referred
        if ($consultation->hasReferral()) {
            return response()->json([
                'success' => false,
                'message' => 'This consultation has already been referred to another doctor.'
            ], 400);
        }

        try {
            \DB::beginTransaction();

            // Get referred to doctor
            $referredToDoctor = \App\Models\Doctor::findOrFail($validated['referred_to_doctor_id']);

            // Generate reference for new consultation
            $newReference = 'CONS-' . strtoupper(\Illuminate\Support\Str::random(8));

            // Create new consultation for referred doctor
            $newConsultation = Consultation::create([
                'reference' => $newReference,
                'patient_id' => $consultation->patient_id,
                'first_name' => $consultation->first_name,
                'last_name' => $consultation->last_name,
                'email' => $consultation->email,
                'mobile' => $consultation->mobile,
                'age' => $consultation->age,
                'gender' => $consultation->gender,
                'problem' => $consultation->problem . ' [Referred from Dr. ' . $doctor->name . ' - Ref: ' . $consultation->reference . ']',
                'medical_documents' => $consultation->medical_documents,
                'severity' => $consultation->severity,
                'emergency_symptoms' => $consultation->emergency_symptoms,
                'consult_mode' => $consultation->consult_mode,
                'doctor_id' => $referredToDoctor->id,
                'status' => 'pending',
                'payment_status' => $consultation->payment_status, // Inherit payment status
                'payment_id' => $consultation->payment_id, // Link to same payment if paid
                // Copy medical history fields
                'presenting_complaint' => $consultation->presenting_complaint,
                'history_of_complaint' => $consultation->history_of_complaint,
                'past_medical_history' => $consultation->past_medical_history,
                'family_history' => $consultation->family_history,
                'drug_history' => $consultation->drug_history,
                'social_history' => $consultation->social_history,
                'diagnosis' => $consultation->diagnosis,
                'investigation' => $consultation->investigation,
                'doctor_notes' => "Referred from Dr. {$doctor->name} (Ref: {$consultation->reference}). " . ($validated['notes'] ?? ''),
            ]);

            // Create referral record
            $referral = \App\Models\Referral::create([
                'consultation_id' => $consultation->id,
                'referring_doctor_id' => $doctor->id,
                'referred_to_doctor_id' => $referredToDoctor->id,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'new_consultation_id' => $newConsultation->id,
                'status' => 'pending',
            ]);

            \DB::commit();

            // Send notifications
            $this->sendReferralNotifications($referral, $consultation, $newConsultation, $doctor, $referredToDoctor);

            \Log::info('Patient referred successfully', [
                'referral_id' => $referral->id,
                'original_consultation_id' => $consultation->id,
                'new_consultation_id' => $newConsultation->id,
                'referring_doctor_id' => $doctor->id,
                'referred_to_doctor_id' => $referredToDoctor->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient has been successfully referred to Dr. ' . $referredToDoctor->name . '. A new consultation has been created.',
                'referral' => $referral,
                'new_consultation' => $newConsultation,
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to refer patient', [
                'consultation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to refer patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notifications for referral
     */
    private function sendReferralNotifications($referral, $originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor)
    {
        // Prepare common notification data
        $notificationData = [
            'referral_id' => $referral->id,
            'original_consultation_id' => $originalConsultation->id,
            'new_consultation_id' => $newConsultation->id,
            'original_consultation_reference' => $originalConsultation->reference,
            'new_consultation_reference' => $newConsultation->reference,
            'referring_doctor_name' => $referringDoctor->name ?? $referringDoctor->full_name,
            'referred_to_doctor_name' => $referredToDoctor->name ?? $referredToDoctor->full_name,
            'referred_to_doctor_specialization' => $referredToDoctor->specialization,
            'patient_name' => $originalConsultation->full_name,
            'patient_age' => $originalConsultation->age,
            'patient_gender' => ucfirst($originalConsultation->gender ?? ''),
            'referral_reason' => $referral->reason,
            'referral_notes' => $referral->notes,
            'action_url_patient' => patient_url('consultations/' . $newConsultation->id),
            'action_url_doctor' => doctor_url('consultations/' . $newConsultation->id),
        ];

        // 1. In-app notification for referred doctor
        try {
            \App\Models\Notification::create([
                'user_type' => 'doctor',
                'user_id' => $referredToDoctor->id,
                'title' => 'New Patient Referral',
                'message' => "Dr. {$referringDoctor->name} has referred a patient (Ref: {$originalConsultation->reference}) to you. A new consultation has been created.",
                'type' => 'info',
                'action_url' => doctor_url('consultations/' . $newConsultation->id),
                'data' => array_merge($notificationData, ['type' => 'patient_referral']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create referral notification for doctor', ['error' => $e->getMessage()]);
        }

        // 2. In-app notification for patient
        if ($newConsultation->patient_id) {
            try {
                \App\Models\Notification::create([
                    'user_type' => 'patient',
                    'user_id' => $newConsultation->patient_id,
                    'title' => 'Consultation Referred',
                    'message' => "Dr. {$referringDoctor->name} has referred you to Dr. {$referredToDoctor->name}. A new consultation has been created for you.",
                    'type' => 'info',
                    'action_url' => patient_url('consultations/' . $newConsultation->id),
                    'data' => array_merge($notificationData, ['type' => 'consultation_referred']),
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create referral notification for patient', ['error' => $e->getMessage()]);
            }
        }

        // 3. Email notification to referred doctor
        try {
            if ($referredToDoctor->email) {
                \Mail::to($referredToDoctor->email)->send(new \App\Mail\ReferralNotification($notificationData, 'doctor'));
                \Log::info('Referral email sent to doctor', [
                    'doctor_id' => $referredToDoctor->id,
                    'consultation_id' => $newConsultation->id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to send referral email to doctor: " . $e->getMessage());
        }

        // 4. Email notification to patient
        try {
            $patientEmail = $originalConsultation->email ?: ($originalConsultation->patient->email ?? null);
            if ($patientEmail) {
                \Mail::to($patientEmail)->send(new \App\Mail\ReferralNotification($notificationData, 'patient'));
                \Log::info('Referral email sent to patient', [
                    'patient_id' => $newConsultation->patient_id,
                    'consultation_id' => $newConsultation->id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to send referral email to patient: " . $e->getMessage());
        }

        // 5. SMS notification to referred doctor
        try {
            if ($referredToDoctor->phone) {
                $smsNotification = new \App\Notifications\ConsultationSmsNotification();
                $smsNotification->sendReferralNotification($originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor, 'doctor');
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to send referral SMS to doctor: " . $e->getMessage());
        }

        // 6. SMS notification to patient
        try {
            if ($originalConsultation->mobile) {
                $smsNotification = new \App\Notifications\ConsultationSmsNotification();
                $smsNotification->sendReferralNotification($originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor, 'patient');
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to send referral SMS to patient: " . $e->getMessage());
        }

        // 7. WhatsApp notification to referred doctor
        if (config('services.termii.whatsapp_enabled')) {
            try {
                if ($referredToDoctor->phone) {
                    $whatsappNotification = new \App\Notifications\ConsultationWhatsAppNotification();
                    $whatsappNotification->sendReferralNotification($originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor, 'doctor');
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send referral WhatsApp to doctor: " . $e->getMessage());
            }
        }

        // 8. WhatsApp notification to patient
        if (config('services.termii.whatsapp_enabled')) {
            try {
                if ($originalConsultation->mobile) {
                    $whatsappNotification = new \App\Notifications\ConsultationWhatsAppNotification();
                    $whatsappNotification->sendReferralNotification($originalConsultation, $newConsultation, $referringDoctor, $referredToDoctor, 'patient');
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send referral WhatsApp to patient: " . $e->getMessage());
            }
        }
    }
}

