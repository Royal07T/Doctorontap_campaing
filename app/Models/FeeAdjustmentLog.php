<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeAdjustmentLog extends Model
{
    public $timestamps = false; // Only created_at

    protected $fillable = [
        'booking_id',
        'patient_id',
        'invoice_item_id',
        'adjusted_by_type',
        'adjusted_by_id',
        'old_amount',
        'new_amount',
        'adjustment_reason',
        'total_invoice_before',
        'total_invoice_after',
        'notification_sent_to_payer',
        'notification_sent_to_accountant',
        'created_at',
    ];

    protected $casts = [
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'total_invoice_before' => 'decimal:2',
        'total_invoice_after' => 'decimal:2',
        'notification_sent_to_payer' => 'boolean',
        'notification_sent_to_accountant' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Get the booking
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
     * Get the invoice item
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    /**
     * Get the adjustment difference
     */
    public function getDifferenceAttribute(): float
    {
        return (float) ($this->new_amount - $this->old_amount);
    }

    /**
     * Get the person who made the adjustment (polymorphic)
     */
    public function adjustedBy()
    {
        $class = match($this->adjusted_by_type) {
            'doctor' => Doctor::class,
            'admin' => Admin::class,
            default => null,
        };

        return $class ? $class::find($this->adjusted_by_id) : null;
    }
}

