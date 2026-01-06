<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\CustomerCare;
use App\Models\AdminUser;
use Illuminate\Auth\Access\Response;

class SupportTicketPolicy
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
    public function view($user, SupportTicket $supportTicket): bool
    {
        if ($user instanceof AdminUser) {
            return true; // Admins can view all tickets
        }

        if ($user instanceof CustomerCare) {
            // Customer care can view tickets assigned to them or all if they have permission
            return $supportTicket->agent_id === $user->id || $supportTicket->agent_id === null;
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
    public function update($user, SupportTicket $supportTicket): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if ($user instanceof CustomerCare) {
            return $supportTicket->agent_id === $user->id || $supportTicket->agent_id === null;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, SupportTicket $supportTicket): bool
    {
        return $user instanceof AdminUser; // Only admins can delete
    }
}
