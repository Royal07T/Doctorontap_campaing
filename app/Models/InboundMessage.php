<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundMessage extends Model
{
    protected $fillable = [
        'message_uuid',
        'message_id',
        'channel',
        'message_type',
        'from_number',
        'to_number',
        'message_text',
        'media_url',
        'media_type',
        'media_caption',
        'media_name',
        'latitude',
        'longitude',
        'location_name',
        'location_address',
        'contact_data',
        'status',
        'received_at',
        'processed_at',
        'raw_data',
        'consultation_id',
        'patient_id',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'contact_data' => 'array',
        'raw_data' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the consultation this message is linked to
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the patient this message is from
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Check if message has media
     */
    public function hasMedia(): bool
    {
        return !empty($this->media_url);
    }

    /**
     * Check if message is a text message
     */
    public function isText(): bool
    {
        return $this->message_type === 'text';
    }

    /**
     * Check if message is an image
     */
    public function isImage(): bool
    {
        return $this->message_type === 'image';
    }

    /**
     * Check if message is a video
     */
    public function isVideo(): bool
    {
        return $this->message_type === 'video';
    }

    /**
     * Check if message is audio
     */
    public function isAudio(): bool
    {
        return $this->message_type === 'audio';
    }

    /**
     * Check if message is a file/document
     */
    public function isFile(): bool
    {
        return $this->message_type === 'file' || $this->message_type === 'document';
    }

    /**
     * Check if message is a location
     */
    public function isLocation(): bool
    {
        return $this->message_type === 'location';
    }

    /**
     * Check if message is a contact
     */
    public function isContact(): bool
    {
        return $this->message_type === 'contact';
    }

    /**
     * Mark message as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark message as replied
     */
    public function markAsReplied(): void
    {
        $this->update([
            'status' => 'replied',
        ]);
    }
}
