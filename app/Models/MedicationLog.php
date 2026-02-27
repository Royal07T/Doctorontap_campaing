<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationLog extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'medication_logs';

    protected $fillable = [
        'patient_id',
        'caregiver_id',
        'medication_name',
        'dosage',
        'scheduled_time',
        'administered_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'administered_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_GIVEN = 'given';
    const STATUS_MISSED = 'missed';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_REFUSED = 'refused';

    /**
     * Get the patient this medication log belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the caregiver who logged this medication event
     */
    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(CareGiver::class, 'caregiver_id');
    }

    // ──────────────────────────────────────────────
    // Business logic
    // ──────────────────────────────────────────────

    /**
     * Mark medication as administered
     */
    public function markAsGiven(): void
    {
        $this->update([
            'status' => self::STATUS_GIVEN,
            'administered_at' => now(),
        ]);
    }

    /**
     * Mark medication as missed
     */
    public function markAsMissed(): void
    {
        $this->update([
            'status' => self::STATUS_MISSED,
        ]);
    }

    /**
     * Is this medication overdue?
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->scheduled_time->isPast();
    }

    /**
     * Is this medication due within the next N minutes?
     */
    public function isDueSoon(int $minutes = 30): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->scheduled_time->isBetween(now(), now()->addMinutes($minutes));
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByCaregiver($query, int $caregiverId)
    {
        return $query->where('caregiver_id', $caregiverId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeMissed($query)
    {
        return $query->where('status', self::STATUS_MISSED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_time', today());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('scheduled_time', '<', now());
    }

    /**
     * Calculate compliance rate for a patient over a date range
     */
    public static function complianceRate(int $patientId, int $days = 7): float
    {
        $since = now()->subDays($days);

        $total = static::forPatient($patientId)
            ->where('scheduled_time', '>=', $since)
            ->count();

        if ($total === 0) {
            return 100.0;
        }

        $given = static::forPatient($patientId)
            ->where('scheduled_time', '>=', $since)
            ->where('status', self::STATUS_GIVEN)
            ->count();

        return round(($given / $total) * 100, 1);
    }
}
