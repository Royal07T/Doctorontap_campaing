<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaregiverPatientAssignment extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'caregiver_patient_assignments';

    protected $fillable = [
        'caregiver_id',
        'patient_id',
        'care_plan_id',
        'role',
        'status',
        'assigned_by',
    ];

    protected $casts = [
        'care_plan_id' => 'integer',
        'status' => 'string',
        'role' => 'string',
    ];

    /**
     * Get the caregiver
     */
    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(CareGiver::class, 'caregiver_id');
    }

    /**
     * Get the patient
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the admin who assigned this
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'assigned_by');
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if caregiver has primary role
     */
    public function isPrimary(): bool
    {
        return $this->role === 'primary';
    }

    /**
     * Check if caregiver has read-only role (backup)
     */
    public function isReadOnly(): bool
    {
        return $this->role === 'backup';
    }
}
