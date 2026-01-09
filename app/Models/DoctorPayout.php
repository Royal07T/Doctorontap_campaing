<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorPayout extends Model
{
    protected $fillable = [
        'doctor_id',
        'consultation_ids',
        'payout_reference',
        'total_consultations_amount',
        'total_consultations_count',
        'doctor_percentage',
        'platform_percentage',
        'amount',
        'platform_fee',
        'currency',
        'status',
        'korapay_response',
        'korapay_reference',
        'period_from',
        'period_to',
        'metadata',
    ];

    protected $casts = [
        'consultation_ids' => 'array',
        'total_consultations_amount' => 'decimal:2',
        'doctor_percentage' => 'decimal:2',
        'platform_percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'korapay_response' => 'array',
        'metadata' => 'array',
        'period_from' => 'date',
        'period_to' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the doctor for this payout
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get all consultations included in this payout
     */
    public function consultations()
    {
        if (empty($this->consultation_ids)) {
            return collect();
        }
        return Consultation::whereIn('id', $this->consultation_ids)->get();
    }

    /**
     * Check if payout is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payout is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payout is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if payout failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope to get successful payouts
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get pending payouts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get failed payouts
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get payouts for a specific doctor
     */
    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Calculate payment details based on consultations
     */
    public static function calculatePayment($consultations, $doctorPercentage = 70)
    {
        $totalAmount = $consultations->sum(function ($consultation) {
            return $consultation->doctor->effective_consultation_fee ?? 0;
        });

        $platformPercentage = 100 - $doctorPercentage;
        $doctorAmount = ($totalAmount * $doctorPercentage) / 100;
        $platformFee = ($totalAmount * $platformPercentage) / 100;

        return [
            'total_consultations_amount' => $totalAmount,
            'total_consultations_count' => $consultations->count(),
            'doctor_percentage' => $doctorPercentage,
            'platform_percentage' => $platformPercentage,
            'doctor_amount' => $doctorAmount,
            'platform_fee' => $platformFee,
        ];
    }
}

