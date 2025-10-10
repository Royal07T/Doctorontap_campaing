<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'gender',
        'specialization',
        'consultation_fee',
        'location',
        'experience',
        'languages',
        'is_available',
        'order',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Scope to get only available doctors
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to get doctors ordered by their order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
