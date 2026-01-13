<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationChatMessage extends Model
{
    protected $fillable = [
        'consultation_session_id',
        'message',
        'message_type',
        'sender_type',
        'sender_id',
        'sender_name',
        'vonage_message_id',
        'file_url',
        'file_name',
        'file_type',
        'file_size',
        'is_read',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Get the consultation session this message belongs to
     */
    public function consultationSession(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class);
    }

    /**
     * Get the sender (doctor or patient)
     */
    public function sender()
    {
        if ($this->sender_type === 'doctor') {
            return $this->belongsTo(Doctor::class, 'sender_id');
        }
        return $this->belongsTo(Patient::class, 'sender_id');
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Check if message has file attachment
     */
    public function hasAttachment(): bool
    {
        return !empty($this->file_url);
    }

    /**
     * Check if message is an image
     */
    public function isImage(): bool
    {
        return $this->message_type === 'image' || 
               ($this->file_type && strpos($this->file_type, 'image/') === 0);
    }

    /**
     * Check if message is a file
     */
    public function isFile(): bool
    {
        return $this->message_type === 'file' && !$this->isImage();
    }
}
