<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Events\NotificationCreated;
use App\Services\PusherBeamsService;
use Illuminate\Support\Facades\Log;

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
            // Dispatch broadcast event when notification is created (WebSocket/Reverb)
            try {
                event(new NotificationCreated($notification));
            } catch (\Exception $e) {
                Log::warning('Failed to broadcast notification', [
                    'error' => $e->getMessage(),
                    'notification_id' => $notification->id,
                ]);
            }

            // Send push notification via Pusher Beams
            try {
                $beamsService = app(PusherBeamsService::class);
                
                if ($beamsService->isEnabled()) {
                    // Create user ID in format: user_type_user_id (e.g., patient_123, doctor_456)
                    $userId = "{$notification->user_type}_{$notification->user_id}";
                    
                    // Prepare notification data
                    $data = [
                        'notification_id' => $notification->id,
                        'type' => $notification->type,
                        'user_type' => $notification->user_type,
                        'user_id' => $notification->user_id,
                    ];
                    
                    // Merge any additional data from the notification
                    if ($notification->data && is_array($notification->data)) {
                        $data = array_merge($data, $notification->data);
                    }
                    
                    // Send push notification
                    $beamsService->publishToUsers(
                        [$userId],
                        $notification->title,
                        $notification->message,
                        $data,
                        $notification->action_url
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send Pusher Beams push notification', [
                    'error' => $e->getMessage(),
                    'notification_id' => $notification->id,
                ]);
            }
        });
    }
}
