<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'user_type',
        'doctor_id',
        'agent_id',
        'category',
        'subject',
        'description',
        'status',
        'priority',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        do {
            $number = 'TKT-' . strtoupper(Str::random(8));
        } while (static::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Get the user (patient) who created this ticket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    /**
     * Get the doctor who created this ticket
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Get the creator (patient or doctor) based on user_type
     */
    public function creator()
    {
        if ($this->user_type === 'doctor') {
            return $this->doctor;
        }
        return $this->user;
    }

    /**
     * Get the agent assigned to this ticket
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'agent_id');
    }

    /**
     * Get the agent who resolved this ticket
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'resolved_by');
    }

    /**
     * Get escalations for this ticket
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(Escalation::class);
    }

    /**
     * Check if ticket is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if ticket is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if ticket is escalated
     */
    public function isEscalated(): bool
    {
        return $this->status === 'escalated';
    }

    /**
     * Mark ticket as resolved
     */
    public function markAsResolved(?int $resolvedBy = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy ?? auth()->guard('customer_care')->id(),
        ]);
    }

    /**
     * Mark ticket as escalated
     */
    public function markAsEscalated(): void
    {
        $this->update(['status' => 'escalated']);
    }
}
