<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietPlan extends Model
{
    protected $fillable = [
        'patient_id',
        'care_plan_id',
        'created_by',
        'title',
        'description',
        'meals',
        'restrictions',
        'supplements',
        'target_calories',
        'start_date',
        'end_date',
        'status',
        'dietician_notes',
    ];

    protected $casts = [
        'meals'        => 'array',
        'restrictions' => 'array',
        'supplements'  => 'array',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'target_calories' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function carePlan(): BelongsTo
    {
        return $this->belongsTo(CarePlan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CareGiver::class, 'created_by');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
