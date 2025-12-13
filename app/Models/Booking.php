<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'reference',
        'payer_name',
        'payer_email',
        'payer_mobile',
        'consult_mode',
        'doctor_id',
        'canvasser_id',
        'nurse_id',
        'status',
        'total_amount',
        'total_adjusted_amount',
        'payment_status',
        'scheduled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_adjusted_amount' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the doctor assigned to this booking
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the canvasser who registered this booking
     */
    public function canvasser(): BelongsTo
    {
        return $this->belongsTo(Canvasser::class);
    }

    /**
     * Get the nurse assigned to this booking
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get all patients associated with this booking (many-to-many)
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'booking_patients')
            ->withPivot([
                'consultation_id',
                'relationship_to_payer',
                'base_fee',
                'adjusted_fee',
                'fee_adjustment_reason',
                'fee_adjusted_by',
                'fee_adjusted_at',
                'consultation_status',
                'order_index',
            ])
            ->withTimestamps()
            ->orderBy('booking_patients.order_index');
    }

    /**
     * Get all booking_patients records (pivot table with extra attributes)
     */
    public function bookingPatients(): HasMany
    {
        return $this->hasMany(BookingPatient::class)->orderBy('order_index');
    }

    /**
     * Get all consultations under this booking
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Get the invoice for this booking
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get fee adjustment logs for this booking
     */
    public function feeAdjustmentLogs(): HasMany
    {
        return $this->hasMany(FeeAdjustmentLog::class);
    }

    /**
     * Check if this is a multi-patient booking
     */
    public function isMultiPatient(): bool
    {
        return $this->patients()->count() > 1;
    }

    /**
     * Calculate the total amount based on patient fees
     */
    public function calculateTotal(): float
    {
        $total = $this->bookingPatients()->sum('adjusted_fee');
        
        if ($total == 0) {
            $total = $this->bookingPatients()->sum('base_fee');
        }
        
        return (float) $total;
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if booking is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Get payer full name
     */
    public function getPayerFullNameAttribute(): string
    {
        return $this->payer_name;
    }
}

