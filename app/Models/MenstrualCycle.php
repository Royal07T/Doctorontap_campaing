<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenstrualCycle extends Model
{
    protected $fillable = [
        'patient_id',
        'start_date',
        'end_date',
        'cycle_length',
        'period_length',
        'notes',
        'symptoms',
        'flow_intensity',
        'spouse_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'symptoms' => 'array',
        'cycle_length' => 'integer',
        'period_length' => 'integer',
    ];

    /**
     * Get the patient that owns this cycle
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Calculate cycle length in days
     */
    public function calculateCycleLength(): ?int
    {
        if (!$this->start_date) {
            return null;
        }

        // Get previous cycle
        $previousCycle = self::where('patient_id', $this->patient_id)
            ->where('start_date', '<', $this->start_date)
            ->orderBy('start_date', 'desc')
            ->first();

        if ($previousCycle) {
            return $this->start_date->diffInDays($previousCycle->start_date);
        }

        return null;
    }

    /**
     * Calculate period length in days
     */
    public function calculatePeriodLength(): ?int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }

        return $this->period_length;
    }
}
