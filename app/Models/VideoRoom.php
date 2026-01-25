<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VideoRoom extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'consultation_id',
        'active_consultation_id',
        'vonage_session_id',
        'status',
        'created_by',
        'started_at',
        'ended_at',
        'duration',
        'participant_count',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $room): void {
            if (empty($room->uuid)) {
                $room->uuid = (string) Str::uuid();
            }
        });
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function archives(): HasMany
    {
        return $this->hasMany(VideoRoomArchive::class);
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended' || $this->ended_at !== null;
    }
}
