<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SexualHealthRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'record_date',
        'libido_level',
        'erectile_health_score',
        'ejaculation_issues',
        'ejaculation_notes',
        'last_sti_test_date',
        'next_sti_test_reminder',
        'sti_test_due',
        'notes',
    ];

    protected $casts = [
        'record_date' => 'date',
        'last_sti_test_date' => 'date',
        'next_sti_test_reminder' => 'date',
        'ejaculation_issues' => 'boolean',
        'sti_test_due' => 'boolean',
        'erectile_health_score' => 'integer',
    ];

    /**
     * Get the patient that owns this record
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get libido level label
     */
    public function getLibidoLevelLabelAttribute(): string
    {
        return match($this->libido_level) {
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            default => 'Not Set',
        };
    }

    /**
     * Check if STI test is due (more than 6 months since last test)
     */
    public function checkStiTestDue(): bool
    {
        if (!$this->last_sti_test_date) {
            return true; // Never tested, should get reminder
        }

        return $this->last_sti_test_date->addMonths(6)->isPast();
    }
}
