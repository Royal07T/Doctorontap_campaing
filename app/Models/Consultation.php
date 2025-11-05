<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'age',
        'gender',
        'problem',
        'symptoms',
        'medical_documents',
        'severity',
        'emergency_symptoms',
        'consult_mode',
        'doctor_id',
        'canvasser_id',
        'nurse_id',
        'status',
        'payment_status',
        'payment_id',
        'payment_request_sent',
        'payment_request_sent_at',
        'consultation_completed_at',
        'doctor_notes',
        // Medical Format Fields
        'presenting_complaint',
        'history_of_complaint',
        'past_medical_history',
        'family_history',
        'drug_history',
        'social_history',
        'diagnosis',
        'investigation',
        'treatment_plan',
        'prescribed_medications',
        'follow_up_instructions',
        'lifestyle_recommendations',
        'referrals',
        'next_appointment_date',
        'additional_notes',
        'treatment_plan_created',
        'treatment_plan_created_at',
        'treatment_plan_accessible',
        'treatment_plan_accessed_at',
        'payment_required_for_treatment',
        'treatment_plan_unlocked',
        'treatment_plan_unlocked_at',
    ];

    protected $casts = [
        'emergency_symptoms' => 'array',
        'medical_documents' => 'array',
        'prescribed_medications' => 'array',
        'referrals' => 'array',
        'payment_request_sent' => 'boolean',
        'payment_request_sent_at' => 'datetime',
        'consultation_completed_at' => 'datetime',
        'documents_forwarded_at' => 'datetime',
        'next_appointment_date' => 'date',
        'treatment_plan_created' => 'boolean',
        'treatment_plan_created_at' => 'datetime',
        'treatment_plan_accessible' => 'boolean',
        'treatment_plan_accessed_at' => 'datetime',
        'payment_required_for_treatment' => 'boolean',
        'treatment_plan_unlocked' => 'boolean',
        'treatment_plan_unlocked_at' => 'datetime',
    ];

    /**
     * Get the doctor for this consultation
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the payment for this consultation
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the canvasser who registered this consultation
     */
    public function canvasser(): BelongsTo
    {
        return $this->belongsTo(Canvasser::class);
    }

    /**
     * Get the nurse assigned to this consultation
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get full patient name
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check if consultation is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is required
     */
    public function requiresPayment(): bool
    {
        return $this->doctor && $this->doctor->effective_consultation_fee > 0;
    }

    /**
     * Check if paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Get all reviews for this consultation
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if patient has reviewed this consultation
     */
    public function hasPatientReview(): bool
    {
        return $this->reviews()->where('reviewer_type', 'patient')->exists();
    }

    /**
     * Check if doctor has reviewed this consultation
     */
    public function hasDoctorReview(): bool
    {
        return $this->reviews()->where('reviewer_type', 'doctor')->exists();
    }

    /**
     * Check if treatment plan has been created
     */
    public function hasTreatmentPlan(): bool
    {
        return $this->treatment_plan_created && !empty($this->treatment_plan);
    }

    /**
     * Check if treatment plan is accessible to patient
     */
    public function isTreatmentPlanAccessible(): bool
    {
        // STRICT PAYMENT GATING: Only accessible if payment is made AND plan is unlocked
        return $this->treatment_plan_unlocked && $this->isPaid();
    }

    /**
     * Check if payment is required to access treatment plan
     */
    public function requiresPaymentForTreatmentPlan(): bool
    {
        // STRICT PAYMENT GATING: Always require payment unless explicitly disabled
        return $this->payment_required_for_treatment && !$this->isPaid();
    }

    /**
     * Unlock treatment plan (called after successful payment)
     */
    public function unlockTreatmentPlan(): void
    {
        $this->update([
            'treatment_plan_unlocked' => true,
            'treatment_plan_unlocked_at' => now(),
            'treatment_plan_accessible' => true,
        ]);
    }

    /**
     * Mark treatment plan as accessed by patient
     */
    public function markTreatmentPlanAccessed(): void
    {
        if (!$this->treatment_plan_accessed_at) {
            $this->update([
                'treatment_plan_accessed_at' => now(),
            ]);
        }
    }
}
