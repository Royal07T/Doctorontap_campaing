<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Doctor;
use App\Models\Consultation;
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
            
            // Send treatment plan notification email
            \Illuminate\Support\Facades\Mail::to($consultation->email)
                ->send(new \App\Mail\TreatmentPlanNotification($consultation));
            
            return response()->json([
                'success' => true,
                'message' => 'Treatment plan unlocked and email sent successfully'
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
        // Log webhook payload for debugging
        Log::info('Korapay Webhook Received', [
            'event' => $request->input('event'),
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'full_payload' => $request->all()
        ]);

        try {
            // Verify webhook signature for security
            $signature = $request->header('x-korapay-signature');
            $secretKey = config('services.korapay.secret_key');
            
            if ($signature && $secretKey) {
                $expectedSignature = hash_hmac('sha256', json_encode($request->input('data')), $secretKey);
                
                if (!hash_equals($expectedSignature, $signature)) {
                    Log::warning('SECURITY ALERT: Invalid webhook signature', [
                        'expected' => $expectedSignature,
                        'received' => $signature,
                        'ip' => $request->ip(),
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    return response()->json(['status' => 'invalid_signature'], 401);
                }
                
                Log::info('Webhook signature verified successfully');
            } else {
                Log::warning('Webhook received without signature verification', [
                    'has_signature' => !empty($signature),
                    'has_secret' => !empty($secretKey)
                ]);
            }

            $event = $request->input('event');
            $data = $request->input('data');

            // Validate webhook data structure
            if (!$data || !is_array($data)) {
                Log::error('Invalid webhook data structure', ['data' => $data]);
                return response()->json(['status' => 'invalid_data'], 400);
            }

            // Extract reference from webhook data
            $reference = $data['reference'] ?? $data['merchant_reference'] ?? null;
            
            if (!$reference) {
                Log::error('Webhook missing payment reference', ['data' => $data]);
                return response()->json(['status' => 'missing_reference'], 400);
            }

            // Process successful charge event
            if ($event === 'charge.success') {
                Log::info('Processing successful charge', ['reference' => $reference]);
                
                $payment = Payment::where('reference', $reference)->first();

                if (!$payment) {
                    Log::warning('Payment record not found for webhook', ['reference' => $reference]);
                    return response()->json(['status' => 'payment_not_found'], 404);
                }

                // Update payment record
                $payment->update([
                    'status' => 'success',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? $reference,
                    'fee' => $data['fee'] ?? null,
                    'korapay_response' => json_encode($data),
                ]);

                Log::info('Payment record updated', [
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
                    
                    // Update consultation payment status
                    if ($consultation->payment_status !== 'paid') {
                        $consultation->update([
                            'payment_status' => 'paid',
                            'payment_id' => $payment->id,
                        ]);
                        
                        Log::info('Consultation payment status updated to PAID', [
                            'consultation_id' => $consultation->id,
                            'payment_id' => $payment->id
                        ]);
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
                        
                        // Send treatment plan notification email AFTER payment confirmation
                        try {
                            \Illuminate\Support\Facades\Mail::to($consultation->email)
                                ->send(new \App\Mail\TreatmentPlanNotification($consultation));
                            
                            Log::info('Treatment plan notification email sent', [
                                'consultation_id' => $consultation->id,
                                'email' => $consultation->email,
                                'payment_reference' => $reference
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send treatment plan email', [
                                'consultation_id' => $consultation->id,
                                'email' => $consultation->email,
                                'payment_reference' => $reference,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::info('Treatment plan already unlocked or not available', [
                            'consultation_id' => $consultation->id,
                            'has_treatment_plan' => $consultation->hasTreatmentPlan(),
                            'already_unlocked' => $consultation->treatment_plan_unlocked,
                            'payment_reference' => $reference
                        ]);
                    }
                } else {
                    Log::warning('Payment has no consultation metadata', [
                        'payment_id' => $payment->id,
                        'reference' => $reference,
                        'metadata' => $payment->metadata
                    ]);
                }

                Log::info('Webhook processing completed successfully', ['reference' => $reference]);
            } else {
                Log::info('Webhook event ignored (not charge.success)', [
                    'event' => $event,
                    'reference' => $reference
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
     * Handle payment request from email link
     */
    public function handlePaymentRequest($reference)
    {
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
                                
                                // Send treatment plan notification email AFTER payment
                                try {
                                    \Illuminate\Support\Facades\Mail::to($consultation->email)
                                        ->send(new \App\Mail\TreatmentPlanNotification($consultation));
                                    \Illuminate\Support\Facades\Log::info('Treatment plan email sent successfully via verification', [
                                        'consultation_id' => $consultation->id,
                                        'email' => $consultation->email,
                                        'payment_reference' => $reference
                                    ]);
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Failed to send treatment plan email via verification', [
                                        'consultation_id' => $consultation->id,
                                        'email' => $consultation->email,
                                        'payment_reference' => $reference,
                                        'error' => $e->getMessage()
                                    ]);
                                }
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
