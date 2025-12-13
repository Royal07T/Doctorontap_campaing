<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'reference',
        'booking_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'total_adjustments',
        'total_amount',
        'amount_paid',
        'status',
        'payment_provider',
        'payment_reference',
        'currency',
        'notes',
        'issued_at',
        'due_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_adjustments' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the booking this invoice is for
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get all line items
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order_index');
    }

    /**
     * Recalculate invoice totals from line items
     */
    public function recalculate(): void
    {
        $items = $this->items;
        
        $this->subtotal = $items->sum('unit_price');
        $this->total_adjustments = $items->sum('adjustment');
        $this->total_amount = $items->sum('total_price');
        
        $this->save();
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is partially paid
     */
    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid' || 
               ($this->amount_paid > 0 && $this->amount_paid < $this->total_amount);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) ($this->total_amount - $this->amount_paid));
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(float $amount = null): void
    {
        $this->amount_paid = $amount ?? $this->total_amount;
        $this->status = 'paid';
        $this->paid_at = now();
        $this->save();
    }
}

