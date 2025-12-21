<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'code',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all active banks ordered by name
     */
    public static function getActiveBanks()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find bank by code
     */
    public static function findByCode(string $code)
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get bank accounts for this bank
     */
    public function bankAccounts()
    {
        return $this->hasMany(DoctorBankAccount::class, 'bank_code', 'code');
    }
}
