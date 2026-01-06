<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInteraction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'agent_id',
        'channel',
        'summary',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the user (patient) associated with this interaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    /**
     * Get the agent (customer care) who handled this interaction
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'agent_id');
    }

    /**
     * Get all notes for this interaction
     */
    public function notes(): HasMany
    {
        return $this->hasMany(InteractionNote::class);
    }

    /**
     * Get escalations related to this interaction
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(Escalation::class);
    }

    /**
     * Calculate duration in minutes
     */
    public function getDurationMinutesAttribute(): ?float
    {
        if ($this->duration_seconds) {
            return round($this->duration_seconds / 60, 2);
        }
        return null;
    }

    /**
     * Check if interaction is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark interaction as resolved
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'ended_at' => now(),
        ]);
    }
}
