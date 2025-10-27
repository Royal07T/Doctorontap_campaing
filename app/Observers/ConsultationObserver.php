<?php

namespace App\Observers;

use App\Models\Consultation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TreatmentPlanNotification;

class ConsultationObserver
{
    /**
     * Handle the Consultation "updated" event.
     */
    public function updated(Consultation $consultation): void
    {
        // Check if payment_status changed to 'paid' and treatment plan exists
        if ($consultation->wasChanged('payment_status') && $consultation->payment_status === 'paid') {
            // Check if treatment plan exists and hasn't been sent yet
            if ($consultation->hasTreatmentPlan() && !$consultation->treatment_plan_unlocked) {
                $consultation->unlockTreatmentPlan();
                
                // Send treatment plan notification email
                try {
                    Mail::to($consultation->email)->queue(new TreatmentPlanNotification($consultation));
                    Log::info('Treatment plan email sent automatically after payment', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'email' => $consultation->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send automatic treatment plan email', [
                        'consultation_id' => $consultation->id,
                        'reference' => $consultation->reference,
                        'email' => $consultation->email,
                        'error' => $e->getMessage()
                    ]);
                }
            } elseif ($consultation->hasTreatmentPlan() && $consultation->treatment_plan_unlocked) {
                // If already unlocked, just log it
                Log::info('Consultation already had treatment plan unlocked', [
                    'consultation_id' => $consultation->id,
                    'reference' => $consultation->reference
                ]);
            }
        }
    }
}
