<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Canvasser;
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
        if ($user instanceof Admin) {
            return true;
        }
        
        // Doctors can view patients from their consultations
        if ($user instanceof Doctor) {
            $authorized = \App\Models\Consultation::where('doctor_id', $user->id)
                ->where('email', $patient->email)
                ->exists();
            
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
     * Determine if the user can view any patients.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny($user): bool
    {
        return $user instanceof Admin 
            || $user instanceof Doctor 
            || $user instanceof Nurse 
            || $user instanceof Canvasser;
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
        if ($user instanceof Admin) {
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
        return $user instanceof Admin;
    }
}

