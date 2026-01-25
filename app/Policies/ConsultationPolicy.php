<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Canvasser;
use Illuminate\Support\Facades\Log;

class ConsultationPolicy
{
    /**
     * Determine if the given consultation can be viewed by the user.
     *
     * @param mixed $user
     * @param Consultation $consultation
     * @return bool
     */
    public function view($user, Consultation $consultation): bool
    {
        // Admins can view all consultations
        if ($user instanceof AdminUser) {
            $this->logAuthorization('view', $user, $consultation, true, 'admin');
            return true;
        }
        
        // Doctors can view their assigned consultations
        if ($user instanceof Doctor) {
            $authorized = $consultation->doctor_id === $user->id;
            $this->logAuthorization('view', $user, $consultation, $authorized, 'doctor');
            return $authorized;
        }
        
        // Nurses can view consultations they're assigned to
        if ($user instanceof Nurse) {
            $authorized = $consultation->nurse_id === $user->id;
            $this->logAuthorization('view', $user, $consultation, $authorized, 'nurse');
            return $authorized;
        }
        
        // Patients can view their own consultations
        if ($user instanceof Patient) {
            $authorized = $consultation->email === $user->email;
            $this->logAuthorization('view', $user, $consultation, $authorized, 'patient');
            return $authorized;
        }
        
        // Canvassers can view consultations they created
        if ($user instanceof Canvasser) {
            $authorized = $consultation->canvasser_id === $user->id;
            $this->logAuthorization('view', $user, $consultation, $authorized, 'canvasser');
            return $authorized;
        }
        
        $this->logAuthorization('view', $user, $consultation, false, 'unknown');
        return false;
    }
    
    /**
     * Determine if the user can update the consultation.
     *
     * @param mixed $user
     * @param Consultation $consultation
     * @return bool
     */
    public function update($user, Consultation $consultation): bool
    {
        // Admins can update all consultations
        if ($user instanceof AdminUser) {
            return true;
        }
        
        // Doctors can update their assigned consultations
        if ($user instanceof Doctor) {
            return $consultation->doctor_id === $user->id;
        }
        
        // Nurses can update consultations they're assigned to (limited fields)
        if ($user instanceof Nurse) {
            return $consultation->nurse_id === $user->id;
        }
        
        // Canvassers can update consultations they created (before assignment)
        if ($user instanceof Canvasser) {
            return $consultation->canvasser_id === $user->id;
        }
        
        return false;
    }
    
    /**
     * Determine if the user can delete the consultation.
     *
     * @param mixed $user
     * @param Consultation $consultation
     * @return bool
     */
    public function delete($user, Consultation $consultation): bool
    {
        // Only admins can delete consultations
        if ($user instanceof AdminUser) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine if the user can view any consultations.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny($user): bool
    {
        // All authenticated users can view consultations (filtered by their access)
        return $user instanceof AdminUser 
            || $user instanceof Doctor 
            || $user instanceof Nurse 
            || $user instanceof Patient 
            || $user instanceof Canvasser;
    }
    
    /**
     * Determine if the user can create consultations.
     *
     * @param mixed $user
     * @return bool
     */
    public function create($user): bool
    {
        // Admins and canvassers can create consultations
        return $user instanceof AdminUser || $user instanceof Canvasser;
    }
    
    /**
     * Log authorization attempts for HIPAA audit trail
     *
     * @param string $action
     * @param mixed $user
     * @param Consultation $consultation
     * @param bool $authorized
     * @param string $userType
     * @return void
     */
    protected function logAuthorization(string $action, $user, Consultation $consultation, bool $authorized, string $userType): void
    {
        $result = $authorized ? 'GRANTED' : 'DENIED';
        
        Log::channel('audit')->info("Authorization Check: {$result}", [
            'action' => $action,
            'resource' => 'Consultation',
            'consultation_id' => $consultation->id,
            'consultation_reference' => $consultation->reference,
            'user_type' => $userType,
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'authorized' => $authorized,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
        
        // Log denied access attempts as warnings
        if (!$authorized) {
            Log::warning("Unauthorized access attempt to consultation", [
                'user_type' => $userType,
                'user_id' => $user->id ?? null,
                'user_email' => $user->email ?? null,
                'consultation_id' => $consultation->id,
                'action' => $action,
                'ip_address' => request()->ip(),
            ]);
        }
    }
}

