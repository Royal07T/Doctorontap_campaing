<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Escalation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'support_ticket_id',
        'customer_interaction_id',
        'escalated_by',
        'escalated_to_type',
        'escalated_to_id',
        'reason',
        'status',
        'outcome',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the support ticket (if escalated from ticket)
     */
    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    /**
     * Get the customer interaction (if escalated from interaction)
     */
    public function customerInteraction(): BelongsTo
    {
        return $this->belongsTo(CustomerInteraction::class);
    }

    /**
     * Get the agent who escalated
     */
    public function escalatedBy(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'escalated_by');
    }

    /**
     * Get the entity this was escalated to (polymorphic)
     */
    public function escalatedTo(): MorphTo
    {
        return $this->morphTo('escalated_to', 'escalated_to_type', 'escalated_to_id');
    }

    /**
     * Get the admin user if escalated to admin
     */
    public function escalatedToAdmin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'escalated_to_id')
            ->where('escalated_to_type', 'admin');
    }

    /**
     * Get the doctor if escalated to doctor
     */
    public function escalatedToDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'escalated_to_id')
            ->where('escalated_to_type', 'doctor');
    }

    /**
     * Check if escalation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if escalation is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Mark escalation as resolved
     */
    public function markAsResolved(string $outcome): void
    {
        $this->update([
            'status' => 'resolved',
            'outcome' => $outcome,
            'resolved_at' => now(),
        ]);
    }
}
