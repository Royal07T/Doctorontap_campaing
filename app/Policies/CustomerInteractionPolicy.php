<?php

namespace App\Policies;

use App\Models\CustomerInteraction;
use App\Models\CustomerCare;
use App\Models\AdminUser;
use Illuminate\Auth\Access\Response;

class CustomerInteractionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return $user instanceof CustomerCare || $user instanceof AdminUser;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, CustomerInteraction $customerInteraction): bool
    {
        if ($user instanceof AdminUser) {
            return true; // Admins can view all interactions
        }

        if ($user instanceof CustomerCare) {
            // Customer care agents can view all interactions (for collaboration and handoffs)
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return $user instanceof CustomerCare;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, CustomerInteraction $customerInteraction): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof CustomerCare) {
            return $customerInteraction->agent_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, CustomerInteraction $customerInteraction): bool
    {
        return $user instanceof AdminUser; // Only admins can delete
    }
}
