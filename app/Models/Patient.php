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

class Patient extends Authenticatable
{
    use Notifiable, MustVerifyEmail, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'gender',
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
     * Send email verification notification
     */
    public function sendEmailVerificationNotification()
    {
        $token = $this->generateEmailVerificationToken();
        
        \Illuminate\Support\Facades\Mail::send('emails.patient-verification', [
            'patient' => $this,
            'verificationUrl' => route('patient.verify', ['token' => $token, 'email' => $this->email]),
        ], function ($message) {
            $message->to($this->email)
                   ->subject('Verify Your Email - DoctorOnTap');
        });
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
}
