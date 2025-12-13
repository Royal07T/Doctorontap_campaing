<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'patient_id',
        'consultation_id',
        'description',
        'quantity',
        'unit_price',
        'adjustment',
        'adjustment_reason',
        'total_price',
        'item_type',
        'order_index',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the invoice this item belongs to
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the patient this item is for
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the consultation this item is for
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Calculate total price
     */
    public function calculateTotal(): float
    {
        return (float) (($this->unit_price * $this->quantity) + $this->adjustment);
    }

    /**
     * Check if item has an adjustment
     */
    public function hasAdjustment(): bool
    {
        return $this->adjustment != 0;
    }
}

