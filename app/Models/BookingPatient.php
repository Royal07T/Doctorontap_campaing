<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPatient extends Model
{
    protected $fillable = [
        'booking_id',
        'patient_id',
        'consultation_id',
        'relationship_to_payer',
        'base_fee',
        'adjusted_fee',
        'fee_adjustment_reason',
        'fee_adjusted_by',
        'fee_adjusted_at',
        'consultation_status',
        'order_index',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'adjusted_fee' => 'decimal:2',
        'fee_adjusted_at' => 'datetime',
    ];

    /**
     * Get the booking this record belongs to
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the patient
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the consultation for this patient
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the doctor who adjusted the fee
     */
    public function feeAdjustedBy(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'fee_adjusted_by');
    }

    /**
     * Check if fee has been adjusted
     */
    public function hasFeeAdjustment(): bool
    {
        return $this->adjusted_fee != $this->base_fee;
    }

    /**
     * Get the adjustment amount
     */
    public function getAdjustmentAmountAttribute(): float
    {
        return (float) ($this->adjusted_fee - $this->base_fee);
    }
}

