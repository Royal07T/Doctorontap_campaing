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
            // This ensures all medical information from the treatment plan is synced to patient medical record
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
                    
                    // Medical History - Always update with latest from treatment plan
                    'presenting_complaint' => $consultation->presenting_complaint,
                    'history_of_complaint' => $consultation->history_of_complaint,
                    'past_medical_history' => $consultation->past_medical_history,
                    'family_history' => $consultation->family_history,
                    'drug_history' => $consultation->drug_history,
                    'social_history' => $consultation->social_history,
                    
                    // Diagnosis & Treatment - Always update with latest from treatment plan
                    'diagnosis' => $consultation->diagnosis,
                    'investigation' => $consultation->investigation,
                    'treatment_plan' => $consultation->treatment_plan,
                    'prescribed_medications' => $consultation->prescribed_medications,
                    'follow_up_instructions' => $consultation->follow_up_instructions,
                    'lifestyle_recommendations' => $consultation->lifestyle_recommendations,
                    'referrals' => $consultation->referrals,
                    'next_appointment_date' => $consultation->next_appointment_date,
                    'additional_notes' => $consultation->additional_notes,
                    
                    // Vital Signs - Update if available
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
            
            // Ensure the medical history record is updated with the latest consultation data
            // This handles cases where the consultation was updated after initial sync
            $history->refresh();
            $history->update([
                // Medical History - Update with latest from treatment plan
                'presenting_complaint' => $consultation->presenting_complaint,
                'history_of_complaint' => $consultation->history_of_complaint,
                'past_medical_history' => $consultation->past_medical_history,
                'family_history' => $consultation->family_history,
                'drug_history' => $consultation->drug_history,
                'social_history' => $consultation->social_history,
                
                // Diagnosis & Treatment - Update with latest from treatment plan
                'diagnosis' => $consultation->diagnosis,
                'investigation' => $consultation->investigation,
                'treatment_plan' => $consultation->treatment_plan,
                'prescribed_medications' => $consultation->prescribed_medications,
                'follow_up_instructions' => $consultation->follow_up_instructions,
                'lifestyle_recommendations' => $consultation->lifestyle_recommendations,
                'referrals' => $consultation->referrals,
                'next_appointment_date' => $consultation->next_appointment_date,
                'additional_notes' => $consultation->additional_notes,
            ]);
            
            // Update patient stats
            $this->updatePatientStats($patient);
            
            // Update consolidated patient medical record with latest information
            $this->updateConsolidatedPatientRecord($patient, $consultation);
            
            DB::commit();
            
            Log::info('Patient medical history synced', [
                'patient_id' => $patient->id,
                'consultation_id' => $consultation->id,
                'history_id' => $history->id,
                'has_vital_signs' => !empty(array_filter($vitalSigns)),
                'vital_signs_count' => count(array_filter($vitalSigns))
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
     * Searches by patient_id (vital signs are linked to patients)
     */
    protected function getVitalSignsForConsultation(Consultation $consultation): array
    {
        $vitalSign = null;
        
        // First, try to find by patient_id if consultation is linked to a patient
        if ($consultation->patient_id) {
            $vitalSign = VitalSign::where('patient_id', $consultation->patient_id)
                ->latest()
                ->first();
        }
        
        // If consultation is not linked to patient yet, try to find patient by email first
        if (!$vitalSign && $consultation->email) {
            $patient = Patient::where('email', $consultation->email)->first();
            if ($patient) {
                $vitalSign = VitalSign::where('patient_id', $patient->id)
                    ->latest()
                    ->first();
            }
        }
        
        if (!$vitalSign) {
            Log::debug('No vital signs found for consultation', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'email' => $consultation->email,
                'reference' => $consultation->reference
            ]);
            return [];
        }
        
        // Calculate BMI if not already set
        $bmi = $vitalSign->bmi;
        if (!$bmi && $vitalSign->height && $vitalSign->weight) {
            $heightInMeters = $vitalSign->height / 100;
            $bmi = round($vitalSign->weight / ($heightInMeters * $heightInMeters), 2);
        }
        
        $vitals = [
            'blood_pressure' => $vitalSign->blood_pressure,
            'temperature' => $vitalSign->temperature,
            'heart_rate' => $vitalSign->heart_rate,
            'respiratory_rate' => $vitalSign->respiratory_rate,
            'weight' => $vitalSign->weight,
            'height' => $vitalSign->height,
            'bmi' => $bmi,
            'oxygen_saturation' => $vitalSign->oxygen_saturation,
        ];
        
        Log::info('Vital signs found and will be synced to medical history', [
            'consultation_id' => $consultation->id,
            'vital_sign_id' => $vitalSign->id,
            'patient_id' => $vitalSign->patient_id,
            'has_vitals' => !empty(array_filter($vitals)),
            'vitals' => array_filter($vitals)
        ]);
        
        return $vitals;
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
     * Backfill medical history for all consultations that have treatment plans
     * This is useful for syncing existing consultations that were created before the sync was implemented
     */
    public function backfillPatientMedicalHistory(Patient $patient): int
    {
        $syncedCount = 0;
        
        // Get all consultations for this patient that have treatment plans
        $consultations = Consultation::where('patient_id', $patient->id)
            ->whereNotNull('treatment_plan')
            ->get();
        
        foreach ($consultations as $consultation) {
            // Check if medical history already exists
            $existingHistory = PatientMedicalHistory::where('consultation_id', $consultation->id)->first();
            if ($existingHistory) {
                continue; // Skip if already synced
            }
            
            try {
                $result = $this->syncConsultationToHistory($consultation);
                if ($result) {
                    $syncedCount++;
                    Log::info('Backfilled medical history for consultation', [
                        'consultation_id' => $consultation->id,
                        'patient_id' => $patient->id,
                        'history_id' => $result->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to backfill medical history for consultation', [
                    'consultation_id' => $consultation->id,
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $syncedCount;
    }

    /**
     * Backfill medical history for all consultations (by email if patient_id is not set)
     * This handles cases where consultations exist but patient_id is not set
     */
    public function backfillMedicalHistoryByEmail(string $email): int
    {
        $syncedCount = 0;
        
        // Get all consultations for this email that have treatment plans
        $consultations = Consultation::where('email', $email)
            ->whereNotNull('treatment_plan')
            ->get();
        
        foreach ($consultations as $consultation) {
            // Check if medical history already exists
            $existingHistory = PatientMedicalHistory::where('consultation_id', $consultation->id)->first();
            if ($existingHistory) {
                continue; // Skip if already synced
            }
            
            try {
                $result = $this->syncConsultationToHistory($consultation);
                if ($result) {
                    $syncedCount++;
                    Log::info('Backfilled medical history for consultation by email', [
                        'consultation_id' => $consultation->id,
                        'email' => $email,
                        'history_id' => $result->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to backfill medical history for consultation by email', [
                    'consultation_id' => $consultation->id,
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $syncedCount;
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

    /**
     * Update consolidated patient medical record with latest information
     * This ensures medical history fields from treatment plans are properly stored and updated
     * The latest record is updated with the most current information from the treatment plan
     */
    protected function updateConsolidatedPatientRecord(Patient $patient, Consultation $consultation): void
    {        // The medical history record is already updated in syncConsultationToHistory
        // This method logs the update for tracking purposes
        $latestHistory = PatientMedicalHistory::where('patient_id', $patient->id)
            ->where('consultation_id', $consultation->id)
            ->where('is_latest', true)
            ->first();
        
        if (!$latestHistory) {
            return;
        }
        
        Log::info('Patient medical record updated with latest treatment plan information', [
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'history_id' => $latestHistory->id,
            'has_family_history' => !empty($latestHistory->family_history),
            'has_social_history' => !empty($latestHistory->social_history),
            'has_past_medical_history' => !empty($latestHistory->past_medical_history),
            'has_drug_history' => !empty($latestHistory->drug_history),
            'has_vital_signs' => !empty($latestHistory->blood_pressure) || !empty($latestHistory->temperature) || !empty($latestHistory->heart_rate),
        ]);
    }
}

