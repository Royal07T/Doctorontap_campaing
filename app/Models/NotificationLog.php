<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'consultation_id',
        'consultation_reference',
        'type',
        'category',
        'subject',
        'message',
        'recipient',
        'recipient_name',
        'status',
        'sent_at',
        'delivered_at',
        'failed_at',
        'provider',
        'provider_message_id',
        'provider_response',
        'error_message',
        'retry_count',
        'last_retry_at',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'metadata' => 'array',
        'retry_count' => 'integer',
    ];

    /**
     * Get the consultation for this notification log
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Scope: Get all notifications for a specific consultation
     */
    public function scopeForConsultation($query, $consultationId)
    {
        return $query->where('consultation_id', $consultationId);
    }

    /**
     * Scope: Get notifications by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get notifications by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Get failed notifications
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get delivered notifications
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope: Get pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent($providerMessageId = null, $providerResponse = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => $providerMessageId,
            'provider_response' => $providerResponse,
        ]);
    }

    /**
     * Mark notification as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
        ]);
    }

    /**
     * Check if notification is successful (sent or delivered)
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'delivered' => 'green',
            'sent' => 'blue',
            'failed', 'bounced' => 'red',
            'pending' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'delivered' => 'Delivered ✓',
            'sent' => 'Sent',
            'failed' => 'Failed ✗',
            'bounced' => 'Bounced',
            'pending' => 'Pending...',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get type icon for UI
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            'email' => '✉️',
            'sms' => '💬',
            'whatsapp' => '📱',
            default => '📧',
        };
    }
}
