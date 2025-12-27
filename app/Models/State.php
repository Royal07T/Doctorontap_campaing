<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get all cities/locations in this state
     */
    public function cities(): HasMany
    {
        return $this->hasMany(Location::class, 'state_id');
    }

    /**
     * Scope to order by name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
