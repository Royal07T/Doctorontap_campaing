<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'consultation_id',
        'reviewer_type',
        'patient_id',
        'doctor_id',
        'reviewee_type',
        'reviewee_doctor_id',
        'reviewee_patient_id',
        'rating',
        'comment',
        'would_recommend',
        'tags',
        'is_published',
        'is_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'rating' => 'integer',
        'would_recommend' => 'boolean',
        'is_published' => 'boolean',
        'is_verified' => 'boolean',
        'tags' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the consultation associated with this review
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the patient reviewer (if reviewer is patient)
     */
    public function patientReviewer(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Get the doctor reviewer (if reviewer is doctor)
     */
    public function doctorReviewer(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Get the doctor being reviewed
     */
    public function revieweeDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewee_doctor_id');
    }

    /**
     * Get the patient being reviewed
     */
    public function revieweePatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'reviewee_patient_id');
    }

    /**
     * Get the admin who verified this review
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    /**
     * Scope to get only published reviews
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to get only verified reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get reviews for a specific doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('reviewee_type', 'doctor')
                     ->where('reviewee_doctor_id', $doctorId);
    }

    /**
     * Scope to get reviews by a specific patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('reviewer_type', 'patient')
                     ->where('patient_id', $patientId);
    }

    /**
     * Scope to get reviews by a specific doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('reviewer_type', 'doctor')
                     ->where('doctor_id', $doctorId);
    }

    /**
     * Get the reviewer's name
     */
    public function getReviewerNameAttribute(): string
    {
        if ($this->reviewer_type === 'patient' && $this->patientReviewer) {
            return $this->patientReviewer->name;
        }
        
        if ($this->reviewer_type === 'doctor' && $this->doctorReviewer) {
            return $this->doctorReviewer->name;
        }
        
        return 'Anonymous';
    }

    /**
     * Get the reviewee's name
     */
    public function getRevieweeNameAttribute(): string
    {
        if ($this->reviewee_type === 'doctor' && $this->revieweeDoctor) {
            return $this->revieweeDoctor->name;
        }
        
        if ($this->reviewee_type === 'patient' && $this->revieweePatient) {
            return $this->revieweePatient->name;
        }
        
        if ($this->reviewee_type === 'platform') {
            return 'DoctorOnTap Platform';
        }
        
        return 'Unknown';
    }

    /**
     * Get star rating as HTML
     */
    public function getStarsHtmlAttribute(): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $html .= '★';
            } else {
                $html .= '☆';
            }
        }
        return $html;
    }
}

