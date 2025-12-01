<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\PatientMedicalHistory;
use App\Models\VitalSign;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PatientMedicalHistoryService
{
    /**
     * Sync consultation data to patient medical history
     * This is called after treatment plan is created or updated
     */
    public function syncConsultationToHistory(Consultation $consultation): ?PatientMedicalHistory
    {
        try {
            DB::beginTransaction();
            
            // Find or create patient record
            $patient = $this->findOrCreatePatient($consultation);
            
            // Link consultation to patient if not already linked
            if (!$consultation->patient_id) {
                $consultation->update(['patient_id' => $patient->id]);
            }
            
            // Mark all previous histories as not latest
            PatientMedicalHistory::where('patient_id', $patient->id)
                ->where('is_latest', true)
                ->update(['is_latest' => false]);
            
            // Get latest vital signs for this consultation
            $vitalSigns = $this->getVitalSignsForConsultation($consultation);
            
            // Create or update medical history record
            $history = PatientMedicalHistory::updateOrCreate(
                [
                    'consultation_id' => $consultation->id,
                ],
                [
                    'patient_id' => $patient->id,
                    'patient_email' => $consultation->email,
                    'patient_name' => $consultation->first_name . ' ' . $consultation->last_name,
                    'patient_mobile' => $consultation->mobile,
                    'consultation_reference' => $consultation->reference,
                    'doctor_id' => $consultation->doctor_id,
                    
                    // Medical History
                    'presenting_complaint' => $consultation->presenting_complaint,
                    'history_of_complaint' => $consultation->history_of_complaint,
                    'past_medical_history' => $consultation->past_medical_history,
                    'family_history' => $consultation->family_history,
                    'drug_history' => $consultation->drug_history,
                    'social_history' => $consultation->social_history,
                    
                    // Diagnosis & Treatment
                    'diagnosis' => $consultation->diagnosis,
                    'investigation' => $consultation->investigation,
                    'treatment_plan' => $consultation->treatment_plan,
                    'prescribed_medications' => $consultation->prescribed_medications,
                    'follow_up_instructions' => $consultation->follow_up_instructions,
                    'lifestyle_recommendations' => $consultation->lifestyle_recommendations,
                    'referrals' => $consultation->referrals,
                    'next_appointment_date' => $consultation->next_appointment_date,
                    'additional_notes' => $consultation->additional_notes,
                    
                    // Vital Signs
                    'blood_pressure' => $vitalSigns['blood_pressure'] ?? null,
                    'temperature' => $vitalSigns['temperature'] ?? null,
                    'heart_rate' => $vitalSigns['heart_rate'] ?? null,
                    'respiratory_rate' => $vitalSigns['respiratory_rate'] ?? null,
                    'weight' => $vitalSigns['weight'] ?? null,
                    'height' => $vitalSigns['height'] ?? null,
                    'bmi' => $vitalSigns['bmi'] ?? null,
                    'oxygen_saturation' => $vitalSigns['oxygen_saturation'] ?? null,
                    
                    // Metadata
                    'consultation_date' => $consultation->created_at->toDateString(),
                    'severity' => $consultation->severity,
                    'is_latest' => true,
                ]
            );
            
            // Update patient stats
            $this->updatePatientStats($patient);
            
            DB::commit();
            
            Log::info('Patient medical history synced', [
                'patient_id' => $patient->id,
                'consultation_id' => $consultation->id,
                'history_id' => $history->id
            ]);
            
            return $history;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to sync patient medical history', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Find or create patient record
     */
    protected function findOrCreatePatient(Consultation $consultation): Patient
    {
        $patient = Patient::where('email', $consultation->email)->first();
        
        if (!$patient) {
            $patient = Patient::create([
                'name' => $consultation->first_name . ' ' . $consultation->last_name,
                'email' => $consultation->email,
                'phone' => $consultation->mobile,
                'gender' => $consultation->gender,
                'age' => $consultation->age,
                'canvasser_id' => $consultation->canvasser_id,
                'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                'is_verified' => false,
            ]);
            
            Log::info('Auto-created patient record from consultation', [
                'patient_id' => $patient->id,
                'consultation_id' => $consultation->id
            ]);
        }
        
        return $patient;
    }

    /**
     * Get vital signs for consultation
     */
    protected function getVitalSignsForConsultation(Consultation $consultation): array
    {
        // Try to find vital signs by patient email or consultation reference
        $vitalSign = VitalSign::where('email', $consultation->email)
            ->orWhere('consultation_reference', $consultation->reference)
            ->latest()
            ->first();
        
        if (!$vitalSign) {
            return [];
        }
        
        return [
            'blood_pressure' => $vitalSign->blood_pressure,
            'temperature' => $vitalSign->temperature,
            'heart_rate' => $vitalSign->heart_rate,
            'respiratory_rate' => $vitalSign->respiratory_rate,
            'weight' => $vitalSign->weight,
            'height' => $vitalSign->height,
            'bmi' => $vitalSign->bmi,
            'oxygen_saturation' => $vitalSign->oxygen_saturation,
        ];
    }

    /**
     * Update patient statistics
     */
    protected function updatePatientStats(Patient $patient): void
    {
        $consultations = Consultation::where('patient_id', $patient->id)->get();
        
        $patient->update([
            'has_consulted' => $consultations->isNotEmpty(),
            'consultations_count' => $consultations->count(),
            'last_consultation_at' => $consultations->max('created_at'),
            'total_amount_paid' => $consultations->where('payment_status', 'paid')
                ->sum(function ($c) {
                    return $c->payment ? $c->payment->amount : 0;
                }),
        ]);
    }

    /**
     * Get patient's complete medical history
     */
    public function getPatientHistory($patientIdentifier): array
    {
        return PatientMedicalHistory::getConsolidatedHistory($patientIdentifier);
    }

    /**
     * Pre-fill treatment plan form with patient's previous data
     */
    public function getPreviousHistoryForConsultation(Consultation $consultation): ?PatientMedicalHistory
    {
        // Get patient's most recent medical history (excluding current consultation)
        $previousHistory = PatientMedicalHistory::byEmail($consultation->email)
            ->where('consultation_id', '!=', $consultation->id)
            ->where('is_latest', true)
            ->first();
        
        return $previousHistory;
    }
}

