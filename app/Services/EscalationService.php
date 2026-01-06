<?php

namespace App\Services;

use App\Models\Escalation;
use App\Models\SupportTicket;
use App\Models\CustomerInteraction;
use Illuminate\Support\Facades\Auth;

class EscalationService
{
    /**
     * Escalate a support ticket
     */
    public function escalateTicket(SupportTicket $ticket, array $data): Escalation
    {
        $escalation = Escalation::create([
            'support_ticket_id' => $ticket->id,
            'escalated_by' => Auth::guard('customer_care')->id(),
            'escalated_to_type' => $data['escalated_to_type'],
            'escalated_to_id' => $data['escalated_to_id'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        // Mark ticket as escalated
        $ticket->markAsEscalated();

        return $escalation;
    }

    /**
     * Escalate a customer interaction
     */
    public function escalateInteraction(CustomerInteraction $interaction, array $data): Escalation
    {
        $escalation = Escalation::create([
            'customer_interaction_id' => $interaction->id,
            'escalated_by' => Auth::guard('customer_care')->id(),
            'escalated_to_type' => $data['escalated_to_type'],
            'escalated_to_id' => $data['escalated_to_id'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return $escalation;
    }

    /**
     * Resolve an escalation
     */
    public function resolveEscalation(Escalation $escalation, string $outcome): Escalation
    {
        $escalation->markAsResolved($outcome);

        return $escalation->fresh();
    }

    /**
     * Get escalated cases count
     */
    public function getEscalatedCasesCount(?int $agentId = null): int
    {
        $query = Escalation::where('status', '!=', 'resolved')
            ->where('status', '!=', 'closed');

        if ($agentId) {
            $query->where('escalated_by', $agentId);
        }

        return $query->count();
    }

    /**
     * Get escalations by type
     */
    public function getEscalationsByType(string $type, ?int $agentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Escalation::where('escalated_to_type', $type);

        if ($agentId) {
            $query->where('escalated_by', $agentId);
        }

        return $query->with(['escalatedBy', 'supportTicket', 'customerInteraction'])
            ->latest()
            ->get();
    }
}
