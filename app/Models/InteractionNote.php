<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InteractionNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_interaction_id',
        'created_by',
        'note',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    /**
     * Get the interaction this note belongs to
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(CustomerInteraction::class, 'customer_interaction_id');
    }

    /**
     * Get the agent who created this note
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'created_by');
    }

    /**
     * Check if note is internal (not visible to patients)
     */
    public function isInternal(): bool
    {
        return $this->is_internal;
    }
}
