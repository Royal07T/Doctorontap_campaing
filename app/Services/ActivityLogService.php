<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Sensitive fields that should be masked in logs
     */
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'secret',
        'ssn',
        'credit_card',
        'bank_account',
        'pin',
    ];

    /**
     * Log an activity
     *
     * @param string $action The action performed (created, updated, deleted, viewed, etc.)
     * @param string|null $modelType The model class name
     * @param int|null $modelId The model ID
     * @param array|null $changes The changes made (for updates)
     * @param array|null $metadata Additional context
     * @return ActivityLog
     */
    public function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $changes = null,
        ?array $metadata = null
    ): ActivityLog {
        $user = Auth::guard('admin')->user() 
            ?? Auth::guard('doctor')->user()
            ?? Auth::guard('patient')->user()
            ?? Auth::guard('nurse')->user()
            ?? Auth::guard('canvasser')->user()
            ?? Auth::guard('customer_care')->user()
            ?? Auth::guard('care_giver')->user()
            ?? Auth::user();

        $userType = $this->getUserType($user);
        $userId = $user?->id ?? 0;

        // Mask sensitive fields in changes
        $maskedChanges = $changes ? $this->maskSensitiveFields($changes) : null;

        try {
            return ActivityLog::create([
                'user_type' => $userType,
                'user_id' => $userId,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'changes' => $maskedChanges,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'route' => request()->route()?->getName(),
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            // Log to Laravel log if activity log fails
            Log::error('Failed to create activity log', [
                'error' => $e->getMessage(),
                'action' => $action,
                'user_type' => $userType,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Log impersonation start
     *
     * @param int $impersonatedUserId
     * @param string $impersonatedUserType
     * @return ActivityLog
     */
    public function logImpersonationStart(int $impersonatedUserId, string $impersonatedUserType): ActivityLog
    {
        return $this->log(
            'impersonated',
            $impersonatedUserType,
            $impersonatedUserId,
            null,
            [
                'impersonation_type' => 'start',
                'impersonated_at' => now()->toIso8601String(),
            ]
        );
    }

    /**
     * Log impersonation end
     *
     * @param int $impersonatedUserId
     * @param string $impersonatedUserType
     * @param int $durationSeconds
     * @return ActivityLog
     */
    public function logImpersonationEnd(int $impersonatedUserId, string $impersonatedUserType, int $durationSeconds): ActivityLog
    {
        return $this->log(
            'impersonation_ended',
            $impersonatedUserType,
            $impersonatedUserId,
            null,
            [
                'impersonation_type' => 'end',
                'duration_seconds' => $durationSeconds,
                'ended_at' => now()->toIso8601String(),
            ]
        );
    }

    /**
     * Log caregiver action with explicit user context
     * Used by policies to log access attempts
     *
     * @param string $action
     * @param int $userId
     * @param string $userType
     * @param int|null $modelId
     * @param array|null $metadata
     * @return ActivityLog
     */
    public function logCaregiverAction(
        string $action,
        int $userId,
        string $userType,
        ?int $modelId = null,
        ?array $metadata = null
    ): ActivityLog {
        try {
            return ActivityLog::create([
                'user_type' => $userType,
                'user_id' => $userId,
                'action' => $action,
                'model_type' => $metadata['model_type'] ?? null,
                'model_id' => $modelId,
                'changes' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'route' => request()->route()?->getName(),
                'metadata' => $this->maskSensitiveFields($metadata ?? []),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create caregiver activity log', [
                'error' => $e->getMessage(),
                'action' => $action,
                'user_type' => $userType,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Get user type string from user model
     *
     * @param mixed $user
     * @return string
     */
    private function getUserType($user): string
    {
        if (!$user) {
            return 'system';
        }

        $class = get_class($user);
        
        if (str_contains($class, 'AdminUser')) {
            return 'admin';
        }
        if (str_contains($class, 'Doctor')) {
            return 'doctor';
        }
        if (str_contains($class, 'Patient')) {
            return 'patient';
        }
        if (str_contains($class, 'Nurse')) {
            return 'nurse';
        }
        if (str_contains($class, 'Canvasser')) {
            return 'canvasser';
        }
        if (str_contains($class, 'CustomerCare')) {
            return 'customer_care';
        }
        if (str_contains($class, 'CareGiver')) {
            return 'caregiver';
        }

        return 'unknown';
    }

    /**
     * Mask sensitive fields in changes array
     *
     * @param array $changes
     * @return array
     */
    private function maskSensitiveFields(array $changes): array
    {
        $masked = [];

        foreach ($changes as $key => $value) {
            $lowerKey = strtolower($key);
            
            // Check if this field should be masked
            $shouldMask = false;
            foreach (self::SENSITIVE_FIELDS as $sensitiveField) {
                if (str_contains($lowerKey, $sensitiveField)) {
                    $shouldMask = true;
                    break;
                }
            }

            if ($shouldMask) {
                $masked[$key] = '***MASKED***';
            } else {
                // For arrays, recursively mask
                if (is_array($value)) {
                    $masked[$key] = $this->maskSensitiveFields($value);
                } else {
                    $masked[$key] = $value;
                }
            }
        }

        return $masked;
    }
}

