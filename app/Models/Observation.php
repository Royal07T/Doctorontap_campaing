<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observation extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'observations';

    protected $fillable = [
        'patient_id',
        'caregiver_id',
        'emoji_code',
        'mobility_notes',
        'pain_level',
        'behavior_notes',
        'general_notes',
    ];

    protected $casts = [
        'pain_level' => 'integer',
        'behavior_notes' => 'encrypted', // HIPAA: PHI encrypted at rest
    ];

    /**
     * Emoji mood options available to caregivers
     */
    const MOOD_OPTIONS = [
        'happy'     => ['emoji' => '😊', 'label' => 'Happy'],
        'calm'      => ['emoji' => '😌', 'label' => 'Calm'],
        'neutral'   => ['emoji' => '😐', 'label' => 'Neutral'],
        'anxious'   => ['emoji' => '😰', 'label' => 'Anxious'],
        'sad'       => ['emoji' => '😢', 'label' => 'Sad'],
        'confused'  => ['emoji' => '😵', 'label' => 'Confused'],
        'agitated'  => ['emoji' => '😤', 'label' => 'Agitated'],
        'sleepy'    => ['emoji' => '😴', 'label' => 'Sleepy'],
        'pain'      => ['emoji' => '😣', 'label' => 'In Pain'],
        'cheerful'  => ['emoji' => '😁', 'label' => 'Cheerful'],
    ];

    /**
     * Get the patient this observation belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the caregiver who recorded this observation
     */
    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(CareGiver::class, 'caregiver_id');
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the emoji character for the mood code
     */
    public function getMoodEmojiAttribute(): ?string
    {
        return self::MOOD_OPTIONS[$this->emoji_code]['emoji'] ?? null;
    }

    /**
     * Get the label for the mood code
     */
    public function getMoodLabelAttribute(): ?string
    {
        return self::MOOD_OPTIONS[$this->emoji_code]['label'] ?? null;
    }

    /**
     * Get human-readable pain level description
     */
    public function getPainDescriptionAttribute(): ?string
    {
        if ($this->pain_level === null) {
            return null;
        }

        return match (true) {
            $this->pain_level === 0 => 'No Pain',
            $this->pain_level <= 3 => 'Mild',
            $this->pain_level <= 6 => 'Moderate',
            $this->pain_level <= 8 => 'Severe',
            default => 'Very Severe',
        };
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to a specific patient
     */
    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to a specific caregiver
     */
    public function scopeByCaregiver($query, int $caregiverId)
    {
        return $query->where('caregiver_id', $caregiverId);
    }

    /**
     * Scope to observations recorded today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope to last N days
     */
    public function scopeLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
