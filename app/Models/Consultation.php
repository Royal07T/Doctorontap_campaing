<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    protected $fillable = [
        'reference',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'age',
        'gender',
        'problem',
        'medical_documents',
        'severity',
        'emergency_symptoms',
        'consult_mode',
        'doctor_id',
        'status',
        'payment_status',
        'payment_id',
        'payment_request_sent',
        'payment_request_sent_at',
        'consultation_completed_at',
    ];

    protected $casts = [
        'emergency_symptoms' => 'array',
        'medical_documents' => 'array',
        'payment_request_sent' => 'boolean',
        'payment_request_sent_at' => 'datetime',
        'consultation_completed_at' => 'datetime',
        'documents_forwarded_at' => 'datetime',
    ];

    /**
     * Get the doctor for this consultation
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the payment for this consultation
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get full patient name
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check if consultation is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is required
     */
    public function requiresPayment(): bool
    {
        return $this->doctor && $this->doctor->consultation_fee > 0;
    }

    /**
     * Check if paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
