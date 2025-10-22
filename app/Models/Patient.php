<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail;

class Patient extends Authenticatable
{
    use Notifiable, MustVerifyEmail;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'age',
        'canvasser_id',
        'has_consulted',
        'total_amount_paid',
        'last_consultation_at',
        'consultations_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'has_consulted' => 'boolean',
        'total_amount_paid' => 'decimal:2',
        'last_consultation_at' => 'datetime',
        'consultations_count' => 'integer',
    ];

    /**
     * Get the canvasser who registered this patient
     */
    public function canvasser(): BelongsTo
    {
        return $this->belongsTo(Canvasser::class);
    }

    /**
     * Get all vital signs for this patient
     */
    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    /**
     * Get the latest vital signs for this patient
     */
    public function latestVitalSigns()
    {
        return $this->hasOne(VitalSign::class)->latestOfMany();
    }

    /**
     * Calculate BMI if height and weight are available
     */
    public function getBmiAttribute()
    {
        $latestVitals = $this->latestVitalSigns;
        
        if ($latestVitals && $latestVitals->height && $latestVitals->weight) {
            $heightInMeters = $latestVitals->height / 100;
            return round($latestVitals->weight / ($heightInMeters * $heightInMeters), 2);
        }
        
        return null;
    }

    /**
     * Get all reviews written by this patient
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'patient_id');
    }

    /**
     * Get all reviews received about this patient
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_patient_id');
    }
}
