<?php

namespace App\Policies;

use App\Models\VitalSign;
use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
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
        if ($user instanceof Admin) {
            return true;
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
     * Determine if the user can view any vital signs.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny($user): bool
    {
        return $user instanceof Admin 
            || $user instanceof Doctor 
            || $user instanceof Nurse;
    }
    
    /**
     * Determine if the user can create vital signs.
     *
     * @param mixed $user
     * @return bool
     */
    public function create($user): bool
    {
        // Admins and nurses can create vital signs
        return $user instanceof Admin || $user instanceof Nurse;
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
        if ($user instanceof Admin) {
            return true;
        }
        
        // Nurses can update vital signs they recorded
        if ($user instanceof Nurse) {
            return $vitalSign->nurse_id === $user->id;
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
        return $user instanceof Admin;
    }
}

