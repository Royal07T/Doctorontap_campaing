<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'reference',
        'customer_email',
        'customer_name',
        'customer_phone',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_reference',
        'fee',
        'checkout_url',
        'metadata',
        'korapay_response',
        'doctor_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the doctor associated with the payment
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
