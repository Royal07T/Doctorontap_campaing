<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'canvasser_id',
        'has_consulted',
        'total_amount_paid',
        'last_consultation_at',
    ];

    protected $casts = [
        'has_consulted' => 'boolean',
        'total_amount_paid' => 'decimal:2',
        'last_consultation_at' => 'datetime',
    ];

    /**
     * Get the canvasser who registered this patient
     */
    public function canvasser(): BelongsTo
    {
        return $this->belongsTo(Canvasser::class);
    }

    /**
     * Get all vital signs for this patient
     */
    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    /**
     * Get the latest vital signs for this patient
     */
    public function latestVitalSigns()
    {
        return $this->hasOne(VitalSign::class)->latestOfMany();
    }

    /**
     * Calculate BMI if height and weight are available
     */
    public function getBmiAttribute()
    {
        $latestVitals = $this->latestVitalSigns;
        
        if ($latestVitals && $latestVitals->height && $latestVitals->weight) {
            $heightInMeters = $latestVitals->height / 100;
            return round($latestVitals->weight / ($heightInMeters * $heightInMeters), 2);
        }
        
        return null;
    }
}
