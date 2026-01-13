<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\CustomerCare;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportTicketService
{
    /**
     * Create a new support ticket
     */
    public function createTicket(array $data): SupportTicket
    {
        $agentId = $data['agent_id'] ?? (Auth::guard('customer_care')->check() ? Auth::guard('customer_care')->id() : null);

        $ticketData = [
            'user_type' => $data['user_type'] ?? 'patient',
            'agent_id' => $agentId,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'status' => $data['status'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
        ];

        // Set user_id or doctor_id based on user_type
        if (($data['user_type'] ?? 'patient') === 'doctor') {
            $ticketData['doctor_id'] = $data['doctor_id'] ?? $data['user_id'];
        } else {
            $ticketData['user_id'] = $data['user_id'];
        }

        $ticket = SupportTicket::create($ticketData);
        
        // Load relationships to get creator info
        $ticket->load(['user', 'doctor', 'agent']);
        
        // Send notification to customer care agents
        $this->notifyCustomerCareAgents($ticket);

        return $ticket;
    }
    
    /**
     * Notify customer care agents about new ticket
     */
    protected function notifyCustomerCareAgents(SupportTicket $ticket): void
    {
        try {
            // Get creator name
            $creatorName = $ticket->user_type === 'doctor' 
                ? ($ticket->doctor ? 'Dr. ' . $ticket->doctor->name : 'A Doctor')
                : ($ticket->user ? $ticket->user->name : 'A Patient');
            
            $priorityBadge = match($ticket->priority) {
                'urgent' => '🔴',
                'high' => '🟠',
                'medium' => '🟡',
                'low' => '🟢',
                default => '📋'
            };
            
            $message = "{$priorityBadge} New {$ticket->priority} priority ticket from {$creatorName}: {$ticket->subject}";
            
            // If ticket is assigned to a specific agent, notify only that agent
            if ($ticket->agent_id) {
                Notification::create([
                    'user_type' => 'customer_care',
                    'user_id' => $ticket->agent_id,
                    'title' => 'New Support Ticket Assigned',
                    'message' => $message,
                    'type' => $ticket->priority === 'urgent' ? 'error' : ($ticket->priority === 'high' ? 'warning' : 'info'),
                    'action_url' => route('customer-care.tickets.show', $ticket),
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'category' => $ticket->category,
                        'priority' => $ticket->priority,
                        'user_type' => $ticket->user_type,
                    ]
                ]);
                
                Log::info('Support ticket notification sent to assigned agent', [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'agent_id' => $ticket->agent_id,
                ]);
            } else {
                // If no agent assigned, notify all active customer care agents
                $activeAgents = CustomerCare::where('is_active', true)->get();
                
                foreach ($activeAgents as $agent) {
                    Notification::create([
                        'user_type' => 'customer_care',
                        'user_id' => $agent->id,
                        'title' => 'New Support Ticket Created',
                        'message' => $message,
                        'type' => $ticket->priority === 'urgent' ? 'error' : ($ticket->priority === 'high' ? 'warning' : 'info'),
                        'action_url' => route('customer-care.tickets.show', $ticket),
                        'data' => [
                            'ticket_id' => $ticket->id,
                            'ticket_number' => $ticket->ticket_number,
                            'category' => $ticket->category,
                            'priority' => $ticket->priority,
                            'user_type' => $ticket->user_type,
                        ]
                    ]);
                }
                
                Log::info('Support ticket notification sent to all active agents', [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'agents_notified' => $activeAgents->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send support ticket notification', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'error' => $e->getMessage(),
            ]);
        }
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
