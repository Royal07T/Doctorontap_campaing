<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Canvasser;
use App\Models\CareGiver;
use Illuminate\Support\Facades\Log;

class PatientPolicy
{
    /**
     * Determine if the user can view the patient.
     *
     * @param mixed $user
     * @param Patient $patient
     * @return bool
     */
    public function view($user, Patient $patient): bool
    {
        // Admins can view all patients
        if ($user instanceof AdminUser) {
            $this->logAccess('view', $user, $patient, true, 'admin');
            return true;
        }
        
        // Caregivers can ONLY view patients explicitly assigned to them
        if ($user instanceof CareGiver) {
            $authorized = $user->isAssignedToPatient($patient->id);
            
            $this->logAccess('view', $user, $patient, $authorized, 'caregiver', [
                'assignment_role' => $authorized ? $user->getAssignmentRoleForPatient($patient->id) : null,
            ]);
            
            if (!$authorized) {
                Log::warning("Caregiver attempted to access unauthorized patient", [
                    'caregiver_id' => $user->id,
                    'caregiver_email' => $user->email,
                    'patient_id' => $patient->id,
                    'patient_email' => $patient->email,
                    'ip_address' => request()->ip(),
                ]);
            }
            
            return $authorized;
        }
        
        // Doctors can view patients from their consultations
        if ($user instanceof Doctor) {
            $authorized = \App\Models\Consultation::where('doctor_id', $user->id)
                ->where('email', $patient->email)
                ->exists();
            
            $this->logAccess('view', $user, $patient, $authorized, 'doctor');
            
            if (!$authorized) {
                Log::warning("Doctor attempted to access unauthorized patient", [
                    'doctor_id' => $user->id,
                    'doctor_email' => $user->email,
                    'patient_id' => $patient->id,
                    'patient_email' => $patient->email,
                    'ip_address' => request()->ip(),
                ]);
            }
            
            return $authorized;
        }
        
        // Nurses can view patients they've attended (recorded vital signs for)
        if ($user instanceof Nurse) {
            $authorized = \App\Models\VitalSign::where('nurse_id', $user->id)
                ->where('patient_id', $patient->id)
                ->exists();
            
            $this->logAccess('view', $user, $patient, $authorized, 'nurse');
            
            if (!$authorized) {
                Log::warning("Nurse attempted to access unauthorized patient", [
                    'nurse_id' => $user->id,
                    'nurse_email' => $user->email,
                    'patient_id' => $patient->id,
                    'patient_email' => $patient->email,
                    'ip_address' => request()->ip(),
                ]);
            }
            
            return $authorized;
        }
        
        // Patients can view their own profile
        if ($user instanceof Patient) {
            return $user->id === $patient->id;
        }
        
        // Canvassers can view patients from consultations they created
        if ($user instanceof Canvasser) {
            $authorized = \App\Models\Consultation::where('canvasser_id', $user->id)
                ->where('email', $patient->email)
                ->exists();
            
            return $authorized;
        }
        
        return false;
    }
    
    /**
     * Log patient access for audit trail
     */
    private function logAccess(string $action, $user, Patient $patient, bool $authorized, string $userType, array $metadata = []): void
    {
        // Use ActivityLog service if available
        if (class_exists(\App\Services\ActivityLogService::class)) {
            try {
                $activityLogService = app(\App\Services\ActivityLogService::class);
                $activityLogService->logCaregiverAction(
                    $action . '_patient',
                    $user->id,
                    $userType,
                    $patient->id,
                    array_merge([
                        'authorized' => $authorized,
                        'model_type' => Patient::class,
                        'ip_address' => request()->ip(),
                    ], $metadata)
                );
            } catch (\Exception $e) {
                // Fallback to basic logging if service unavailable
                Log::info("Patient access", [
                    'action' => $action,
                    'user_id' => $user->id,
                    'user_type' => $userType,
                    'patient_id' => $patient->id,
                    'authorized' => $authorized,
                    'ip_address' => request()->ip(),
                ]);
            }
        } else {
            // Fallback logging
            Log::info("Patient access", [
                'action' => $action,
                'user_id' => $user->id,
                'user_type' => $userType,
                'patient_id' => $patient->id,
                'authorized' => $authorized,
                'ip_address' => request()->ip(),
            ]);
        }
    }
    
    /**
     * Determine if the user can view any patients.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny($user): bool
    {
        return $user instanceof AdminUser
            || $user instanceof Doctor 
            || $user instanceof Nurse 
            || $user instanceof Canvasser
            || $user instanceof CareGiver; // Caregivers can view their assigned patients
    }
    
    /**
     * Determine if the user can update the patient.
     *
     * @param mixed $user
     * @param Patient $patient
     * @return bool
     */
    public function update($user, Patient $patient): bool
    {
        // Admins can update all patients
        if ($user instanceof AdminUser) {
            return true;
        }
        
        // Patients can update their own profile
        if ($user instanceof Patient) {
            return $user->id === $patient->id;
        }
        
        return false;
    }
    
    /**
     * Determine if the user can delete the patient.
     *
     * @param mixed $user
     * @param Patient $patient
     * @return bool
     */
    public function delete($user, Patient $patient): bool
    {
        // Only admins can delete patients
        return $user instanceof AdminUser;
    }
}

