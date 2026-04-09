<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Database-backed audit logging service.
 *
 * Supplements the file-based Auditable trait with structured
 * DB records for compliance dashboards and HIPAA reporting.
 */
class AuditLogService
{
    /**
     * Record a generic audit event
     */
    public function log(
        string $action,
        ?int $patientId = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?array $metadata = null,
    ): AuditLog {
        return AuditLog::record($action, $patientId, $resourceType, $resourceId, $metadata);
    }

    /**
     * Record that a user viewed a patient record
     */
    public function logView(int $patientId, string $resourceType = 'patient_profile', ?int $resourceId = null): AuditLog
    {
        return AuditLog::recordView($patientId, $resourceType, $resourceId);
    }

    /**
     * Record that a vital sign was created / updated
     */
    public function logVitalSign(int $patientId, int $vitalSignId, string $action = 'created'): AuditLog
    {
        return $this->log($action, $patientId, 'vital_sign', $vitalSignId);
    }

    /**
     * Record that an observation was logged
     */
    public function logObservation(int $patientId, int $observationId, string $action = 'created'): AuditLog
    {
        return $this->log($action, $patientId, 'observation', $observationId);
    }

    /**
     * Record that a medication event was logged
     */
    public function logMedication(int $patientId, int $medicationLogId, string $action = 'created'): AuditLog
    {
        return $this->log($action, $patientId, 'medication_log', $medicationLogId);
    }

    /**
     * Record a data export (CSV / PDF)
     */
    public function logExport(int $patientId, string $exportType, ?array $metadata = null): AuditLog
    {
        return $this->log('exported', $patientId, $exportType, null, $metadata);
    }

    /**
     * Record a login or logout event
     */
    public function logAuth(string $action): AuditLog
    {
        return $this->log($action);
    }

    // ──────────────────────────────────────────────
    // Query helpers (for compliance dashboards)
    // ──────────────────────────────────────────────

    /**
     * Get all audit entries for a patient, newest first
     */
    public function getPatientAuditTrail(int $patientId, int $limit = 50)
    {
        return AuditLog::forPatient($patientId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all audit entries for a specific user (any guard type)
     */
    public function getUserActivity(int $userId, string $userType, int $days = 7)
    {
        return AuditLog::byUser($userId, $userType)
            ->recent($days)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Count access events per patient in the last N days (for anomaly detection)
     */
    public function getAccessCountByPatient(int $days = 7)
    {
        return AuditLog::ofAction('viewed')
            ->recent($days)
            ->selectRaw('patient_id, count(*) as access_count')
            ->groupBy('patient_id')
            ->orderByDesc('access_count')
            ->get();
    }
}
