<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenstrualDailyLog extends Model
{
    protected $fillable = [
        'patient_id',
        'cycle_id',
        'date',
        'mood',
        'flow',
        'sleep',
        'water',
        'urination',
        'eating_habits',
        'symptoms',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'symptoms' => 'json',
        'sleep' => 'integer',
        'water' => 'integer',
        'urination' => 'integer',
        'eating_habits' => 'integer',
    ];

    /**
     * Get the patient that owns this log
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
