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
            $apiUrl = env('KORAPAY_API_URL');
            $secretKey = env('KORAPAY_SECRET_KEY');
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
                'api_url' => env('KORAPAY_API_URL'),
                'has_secret' => !empty(env('KORAPAY_SECRET_KEY'))
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment system error. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle payment callback (redirect URL)
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('consultation.index')
                ->with('error', 'Payment reference not found');
        }

        // Verify the transaction
        $verificationResult = $this->verifyTransaction($reference);

        if ($verificationResult['success']) {
            $payment = Payment::where('reference', $reference)->first();
            
            // Handle treatment plan unlocking for callback payments
            if ($payment && $payment->metadata && isset($payment->metadata['consultation_id'])) {
                $consultation = Consultation::find($payment->metadata['consultation_id']);
                if ($consultation) {
                    // Only update if not already paid to avoid duplicate processing
                    if ($consultation->payment_status !== 'paid') {
                        $consultation->update([
                            'payment_status' => 'paid',
                            'payment_id' => $payment->id,
                        ]);
                    }
                    
                    // Always check if treatment plan needs to be unlocked (regardless of payment status)
                    if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                        $consultation->unlockTreatmentPlan();
                        
                        // Send treatment plan notification email
                        try {
                            \Illuminate\Support\Facades\Mail::to($consultation->email)
                                ->send(new \App\Mail\TreatmentPlanNotification($consultation));
                            \Illuminate\Support\Facades\Log::info('Treatment plan email sent successfully via callback', [
                                'consultation_id' => $consultation->id,
                                'email' => $consultation->email,
                                'payment_reference' => $reference
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send treatment plan email via callback', [
                                'consultation_id' => $consultation->id,
                                'email' => $consultation->email,
                                'payment_reference' => $reference,
                                'error' => $e->getMessage()
                            ]);
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::info('Payment already processed for consultation', [
                            'consultation_id' => $consultation->id,
                            'payment_reference' => $reference
                        ]);
                    }
                }
            }
            
            return view('payment.success', [
                'payment' => $payment,
                'message' => 'Payment successful! Your treatment plan has been sent to your email.'
            ]);
        } else {
            return view('payment.failed', [
                'reference' => $reference,
                'message' => $verificationResult['message'] ?? 'Payment verification failed'
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
     */
    public function webhook(Request $request)
    {
        // Log webhook payload for debugging
        Log::info('Korapay Webhook Received', ['payload' => $request->all()]);

        // Verify webhook signature for security
        $signature = $request->header('x-korapay-signature');
        $secretKey = env('KORAPAY_SECRET_KEY');
        
        Log::info('Korapay Webhook', [
            'has_signature' => !empty($signature),
            'has_secret' => !empty($secretKey),
            'data' => $request->all()
        ]);
        
        if ($signature && $secretKey) {
            $expectedSignature = hash_hmac('sha256', json_encode($request->input('data')), $secretKey);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Invalid webhook signature', [
                    'expected' => $expectedSignature,
                    'received' => $signature
                ]);
                return response()->json(['status' => 'invalid_signature'], 400);
            }
        }

        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success' && $data) {
            $reference = $data['reference'];
            $payment = Payment::where('reference', $reference)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'success',
                    'payment_method' => $data['payment_method'] ?? null,
                    'payment_reference' => $data['payment_reference'] ?? $reference,
                    'fee' => $data['fee'] ?? null,
                    'korapay_response' => json_encode($data),
                ]);

                // Update consultation payment status and unlock treatment plan
                if ($payment->metadata && isset($payment->metadata['consultation_id'])) {
                    $consultation = Consultation::find($payment->metadata['consultation_id']);
                    if ($consultation) {
                        // Only update if not already paid to avoid duplicate processing
                        if ($consultation->payment_status !== 'paid') {
                            $consultation->update([
                                'payment_status' => 'paid',
                                'payment_id' => $payment->id,
                            ]);
                        }
                        
                        // Always check if treatment plan needs to be unlocked (regardless of payment status)
                        if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                            $consultation->unlockTreatmentPlan();
                            
                            // Send treatment plan notification email AFTER payment
                            try {
                                \Illuminate\Support\Facades\Mail::to($consultation->email)
                                    ->send(new \App\Mail\TreatmentPlanNotification($consultation));
                                \Illuminate\Support\Facades\Log::info('Treatment plan email sent successfully via webhook', [
                                    'consultation_id' => $consultation->id,
                                    'email' => $consultation->email,
                                    'payment_reference' => $reference
                                ]);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Failed to send treatment plan email via webhook', [
                                    'consultation_id' => $consultation->id,
                                    'email' => $consultation->email,
                                    'payment_reference' => $reference,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        } else {
                            \Illuminate\Support\Facades\Log::info('Payment already processed for consultation via webhook', [
                                'consultation_id' => $consultation->id,
                                'payment_reference' => $reference
                            ]);
                        }
                    }
                }

                Log::info('Payment updated successfully', ['reference' => $reference]);
            }
        }

        // Always return 200 to acknowledge receipt
        return response()->json(['status' => 'success']);
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
            $apiUrl = env('KORAPAY_API_URL');
            $secretKey = env('KORAPAY_SECRET_KEY');
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
                'api_url' => env('KORAPAY_API_URL'),
                'has_secret' => !empty(env('KORAPAY_SECRET_KEY'))
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
            $apiUrl = env('KORAPAY_API_URL');
            $secretKey = env('KORAPAY_SECRET_KEY');
            $fullUrl = $apiUrl . '/charges/' . $reference;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get($fullUrl);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === true) {
                $data = $responseData['data'];
                
                // Update payment record
                $payment = Payment::where('reference', $reference)->first();
                
                if ($payment) {
                    $payment->update([
                        'status' => $data['status'],
                        'payment_method' => $data['payment_method'] ?? null,
                        'payment_reference' => $data['payment_reference'] ?? $reference,
                        'fee' => $data['fee'] ?? null,
                        'korapay_response' => json_encode($data),
                    ]);

                    // Update consultation payment status if metadata has consultation_id
                    if ($payment->metadata && isset($payment->metadata['consultation_id'])) {
                        $consultation = Consultation::find($payment->metadata['consultation_id']);
                        if ($consultation && $data['status'] === 'success') {
                            // Only update if not already paid to avoid duplicate processing
                            if ($consultation->payment_status !== 'paid') {
                                $consultation->update([
                                    'payment_status' => 'paid',
                                    'payment_id' => $payment->id,
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
                    }
                }

                return [
                    'success' => true,
                    'status' => $data['status'],
                    'amount' => $data['amount'],
                    'payment_method' => $data['payment_method'] ?? null,
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
