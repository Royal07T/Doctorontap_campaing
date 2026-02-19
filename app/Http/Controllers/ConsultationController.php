<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\PaymentRequest;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Services\ConsultationService;
use App\Http\Requests\ConsultationRequest;

class ConsultationController extends Controller
{
    protected $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }
    /**
     * Display the landing page with consultation form
     */
    public function index()
    {
        // Get only General Practitioner/General Practice doctors for patients
        // Available doctors are shown first, then unavailable ones
        $doctors = Doctor::approved()
            ->generalPractitioner() // Only show GP doctors to patients
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
    public function store(ConsultationRequest $request)
    {
        try {
            $validated = $request->validated();

            // Handle medical document uploads
            $uploadedDocuments = [];
            if ($request->hasFile('medical_documents')) {
                $uploadedDocuments = $this->consultationService->handleDocumentUploads(
                    $request->file('medical_documents')
                );
            }

            // Create consultation using service
            $result = $this->consultationService->createConsultation($validated, $uploadedDocuments);

            // Check if payment is required before consultation can proceed
            $consultation = $result['consultation'];
            if ($consultation->requiresPayment() && !$consultation->isPaid()) {
                // Initialize payment and return payment URL
                $paymentController = app(\App\Http\Controllers\PaymentController::class);
                $paymentRequest = new \Illuminate\Http\Request([
                    'amount' => $consultation->doctor ? $consultation->doctor->effective_consultation_fee : 0,
                    'customer_email' => $validated['email'],
                    'customer_name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'customer_phone' => $validated['mobile'],
                    'doctor_id' => $consultation->doctor_id,
                    'metadata' => [
                        'consultation_id' => $consultation->id,
                        'consultation_reference' => $result['reference'],
                    ],
                ]);

                $paymentResponse = $paymentController->initialize($paymentRequest);
                $paymentData = json_decode($paymentResponse->getContent(), true);

                if ($paymentData['success'] && isset($paymentData['checkout_url'])) {
                    // Link payment to consultation
                    $payment = \App\Models\Payment::where('reference', $paymentData['reference'])->first();
                    if ($payment) {
                        $consultation->update([
                            'payment_id' => $payment->id,
                            'payment_status' => 'pending',
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'requires_payment' => true,
                        'message' => 'Please complete payment to confirm your consultation booking.',
                        'consultation_reference' => $result['reference'],
                        'payment_url' => $paymentData['checkout_url'],
                        'redirect_to_payment' => true,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your consultation has been booked successfully. We will contact you shortly via WhatsApp to schedule your consultation.',
                'consultation_reference' => $result['reference'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they're handled by Laravel
            throw $e;
        } catch (\Exception $e) {
            // Catch-all for any unexpected errors
            Log::error('Unexpected error in consultation submission: ' . $e->getMessage(), [
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
