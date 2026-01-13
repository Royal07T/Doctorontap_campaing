<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, Auditable;
    
    protected $appends = ['average_rating', 'total_reviews', 'full_name', 'effective_consultation_fee'];

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'gender',
        'password',
        'specialization',
        'consultation_fee',
        'min_consultation_fee',
        'max_consultation_fee',
        'use_default_fee',
        'location',
        'experience',
        'languages',
        'bio',
        'photo',
        'days_of_availability',
        'availability_schedule',
        'availability_start_time',
        'availability_end_time',
        'place_of_work',
        'role',
        'mdcn_license_current',
        'certificate_path',
        'certificate_data',
        'certificate_mime_type',
        'certificate_original_name',
        'mdcn_certificate_verified',
        'mdcn_certificate_verified_at',
        'mdcn_certificate_verified_by',
        'is_available',
        'is_auto_unavailable',
        'missed_consultations_count',
        'last_missed_consultation_at',
        'penalty_applied_at',
        'unavailable_reason',
        'is_approved',
        'approved_by',
        'approved_at',
        'order',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_auto_unavailable' => 'boolean',
        'missed_consultations_count' => 'integer',
        'last_missed_consultation_at' => 'datetime',
        'penalty_applied_at' => 'datetime',
        'is_approved' => 'boolean',
        'mdcn_license_current' => 'boolean',
        'mdcn_certificate_verified' => 'boolean',
        'mdcn_certificate_verified_at' => 'datetime',
        'use_default_fee' => 'boolean',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'approved_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'availability_schedule' => 'array',
    ];

    /**
     * Get the admin who approved this doctor
     */
    public function approvedBy()
    {
        return $this->belongsTo(AdminUser::class, 'approved_by');
    }

    /**
     * Get the admin who verified the MDCN certificate
     */
    public function verifiedByAdmin()
    {
        return $this->belongsTo(AdminUser::class, 'mdcn_certificate_verified_by');
    }

    /**
     * Get all reviews received by this doctor
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewee_doctor_id');
    }

    /**
     * Get all reviews written by this doctor
     */
    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'doctor_id');
    }

    /**
     * Get all bank accounts for this doctor
     */
    public function bankAccounts()
    {
        return $this->hasMany(DoctorBankAccount::class);
    }

    /**
     * Get the default bank account
     */
    public function defaultBankAccount()
    {
        return $this->hasOne(DoctorBankAccount::class)->where('is_default', true);
    }

    /**
     * Get all payments for this doctor
     */
    public function payments()
    {
        return $this->hasMany(DoctorPayment::class);
    }

    /**
     * Get consultations for this doctor
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Get the specialty model for this doctor
     */
    public function specialtyModel()
    {
        if (!$this->specialization) {
            return null;
        }
        
        return Specialty::where('name', $this->specialization)
            ->orWhere('slug', $this->specialization)
            ->first();
    }

    /**
     * Get specialty relationship (using name matching since we use string field)
     */
    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialization', 'name');
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->published()->avg('rating') ?? 0;
    }

    /**
     * Get total number of reviews
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->published()->count();
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
     * Get the full name of the doctor
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->name;
    }

    /**
     * Get the effective consultation fee
     */
    public function getEffectiveConsultationFeeAttribute()
    {
        if ($this->use_default_fee) {
            return Setting::get('default_consultation_fee', 5000);
        }
        return $this->consultation_fee;
    }

    /**
     * Get the consultation fee range
     */
    public function getConsultationFeeRangeAttribute()
    {
        if ($this->use_default_fee) {
            $defaultFee = Setting::get('default_consultation_fee', 5000);
            return '₦' . number_format($defaultFee, 0) . ' (Default)';
        }

        if ($this->min_consultation_fee && $this->max_consultation_fee) {
            return '₦' . number_format($this->min_consultation_fee, 0) . ' - ₦' . number_format($this->max_consultation_fee, 0);
        }
        return $this->consultation_fee ? '₦' . number_format($this->consultation_fee, 0) : 'N/A';
    }

    /**
     * Scope to get only available doctors
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to get only approved doctors
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get pending approval doctors
     */
    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope to get doctors ordered by their order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope to get only General Practitioner/General Practice doctors
     * This is used for patient-facing views where only GP doctors should be shown
     */
    public function scopeGeneralPractitioner($query)
    {
        return $query->where(function($q) {
            $q->whereRaw('LOWER(specialization) LIKE ?', ['%general practitioner%'])
              ->orWhereRaw('LOWER(specialization) LIKE ?', ['%general practice%'])
              ->orWhereRaw('LOWER(specialization) = ?', ['gp'])
              ->orWhereRaw('LOWER(specialization) = ?', ['general practitioner'])
              ->orWhereRaw('LOWER(specialization) = ?', ['general practice']);
        });
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\DoctorVerifyEmail);
    }
    
    /**
     * Update last activity timestamp
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get total earnings from paid consultations
     */
    public function getTotalEarningsAttribute()
    {
        return $this->consultations()
            ->where('payment_status', 'paid')
            ->sum('consultations.id');
    }

    /**
     * Get pending earnings from unpaid consultations
     */
    public function getPendingEarningsAttribute()
    {
        $unpaidConsultations = $this->consultations()
            ->where('status', 'completed')
            ->where('payment_status', '!=', 'paid')
            ->get();

        return $unpaidConsultations->sum(function ($consultation) {
            return $this->effective_consultation_fee;
        });
    }

    /**
     * Get paid consultations count
     */
    public function getPaidConsultationsCountAttribute()
    {
        return $this->consultations()
            ->where('payment_status', 'paid')
            ->count();
    }

    /**
     * Get unpaid consultations count
     */
    public function getUnpaidConsultationsCountAttribute()
    {
        return $this->consultations()
            ->where('status', 'completed')
            ->where('payment_status', '!=', 'paid')
            ->count();
    }

    /**
     * Get total amount paid to doctor
     */
    public function getTotalPaidAmountAttribute()
    {
        return $this->payments()
            ->where('status', 'completed')
            ->sum('doctor_amount');
    }

    /**
     * Calculate doctor's share from consultations
     */
    public function calculateDoctorShare($consultations, $percentage = 70)
    {
        $totalAmount = $consultations->sum(function ($consultation) {
            return $this->effective_consultation_fee;
        });

        return [
            'total_amount' => $totalAmount,
            'doctor_percentage' => $percentage,
            'doctor_share' => ($totalAmount * $percentage) / 100,
            'platform_fee' => ($totalAmount * (100 - $percentage)) / 100,
        ];
    }
}
