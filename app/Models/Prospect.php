<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'location',
        'source',
        'notes',
        'status',
        'created_by',
        'silent_prospect',
    ];

    protected $casts = [
        'silent_prospect' => 'boolean',
    ];

    /**
     * Get the customer care agent who created this prospect
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CustomerCare::class, 'created_by');
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
