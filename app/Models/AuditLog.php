<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /**
     * Disable default timestamps — we only use created_at
     */
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_type',
        'user_email',
        'patient_id',
        'action',
        'resource_type',
        'resource_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Action constants
     */
    const ACTION_VIEWED = 'viewed';
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_EXPORTED = 'exported';
    const ACTION_ACCESSED = 'accessed';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';

    /**
     * Get the patient related to this audit entry
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    // ──────────────────────────────────────────────
    // Static recording helpers
    // ──────────────────────────────────────────────

    /**
     * Record an audit log entry
     */
    public static function record(
        string $action,
        ?int $patientId = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?array $metadata = null,
    ): static {
        $guard = auth()->guard()->name ?? 'web';
        $user = auth()->user();

        return static::create([
            'user_id' => $user?->id,
            'user_type' => $guard,
            'user_email' => $user?->email ?? 'system',
            'patient_id' => $patientId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Shorthand: record a "viewed" event
     */
    public static function recordView(int $patientId, string $resourceType, ?int $resourceId = null): static
    {
        return static::record(self::ACTION_VIEWED, $patientId, $resourceType, $resourceId);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByUser($query, int $userId, string $userType)
    {
        return $query->where('user_id', $userId)->where('user_type', $userType);
    }

    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
