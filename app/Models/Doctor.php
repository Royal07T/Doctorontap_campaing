<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Doctor extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

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
        'days_of_availability',
        'place_of_work',
        'role',
        'mdcn_license_current',
        'certificate_path',
        'certificate_data',
        'certificate_mime_type',
        'certificate_original_name',
        'is_available',
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
        'is_approved' => 'boolean',
        'mdcn_license_current' => 'boolean',
        'use_default_fee' => 'boolean',
        'last_login_at' => 'datetime',
        'approved_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the admin who approved this doctor
     */
    public function approvedBy()
    {
        return $this->belongsTo(AdminUser::class, 'approved_by');
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
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\DoctorVerifyEmail);
    }
}
