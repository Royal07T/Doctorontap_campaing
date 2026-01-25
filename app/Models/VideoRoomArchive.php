<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRoomArchive extends Model
{
    protected $fillable = [
        'video_room_id',
        'vonage_archive_id',
        'status',
        'started_at',
        'stopped_at',
        'duration',
        'size_bytes',
        'download_url',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function videoRoom(): BelongsTo
    {
        return $this->belongsTo(VideoRoom::class);
    }
}
