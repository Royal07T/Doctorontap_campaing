<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Specialty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all doctors with this specialty
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'specialization', 'name');
    }

    /**
     * Scope to get only active specialties
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
