<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Doctor;
use App\Models\Consultation;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Initialize Korapay payment
     */
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string',
            'customer_phone' => 'nullable|string',
            'doctor_id' => 'nullable|exists:doctors,id',
            'metadata' => 'nullable|array',
        ]);

        // Generate unique reference
        $reference = 'DOT-' . time() . '-' . Str::random(8);

        // Get doctor details if provided
        $doctor = null;
        if (!empty($validated['doctor_id'])) {
            $doctor = Doctor::find($validated['doctor_id']);
        }

        // Create payment record
        $payment = Payment::create([
            'reference' => $reference,
            'customer_email' => $validated['customer_email'],
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'amount' => $validated['amount'],
            'currency' => 'NGN',
            'status' => 'pending',
            'doctor_id' => $validated['doctor_id'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        // Prepare Korapay API request
        $payload = [
            'amount' => $validated['amount'],
            'redirect_url' => route('payment.callback'),
            'currency' => 'NGN',
            'reference' => $reference,
            'notification_url' => route('payment.webhook'),
            'narration' => 'Consultation payment for ' . ($doctor ? $doctor->name : 'Doctor'),
            'customer' => [
                'email' => $validated['customer_email'],
                'name' => $validated['customer_name'],
            ],
            'merchant_bears_cost' => false, // Customer pays the fee
        ];

        // Make API call to Korapay
        try {
            $apiUrl = config('services.korapay.api_url');
            $secretKey = config('services.korapay.secret_key');
            $fullUrl = $apiUrl . '/charges/initialize';
            
            Log::info('Korapay API Request', [
                'api_url' => $apiUrl,
                'full_url' => $fullUrl,
                'has_secret' => !empty($secretKey)
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post($fullUrl, $payload);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === true) {
                // Update payment with checkout URL
                $payment->update([
                    'checkout_url' => $responseData['data']['checkout_url'],
                    'korapay_response' => json_encode($responseData),
                ]);

                return response()->json([
                    'success' => true,
                    'checkout_url' => $responseData['data']['checkout_url'],
                    'reference' => $reference,
                ]);
            } else {
                Log::error('Korapay initialization failed', ['response' => $responseData]);
                
                return response()->json([
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Payment initialization failed',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Korapay API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'api_url' => config('services.korapay.api_url'),
                'has_secret' => !empty(config('services.korapay.secret_key'))
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment system error. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle payment callback (redirect URL)
     * 
     * SECURITY FIX: Now properly checks payment status before showing success
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            Log::warning('Payment callback without reference', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);
            
            return redirect()->route('consultation.index')
                ->with('error', 'Payment reference not found');
        }

        Log::info('Payment callback received', [
            'reference' => $reference,
            'url' => $request->fullUrl()
        ]);

        // Verify the transaction with Korapay
        $verificationResult = $this->verifyTransaction($reference);

        Log::info('Payment callback verification result', [
            'reference' => $reference,
            'success' => $verificationResult['success'],
            'status' => $verificationResult['status'] ?? 'unknown'
        ]);

        // CRITICAL FIX: Only show success if payment actually succeeded
        if ($verificationResult['success'] && $verificationResult['status'] === 'success') {
            $payment = Payment::where('reference', $reference)->first();
            
            Log::info('Payment callback showing success page', [
                'reference' => $reference,
                'payment_id' => $payment ? $payment->id : null
            ]);
            
            return view('payment.success', [
                'payment' => $payment,
                'message' => 'Payment successful! Your treatment plan has been sent to your email.'
            ]);
        } else {
            // Payment failed or pending
            $status = $verificationResult['status'] ?? 'unknown';
            $message = $verificationResult['message'] ?? 'Payment was not completed';
            
            Log::warning('Payment callback showing failure page', [
                'reference' => $reference,
                'status' => $status,
                'message' => $message
            ]);
            
            // Customize message based on status
            if ($status === 'pending') {
                $message = 'Your payment is still pending. Please complete the payment to access your treatment plan.';
            } elseif ($status === 'failed') {
                $message = 'Your payment failed. Please try again or contact support.';
            } elseif ($status === 'cancelled') {
                $message = 'Payment was cancelled. You can try again when ready.';
            }
            
            return view('payment.failed', [
                'reference' => $reference,
                'message' => $message,
                'status' => $status
            ]);
        }
    }

    /**
     * Manually unlock treatment plan for a consultation (fallback method)
     */
    public function unlockTreatmentPlan($consultationId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            
            if (!$consultation->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not confirmed for this consultation'
                ], 400);
            }
            
            if (!$consultation->hasTreatmentPlan()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No treatment plan exists for this consultation'
                ], 400);
            }
            
            // Unlock treatment plan
            $consultation->unlockTreatmentPlan();
            
            // Email will be sent automatically by ConsultationObserver when payment_status changes to 'paid'
            // No need to send here to avoid duplicates
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan unlocked successfully. Email will be sent automatically.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock treatment plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify transaction with Korapay
     */
    public function verify(Request $request)
    {
        $reference = $request->query('reference') ?? $request->input('reference');

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Reference is required'
            ], 400);
        }

        $result = $this->verifyTransaction($reference);

        return response()->json($result);
    }

    /**
     * Handle webhook notification from Korapay
     * 
     * SECURITY: This endpoint verifies payment and unlocks treatment plans
     * Only processes after webhook signature verification
     */
    public function webhook(Request $request)
    {
        // Set precision to maintain amount field precision (per KoraPay docs)
        ini_set('serialize_precision', '-1');
        
        // Check for POST method and signature header (per KoraPay PHP example)
        if (strtoupper($request->method()) !== 'POST' || !$request->hasHeader('x-korapay-signature')) {
            Log::warning('Invalid webhook request', [
                'method' => $request->method(),
                'has_signature' => $request->hasHeader('x-korapay-signature'),
                'ip' => $request->ip()
            ]);
            // Return 200 to prevent retries (per KoraPay docs)
            return response()->json(['status' => 'invalid_request'], 200);
        }

        // Log webhook payload for debugging
        Log::info('Korapay Webhook Received', [
            'event' => $request->input('event'),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'full_payload' => $request->all()
        ]);

        try {
            // Get request body and signature (per KoraPay PHP example)
            $requestBody = $request->all();
            $webhookSignature = $request->header('x-korapay-signature');
            $korapaySecretKey = config('services.korapay.secret_key');
            
            // Verify signature according to KoraPay docs
            if ($webhookSignature && $korapaySecretKey && isset($requestBody['data'])) {
                // Signature is HMAC SHA256 of ONLY the data object (per KoraPay docs)
                $dataJson = json_encode($requestBody['data'], JSON_UNESCAPED_SLASHES);
                $expectedSignature = hash_hmac('sha256', $dataJson, $korapaySecretKey);
                
                if ($webhookSignature !== $expectedSignature) {
                    Log::warning('SECURITY ALERT: Invalid webhook signature', [
                        'expected' => $expectedSignature,
                        'received' => $webhookSignature,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    // Return 200 to prevent retries (per KoraPay docs)
                    return response()->json(['status' => 'invalid_signature'], 200);
                }
                
                Log::info('Webhook signature verified successfully');
            } else {
                Log::warning('Webhook received without signature or data', [
                    'has_signature' => !empty($webhookSignature),
                    'has_secret' => !empty($korapaySecretKey),
                    'has_data' => isset($requestBody['data'])
                ]);
                // Return 200 to prevent retries
                return response()->json(['status' => 'missing_signature_or_data'], 200);
            }

            $event = $request->input('event');
            $data = $request->input('data');

            // Validate webhook data structure
            if (!$data || !is_array($data)) {
                Log::error('Invalid webhook data structure', ['data' => $data]);
                return response()->json(['status' => 'invalid_data'], 400);
            }

            // Check if this is a payout/disbursement webhook
            // According to KoraPay docs: payout webhooks use events "transfer.success" or "transfer.failed"
            // Patient payment webhooks use events "charge.success" or "charge.failed"
            $isPayoutWebhook = false;
            if ($event === 'transfer.success' || $event === 'transfer.failed') {
                $isPayoutWebhook = true;
            } elseif (isset($data['status']) && in_array($data['status'], ['success', 'failed', 'processing'])) {
                // Fallback: Check if it's a payout by looking for KoraPay reference format (KPY-D-*)
                $reference = $data['reference'] ?? null;
                if ($reference && (strpos($reference, 'KPY-D-') === 0 || strpos($reference, 'KPY-') === 0)) {
                    $isPayoutWebhook = true;
                }
            }

            // Handle payout webhooks
            if ($isPayoutWebhook) {
                Log::info('Detected payout webhook, routing to payout handler', [
                    'event' => $event,
                    'reference' => $data['reference'] ?? null
                ]);
                
                // Route to payout webhook handler
                return $this->payoutWebhook($request);
            }

            // Extract reference from webhook data (for patient payments)
            $reference = $data['reference'] ?? $data['merchant_reference'] ?? null;
            
            if (!$reference) {
                Log::error('Webhook missing payment reference', ['data' => $data]);
                return response()->json(['status' => 'missing_reference'], 400);
            }

            // Find payment record first (patient payment)
            $payment = Payment::where('reference', $reference)->first();

            if (!$payment) {
                // Check if it might be a doctor payment (payout) that wasn't detected above
                $doctorPayment = \App\Models\DoctorPayment::where('korapay_reference', $reference)->first();
                if ($doctorPayment) {
                    Log::info('Found doctor payment by KoraPay reference, routing to payout handler');
                    return $this->payoutWebhook($request);
                }
                
                Log::warning('Payment record not found for webhook', ['reference' => $reference]);
                return response()->json(['status' => 'payment_not_found'], 404);
            }

            // ============================================================
            // HANDLE ALL PAYMENT EVENTS
            // ============================================================

            // 1. SUCCESSFUL PAYMENT
            if ($event === 'charge.success') {
                Log::info('✅ Processing SUCCESSFUL charge', [
                    'reference' => $reference,
                    'amount' => $data['amount'] ?? null
                ]);
                
                // Update payment record to success
                $payment->update([
                    'status' => 'success',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? $reference,
                    'fee' => $data['fee'] ?? null,
                    'korapay_response' => json_encode($data),
                ]);

                Log::info('Payment record updated to SUCCESS', [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'amount' => $payment->amount
                ]);

                // ===== CRITICAL: UNLOCK TREATMENT PLAN AFTER PAYMENT CONFIRMATION =====
                if ($payment->metadata && isset($payment->metadata['consultation_id'])) {
                    $consultation = Consultation::find($payment->metadata['consultation_id']);
                    
                    if (!$consultation) {
                        Log::error('Consultation not found for payment', [
                            'consultation_id' => $payment->metadata['consultation_id'],
                            'payment_reference' => $reference
                        ]);
                        return response()->json(['status' => 'consultation_not_found'], 404);
                    }

                    Log::info('Processing consultation payment', [
                        'consultation_id' => $consultation->id,
                        'consultation_ref' => $consultation->reference,
                        'current_payment_status' => $consultation->payment_status,
                        'treatment_plan_exists' => $consultation->hasTreatmentPlan(),
                        'treatment_plan_unlocked' => $consultation->treatment_plan_unlocked
                    ]);
                    
                    // Update consultation payment status to PAID
                    if ($consultation->payment_status !== 'paid') {
                        $consultation->update([
                            'payment_status' => 'paid',
                            'payment_id' => $payment->id,
                        ]);
                        
                        Log::info('Consultation payment status updated to PAID', [
                            'consultation_id' => $consultation->id,
                            'payment_id' => $payment->id
                        ]);

                        // Create notifications for patient and doctor
                        try {
                            // Notification for patient
                            if ($consultation->patient_id) {
                                \App\Models\Notification::create([
                                    'user_type' => 'patient',
                                    'user_id' => $consultation->patient_id,
                                    'title' => 'Payment Confirmed',
                                    'message' => "Your payment for consultation (Ref: {$consultation->reference}) has been confirmed successfully.",
                                    'type' => 'success',
                                    'action_url' => patient_url('consultations/' . $consultation->id),
                                    'data' => [
                                        'consultation_id' => $consultation->id,
                                        'consultation_reference' => $consultation->reference,
                                        'payment_id' => $payment->id,
                                        'type' => 'payment_confirmed'
                                    ]
                                ]);
                            }

                            // Notification for doctor
                            if ($consultation->doctor_id) {
                                \App\Models\Notification::create([
                                    'user_type' => 'doctor',
                                    'user_id' => $consultation->doctor_id,
                                    'title' => 'Payment Received',
                                    'message' => "Payment has been confirmed for consultation (Ref: {$consultation->reference}) with {$consultation->full_name}.",
                                    'type' => 'success',
                                    'action_url' => doctor_url('consultations/' . $consultation->id),
                                    'data' => [
                                        'consultation_id' => $consultation->id,
                                        'consultation_reference' => $consultation->reference,
                                        'patient_name' => $consultation->full_name,
                                        'payment_id' => $payment->id,
                                        'type' => 'payment_received'
                                    ]
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to create payment confirmation notifications', [
                                'consultation_id' => $consultation->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    // UNLOCK TREATMENT PLAN - Only after payment confirmed via webhook
                    if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                        $consultation->unlockTreatmentPlan();
                        
                        Log::info('✅ TREATMENT PLAN UNLOCKED SUCCESSFULLY', [
                            'consultation_id' => $consultation->id,
                            'consultation_ref' => $consultation->reference,
                            'payment_reference' => $reference,
                            'unlocked_at' => now()->toDateTimeString()
                        ]);
                        
                        // Email will be sent automatically by ConsultationObserver when payment_status changes to 'paid'
                        // No need to send here to avoid duplicates
                        Log::info('Treatment plan unlocked, email will be sent by Observer', [
                                'consultation_id' => $consultation->id,
                                'payment_reference' => $reference
                            ]);
                    } else {
                        Log::info('Treatment plan already unlocked or not available', [
                            'consultation_id' => $consultation->id,
                            'has_treatment_plan' => $consultation->hasTreatmentPlan(),
                            'already_unlocked' => $consultation->treatment_plan_unlocked,
                            'payment_reference' => $reference
                        ]);
                    }
                }
                // Handle booking payment (multi-patient)
                elseif ($payment->metadata && isset($payment->metadata['booking_id'])) {
                    $booking = Booking::with(['invoice', 'consultations'])->find($payment->metadata['booking_id']);
                    
                    if (!$booking) {
                        Log::error('Booking not found for payment', [
                            'booking_id' => $payment->metadata['booking_id'],
                            'payment_reference' => $reference
                        ]);
                        return response()->json(['status' => 'booking_not_found'], 404);
                    }

                    Log::info('Processing booking payment', [
                        'booking_id' => $booking->id,
                        'booking_ref' => $booking->reference,
                        'current_payment_status' => $booking->payment_status
                    ]);
                    
                    // Update booking payment status to PAID
                    if ($booking->payment_status !== 'paid') {
                        $booking->update([
                            'payment_status' => 'paid',
                        ]);
                        
                        Log::info('Booking payment status updated to PAID', [
                            'booking_id' => $booking->id
                        ]);
                    }

                    // Update invoice
                    if ($booking->invoice) {
                        $booking->invoice->markAsPaid($payment->amount);
                        
                        Log::info('Invoice marked as paid', [
                            'invoice_id' => $booking->invoice->id,
                            'amount' => $payment->amount
                        ]);
                    }

                    // Update all consultations under this booking
                    $booking->consultations()->update([
                        'payment_status' => 'paid',
                        'payment_id' => $payment->id,
                    ]);

                    // Unlock treatment plans for completed consultations
                    foreach ($booking->consultations as $consultation) {
                        if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                            $consultation->unlockTreatmentPlan();
                            
                            Log::info('✅ TREATMENT PLAN UNLOCKED for consultation', [
                                'consultation_id' => $consultation->id,
                                'booking_ref' => $booking->reference
                            ]);
                        }
                    }
                    
                    Log::info('✅ Booking payment processed successfully', [
                        'booking_id' => $booking->id,
                        'num_patients' => $booking->patients()->count()
                    ]);
                }
                else {
                    Log::warning('Payment has no consultation or booking metadata', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'metadata' => $payment->metadata
                    ]);
                }

                Log::info('✅ Webhook processing completed successfully', ['reference' => $reference]);
            }
            
            // 2. FAILED PAYMENT
            elseif ($event === 'charge.failed') {
                Log::warning('❌ Processing FAILED charge', [
                    'reference' => $reference,
                    'amount' => $data['amount'] ?? null,
                    'failure_reason' => $data['failure_message'] ?? 'Unknown'
                ]);
                
                // Update payment record to failed
                $payment->update([
                    'status' => 'failed',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? $reference,
                    'korapay_response' => json_encode($data),
                ]);

                Log::info('Payment record updated to FAILED', [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'failure_reason' => $data['failure_message'] ?? 'Unknown'
                ]);

                // Update consultation payment status to failed
                if ($payment->metadata && isset($payment->metadata['consultation_id'])) {
                    $consultation = Consultation::find($payment->metadata['consultation_id']);
                    
                    if ($consultation) {
                        $consultation->update([
                            'payment_status' => 'failed',
                        ]);
                        
                        Log::info('Consultation payment status updated to FAILED', [
                            'consultation_id' => $consultation->id,
                            'reference' => $reference
                        ]);

                        // Optionally send failure notification email
                        try {
                            \Illuminate\Support\Facades\Mail::to($consultation->email)->send(
                                new \App\Mail\PaymentFailedNotification($consultation, $payment, $data['failure_message'] ?? 'Payment could not be processed')
                            );
                            
                            Log::info('Payment failure notification sent', [
                                'consultation_id' => $consultation->id,
                                'email' => $consultation->email
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send payment failure email', [
                                'consultation_id' => $consultation->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                // Update booking payment status to failed
                elseif ($payment->metadata && isset($payment->metadata['booking_id'])) {
                    $booking = Booking::find($payment->metadata['booking_id']);
                    
                    if ($booking) {
                        $booking->update(['payment_status' => 'failed']);
                        if ($booking->invoice) {
                            $booking->invoice->update(['status' => 'cancelled']);
                        }
                        Log::info('Booking payment status updated to FAILED', [
                            'booking_id' => $booking->id
                        ]);
                    }
                }

                Log::info('❌ Failed payment webhook processed', ['reference' => $reference]);
            }
            
            // 3. PENDING/PROCESSING PAYMENT
            elseif ($event === 'charge.pending' || $event === 'charge.processing') {
                Log::info('⏳ Processing PENDING/PROCESSING charge', [
                    'reference' => $reference,
                    'event' => $event
                ]);
                
                // Update payment record to pending
                $payment->update([
                    'status' => 'pending',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? $reference,
                    'korapay_response' => json_encode($data),
                ]);

                Log::info('Payment record updated to PENDING', [
                    'payment_id' => $payment->id,
                    'reference' => $reference
                ]);

                // Update consultation payment status to pending
                if ($payment->metadata && isset($payment->metadata['consultation_id'])) {
                    $consultation = Consultation::find($payment->metadata['consultation_id']);
                    
                    if ($consultation && $consultation->payment_status !== 'paid') {
                        $consultation->update([
                            'payment_status' => 'pending',
                        ]);
                        
                        Log::info('Consultation payment status updated to PENDING', [
                            'consultation_id' => $consultation->id,
                            'reference' => $reference
                        ]);
                    }
                }

                Log::info('⏳ Pending payment webhook processed', ['reference' => $reference]);
            }
            
            // 4. CANCELLED/ABANDONED PAYMENT
            elseif ($event === 'charge.cancelled' || $event === 'charge.abandoned') {
                Log::info('🚫 Processing CANCELLED/ABANDONED charge', [
                    'reference' => $reference,
                    'event' => $event
                ]);
                
                // Update payment record to cancelled
                $payment->update([
                    'status' => 'cancelled',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? $reference,
                    'korapay_response' => json_encode($data),
                ]);

                Log::info('Payment record updated to CANCELLED', [
                    'payment_id' => $payment->id,
                    'reference' => $reference
                ]);

                // Update consultation payment status to cancelled
                if ($payment->metadata && isset($payment->metadata['consultation_id'])) {
                    $consultation = Consultation::find($payment->metadata['consultation_id']);
                    
                    if ($consultation && $consultation->payment_status !== 'paid') {
                        $consultation->update([
                            'payment_status' => 'cancelled',
                        ]);
                        
                        Log::info('Consultation payment status updated to CANCELLED', [
                            'consultation_id' => $consultation->id,
                            'reference' => $reference
                        ]);
                    }
                }

                Log::info('🚫 Cancelled payment webhook processed', ['reference' => $reference]);
            }
            
            // 5. ANY OTHER EVENT
            else {
                Log::info('ℹ️ Webhook event received (not handled)', [
                    'event' => $event,
                    'reference' => $reference,
                    'data' => $data
                ]);
                
                // Still update the korapay_response to keep track
                $payment->update([
                    'korapay_response' => json_encode($data),
                ]);
            }

            // Always return 200 to acknowledge receipt
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'webhook_data' => $request->all()
            ]);
            
            // Still return 200 to prevent webhook retries on our errors
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 200);
        }
    }

    /**
     * Handle payout webhook notification from Korapay
     * 
     * This handles webhooks for doctor payout transactions
     */
    public function payoutWebhook(Request $request)
    {
        // Set precision to maintain amount field precision (per KoraPay docs)
        ini_set('serialize_precision', '-1');
        
        // Check for POST method and signature header (per KoraPay docs)
        if (strtoupper($request->method()) !== 'POST' || !$request->hasHeader('x-korapay-signature')) {
            Log::warning('Invalid payout webhook request', [
                'method' => $request->method(),
                'has_signature' => $request->hasHeader('x-korapay-signature'),
                'ip' => $request->ip()
            ]);
            // Return 200 to prevent retries
            return response()->json(['status' => 'invalid_request'], 200);
        }

        // Log webhook payload for debugging
        Log::info('Korapay Payout Webhook Received', [
            'event' => $request->input('event'),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'full_payload' => $request->all()
        ]);

        try {
            // Get request body and signature (per KoraPay PHP example)
            $requestBody = $request->all();
            $webhookSignature = $request->header('x-korapay-signature');
            $korapaySecretKey = config('services.korapay.secret_key');
            
            // Verify signature according to KoraPay docs
            if ($webhookSignature && $korapaySecretKey && isset($requestBody['data'])) {
                // Signature is HMAC SHA256 of ONLY the data object (per KoraPay docs)
                $dataJson = json_encode($requestBody['data'], JSON_UNESCAPED_SLASHES);
                $expectedSignature = hash_hmac('sha256', $dataJson, $korapaySecretKey);
                
                if ($webhookSignature !== $expectedSignature) {
                    Log::warning('SECURITY ALERT: Invalid payout webhook signature', [
                        'expected' => $expectedSignature,
                        'received' => $webhookSignature,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    // Return 200 to prevent retries (per KoraPay docs)
                    return response()->json(['status' => 'invalid_signature'], 200);
                }
                
                Log::info('Payout webhook signature verified successfully');
            } else {
                Log::warning('Payout webhook received without signature or data', [
                    'has_signature' => !empty($webhookSignature),
                    'has_secret' => !empty($korapaySecretKey),
                    'has_data' => isset($requestBody['data'])
                ]);
                // Return 200 to prevent retries
                return response()->json(['status' => 'missing_signature_or_data'], 200);
            }

            $event = $request->input('event');
            $data = $request->input('data');

            if (!$data || !isset($data['reference'])) {
                Log::error('Invalid payout webhook payload', ['payload' => $request->all()]);
                // Return 200 to acknowledge receipt even if invalid
                return response()->json(['status' => 'invalid_payload'], 200);
            }

            $korapayReference = $data['reference'];
            // According to KoraPay docs: status can be "success" or "failed"
            $status = $data['status'] ?? 'processing';

            // Find payment by KoraPay reference
            $payment = \App\Models\DoctorPayment::where('korapay_reference', $korapayReference)->first();

            if (!$payment) {
                Log::warning('Payout webhook received for unknown payment', [
                    'korapay_reference' => $korapayReference,
                    'event' => $event
                ]);
                // Return 200 to prevent retries (per KoraPay best practices)
                return response()->json(['status' => 'payment_not_found'], 200);
            }

            // Idempotency check: Prevent duplicate processing (per KoraPay best practices)
            // Check if this webhook has already been processed by comparing current status
            $currentStatus = $payment->korapay_status;
            $currentPaymentStatus = $payment->status;
            
            // If already processed with same status, acknowledge and skip
            if ($currentStatus === $status && 
                (($status === 'success' && $currentPaymentStatus === 'completed') || 
                 ($status === 'failed' && $currentPaymentStatus === 'failed'))) {
                Log::info('Webhook already processed (idempotency check)', [
                    'payment_reference' => $payment->reference,
                    'korapay_reference' => $korapayReference,
                    'status' => $status,
                    'event' => $event
                ]);
                // Return 200 to acknowledge receipt
                return response()->json(['status' => 'already_processed'], 200);
            }

            Log::info('Processing payout webhook', [
                'payment_reference' => $payment->reference,
                'korapay_reference' => $korapayReference,
                'status' => $status,
                'event' => $event,
                'current_status' => $currentStatus
            ]);

            // Update payment based on status
            $updateData = [
                'korapay_status' => $status,
                'korapay_response' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ];

            if ($status === 'success') {
                $updateData['status'] = 'completed';
                $updateData['paid_at'] = now();
                $updateData['payout_completed_at'] = now();
                $updateData['transaction_reference'] = $korapayReference;
                
                if (isset($data['fee'])) {
                    $updateData['korapay_fee'] = (float) $data['fee'];
                }

                // Mark consultations as paid
                if ($payment->consultation_ids) {
                    \App\Models\Consultation::whereIn('id', $payment->consultation_ids)
                        ->update(['payment_status' => 'paid']);
                }

                Log::info('✅ Payout completed successfully', [
                    'payment_reference' => $payment->reference,
                    'doctor_id' => $payment->doctor_id,
                    'amount' => $payment->doctor_amount,
                ]);

            } elseif ($status === 'failed') {
                $updateData['status'] = 'failed';
                
                Log::warning('Payout failed', [
                    'payment_reference' => $payment->reference,
                    'message' => $data['message'] ?? 'Payout failed',
                ]);
            } else {
                $updateData['status'] = 'processing';
            }

            $payment->update($updateData);

            // Always return 200 to acknowledge receipt (per KoraPay docs)
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Payout webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle payment request from email link
     */
    public function handlePaymentRequest($reference)
    {
        // Check if it's a booking or consultation reference
        if (Str::startsWith($reference, 'BOOK-')) {
            return $this->handleBookingPayment($reference);
        }

        // Find consultation by reference
        $consultation = Consultation::with('doctor')->where('reference', $reference)->first();

        if (!$consultation) {
            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'Consultation not found. Please contact support.'
            ]);
        }

        // Check if payment already made
        if ($consultation->isPaid()) {
            return view('payment.success', [
                'payment' => $consultation->payment,
                'message' => 'This consultation has already been paid for.'
            ]);
        }

        // Check if doctor has consultation fee
        if (!$consultation->doctor || $consultation->doctor->effective_consultation_fee <= 0) {
            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'No payment is required for this consultation.'
            ]);
        }

        // Generate unique payment reference
        $paymentReference = 'PAY-' . time() . '-' . Str::random(8);

        // Determine if this is upfront or post-consultation payment
        $isUpfrontPayment = !$consultation->isCompleted();

        // Create payment record
        $payment = Payment::create([
            'reference' => $paymentReference,
            'customer_email' => $consultation->email,
            'customer_name' => $consultation->full_name,
            'customer_phone' => $consultation->mobile,
            'amount' => $consultation->doctor->effective_consultation_fee,
            'currency' => 'NGN',
            'status' => 'pending',
            'doctor_id' => $consultation->doctor_id,
            'metadata' => [
                'consultation_reference' => $consultation->reference,
                'consultation_id' => $consultation->id,
                'payment_type' => $isUpfrontPayment ? 'upfront' : 'post_consultation',
            ],
        ]);

        // Determine payment type
        $paymentType = $consultation->isCompleted() ? 'Post-consultation' : 'Upfront';
        
        // Prepare Korapay API request
        $payload = [
            'amount' => $consultation->doctor->effective_consultation_fee,
            'redirect_url' => route('payment.callback'),
            'currency' => 'NGN',
            'reference' => $paymentReference,
            'notification_url' => route('payment.webhook'),
            'narration' => $paymentType . ' payment for ' . $consultation->doctor->name . ' - Ref: ' . $consultation->reference,
            'customer' => [
                'email' => $consultation->email,
                'name' => $consultation->full_name,
            ],
            'merchant_bears_cost' => false,
        ];

        // Make API call to Korapay
        try {
            $apiUrl = config('services.korapay.api_url');
            $secretKey = config('services.korapay.secret_key');
            $fullUrl = $apiUrl . '/charges/initialize';
            
            Log::info('Korapay API Request', [
                'api_url' => $apiUrl,
                'full_url' => $fullUrl,
                'has_secret' => !empty($secretKey)
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post($fullUrl, $payload);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === true) {
                // Update payment with checkout URL
                $payment->update([
                    'checkout_url' => $responseData['data']['checkout_url'],
                    'korapay_response' => json_encode($responseData),
                ]);

                // Link payment to consultation
                $consultation->update([
                    'payment_id' => $payment->id,
                    'payment_status' => 'pending',
                ]);

                // Redirect to Korapay checkout
                return redirect($responseData['data']['checkout_url']);
            } else {
                Log::error('Korapay initialization failed', ['response' => $responseData]);
                
                return view('payment.failed', [
                    'reference' => $reference,
                    'message' => $responseData['message'] ?? 'Payment initialization failed. Please try again or contact support.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Korapay API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'api_url' => config('services.korapay.api_url'),
                'has_secret' => !empty(config('services.korapay.secret_key'))
            ]);
            
            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'Payment system error. Please try again later or contact support.'
            ]);
        }
    }

    /**
     * Handle payment for multi-patient booking
     */
    protected function handleBookingPayment($reference)
    {
        // Find booking by reference
        $booking = Booking::with(['doctor', 'invoice.items'])->where('reference', $reference)->first();

        if (!$booking) {
            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'Booking not found. Please contact support.'
            ]);
        }

        // Get the invoice
        $invoice = $booking->invoice;

        if (!$invoice) {
            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'Invoice not found for this booking.'
            ]);
        }

        // Check if payment already made
        if ($invoice->isPaid()) {
            return view('payment.success', [
                'message' => 'This booking has already been paid for.'
            ]);
        }

        // Check if doctor has consultation fee
        if (!$booking->doctor || $booking->total_adjusted_amount <= 0) {
            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'No payment is required for this booking.'
            ]);
        }

        // Generate unique payment reference
        $paymentReference = 'PAY-' . time() . '-' . Str::random(8);

        // Create payment record
        $payment = Payment::create([
            'reference' => $paymentReference,
            'customer_email' => $booking->payer_email,
            'customer_name' => $booking->payer_name,
            'customer_phone' => $booking->payer_mobile,
            'amount' => $booking->total_adjusted_amount,
            'currency' => 'NGN',
            'status' => 'pending',
            'doctor_id' => $booking->doctor_id,
            'metadata' => [
                'booking_reference' => $booking->reference,
                'booking_id' => $booking->id,
                'invoice_id' => $invoice->id,
                'payment_type' => 'multi_patient_booking',
                'number_of_patients' => $booking->patients()->count(),
            ],
        ]);

        // Prepare line items for Korapay
        $lineItems = [];
        foreach ($invoice->items as $item) {
            $lineItems[] = [
                'patient_id' => $item->patient_id,
                'patient_name' => $item->patient->name ?? 'Patient',
                'description' => $item->description,
                'amount' => $item->total_price,
            ];
        }

        // Prepare Korapay API request
        $payload = [
            'amount' => $booking->total_adjusted_amount,
            'redirect_url' => route('payment.callback'),
            'currency' => 'NGN',
            'reference' => $paymentReference,
            'notification_url' => route('payment.webhook'),
            'narration' => 'Multi-patient consultation payment - ' . $booking->doctor->name . ' - Ref: ' . $booking->reference,
            'customer' => [
                'email' => $booking->payer_email,
                'name' => $booking->payer_name,
            ],
            'merchant_bears_cost' => false,
            'metadata' => [
                'booking_reference' => $booking->reference,
                'line_items' => $lineItems,
            ],
        ];

        // Make API call to Korapay
        try {
            $apiUrl = config('services.korapay.api_url');
            $secretKey = config('services.korapay.secret_key');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/charges/initialize', $payload);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === true) {
                // Update payment record with Korapay response
                $payment->update([
                    'checkout_url' => $responseData['data']['checkout_url'] ?? null,
                    'payment_reference' => $responseData['data']['reference'] ?? $paymentReference,
                    'korapay_response' => json_encode($responseData),
                ]);

                // Update invoice
                $invoice->update([
                    'payment_reference' => $paymentReference,
                    'payment_provider' => 'korapay',
                    'status' => 'pending',
                ]);

                // Link payment to booking consultations
                $booking->consultations()->update([
                    'payment_id' => $payment->id,
                    'payment_status' => 'pending',
                ]);

                // Redirect to Korapay checkout
                return redirect($responseData['data']['checkout_url']);
            } else {
                Log::error('Korapay initialization failed for booking', ['response' => $responseData]);

                return view('payment.failed', [
                    'reference' => $reference,
                    'message' => $responseData['message'] ?? 'Payment initialization failed. Please try again or contact support.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Korapay API error for booking', [
                'error' => $e->getMessage(),
                'booking_reference' => $reference
            ]);

            return view('payment.failed', [
                'reference' => $reference,
                'message' => 'Payment system error. Please try again later or contact support.'
            ]);
        }
    }

    /**
     * Verify transaction with Korapay API
     */
    private function verifyTransaction($reference)
    {
        try {
            $apiUrl = config('services.korapay.api_url');
            $secretKey = config('services.korapay.secret_key');
            $fullUrl = $apiUrl . '/charges/' . $reference;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get($fullUrl);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === true) {
                $data = $responseData['data'];
                
                // CRITICAL FIX: Check actual payment status from Korapay
                $paymentStatus = $data['status'] ?? 'pending';
                $isPaymentSuccessful = ($paymentStatus === 'success');
                
                Log::info('Payment verification result', [
                    'reference' => $reference,
                    'payment_status' => $paymentStatus,
                    'is_successful' => $isPaymentSuccessful,
                    'amount' => $data['amount'] ?? null
                ]);
                
                // Update payment record
                $payment = Payment::where('reference', $reference)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => $paymentStatus,
                        'payment_method' => $data['payment_method'] ?? null,
                        'payment_reference' => $data['payment_reference'] ?? $reference,
                        'fee' => $data['fee'] ?? null,
                        'korapay_response' => json_encode($data),
                    ]);

                    // ONLY process if payment status is 'success'
                    if ($isPaymentSuccessful && $payment->metadata && isset($payment->metadata['consultation_id'])) {
                        $consultation = Consultation::find($payment->metadata['consultation_id']);
                        if ($consultation) {
                            // Only update if not already paid to avoid duplicate processing
                            if ($consultation->payment_status !== 'paid') {
                                $consultation->update([
                                    'payment_status' => 'paid',
                                    'payment_id' => $payment->id,
                                ]);
                                
                                Log::info('Consultation marked as paid via verification', [
                                    'consultation_id' => $consultation->id,
                                    'payment_reference' => $reference
                                ]);
                            }
                            
                            // Always check if treatment plan needs to be unlocked (regardless of payment status)
                            if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                                $consultation->unlockTreatmentPlan();
                                
                                // Email will be sent automatically by ConsultationObserver when payment_status changes to 'paid'
                                // No need to send here to avoid duplicates
                                \Illuminate\Support\Facades\Log::info('Treatment plan unlocked via verification, email will be sent by Observer', [
                                        'consultation_id' => $consultation->id,
                                        'payment_reference' => $reference
                                    ]);
                            } else {
                                \Illuminate\Support\Facades\Log::info('Payment already processed for consultation via verification', [
                                    'consultation_id' => $consultation->id,
                                    'payment_reference' => $reference
                                ]);
                            }
                        }
                    } else if (!$isPaymentSuccessful) {
                        Log::warning('Payment verification called but payment not successful', [
                            'reference' => $reference,
                            'payment_status' => $paymentStatus,
                            'consultation_id' => $payment->metadata['consultation_id'] ?? null
                        ]);
                    }
                }

                // CRITICAL: Only return success if payment status is 'success'
                return [
                    'success' => $isPaymentSuccessful,
                    'status' => $paymentStatus,
                    'amount' => $data['amount'] ?? null,
                    'payment_method' => $data['payment_method'] ?? null,
                    'message' => $isPaymentSuccessful ? 'Payment verified successfully' : 'Payment not completed. Status: ' . $paymentStatus
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Verification failed',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment verification error', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Verification system error',
            ];
        }
    }
}
