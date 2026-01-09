<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Models\DoctorPayout;
use App\Services\KorapayPayoutService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TreatmentPlanNotification;
use App\Mail\ReviewRequest;

class ConsultationObserver
{
    /**
     * Handle the Consultation "updated" event.
     * 
     * Note: Payouts are NOT automatically triggered.
     * Admin must manually create batch payouts via the admin panel,
     * similar to the existing doctor-payments system.
     */
    public function updated(Consultation $consultation): void
    {
        // Check if payment_status changed to 'paid' and treatment plan exists
        if ($consultation->wasChanged('payment_status') && $consultation->payment_status === 'paid') {
            // Reload relationships to ensure we have access to patient and booking emails
            $consultation->loadMissing(['patient', 'booking']);
            
            // Ensure treatment plan is unlocked if it exists
            if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                $consultation->unlockTreatmentPlan();
                Log::info('Treatment plan unlocked by observer after payment status change', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference
                ]);
            }
            
            // Send treatment plan notification email if treatment plan exists (regardless of unlock status)
            // This ensures email is sent even if treatment plan was unlocked before payment_status changed
            if ($consultation->hasTreatmentPlan()) {
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
                        Log::info('Treatment plan email sent automatically after payment', [
                            'consultation_id' => $consultation->id,
                            'reference' => $consultation->reference,
                            'email' => $recipientEmail,
                            'is_payer_email' => empty($consultation->email),
                            'treatment_plan_unlocked' => $consultation->treatment_plan_unlocked
                        ]);

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
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    Log::warning('No email available for treatment plan notification', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference
                    ]);
                }
            } else {
                Log::info('Payment status changed to paid but no treatment plan exists yet', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference
                ]);
            }
        }
    }
}
