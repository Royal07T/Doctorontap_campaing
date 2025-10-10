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

class ConsultationController extends Controller
{
    /**
     * Display the landing page with consultation form
     */
    public function index()
    {
        $doctors = Doctor::available()->ordered()->get();
        
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'age' => 'required|integer|min:1|max:120',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            
            // Triage Block
            'problem' => 'required|string|max:500',
            'medical_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // Max 5MB per file
            'severity' => 'required|in:mild,moderate,severe',
            'emergency_symptoms' => 'nullable|array',
            
            // Doctor's Choice
            'doctor' => 'nullable|string|max:255',
            'consult_mode' => 'required|in:voice,video,chat',
            
            // Consent
            'informed_consent' => 'required|accepted',
            'data_privacy' => 'required|accepted',
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
}
