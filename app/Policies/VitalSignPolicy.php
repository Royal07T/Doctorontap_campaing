<?php

namespace App\Policies;

use App\Models\VitalSign;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\CareGiver;
use Illuminate\Support\Facades\Log;

class VitalSignPolicy
{
    /**
     * Determine if the user can view the vital sign.
     *
     * @param mixed $user
     * @param VitalSign $vitalSign
     * @return bool
     */
    public function view($user, VitalSign $vitalSign): bool
    {
        // Admins can view all vital signs
        if ($user instanceof AdminUser) {
            return true;
        }
        
        // Caregivers can view vital signs for patients assigned to them
        if ($user instanceof CareGiver) {
            $authorized = $user->isAssignedToPatient($vitalSign->patient_id);
            
            if (!$authorized) {
                Log::warning("Caregiver attempted to access unauthorized vital sign", [
                    'caregiver_id' => $user->id,
                    'caregiver_email' => $user->email,
                    'vital_sign_id' => $vitalSign->id,
                    'patient_id' => $vitalSign->patient_id,
                    'ip_address' => request()->ip(),
                ]);
            }
            
            // Log access attempt
            $this->logVitalSignAccess('view', $user, $vitalSign, $authorized, 'caregiver');
            
            return $authorized;
        }
        
        // Doctors can view vital signs for their patients
        if ($user instanceof Doctor) {
            $authorized = \App\Models\Consultation::where('doctor_id', $user->id)
                ->where(function($query) use ($vitalSign) {
                    $query->where('email', $vitalSign->patient->email ?? null);
                })
                ->exists();
            
            return $authorized;
        }
        
        // Nurses can view vital signs they recorded
        if ($user instanceof Nurse) {
            $authorized = $vitalSign->nurse_id === $user->id;
            
            if (!$authorized) {
                Log::warning("Nurse attempted to access unauthorized vital sign", [
                    'nurse_id' => $user->id,
                    'vital_sign_id' => $vitalSign->id,
                    'vital_sign_nurse_id' => $vitalSign->nurse_id,
                    'ip_address' => request()->ip(),
                ]);
            }
            
            return $authorized;
        }
        
        // Patients can view their own vital signs
        if ($user instanceof Patient) {
            return $vitalSign->patient_id === $user->id;
        }
        
        return false;
    }
    
    /**
     * Log vital sign access for audit trail
     */
    private function logVitalSignAccess(string $action, $user, VitalSign $vitalSign, bool $authorized, string $userType): void
    {
        if (class_exists(\App\Services\ActivityLogService::class)) {
            try {
                $activityLogService = app(\App\Services\ActivityLogService::class);
                $activityLogService->logCaregiverAction(
                    $action . '_vital_sign',
                    $user->id,
                    $userType,
                    $vitalSign->patient_id,
                    [
                        'vital_sign_id' => $vitalSign->id,
                        'model_type' => VitalSign::class,
                        'authorized' => $authorized,
                        'ip_address' => request()->ip(),
                    ]
                );
            } catch (\Exception $e) {
                Log::info("Vital sign access", [
                    'action' => $action,
                    'user_id' => $user->id,
                    'user_type' => $userType,
                    'vital_sign_id' => $vitalSign->id,
                    'patient_id' => $vitalSign->patient_id,
                    'authorized' => $authorized,
                    'ip_address' => request()->ip(),
                ]);
            }
        }
    }
    
    /**
     * Determine if the user can view any vital signs.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny($user): bool
    {
        return $user instanceof AdminUser
            || $user instanceof Doctor 
            || $user instanceof Nurse
            || $user instanceof CareGiver; // Caregivers can view vital signs for assigned patients
    }
    
    /**
     * Determine if the user can create vital signs.
     *
     * @param mixed $user
     * @return bool
     */
    public function create($user): bool
    {
        // Admins, nurses, and caregivers can create vital signs
        // Caregivers must be assigned to the patient (checked in controller)
        return $user instanceof AdminUser
            || $user instanceof Nurse
            || $user instanceof CareGiver;
    }
    
    /**
     * Determine if the user can create vital signs for a specific patient.
     *
     * @param mixed $user
     * @param Patient $patient
     * @return bool
     */
    public function createForPatient($user, Patient $patient): bool
    {
        // Admins can create for any patient
        if ($user instanceof AdminUser) {
            return true;
        }
        
        // Nurses can create for any patient
        if ($user instanceof Nurse) {
            return true;
        }
        
        // Caregivers can ONLY create for assigned patients
        if ($user instanceof CareGiver) {
            $authorized = $user->isAssignedToPatient($patient->id);
            
            if ($authorized) {
                // Log the creation attempt
                $this->logVitalSignAccess('create', $user, (object)['patient_id' => $patient->id, 'id' => null], $authorized, 'caregiver');
            }
            
            return $authorized;
        }
        
        return false;
    }
    
    /**
     * Determine if the user can update the vital sign.
     *
     * @param mixed $user
     * @param VitalSign $vitalSign
     * @return bool
     */
    public function update($user, VitalSign $vitalSign): bool
    {
        // Admins can update all vital signs
        if ($user instanceof AdminUser) {
            return true;
        }
        
        // Nurses can update vital signs they recorded
        if ($user instanceof Nurse) {
            return $vitalSign->nurse_id === $user->id;
        }
        
        // Caregivers can update vital signs they recorded AND are assigned to the patient
        if ($user instanceof CareGiver) {
            $authorized = $vitalSign->caregiver_id === $user->id 
                && $user->isAssignedToPatient($vitalSign->patient_id);
            
            if ($authorized) {
                $this->logVitalSignAccess('update', $user, $vitalSign, true, 'caregiver');
            }
            
            return $authorized;
        }
        
        return false;
    }
    
    /**
     * Determine if the user can delete the vital sign.
     *
     * @param mixed $user
     * @param VitalSign $vitalSign
     * @return bool
     */
    public function delete($user, VitalSign $vitalSign): bool
    {
        // Only admins can delete vital signs
        return $user instanceof AdminUser;
    }
}

