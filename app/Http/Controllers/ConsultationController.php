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
        // Validate the form data
        $validated = $request->validate([
            // Personal Details
            'first_name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z\s\'-]+$/',
            'last_name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z\s\'-]+$/',
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:1|max:120',
            'mobile' => 'required|string|regex:/^(\+234|0)[0-9]{10}$/',
            'email' => 'required|email:rfc,dns|max:255',
            
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

        // Create consultation record
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

        // Update patient aggregates
        $patient->increment('consultations_count');
        $patient->last_consultation_at = now();
        $patient->save();

        // Add reference and documents to validated data for emails
        $validated['consultation_reference'] = $reference;
        $validated['has_documents'] = !empty($uploadedDocuments);
        $validated['documents_count'] = count($uploadedDocuments);

        // Send confirmation email to the user
        Mail::to($validated['email'])->send(new ConsultationConfirmation($validated));

        // Send alert email to admin
        Mail::to(env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng'))->send(new ConsultationAdminAlert($validated));

        // Send notification email to the assigned doctor
        if ($doctorEmail) {
            Mail::to($doctorEmail)->send(new ConsultationDoctorNotification($validated));
        }

        // Return success response (NO PAYMENT REQUIRED UPFRONT)
        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your consultation has been booked successfully. We will contact you shortly via WhatsApp to schedule your consultation. Remember: You only pay AFTER your consultation is complete.',
            'consultation_reference' => $reference,
        ]);
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

        // Send payment request email
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
