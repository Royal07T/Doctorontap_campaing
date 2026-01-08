<?php

namespace App\Policies;

use App\Models\Escalation;
use App\Models\CustomerCare;
use App\Models\AdminUser;
use Illuminate\Auth\Access\Response;

class EscalationPolicy
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
    public function view($user, Escalation $escalation): bool
    {
        if ($user instanceof AdminUser) {
            return true; // Admins can view all escalations
        }

        if ($user instanceof CustomerCare) {
            // Customer care agents can view all escalations (for collaboration and handoffs)
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
    public function update($user, Escalation $escalation): bool
    {
        if ($user instanceof AdminUser) {
            return true; // Admins can resolve escalations
        }

        // Customer care can only update escalations they created if still pending
        if ($user instanceof CustomerCare) {
            return $escalation->escalated_by === $user->id && $escalation->status === 'pending';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, Escalation $escalation): bool
    {
        return $user instanceof AdminUser; // Only admins can delete
    }
}
