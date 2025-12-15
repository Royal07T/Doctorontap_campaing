<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Mail\PaymentRequest;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Patient;
use App\Notifications\ConsultationSmsNotification;

class ConsultationController extends Controller
{
    /**
     * Display the landing page with consultation form
     */
    public function index()
    {
        // Get all approved doctors for multi-patient booking
        // Available doctors are shown first, then unavailable ones
        $doctors = Doctor::approved()
            ->orderByRaw('CASE WHEN is_available = 1 THEN 0 ELSE 1 END')
            ->orderBy('order', 'asc')
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->with('reviews')
            ->get();
        
        return view('consultation.index', compact('doctors'));
    }

    /**
     * Handle form submission
     */
    public function store(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
            // Personal Details
            'first_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'last_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:1|max:120',
            'mobile' => ['required', 'string', 'regex:/^(\+234|0)[0-9]{10}$/'],
            'email' => 'required|email:rfc|max:255',
            
            // Triage Block
            'problem' => 'required|string|min:10|max:500',
            'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // Max 5MB per file
            'severity' => 'required|in:mild,moderate,severe',
            'emergency_symptoms' => 'nullable|array',
            
            // Doctor's Choice
            'doctor' => 'nullable|string|max:255',
            'consult_mode' => 'required|in:voice,video,chat',
            
            // Consent
            'informed_consent' => 'required|accepted',
            'data_privacy' => 'required|accepted',
        ], [
            // Custom error messages
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
            'gender.required' => 'Please select your gender.',
            'gender.in' => 'Gender must be either Male or Female.',
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a valid number.',
            'age.min' => 'Age must be at least 1.',
            'age.max' => 'Age cannot exceed 120.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Please enter a valid Nigerian phone number (e.g., +2348012345678 or 08012345678).',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'problem.required' => 'Please describe your medical problem.',
            'problem.min' => 'Problem description must be at least 10 characters.',
            'severity.required' => 'Please indicate the severity of your condition.',
            'consult_mode.required' => 'Please select a consultation mode.',
            'informed_consent.required' => 'You must accept the informed consent.',
            'informed_consent.accepted' => 'You must accept the informed consent to proceed.',
            'data_privacy.required' => 'You must accept the data privacy policy.',
            'data_privacy.accepted' => 'You must accept the data privacy policy to proceed.',
        ]);

        // Generate unique consultation reference
        $reference = 'CONSULT-' . time() . '-' . Str::random(6);

        // Handle medical document uploads - HIPAA: Store in private storage
        $uploadedDocuments = [];
        if ($request->hasFile('medical_documents')) {
            try {
                foreach ($request->file('medical_documents') as $file) {
                    $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    // Store in private storage (storage/app/private/medical_documents)
                    $filePath = $file->storeAs('medical_documents', $fileName);
                    
                    $uploadedDocuments[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_name' => $fileName,
                        'path' => $filePath,
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Failed to upload medical documents: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue without documents rather than failing completely
            }
        }

        // Get doctor details from ID if a doctor was selected
        $doctorEmail = null;
        $doctorId = null;
        
        if (!empty($validated['doctor'])) {
            $doctor = Doctor::find($validated['doctor']);
            if ($doctor) {
                $validated['doctor_name'] = $doctor->name;
                $validated['doctor_id'] = $validated['doctor'];
                $validated['doctor'] = $doctor->name; // Replace ID with name for emails
                $validated['doctor_fee'] = $doctor->consultation_fee;
                $doctorEmail = $doctor->email;
                $doctorId = $doctor->id;
            }
        }

        // Create or update patient record with all information
        // Handle soft-deleted patients to avoid unique constraint violations
        try {
            // First, check if a soft-deleted patient exists with this email
            $patient = Patient::withTrashed()->where('email', $validated['email'])->first();
            
            if ($patient) {
                // Patient exists (soft-deleted or not)
                if ($patient->trashed()) {
                    // Restore the soft-deleted patient
                    $patient->restore();
                    \Log::info('Restored soft-deleted patient', [
                        'patient_id' => $patient->id,
                        'email' => $validated['email']
                    ]);
                }
                
                // Update the patient record
                $patient->update([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'phone' => $validated['mobile'],
                    'gender' => $validated['gender'],
                    'age' => $validated['age'],
                ]);
            } else {
                // Create new patient
                $patient = Patient::create([
                    'email' => $validated['email'],
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'phone' => $validated['mobile'],
                    'gender' => $validated['gender'],
                    'age' => $validated['age'],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create/update patient record: ' . $e->getMessage(), [
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to process your request. Please try again later or contact support.'
            ], 500);
        }

        // Create consultation record
        try {
            $consultation = Consultation::create([
                'reference' => $reference,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'problem' => $validated['problem'],
                'medical_documents' => !empty($uploadedDocuments) ? $uploadedDocuments : null,
                'severity' => $validated['severity'],
                'emergency_symptoms' => $validated['emergency_symptoms'] ?? null,
                'consult_mode' => $validated['consult_mode'],
                'doctor_id' => $doctorId,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create consultation record: ' . $e->getMessage(), [
                'reference' => $reference,
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to create consultation. Please try again later or contact support.'
            ], 500);
        }

        // Update patient aggregates
        try {
            $patient->increment('consultations_count');
            $patient->last_consultation_at = now();
            $patient->save();
        } catch (\Exception $e) {
            \Log::warning('Failed to update patient aggregates: ' . $e->getMessage());
            // Non-critical, continue anyway
        }

        // Add reference and documents to validated data for emails
        $validated['consultation_reference'] = $reference;
        $validated['has_documents'] = !empty($uploadedDocuments);
        $validated['documents_count'] = count($uploadedDocuments);

        // Send emails immediately (synchronous sending)
        // Emails are non-critical - we continue even if they fail
        
        // Count emails to be sent
        $emailsSent = 0;
        // Use ADMIN_EMAIL from config (reads from env)
        $adminEmail = config('mail.admin_email');
        
        try {
            // Send confirmation email to the patient
            Mail::to($validated['email'])->send(new ConsultationConfirmation($validated));
            $emailsSent++;
            \Log::info('Patient confirmation email sent successfully', [
                'consultation_reference' => $reference,
                'patient_email' => $validated['email']
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to send patient confirmation email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'patient_email' => $validated['email']
            ]);
        }

        // Send SMS confirmation to the patient
        try {
            $smsNotification = new ConsultationSmsNotification();
            $smsResult = $smsNotification->sendConsultationConfirmation($validated);
            
            if ($smsResult['success']) {
                \Log::info('Patient confirmation SMS sent successfully', [
                    'consultation_reference' => $reference,
                    'patient_mobile' => $validated['mobile']
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send patient confirmation SMS: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'patient_mobile' => $validated['mobile'] ?? 'N/A'
            ]);
        }

        try {
            // Send alert email to admin
            Mail::to($adminEmail)->send(new ConsultationAdminAlert($validated));
            $emailsSent++;
            \Log::info('Admin alert email sent successfully', [
                'consultation_reference' => $reference,
                'admin_email' => $adminEmail
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to send admin alert email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'admin_email' => $adminEmail
            ]);
        }

        try {
            // Send notification email to the assigned doctor
            if ($doctorEmail) {
                Mail::to($doctorEmail)->send(new ConsultationDoctorNotification($validated));
                $emailsSent++;
                \Log::info('Doctor notification email sent successfully', [
                    'consultation_reference' => $reference,
                    'doctor_email' => $doctorEmail
                ]);
            } else {
                \Log::warning('No doctor email available - skipping doctor notification', [
                    'consultation_reference' => $reference
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send doctor notification email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'doctor_email' => $doctorEmail ?? 'N/A'
            ]);
        }

        // Send SMS notification to the assigned doctor
        if ($doctorId) {
            try {
                $smsNotification = new ConsultationSmsNotification();
                $assignedDoctor = Doctor::find($doctorId);
                
                if ($assignedDoctor) {
                    $smsResult = $smsNotification->sendDoctorNewConsultation($assignedDoctor, $validated);
                    
                    if ($smsResult['success']) {
                        \Log::info('Doctor notification SMS sent successfully', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'doctor_phone' => $assignedDoctor->phone
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send doctor notification SMS: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_id' => $doctorId
                ]);
            }
        }

        // ============================================
        // SEND WHATSAPP NOTIFICATIONS (More Reliable!)
        // ============================================
        
        // Send WhatsApp notification to PATIENT (if enabled)
        if (config('services.termii.whatsapp_enabled')) {
            try {
                $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                
                $patientResult = $whatsapp->sendConsultationConfirmationTemplate(
                    $validated,
                    'patient_booking_confirmation' // Template ID from Termii dashboard
                );
                
                if ($patientResult['success']) {
                    \Log::info('Patient WhatsApp notification sent successfully', [
                        'consultation_reference' => $reference,
                        'patient_phone' => $validated['mobile']
                    ]);
                } else {
                    \Log::warning('Patient WhatsApp notification failed', [
                        'consultation_reference' => $reference,
                        'error' => $patientResult['message'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Patient WhatsApp notification error: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'phone' => $validated['mobile'] ?? 'N/A'
                ]);
            }
        }
        
        // Send WhatsApp notification to DOCTOR (if doctor assigned and WhatsApp enabled)
        if (config('services.termii.whatsapp_enabled') && $doctorId) {
            try {
                $assignedDoctor = Doctor::find($doctorId);
                
                if ($assignedDoctor && $assignedDoctor->phone) {
                    $whatsapp = new \App\Notifications\ConsultationWhatsAppNotification();
                    
                    $doctorResult = $whatsapp->sendDoctorNewConsultationTemplate(
                        $assignedDoctor,
                        $validated,
                        'doctor_new_consultation' // Template ID from Termii dashboard
                    );
                    
                    if ($doctorResult['success']) {
                        \Log::info('Doctor WhatsApp notification sent successfully', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'doctor_phone' => $assignedDoctor->phone
                        ]);
                    } else {
                        \Log::warning('Doctor WhatsApp notification failed', [
                            'consultation_reference' => $reference,
                            'doctor_id' => $doctorId,
                            'error' => $doctorResult['message'] ?? 'Unknown error'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Doctor WhatsApp notification error: ' . $e->getMessage(), [
                    'consultation_reference' => $reference,
                    'doctor_id' => $doctorId
                ]);
            }
        }
        
        \Log::info('Consultation booking completed - emails sent', [
            'consultation_reference' => $reference,
            'total_emails_sent' => $emailsSent,
            'patient_email' => $validated['email'],
            'admin_email' => $adminEmail,
            'doctor_email' => $doctorEmail ?? 'N/A'
        ]);

        // Return success response immediately (NO PAYMENT REQUIRED UPFRONT)
        // Emails will be sent in the background via queue
        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your consultation has been booked successfully. We will contact you shortly via WhatsApp to schedule your consultation. Remember: You only pay AFTER your consultation is complete.',
            'consultation_reference' => $reference,
        ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they're handled by Laravel
            throw $e;
        } catch (\Exception $e) {
            // Catch-all for any unexpected errors
            \Log::error('Unexpected error in consultation submission: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['medical_documents', '_token'])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again or contact support if the problem persists.'
            ], 500);
        }
    }

    /**
     * Send payment request email after consultation is completed
     */
    public function sendPaymentRequest($consultationId)
    {
        $consultation = Consultation::with('doctor')->findOrFail($consultationId);

        // Check if consultation is completed and payment not already sent
        if (!$consultation->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation must be completed before sending payment request'
            ], 400);
        }

        if ($consultation->payment_request_sent) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request already sent'
            ], 400);
        }

        if (!$consultation->requiresPayment()) {
            return response()->json([
                'success' => false,
                'message' => 'No payment required for this consultation'
            ], 400);
        }

        // Send payment request email immediately
        try {
            Mail::to($consultation->email)->send(new PaymentRequest($consultation));

            // Update consultation
            $consultation->update([
                'payment_request_sent' => true,
                'payment_request_sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment request email sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send payment request email: ' . $e->getMessage(), [
                'consultation_id' => $consultation->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send payment request. Please try again.'
            ], 500);
        }
    }

    /**
     * View treatment plan access page
     */
    public function viewTreatmentPlan($reference)
    {
        $consultation = Consultation::with('doctor')->where('reference', $reference)->firstOrFail();
        
        // Check if treatment plan exists
        if (!$consultation->hasTreatmentPlan()) {
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'No treatment plan has been created for this consultation yet.',
                'showPaymentButton' => false
            ]);
        }
        
        // STRICT PAYMENT GATING: Check if payment is required and if it's been made
        if ($consultation->requiresPaymentForTreatmentPlan()) {
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'Payment is required to access your treatment plan. Please complete payment first.',
                'showPaymentButton' => true,
                'paymentRequired' => true
            ]);
        }
        
        // If payment is not required or has been made, show access button
        return view('consultation.treatment-plan-access', [
            'consultation' => $consultation,
            'showAccessButton' => true
        ]);
    }

    /**
     * Access treatment plan (after payment verification)
     * 
     * SECURITY: This method enforces strict payment verification before granting access
     */
    public function accessTreatmentPlan(Request $request, $reference)
    {
        $consultation = Consultation::with(['doctor', 'payment'])->where('reference', $reference)->firstOrFail();
        
        Log::info('Treatment plan access attempt', [
            'consultation_id' => $consultation->id,
            'consultation_ref' => $reference,
            'payment_status' => $consultation->payment_status,
            'treatment_plan_unlocked' => $consultation->treatment_plan_unlocked,
            'ip_address' => $request->ip()
        ]);
        
        // Check if treatment plan exists
        if (!$consultation->hasTreatmentPlan()) {
            Log::warning('Treatment plan access denied: plan not created', [
                'consultation_id' => $consultation->id,
                'consultation_ref' => $reference
            ]);
            
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'No treatment plan has been created for this consultation yet.',
                'showPaymentButton' => false
            ]);
        }
        
        // STRICT PAYMENT GATING: Verify payment status from database
        if ($consultation->requiresPaymentForTreatmentPlan()) {
            Log::warning('Treatment plan access denied: payment required', [
                'consultation_id' => $consultation->id,
                'consultation_ref' => $reference,
                'payment_status' => $consultation->payment_status,
                'payment_required' => $consultation->payment_required_for_treatment
            ]);
            
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'Payment is required to access your treatment plan. Please complete payment first.',
                'showPaymentButton' => true,
                'paymentRequired' => true
            ]);
        }
        
        // CRITICAL SECURITY CHECK: Verify treatment plan is unlocked
        // This ensures payment was confirmed via webhook
        if (!$consultation->treatment_plan_unlocked) {
            Log::warning('SECURITY: Treatment plan access denied - not unlocked via webhook', [
                'consultation_id' => $consultation->id,
                'consultation_ref' => $reference,
                'payment_status' => $consultation->payment_status,
                'unlocked' => $consultation->treatment_plan_unlocked
            ]);
            
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'Your payment is being processed. Please wait a moment and try again.',
                'showPaymentButton' => false
            ]);
        }
        
        // FINAL CHECK: Ensure payment is confirmed (both status and unlock)
        if (!$consultation->isTreatmentPlanAccessible()) {
            Log::critical('SECURITY ALERT: Treatment plan access attempt with invalid state', [
                'consultation_id' => $consultation->id,
                'consultation_ref' => $reference,
                'payment_status' => $consultation->payment_status,
                'treatment_plan_unlocked' => $consultation->treatment_plan_unlocked,
                'is_paid' => $consultation->isPaid(),
                'ip_address' => $request->ip()
            ]);
            
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'Unable to access treatment plan. Please contact support if you have completed payment.',
                'showPaymentButton' => false
            ]);
        }
        
        // All checks passed - grant access
        Log::info('✅ Treatment plan accessed successfully', [
            'consultation_id' => $consultation->id,
            'consultation_ref' => $reference,
            'payment_id' => $consultation->payment_id,
            'accessed_at' => now()->toDateTimeString()
        ]);
        
        // Mark as accessed
        $consultation->markTreatmentPlanAccessed();
        
        return view('consultation.treatment-plan', compact('consultation'));
    }
}
