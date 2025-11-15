<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logActivity($model, 'created');
        });

        static::updated(function ($model) {
            self::logActivity($model, 'updated', $model->getDirty());
        });

        static::deleted(function ($model) {
            self::logActivity($model, 'deleted');
        });
    }

    /**
     * Log PHI access activity
     *
     * @param mixed $model
     * @param string $action
     * @param array $changes
     * @return void
     */
    protected static function logActivity($model, string $action, array $changes = [])
    {
        try {
            $guard = auth()->guard()->name ?? 'web';
            $user = auth()->user();
            
            Log::channel('audit')->info('PHI Access', [
                'action' => $action,
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_type' => $guard,
                'user_id' => $user?->id ?? 0,
                'user_email' => $user?->email ?? 'system',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'changes' => !empty($changes) ? array_keys($changes) : null,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            // Fail silently - don't break the application
            Log::error('Audit logging failed', [
                'error' => $e->getMessage(),
                'model' => get_class($model),
            ]);
        }
    }

    /**
     * Log when a model is viewed
     *
     * @return void
     */
    public function logViewed()
    {
        self::logActivity($this, 'viewed');
    }
}

