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
     * Get consultations handled by this customer care
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'customer_care_id');
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
}
