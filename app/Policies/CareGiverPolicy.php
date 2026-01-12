<?php

namespace App\Policies;

use App\Models\CareGiver;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Log;

class CareGiverPolicy
{
    /**
     * Determine if the user can view any caregivers.
     *
     * @param mixed $user
     * @return bool
     */
    public function viewAny($user): bool
    {
        // Only admins can view list of caregivers
        return $user instanceof AdminUser;
    }

    /**
     * Determine if the user can view the caregiver.
     *
     * @param mixed $user
     * @param CareGiver $careGiver
     * @return bool
     */
    public function view($user, CareGiver $careGiver): bool
    {
        // Admins can view all caregivers
        if ($user instanceof AdminUser) {
            return true;
        }

        // Caregivers can view their own profile
        if ($user instanceof CareGiver) {
            return $user->id === $careGiver->id;
        }

        return false;
    }

    /**
     * Determine if the user can create caregivers.
     *
     * @param mixed $user
     * @return bool
     */
    public function create($user): bool
    {
        // Only admins can create caregivers
        return $user instanceof AdminUser;
    }

    /**
     * Determine if the user can update the caregiver.
     *
     * @param mixed $user
     * @param CareGiver $careGiver
     * @return bool
     */
    public function update($user, CareGiver $careGiver): bool
    {
        // Admins can update all caregivers
        if ($user instanceof AdminUser) {
            return true;
        }

        // Caregivers can update their own profile (limited fields)
        if ($user instanceof CareGiver) {
            return $user->id === $careGiver->id;
        }

        return false;
    }

    /**
     * Determine if the user can delete the caregiver.
     *
     * @param mixed $user
     * @param CareGiver $careGiver
     * @return bool
     */
    public function delete($user, CareGiver $careGiver): bool
    {
        // Only admins can delete caregivers
        return $user instanceof AdminUser;
    }

    /**
     * Determine if the user can manage patient assignments.
     *
     * @param mixed $user
     * @return bool
     */
    public function manageAssignments($user): bool
    {
        // Only admins can manage caregiver-patient assignments
        return $user instanceof AdminUser;
    }

    /**
     * Determine if the user can manage PIN.
     *
     * @param mixed $user
     * @param CareGiver $careGiver
     * @return bool
     */
    public function managePin($user, CareGiver $careGiver): bool
    {
        // Admins can manage any caregiver's PIN
        if ($user instanceof AdminUser) {
            return true;
        }

        // Caregivers can change their own PIN
        if ($user instanceof CareGiver) {
            return $user->id === $careGiver->id;
        }

        return false;
    }
}
