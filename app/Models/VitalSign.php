<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VitalSign extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'patient_id',
        'nurse_id',
        'blood_pressure',
        'oxygen_saturation',
        'temperature',
        'blood_sugar',
        'height',
        'weight',
        'heart_rate',
        'respiratory_rate',
        'notes',
        'email_sent',
        'email_sent_at',
        'is_walk_in',
    ];

    protected $casts = [
        'oxygen_saturation' => 'decimal:2',
        'temperature' => 'decimal:2',
        'blood_sugar' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'is_walk_in' => 'boolean',
    ];

    /**
     * Get the patient these vital signs belong to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the nurse who recorded these vital signs
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Calculate BMI
     */
    public function getBmiAttribute()
    {
        if ($this->height && $this->weight) {
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }
        
        return null;
    }

    /**
     * Get blood pressure interpretation
     */
    public function getBloodPressureInterpretationAttribute()
    {
        if (!$this->blood_pressure) {
            return null;
        }

        $parts = explode('/', $this->blood_pressure);
        if (count($parts) !== 2) {
            return 'Invalid';
        }

        $systolic = (int) $parts[0];
        $diastolic = (int) $parts[1];

        if ($systolic < 120 && $diastolic < 80) {
            return 'Normal';
        } elseif ($systolic < 130 && $diastolic < 80) {
            return 'Elevated';
        } elseif ($systolic < 140 || $diastolic < 90) {
            return 'High Blood Pressure (Stage 1)';
        } elseif ($systolic >= 140 || $diastolic >= 90) {
            return 'High Blood Pressure (Stage 2)';
        } elseif ($systolic >= 180 || $diastolic >= 120) {
            return 'Hypertensive Crisis';
        }

        return 'Unknown';
    }
}
