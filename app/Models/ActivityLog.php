<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'changes',
        'ip_address',
        'user_agent',
        'route',
        'metadata',
    ];

    protected $casts = [
        'changes' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action
     * Note: This is a dynamic relationship based on user_type
     * Cannot be eager loaded, access via $log->getUser() instead
     */
    public function getUser()
    {
        if (!$this->user_type || !$this->user_id) {
            return null;
        }

        return match ($this->user_type) {
            'admin' => \App\Models\AdminUser::find($this->user_id),
            'doctor' => \App\Models\Doctor::find($this->user_id),
            'patient' => \App\Models\Patient::find($this->user_id),
            'nurse' => \App\Models\Nurse::find($this->user_id),
            'canvasser' => \App\Models\Canvasser::find($this->user_id),
            'customer_care' => \App\Models\CustomerCare::find($this->user_id),
            'care_giver' => \App\Models\CareGiver::find($this->user_id),
            default => null,
        };
    }

    /**
     * Get user attribute (accessor)
     */
    public function getUserAttribute()
    {
        return $this->getUser();
    }

    /**
     * Get the model that was acted upon
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Scope to filter by user type
     */
    public function scopeForUserType($query, string $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope to filter by action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
