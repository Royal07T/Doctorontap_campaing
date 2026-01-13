<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DoctorPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'doctor_id',
        'bank_account_id',
        'total_consultations_amount',
        'total_consultations_count',
        'paid_consultations_count',
        'unpaid_consultations_count',
        'doctor_percentage',
        'platform_percentage',
        'doctor_amount',
        'platform_fee',
        'status',
        'paid_at',
        'paid_by',
        'payment_method',
        'transaction_reference',
        'payment_notes',
        'admin_notes',
        'consultation_ids',
        'period_from',
        'period_to',
        'korapay_reference',
        'korapay_status',
        'korapay_fee',
        'korapay_response',
        'payout_initiated_at',
        'payout_completed_at',
    ];

    protected $casts = [
        'total_consultations_amount' => 'decimal:2',
        'doctor_percentage' => 'decimal:2',
        'platform_percentage' => 'decimal:2',
        'doctor_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'korapay_fee' => 'decimal:2',
        'paid_at' => 'datetime',
        'payout_initiated_at' => 'datetime',
        'payout_completed_at' => 'datetime',
        'consultation_ids' => 'array',
        'korapay_response' => 'array',
        'period_from' => 'date',
        'period_to' => 'date',
    ];

    /**
     * Boot method to generate reference
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->reference)) {
                $payment->reference = 'DOCPAY-' . strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Get the doctor that this payment belongs to
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the bank account for this payment
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(DoctorBankAccount::class, 'bank_account_id');
    }

    /**
     * Get the admin who processed this payment
     */
    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'paid_by');
    }

    /**
     * Get consultations included in this payment
     */
    public function consultations()
    {
        if (empty($this->consultation_ids)) {
            return collect();
        }

        return Consultation::whereIn('id', $this->consultation_ids)->get();
    }

    /**
     * Scope to get pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get payments for a specific doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($adminId, $paymentMethod = null, $transactionRef = null, $notes = null): void
    {
        $wasCompleted = $this->status === 'completed';
        
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'paid_by' => $adminId,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'payment_notes' => $notes,
        ]);
        
        // Send notification to doctor if this is a new completion
        if (!$wasCompleted && $this->doctor_id) {
            try {
                \App\Models\Notification::create([
                    'user_type' => 'doctor',
                    'user_id' => $this->doctor_id,
                    'title' => 'Payment Received',
                    'message' => "Your payment of ₦" . number_format($this->doctor_amount, 2) . " has been completed. Reference: {$this->reference}",
                    'type' => 'success',
                    'action_url' => doctor_url('payment-history'),
                    'data' => [
                        'payment_id' => $this->id,
                        'payment_reference' => $this->reference,
                        'amount' => $this->doctor_amount,
                        'consultation_count' => $this->total_consultations_count,
                        'type' => 'doctor_payment_completed'
                    ]
                ]);
                
                \Illuminate\Support\Facades\Log::info('Doctor payment completion notification created', [
                    'payment_id' => $this->id,
                    'doctor_id' => $this->doctor_id,
                    'amount' => $this->doctor_amount
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to create doctor payment completion notification', [
                    'payment_id' => $this->id,
                    'doctor_id' => $this->doctor_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Calculate payment details based on consultations
     */
    public static function calculatePayment($consultations, $doctorPercentage = 70)
    {
        $totalAmount = $consultations->sum(function ($consultation) {
            return $consultation->doctor->effective_consultation_fee ?? 0;
        });

        $paidCount = $consultations->where('payment_status', 'paid')->count();
        $unpaidCount = $consultations->where('payment_status', '!=', 'paid')->count();

        $platformPercentage = 100 - $doctorPercentage;
        $doctorAmount = ($totalAmount * $doctorPercentage) / 100;
        $platformFee = ($totalAmount * $platformPercentage) / 100;

        return [
            'total_consultations_amount' => $totalAmount,
            'total_consultations_count' => $consultations->count(),
            'paid_consultations_count' => $paidCount,
            'unpaid_consultations_count' => $unpaidCount,
            'doctor_percentage' => $doctorPercentage,
            'platform_percentage' => $platformPercentage,
            'doctor_amount' => $doctorAmount,
            'platform_fee' => $platformFee,
        ];
    }
}
