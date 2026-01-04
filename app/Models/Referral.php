<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'consultation_id',
        'referring_doctor_id',
        'referred_to_doctor_id',
        'reason',
        'notes',
        'new_consultation_id',
        'status',
        'accepted_at',
        'completed_at',
        'declined_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    /**
     * Get the original consultation
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    /**
     * Get the referring doctor
     */
    public function referringDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'referring_doctor_id');
    }

    /**
     * Get the doctor being referred to
     */
    public function referredToDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'referred_to_doctor_id');
    }

    /**
     * Get the new consultation created for the referred doctor
     */
    public function newConsultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'new_consultation_id');
    }

    /**
     * Check if referral is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if referral is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if referral is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if referral is declined
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Mark referral as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark referral as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark referral as declined
     */
    public function markAsDeclined(): void
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }
}
