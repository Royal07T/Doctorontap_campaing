<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ConsultationConfirmation;
use App\Mail\ConsultationAdminAlert;
use App\Mail\ConsultationDoctorNotification;
use App\Mail\PaymentRequest;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Patient;

class ConsultationController extends Controller
{
    /**
     * Display the landing page with consultation form
     */
    public function index()
    {
        $doctors = Doctor::available()->ordered()->with('reviews')->get();
        
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

        // Handle medical document uploads
        $uploadedDocuments = [];
        if ($request->hasFile('medical_documents')) {
            try {
                foreach ($request->file('medical_documents') as $file) {
                    $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('medical_documents', $fileName, 'public');
                    
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
        try {
            $patient = Patient::updateOrCreate(
                [
                    'email' => $validated['email'],
                ],
                [
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'phone' => $validated['mobile'],
                    'gender' => $validated['gender'],
                    'age' => $validated['age'],
                ]
            );
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

        // Queue emails for asynchronous sending (improves performance under load)
        // Emails are non-critical - we continue even if they fail
        
        // Count emails to be sent
        $emailsQueued = 0;
        // Use ADMIN_EMAIL env variable, fallback to mail.from.address
        $adminEmail = env('ADMIN_EMAIL', config('mail.from.address', 'inquiries@doctorontap.com.ng'));
        
        try {
            // Send confirmation email to the patient
            Mail::to($validated['email'])->queue(new ConsultationConfirmation($validated));
            $emailsQueued++;
            \Log::info('Patient confirmation email queued successfully', [
                'consultation_reference' => $reference,
                'patient_email' => $validated['email']
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to queue patient confirmation email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'patient_email' => $validated['email']
            ]);
        }

        try {
            // Send alert email to admin
            Mail::to($adminEmail)->queue(new ConsultationAdminAlert($validated));
            $emailsQueued++;
            \Log::info('Admin alert email queued successfully', [
                'consultation_reference' => $reference,
                'admin_email' => $adminEmail
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to queue admin alert email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'admin_email' => $adminEmail
            ]);
        }

        try {
            // Send notification email to the assigned doctor
            if ($doctorEmail) {
                Mail::to($doctorEmail)->queue(new ConsultationDoctorNotification($validated));
                $emailsQueued++;
                \Log::info('Doctor notification email queued successfully', [
                    'consultation_reference' => $reference,
                    'doctor_email' => $doctorEmail
                ]);
            } else {
                \Log::warning('No doctor email available - skipping doctor notification', [
                    'consultation_reference' => $reference
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to queue doctor notification email: ' . $e->getMessage(), [
                'consultation_reference' => $reference,
                'doctor_email' => $doctorEmail ?? 'N/A'
            ]);
        }
        
        \Log::info('Consultation booking completed - emails queued', [
            'consultation_reference' => $reference,
            'total_emails_queued' => $emailsQueued,
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

        // Queue payment request email for asynchronous sending
        try {
            Mail::to($consultation->email)->queue(new PaymentRequest($consultation));

            // Update consultation
            $consultation->update([
                'payment_request_sent' => true,
                'payment_request_sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment request email queued successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to queue payment request email: ' . $e->getMessage(), [
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
     */
    public function accessTreatmentPlan(Request $request, $reference)
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
        
        // STRICT PAYMENT GATING: Double-check payment status
        if ($consultation->requiresPaymentForTreatmentPlan()) {
            return view('consultation.treatment-plan-access', [
                'consultation' => $consultation,
                'error' => 'Payment is required to access your treatment plan. Please complete payment first.',
                'showPaymentButton' => true,
                'paymentRequired' => true
            ]);
        }
        
        // Mark as accessed
        $consultation->markTreatmentPlanAccessed();
        
        return view('consultation.treatment-plan', compact('consultation'));
    }
}
