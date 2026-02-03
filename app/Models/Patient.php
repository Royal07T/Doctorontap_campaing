<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    use Notifiable, MustVerifyEmail, SoftDeletes, Auditable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_id',
        'phone',
        'gender',
        'photo',
        'age',
        'guardian_id',
        'date_of_birth',
        'is_minor',
        'canvasser_id',
        'has_consulted',
        'total_amount_paid',
        'last_consultation_at',
        'consultations_count',
        'email_verification_token',
        'is_verified',
        'verification_sent_at',
        // Medical Information
        'blood_group',
        'genotype',
        'allergies',
        'chronic_conditions',
        'current_medications',
        'surgical_history',
        'family_medical_history',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'height',
        'weight',
        'medical_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_minor' => 'boolean',
        'has_consulted' => 'boolean',
        'total_amount_paid' => 'decimal:2',
        'last_consultation_at' => 'datetime',
        'consultations_count' => 'integer',
        'is_verified' => 'boolean',
        'verification_sent_at' => 'datetime',
    ];

    /**
     * Get the user associated with this patient
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the canvasser who registered this patient
     */
    public function canvasser(): BelongsTo
    {
        return $this->belongsTo(Canvasser::class);
    }

    /**
     * Get all vital signs for this patient
     */
    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    /**
     * Get the latest vital signs for this patient
     */
    public function latestVitalSigns()
    {
        return $this->hasOne(VitalSign::class)->latestOfMany();
    }

    /**
     * Calculate BMI if height and weight are available
     */
    public function getBmiAttribute()
    {
        $latestVitals = $this->latestVitalSigns;
        
        if ($latestVitals && $latestVitals->height && $latestVitals->weight) {
            $heightInMeters = $latestVitals->height / 100;
            return round($latestVitals->weight / ($heightInMeters * $heightInMeters), 2);
        }
        
        return null;
    }

    /**
     * Get all reviews written by this patient
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'patient_id');
    }

    /**
     * Get all reviews received about this patient
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_patient_id');
    }

    /**
     * Get the photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }

        // Check if file exists in public storage
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo)) {
            return \Illuminate\Support\Facades\Storage::url($this->photo);
        }

        return null;
    }

    /**
     * Get all consultations for this patient
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Get all medical histories for this patient
     */
    public function medicalHistories(): HasMany
    {
        return $this->hasMany(PatientMedicalHistory::class);
    }

    /**
     * Get the latest medical history
     */
    public function latestMedicalHistory()
    {
        return $this->hasOne(PatientMedicalHistory::class)
            ->where('is_latest', true)
            ->latestOfMany('consultation_date');
    }

    /**
     * Generate email verification token
     */
    public function generateEmailVerificationToken()
    {
        $this->email_verification_token = \Illuminate\Support\Str::random(64);
        $this->verification_sent_at = now();
        $this->save();
        return $this->email_verification_token;
    }

    /**
     * Verify email with token
     */
    public function verifyEmail($token)
    {
        if ($this->email_verification_token === $token) {
            $this->is_verified = true;
            $this->email_verified_at = now();
            $this->email_verification_token = null;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified()
    {
        return $this->is_verified && $this->email_verified_at !== null;
    }

    /**
     * Check if email is verified (Laravel standard method)
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at) && $this->is_verified;
    }

    /**
     * Mark email as verified (Laravel standard method)
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
            'is_verified' => true,
            'email_verification_token' => null,
        ])->save();
    }

    /**
     * Get the email address that should be used for verification
     * Prefers user email (source of truth) if user relationship exists
     */
    public function getEmailForVerification()
    {
        // Use user email if available (source of truth), otherwise fallback to direct email
        if ($this->user_id && $this->relationLoaded('user') && $this->user) {
            return $this->user->email;
        }
        
        // Fallback to direct email field for backward compatibility
        return $this->attributes['email'] ?? null;
    }

    /**
     * Get email from user relationship if available, otherwise from direct field
     * This is a helper method to access email via user relationship
     */
    public function getEmailFromUser()
    {
        if ($this->user_id && $this->user) {
            return $this->user->email;
        }
        return $this->attributes['email'] ?? null;
    }

    /**
     * Send email verification notification
     * Supports both old token-based and new Laravel standard verification
     */
    public function sendEmailVerificationNotification()
    {
        // Use Laravel's standard notification system if PatientVerifyEmail notification exists
        if (class_exists(\App\Notifications\PatientVerifyEmail::class)) {
            $this->notify(new \App\Notifications\PatientVerifyEmail);
        } else {
            // Fallback to old token-based system
            $token = $this->generateEmailVerificationToken();
            
            \Illuminate\Support\Facades\Mail::send('emails.patient-verification', [
                'patient' => $this,
                'verificationUrl' => route('patient.verify', ['token' => $token, 'email' => $this->email]),
            ], function ($message) {
                $message->to($this->email)
                       ->subject('Verify Your Email - DoctorOnTap');
            });
        }
    }

    // ============================================
    // Multi-Patient Booking Relationships
    // ============================================

    /**
     * Get the guardian of this patient (for minors)
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'guardian_id');
    }

    /**
     * Get all dependents (children/minors) of this patient
     */
    public function dependents(): HasMany
    {
        return $this->hasMany(Patient::class, 'guardian_id');
    }

    /**
     * Get all bookings this patient is part of
     */
    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_patients')
            ->withPivot([
                'consultation_id',
                'relationship_to_payer',
                'base_fee',
                'adjusted_fee',
                'fee_adjustment_reason',
                'consultation_status',
            ])
            ->withTimestamps();
    }

    /**
     * Get all invoice items for this patient
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get all menstrual cycles for this patient
     */
    public function menstrualCycles(): HasMany
    {
        return $this->hasMany(MenstrualCycle::class);
    }

    /**
     * Get the latest menstrual cycle
     */
    public function latestMenstrualCycle()
    {
        return $this->hasOne(MenstrualCycle::class)->latestOfMany();
    }

    /**
     * Get all sexual health records for this patient
     */
    public function sexualHealthRecords(): HasMany
    {
        return $this->hasMany(SexualHealthRecord::class);
    }

    /**
     * Get the latest sexual health record
     */
    public function latestSexualHealthRecord()
    {
        return $this->hasOne(SexualHealthRecord::class)->latestOfMany();
    }

    /**
     * Check if patient is a minor
     */
    public function isMinor(): bool
    {
        return $this->is_minor || $this->age < 18;
    }

    /**
     * Check if patient requires a guardian
     */
    public function requiresGuardian(): bool
    {
        return $this->isMinor() && !$this->guardian_id;
    }

    /**
     * Get patient's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get first name (split from name)
     */
    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return $parts[0] ?? '';
    }

    /**
     * Get last name (split from name)
     */
    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    /**
     * Get all customer interactions for this patient
     */
    public function customerInteractions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class, 'user_id');
    }

    /**
     * Get all support tickets for this patient
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }

    /**
     * Get all caregivers assigned to this patient
     */
    public function assignedCaregivers(): BelongsToMany
    {
        return $this->belongsToMany(CareGiver::class, 'caregiver_patient_assignments', 'patient_id', 'caregiver_id')
            ->withPivot(['role', 'status', 'care_plan_id', 'assigned_by'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }

    /**
     * Get all caregiver assignments (including inactive)
     */
    public function caregiverAssignments(): HasMany
    {
        return $this->hasMany(CaregiverPatientAssignment::class, 'patient_id');
    }

    /**
     * Check if a specific caregiver is assigned to this patient
     */
    public function hasCaregiver(int $caregiverId, string $role = null): bool
    {
        $query = $this->assignedCaregivers()->where('care_givers.id', $caregiverId);
        
        if ($role) {
            $query->wherePivot('role', $role);
        }
        
        return $query->exists();
    }

    /**
     * Get the patient's age, calculated from date_of_birth if available.
     * Fallback to stored age field if DOB is missing.
     */
    public function getAgeAttribute($value)
    {
        if ($this->date_of_birth) {
            return $this->date_of_birth->age;
        }
        return $value;
    }

    /**
     * Get primary caregiver for this patient
     */
    public function primaryCaregiver()
    {
        return $this->assignedCaregivers()
            ->wherePivot('role', 'primary')
            ->first();
    }
}
