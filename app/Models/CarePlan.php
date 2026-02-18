<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarePlan extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'care_plans';

    protected $fillable = [
        'patient_id',
        'plan_type',
        'start_date',
        'expiry_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Plan type constants for feature scoping
     */
    const PLAN_MERIDIAN = 'meridian';
    const PLAN_EXECUTIVE = 'executive';
    const PLAN_SOVEREIGN = 'sovereign';

    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the patient this care plan belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the admin who created this care plan
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    /**
     * Get all caregiver assignments linked to this care plan
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(CaregiverPatientAssignment::class, 'care_plan_id');
    }

    // ──────────────────────────────────────────────
    // Feature-scoping helpers
    // ──────────────────────────────────────────────

    /**
     * Does this plan include physician review?
     */
    public function hasPhysicianReview(): bool
    {
        return in_array($this->plan_type, [self::PLAN_EXECUTIVE, self::PLAN_SOVEREIGN]);
    }

    /**
     * Does this plan include risk flags & weekly reports?
     */
    public function hasWeeklyReports(): bool
    {
        return in_array($this->plan_type, [self::PLAN_EXECUTIVE, self::PLAN_SOVEREIGN]);
    }

    /**
     * Does this plan include the dietician module?
     */
    public function hasDietician(): bool
    {
        return $this->plan_type === self::PLAN_SOVEREIGN;
    }

    /**
     * Does this plan include the physiotherapy module?
     */
    public function hasPhysiotherapy(): bool
    {
        return $this->plan_type === self::PLAN_SOVEREIGN;
    }

    /**
     * Is this plan currently active?
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->expiry_date === null || $this->expiry_date->isFuture());
    }

    /**
     * Check if plan has expired based on date
     */
    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to active plans only
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to a specific plan type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('plan_type', $type);
    }

    /**
     * Scope to plans for a specific patient
     */
    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
