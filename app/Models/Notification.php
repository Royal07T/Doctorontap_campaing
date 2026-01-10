<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Events\NotificationCreated;

class Notification extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'read_at',
        'data',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get read notifications
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser(Builder $query, string $userType, int $userId): Builder
    {
        return $query->where('user_type', $userType)
                     ->where('user_id', $userId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            // Dispatch broadcast event when notification is created
            try {
                event(new NotificationCreated($notification));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to broadcast notification', [
                    'error' => $e->getMessage(),
                    'notification_id' => $notification->id,
                ]);
            }
        });
    }
}
