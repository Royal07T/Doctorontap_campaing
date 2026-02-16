<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_id',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'role',
        'permissions',
        'can_impersonate',
        'last_impersonation_at',
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
        'permissions' => 'array',
        'can_impersonate' => 'boolean',
        'last_impersonation_at' => 'datetime',
    ];
    
    /**
     * Update last activity timestamp
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
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
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\AdminVerifyEmail);
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true; // Super admins have all permissions
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get the user associated with this admin
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get activity logs for this admin
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id')
                    ->where('user_type', 'admin');
    }

    /**
     * Get communication templates created by this admin
     */
    public function communicationTemplates()
    {
        return $this->hasMany(CommunicationTemplate::class, 'created_by');
    }
}
