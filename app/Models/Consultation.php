<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'reference',
        'patient_id',
        'booking_id',
        'is_multi_patient_booking',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'age',
        'gender',
        'problem',
        'medical_documents',
        'severity',
        'emergency_symptoms',
        'consult_mode',
        'consultation_mode',
        'session_status',
        'started_at',
        'ended_at',
        'doctor_id',
        'canvasser_id',
        'nurse_id',
        'customer_care_id',
        'booked_by_customer_service',
        'booked_by_agent_id',
        'care_giver_id',
        'status',
        'payment_status',
        'payment_id',
        'payment_request_sent',
        'payment_request_sent_at',
        'consultation_completed_at',
        'scheduled_at',
        'consultation_type',
        'requires_payment_first',
        'payment_completed_at',
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
        'treatment_plan_email_sent',
        'treatment_plan_email_sent_at',
        'treatment_plan_email_status',
        'treatment_plan_attachments',
        // Second Opinion Fields
        'service_type',
        'can_escalate_to_full',
        'escalated_from_consultation_id',
        'escalated_at',
        'second_opinion_notes',
        'second_opinion_documents',
    ];

    protected $casts = [
        'is_multi_patient_booking' => 'boolean',
        'emergency_symptoms' => 'array',
        'medical_documents' => 'array',
        'prescribed_medications' => 'array',
        'referrals' => 'array',
        'treatment_plan_attachments' => 'array',
        'payment_request_sent' => 'boolean',
        'booked_by_customer_service' => 'boolean',
        'payment_request_sent_at' => 'datetime',
        'consultation_completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'documents_forwarded_at' => 'datetime',
        'next_appointment_date' => 'date',
        'treatment_plan_created' => 'boolean',
        'treatment_plan_created_at' => 'datetime',
        'treatment_plan_accessible' => 'boolean',
        'treatment_plan_accessed_at' => 'datetime',
        'payment_required_for_treatment' => 'boolean',
        'treatment_plan_unlocked' => 'boolean',
        'treatment_plan_unlocked_at' => 'datetime',
        'treatment_plan_email_sent' => 'boolean',
        'treatment_plan_email_sent_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        // Second Opinion Casts
        'can_escalate_to_full' => 'boolean',
        'escalated_at' => 'datetime',
        'second_opinion_documents' => 'array',
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
     * Get the customer care for this consultation
     */
    public function customerCare(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class);
    }

    /**
     * Get the care giver assigned to this consultation
     */
    public function careGiver(): BelongsTo
    {
        return $this->belongsTo(CareGiver::class);
    }

    /**
     * Get the patient this consultation belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the booking this consultation is part of (for multi-patient bookings)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get all consultation sessions for this consultation
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ConsultationSession::class);
    }

    public function videoRooms(): HasMany
    {
        return $this->hasMany(VideoRoom::class);
    }

    public function activeVideoRoom()
    {
        return $this->videoRooms()->whereIn('status', ['pending', 'active'])->latest()->first();
    }

    /**
     * Get the active session for this consultation
     */
    public function activeSession()
    {
        return $this->sessions()->where('status', 'active')->latest()->first();
    }

    /**
     * Get all notification logs for this consultation
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Get the medical history record for this consultation
     */
    public function medicalHistory(): HasMany
    {
        return $this->hasMany(PatientMedicalHistory::class);
    }

    /**
     * Get treatment plan notification logs
     */
    public function treatmentPlanNotifications(): HasMany
    {
        return $this->hasMany(NotificationLog::class)
            ->where('category', 'treatment_plan')
            ->orderBy('created_at', 'desc');
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
     * Check if consultation is using WhatsApp mode
     */
    public function isWhatsAppMode(): bool
    {
        return $this->consultation_mode === 'whatsapp' || 
               ($this->consultation_mode === null && $this->consult_mode !== null);
    }

    /**
     * Check if consultation is using in-app mode (voice, video, or chat)
     */
    public function isInAppMode(): bool
    {
        return in_array($this->consultation_mode, ['voice', 'video', 'chat']);
    }

    /**
     * Check if session is active
     */
    public function hasActiveSession(): bool
    {
        return $this->session_status === 'active' && $this->activeSession() !== null;
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
     * Get referrals from this consultation (where this is the original consultation)
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'consultation_id');
    }

    /**
     * Get referral that created this consultation (if this consultation was created from a referral)
     */
    public function referralFrom(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'id', 'new_consultation_id');
    }

    /**
     * Check if this consultation has been referred
     */
    public function hasReferral(): bool
    {
        return $this->referrals()->exists();
    }

    /**
     * Check if this consultation was created from a referral
     */
    public function isFromReferral(): bool
    {
        return $this->referralFrom()->exists();
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
     * Check if consultation can proceed (payment must be made first)
     * 
     * @return bool
     */
    public function canProceed(): bool
    {
        // If consultation doesn't require payment, it can proceed
        if (!$this->requiresPayment()) {
            return true;
        }

        // If payment is required, it must be paid before consultation can proceed
        return $this->isPaid();
    }

    /**
     * Check if consultation requires payment before it can start
     * 
     * @return bool
     */
    public function requiresPaymentBeforeStart(): bool
    {
        return $this->requiresPayment() && !$this->isPaid();
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
    
    // ============================================
    // RBAC Query Scopes for Access Control
    // ============================================
    
    /**
     * Scope: Filter consultations for a specific doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
    
    /**
     * Scope: Filter consultations for a specific nurse
     */
    public function scopeForNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }
    
    /**
     * Scope: Filter consultations for a specific patient email
     */
    public function scopeForPatient($query, $patientEmail)
    {
        return $query->where('email', $patientEmail);
    }
    
    /**
     * Scope: Filter consultations for a specific canvasser
     */
    public function scopeForCanvasser($query, $canvasserId)
    {
        return $query->where('canvasser_id', $canvasserId);
    }
    
    /**
     * Scope: Filter consultations based on current authenticated user
     */
    public function scopeForCurrentUser($query)
    {
        $user = auth()->user();
        
        if (!$user) {
            // Try multiple guards
            if (auth()->guard('admin')->check()) {
                return $query; // Admins see all
            } elseif (auth()->guard('doctor')->check()) {
                $user = auth()->guard('doctor')->user();
                return $query->where('doctor_id', $user->id);
            } elseif (auth()->guard('nurse')->check()) {
                $user = auth()->guard('nurse')->user();
                return $query->where('nurse_id', $user->id);
            } elseif (auth()->guard('patient')->check()) {
                $user = auth()->guard('patient')->user();
                return $query->where('email', $user->email);
            } elseif (auth()->guard('canvasser')->check()) {
                $user = auth()->guard('canvasser')->user();
                return $query->where('canvasser_id', $user->id);
            }
            
            return $query->whereRaw('1 = 0'); // Return no results
        }
        
        // Check user type and filter accordingly
        if ($user instanceof \App\Models\Admin) {
            return $query; // Admins see all
        } elseif ($user instanceof \App\Models\Doctor) {
            return $query->where('doctor_id', $user->id);
        } elseif ($user instanceof \App\Models\Nurse) {
            return $query->where('nurse_id', $user->id);
        } elseif ($user instanceof \App\Models\Patient) {
            return $query->where('email', $user->email);
        } elseif ($user instanceof \App\Models\Canvasser) {
            return $query->where('canvasser_id', $user->id);
        }
        
        return $query->whereRaw('1 = 0'); // Return no results if user type unknown
    }

    /**
     * Check if this is part of a multi-patient booking
     */
    public function isPartOfMultiPatientBooking(): bool
    {
        return $this->is_multi_patient_booking && $this->booking_id !== null;
    }

    /**
     * Get invoice item for this consultation (if part of multi-patient booking)
     */
    public function invoiceItem()
    {
        if (!$this->isPartOfMultiPatientBooking()) {
            return null;
        }

        return InvoiceItem::where('consultation_id', $this->id)->first();
    }
    
    /**
     * Check if this is a second opinion consultation
     */
    public function isSecondOpinion(): bool
    {
        return $this->service_type === 'second_opinion';
    }
    
    /**
     * Check if this is a full consultation
     */
    public function isFullConsultation(): bool
    {
        return $this->service_type === 'full_consultation';
    }
    
    /**
     * Check if this consultation can be escalated to a full consultation
     */
    public function canEscalateToFull(): bool
    {
        return $this->isSecondOpinion() 
            && $this->can_escalate_to_full 
            && $this->doctor 
            && $this->doctor->canConductFullConsultation();
    }
    
    /**
     * Escalate second opinion to full consultation
     */
    public function escalateToFullConsultation(): ?self
    {
        if (!$this->canEscalateToFull()) {
            return null;
        }
        
        // Create new full consultation
        $fullConsultation = self::create([
            'reference' => 'FC-' . strtoupper(uniqid()),
            'patient_id' => $this->patient_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'age' => $this->age,
            'gender' => $this->gender,
            'problem' => $this->problem,
            'presenting_complaint' => $this->presenting_complaint,
            'doctor_id' => $this->doctor_id,
            'service_type' => 'full_consultation',
            'escalated_from_consultation_id' => $this->id,
            'escalated_at' => now(),
            'status' => 'pending',
            'consultation_mode' => $this->consultation_mode,
        ]);
        
        // Update original second opinion
        $this->update([
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);
        
        return $fullConsultation;
    }
    
    /**
     * Get the consultation this was escalated from (if any)
     */
    public function escalatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'escalated_from_consultation_id');
    }
    
    /**
     * Get consultations that were escalated from this one
     */
    public function escalatedTo(): HasMany
    {
        return $this->hasMany(self::class, 'escalated_from_consultation_id');
    }
    
    /**
     * Scope: Get only second opinion consultations
     */
    public function scopeSecondOpinions($query)
    {
        return $query->where('service_type', 'second_opinion');
    }
    
    /**
     * Scope: Get only full consultations
     */
    public function scopeFullConsultations($query)
    {
        return $query->where('service_type', 'full_consultation');
    }
}
