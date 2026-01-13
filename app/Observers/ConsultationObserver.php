<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Models\Notification;
use App\Services\DoctorPenaltyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TreatmentPlanNotification;
use App\Mail\ReviewRequest;
use App\Mail\PaymentRequest;

class ConsultationObserver
{
    /**
     * Handle the Consultation "updated" event.
     */
    public function updated(Consultation $consultation): void
    {
        // Reload relationships to ensure we have access to patient and booking emails
        $consultation->loadMissing(['patient', 'booking']);
        
        // Check if payment_status changed to 'paid' and treatment plan exists
        $paymentStatusChanged = $consultation->wasChanged('payment_status') && $consultation->payment_status === 'paid';
        
        // Check if treatment plan was just created
        $treatmentPlanCreated = $consultation->wasChanged('treatment_plan_created') && 
                                $consultation->treatment_plan_created === true;
        
        // Send payment request email if treatment plan was just created and payment is NOT made
        if ($treatmentPlanCreated && $consultation->hasTreatmentPlan() && !$consultation->isPaid()) {
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
            
            // Send payment request email
            if ($recipientEmail) {
                try {
                    Mail::to($recipientEmail)->send(new PaymentRequest($consultation));
                    Log::info('Payment request email sent automatically after treatment plan creation', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'email' => $recipientEmail,
                        'payment_status' => $consultation->payment_status
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send automatic payment request email', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'email' => $recipientEmail,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                Log::warning('No email available for payment request notification', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference
                ]);
            }
            
            // Create in-app notification (bell icon) for patient
            if ($consultation->patient_id) {
                try {
                    $fee = $consultation->doctor ? $consultation->doctor->effective_consultation_fee : 5000;
                    Notification::create([
                        'user_type' => 'patient',
                        'user_id' => $consultation->patient_id,
                        'title' => 'Treatment Plan Ready - Payment Required',
                        'message' => "Your treatment plan for consultation (Ref: {$consultation->reference}) is ready! Complete payment of ₦" . number_format($fee, 2) . " to view your treatment plan.",
                        'type' => 'info',
                        'action_url' => patient_url('consultations/' . $consultation->id),
                        'data' => [
                            'consultation_id' => $consultation->id,
                            'consultation_reference' => $consultation->reference,
                            'type' => 'treatment_plan_ready',
                            'payment_required' => true,
                            'fee' => $fee
                        ]
                    ]);
                    Log::info('Treatment plan ready notification created for patient', [
                        'consultation_id' => $consultation->id,
                        'patient_id' => $consultation->patient_id,
                        'reference' => $consultation->reference
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create treatment plan ready notification', [
                        'consultation_id' => $consultation->id,
                        'patient_id' => $consultation->patient_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Send treatment plan email if:
        // 1. Payment status changed to 'paid' AND treatment plan exists, OR
        // 2. Treatment plan was just created AND payment is already made
        if (($paymentStatusChanged || ($treatmentPlanCreated && $consultation->isPaid())) && $consultation->hasTreatmentPlan()) {
            // Ensure treatment plan is unlocked if it exists
            if (!$consultation->treatment_plan_unlocked) {
                $consultation->unlockTreatmentPlan();
                Log::info('Treatment plan unlocked by observer', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'trigger' => $paymentStatusChanged ? 'payment_status_change' : 'treatment_plan_created'
                ]);
            }
            
            // Determine recipient email: check multiple sources
            $recipientEmail = null;
            
            // 1. First try consultation email field
            if (!empty($consultation->email)) {
                $recipientEmail = $consultation->email;
                Log::info('Using consultation email for treatment plan', [
                    'consultation_id' => $consultation->id,
                    'email' => $recipientEmail
                ]);
            }
            // 2. Try patient relationship email
            elseif ($consultation->patient && !empty($consultation->patient->email)) {
                $recipientEmail = $consultation->patient->email;
                Log::info('Using patient email for treatment plan', [
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'email' => $recipientEmail
                ]);
            }
            // 3. Try booking payer email (for multi-patient bookings)
            elseif ($consultation->booking && !empty($consultation->booking->payer_email)) {
                $recipientEmail = $consultation->booking->payer_email;
                Log::info('Using payer email for treatment plan', [
                    'consultation_id' => $consultation->id,
                    'booking_id' => $consultation->booking_id,
                    'payer_email' => $recipientEmail
                ]);
            }
            
            // Send treatment plan notification email
            if ($recipientEmail) {
                try {
                    Mail::to($recipientEmail)->send(new TreatmentPlanNotification($consultation));
                    Log::info('Treatment plan email sent automatically', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'email' => $recipientEmail,
                        'is_payer_email' => empty($consultation->email),
                        'treatment_plan_unlocked' => $consultation->treatment_plan_unlocked,
                        'trigger' => $paymentStatusChanged ? 'payment_status_change' : 'treatment_plan_created'
                    ]);

                    // Create in-app notification (bell icon) for patient
                    // When payment is made OR when treatment plan is created and payment already exists
                    if (($paymentStatusChanged || ($treatmentPlanCreated && $consultation->isPaid())) && $consultation->patient_id) {
                        try {
                            $notificationTitle = $paymentStatusChanged 
                                ? 'Treatment Plan Sent' 
                                : 'Treatment Plan Ready';
                            $notificationMessage = $paymentStatusChanged
                                ? "Your treatment plan for consultation (Ref: {$consultation->reference}) has been sent to your email. Please check your inbox."
                                : "Your treatment plan for consultation (Ref: {$consultation->reference}) is ready! You can view it now.";
                            
                            Notification::create([
                                'user_type' => 'patient',
                                'user_id' => $consultation->patient_id,
                                'title' => $notificationTitle,
                                'message' => $notificationMessage,
                                'type' => 'success',
                                'action_url' => patient_url('consultations/' . $consultation->id),
                                'data' => [
                                    'consultation_id' => $consultation->id,
                                    'consultation_reference' => $consultation->reference,
                                    'type' => 'treatment_plan_ready',
                                    'email' => $recipientEmail
                                ]
                            ]);
                            Log::info('Treatment plan ready notification created for patient', [
                                'consultation_id' => $consultation->id,
                                'patient_id' => $consultation->patient_id,
                                'reference' => $consultation->reference,
                                'trigger' => $paymentStatusChanged ? 'payment_made' : 'treatment_plan_created'
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to create treatment plan ready notification', [
                                'consultation_id' => $consultation->id,
                                'patient_id' => $consultation->patient_id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Send Review Request email immediately after
                    try {
                        Mail::to($recipientEmail)->send(new ReviewRequest($consultation));
                        Log::info('Review request email sent automatically after treatment plan', [
                            'consultation_id' => $consultation->id,
                            'email' => $recipientEmail
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send automatic review request email', [
                            'consultation_id' => $consultation->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                } catch (\Exception $e) {
                    Log::error('Failed to send automatic treatment plan email', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'email' => $recipientEmail,
                        'error' => $e->getMessage(),
                        'trigger' => $paymentStatusChanged ? 'payment_status_change' : 'treatment_plan_created'
                    ]);
                }
            } else {
                Log::warning('No email available for treatment plan notification', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'trigger' => $paymentStatusChanged ? 'payment_status_change' : 'treatment_plan_created'
                ]);
            }
        } elseif ($paymentStatusChanged && !$consultation->hasTreatmentPlan()) {
            // Payment made but no treatment plan yet - notify both patient and doctor
            try {
                // Notification for patient
                if ($consultation->patient_id) {
                    Notification::create([
                        'user_type' => 'patient',
                        'user_id' => $consultation->patient_id,
                        'title' => 'Payment Confirmed',
                        'message' => "Your payment for consultation (Ref: {$consultation->reference}) has been confirmed. The doctor will prepare your treatment plan.",
                        'type' => 'success',
                        'action_url' => patient_url('consultations/' . $consultation->id),
                        'data' => [
                            'consultation_id' => $consultation->id,
                            'consultation_reference' => $consultation->reference,
                            'type' => 'payment_confirmed'
                        ]
                    ]);
                }

                // Notification for doctor
                if ($consultation->doctor_id) {
                    Notification::create([
                        'user_type' => 'doctor',
                        'user_id' => $consultation->doctor_id,
                        'title' => 'Payment Received',
                        'message' => "Payment has been confirmed for consultation (Ref: {$consultation->reference}) with {$consultation->full_name}. You can now prepare the treatment plan.",
                        'type' => 'success',
                        'action_url' => doctor_url('consultations/' . $consultation->id),
                        'data' => [
                            'consultation_id' => $consultation->id,
                            'consultation_reference' => $consultation->reference,
                            'patient_name' => $consultation->full_name,
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
            
            Log::info('Payment status changed to paid but no treatment plan exists yet', [
                'consultation_id' => $consultation->id,
                'reference' => $consultation->reference
            ]);
        }
        
        // Check for missed consultations when consultation is updated
        // This runs in the background and doesn't block the request
        if ($consultation->doctor_id && $consultation->scheduled_at) {
            try {
                $penaltyService = app(DoctorPenaltyService::class);
                // Only check if consultation time has passed and status hasn't changed to completed
                if ($consultation->scheduled_at->isPast() && 
                    !in_array($consultation->status, ['completed', 'cancelled']) &&
                    ($consultation->wasChanged('status') || $consultation->wasChanged('session_status'))) {
                    $penaltyService->checkMissedConsultations($consultation->doctor);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to check missed consultations in observer', [
                    'consultation_id' => $consultation->id,
                    'doctor_id' => $consultation->doctor_id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the request if penalty check fails
            }
        }
    }
}
