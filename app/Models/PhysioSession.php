<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysioSession extends Model
{
    protected $fillable = [
        'patient_id',
        'care_plan_id',
        'created_by',
        'session_type',
        'scheduled_at',
        'completed_at',
        'duration_minutes',
        'exercises',
        'findings',
        'treatment_notes',
        'pain_level_before',
        'pain_level_after',
        'mobility_score',
        'status',
        'next_session_plan',
    ];

    protected $casts = [
        'scheduled_at'    => 'datetime',
        'completed_at'    => 'datetime',
        'exercises'       => 'array',
        'duration_minutes'=> 'integer',
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

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())->where('status', 'scheduled');
    }

    // ── Helpers ──

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function painImproved(): bool
    {
        if (!$this->pain_level_before || !$this->pain_level_after) return false;
        return (int) $this->pain_level_after < (int) $this->pain_level_before;
    }
}
