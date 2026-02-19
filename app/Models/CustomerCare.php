<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class CustomerCare extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_id',
        'is_active',
        'created_by',
        'last_login_at',
        'dashboard_preferences',
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
        'dashboard_preferences' => 'array',
    ];

    /**
     * Get consultations handled by this customer care
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'customer_care_id');
    }

    /**
     * Get the user associated with this customer care
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created this customer care
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get all customer interactions handled by this agent
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class, 'agent_id');
    }

    /**
     * Get all support tickets assigned to this agent
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'agent_id');
    }

    /**
     * Get all escalations created by this agent
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(Escalation::class, 'escalated_by');
    }

    /**
     * Get all interaction notes created by this agent
     */
    public function interactionNotes(): HasMany
    {
        return $this->hasMany(InteractionNote::class, 'created_by');
    }

    /**
     * Get all prospects created by this agent
     */
    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'created_by');
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
        $this->notify(new \App\Notifications\CustomerCareVerifyEmail);
    }
    
    /**
     * Update last activity timestamp
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get dashboard preferences with defaults
     */
    public function getDashboardPreferences()
    {
        $defaults = [
            'auto_refresh_interval' => 30, // seconds
            'items_per_page' => 10,
            'show_statistics' => true,
            'show_queue_management' => true,
            'show_team_status' => true,
            'show_performance_metrics' => true,
            'show_activity_feed' => true,
            'show_priority_queue' => true,
            'show_pipeline_metrics' => true,
            'default_view' => 'enhanced', // enhanced or standard
        ];

        $preferences = $this->dashboard_preferences ?? [];
        return array_merge($defaults, $preferences);
    }

    /**
     * Update dashboard preferences
     */
    public function updateDashboardPreferences(array $preferences)
    {
        $current = $this->dashboard_preferences ?? [];
        $merged = array_merge($current, $preferences);
        $this->update(['dashboard_preferences' => $merged]);
        return $this;
    }
}
