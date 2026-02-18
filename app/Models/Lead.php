<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $table = 'leads';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'source',
        'followup_stage',
        'last_contacted_at',
        'notes',
        'status',
        'interest_type',
        'assigned_to',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
    ];

    const STAGE_NEW = 'new';
    const STAGE_DAY1 = 'day1';
    const STAGE_DAY3 = 'day3';
    const STAGE_DAY7 = 'day7';
    const STAGE_CONVERTED = 'converted';
    const STAGE_LOST = 'lost';

    const STATUS_ACTIVE = 'active';
    const STATUS_CONVERTED = 'converted';
    const STATUS_LOST = 'lost';
    const STATUS_UNRESPONSIVE = 'unresponsive';

    /**
     * Get the admin this lead is assigned to
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'assigned_to');
    }

    // ──────────────────────────────────────────────
    // Follow-up logic
    // ──────────────────────────────────────────────

    /**
     * Advance to the next follow-up stage
     */
    public function advanceStage(): void
    {
        $nextStage = match ($this->followup_stage) {
            self::STAGE_NEW => self::STAGE_DAY1,
            self::STAGE_DAY1 => self::STAGE_DAY3,
            self::STAGE_DAY3 => self::STAGE_DAY7,
            self::STAGE_DAY7 => self::STAGE_LOST, // After Day 7 with no conversion
            default => $this->followup_stage,
        };

        $this->update([
            'followup_stage' => $nextStage,
            'last_contacted_at' => now(),
        ]);
    }

    /**
     * Mark lead as converted
     */
    public function markConverted(): void
    {
        $this->update([
            'followup_stage' => self::STAGE_CONVERTED,
            'status' => self::STATUS_CONVERTED,
        ]);
    }

    /**
     * Is this lead due for follow-up?
     */
    public function isDueForFollowUp(): bool
    {
        if (!in_array($this->followup_stage, [self::STAGE_NEW, self::STAGE_DAY1, self::STAGE_DAY3])) {
            return false;
        }

        $daysSinceContact = $this->last_contacted_at
            ? $this->last_contacted_at->diffInDays(now())
            : $this->created_at->diffInDays(now());

        return match ($this->followup_stage) {
            self::STAGE_NEW => $daysSinceContact >= 1,
            self::STAGE_DAY1 => $daysSinceContact >= 2, // 3 days since creation
            self::STAGE_DAY3 => $daysSinceContact >= 4, // 7 days since creation
            default => false,
        };
    }

    /**
     * Get the preferred channel for the current follow-up stage
     */
    public function getFollowUpChannel(): string
    {
        return match ($this->followup_stage) {
            self::STAGE_NEW => 'whatsapp',   // Day 1 → WhatsApp
            self::STAGE_DAY1 => 'email',     // Day 3 → Email
            self::STAGE_DAY3 => 'sms',       // Day 7 → SMS
            default => 'email',
        };
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDueForFollowUp($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->whereIn('followup_stage', [self::STAGE_NEW, self::STAGE_DAY1, self::STAGE_DAY3]);
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
