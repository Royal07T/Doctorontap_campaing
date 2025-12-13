<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorBankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'bank_name',
        'account_name',
        'account_number',
        'account_type',
        'bank_code',
        'swift_code',
        'is_verified',
        'verified_at',
        'verified_by',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_default' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the doctor that owns the bank account
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the admin who verified this account
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    /**
     * Get payments made to this bank account
     */
    public function payments()
    {
        return $this->hasMany(DoctorPayment::class, 'bank_account_id');
    }

    /**
     * Scope to get only verified bank accounts
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get only default bank accounts
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get masked account number (show only last 4 digits)
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $length = strlen($this->account_number);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return str_repeat('*', $length - 4) . substr($this->account_number, -4);
    }

    /**
     * Mark this account as default and unset others
     */
    public function setAsDefault(): void
    {
        // Unset all other default accounts for this doctor
        self::where('doctor_id', $this->doctor_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }
}
