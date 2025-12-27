<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'state_id',
    ];

    /**
     * Get the state that owns this location
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get all doctors in this location
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'location', 'name');
    }

    /**
     * Scope to order by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
