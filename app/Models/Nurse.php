<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Nurse extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, Auditable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_id',
        'is_active',
        'created_by',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get consultations attended by this nurse
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Get vital signs recorded by this nurse
     */
    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    /**
     * Get the user associated with this nurse
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created this nurse
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
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
     */
    public function getEmailFromUser()
    {
        if ($this->user_id && $this->user) {
            return $this->user->email;
        }
        return $this->attributes['email'] ?? null;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\NurseVerifyEmail);
    }
    
    /**
     * Update last activity timestamp
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }
}
