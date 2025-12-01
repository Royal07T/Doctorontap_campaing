<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalHistory extends Model
{
    protected $fillable = [
        'patient_id',
        'patient_email',
        'patient_name',
        'patient_mobile',
        'consultation_id',
        'consultation_reference',
        'doctor_id',
        // Medical History
        'presenting_complaint',
        'history_of_complaint',
        'past_medical_history',
        'family_history',
        'drug_history',
        'social_history',
        'allergies',
        // Diagnosis & Treatment
        'diagnosis',
        'investigation',
        'treatment_plan',
        'prescribed_medications',
        'follow_up_instructions',
        'lifestyle_recommendations',
        'referrals',
        'next_appointment_date',
        'additional_notes',
        // Vital Signs
        'blood_pressure',
        'temperature',
        'heart_rate',
        'respiratory_rate',
        'weight',
        'height',
        'bmi',
        'oxygen_saturation',
        // Metadata
        'consultation_date',
        'severity',
        'is_latest',
    ];

    protected $casts = [
        'prescribed_medications' => 'array',
        'referrals' => 'array',
        'next_appointment_date' => 'date',
        'consultation_date' => 'date',
        'is_latest' => 'boolean',
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
    ];

    /**
     * Get the patient this history belongs to
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the consultation this history is from
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Get the doctor who created this history
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Scope: Get latest medical history for patient
     */
    public function scopeLatest($query)
    {
        return $query->where('is_latest', true)->orderBy('consultation_date', 'desc');
    }

    /**
     * Scope: For specific patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: By patient email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('patient_email', $email);
    }

    /**
     * Get consolidated medical history for patient
     */
    public static function getConsolidatedHistory($patientIdentifier): array
    {
        $histories = is_numeric($patientIdentifier)
            ? static::forPatient($patientIdentifier)->orderBy('consultation_date', 'desc')->get()
            : static::byEmail($patientIdentifier)->orderBy('consultation_date', 'desc')->get();

        if ($histories->isEmpty()) {
            return [];
        }

        // Consolidate all histories
        return [
            'patient_info' => [
                'name' => $histories->first()->patient_name,
                'email' => $histories->first()->patient_email,
                'mobile' => $histories->first()->patient_mobile,
            ],
            'latest_vitals' => $histories->first(function ($h) {
                return $h->blood_pressure || $h->temperature || $h->heart_rate;
            }),
            'all_diagnoses' => $histories->pluck('diagnosis')->filter()->unique()->values(),
            'all_medications' => $histories->pluck('prescribed_medications')->flatten(1)->filter()->unique('name')->values(),
            'medical_history' => [
                'past_medical' => $histories->pluck('past_medical_history')->filter()->first(),
                'family_history' => $histories->pluck('family_history')->filter()->first(),
                'drug_history' => $histories->pluck('drug_history')->filter()->first(),
                'social_history' => $histories->pluck('social_history')->filter()->first(),
                'allergies' => $histories->pluck('allergies')->filter()->first(),
            ],
            'consultation_count' => $histories->count(),
            'last_consultation_date' => $histories->first()->consultation_date,
            'all_records' => $histories,
        ];
    }
}
