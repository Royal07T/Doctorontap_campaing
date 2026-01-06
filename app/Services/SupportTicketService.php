<?php

namespace App\Services;

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;

class SupportTicketService
{
    /**
     * Create a new support ticket
     */
    public function createTicket(array $data): SupportTicket
    {
        $agentId = $data['agent_id'] ?? Auth::guard('customer_care')->id();

        $ticket = SupportTicket::create([
            'user_id' => $data['user_id'],
            'agent_id' => $agentId,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'status' => $data['status'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
        ]);

        return $ticket;
    }

    /**
     * Update ticket status
     */
    public function updateTicketStatus(SupportTicket $ticket, string $status): SupportTicket
    {
        $updateData = ['status' => $status];

        if ($status === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = Auth::guard('customer_care')->id();
        }

        $ticket->update($updateData);

        return $ticket->fresh();
    }

    /**
     * Assign ticket to an agent
     */
    public function assignTicket(SupportTicket $ticket, int $agentId): SupportTicket
    {
        $ticket->update(['agent_id' => $agentId]);

        return $ticket->fresh();
    }

    /**
     * Get tickets by status
     */
    public function getTicketsByStatus(string $status, ?int $agentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = SupportTicket::where('status', $status);

        if ($agentId) {
            $query->where('agent_id', $agentId);
        }

        return $query->with(['user', 'agent'])->latest()->get();
    }

    /**
     * Get resolved tickets count for today
     */
    public function getResolvedTodayCount(?int $agentId = null): int
    {
        $query = SupportTicket::where('status', 'resolved')
            ->whereDate('resolved_at', today());

        if ($agentId) {
            $query->where('resolved_by', $agentId);
        }

        return $query->count();
    }

    /**
     * Get pending tickets count
     */
    public function getPendingTicketsCount(?int $agentId = null): int
    {
        $query = SupportTicket::where('status', 'pending');

        if ($agentId) {
            $query->where('agent_id', $agentId);
        }

        return $query->count();
    }
}
