<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerInteraction;
use App\Models\SupportTicket;
use App\Models\Escalation;
use App\Models\CustomerCare;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Services\CustomerInteractionService;
use App\Services\SupportTicketService;
use App\Services\EscalationService;

class CustomerCareOversightController extends Controller
{
    /**
     * Display all customer care interactions
     */
    public function interactions(Request $request)
    {
        $query = CustomerInteraction::with(['user', 'agent']);

        // Filter by agent
        if ($request->has('agent_id') && $request->agent_id !== '') {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $interactions = $query->latest()->paginate(20);
        $agents = CustomerCare::where('is_active', true)->get();

        return view('admin.customer-care.interactions', compact('interactions', 'agents'));
    }

    /**
     * Display interaction details
     */
    public function showInteraction(CustomerInteraction $interaction)
    {
        $interaction->load(['user', 'agent', 'notes.creator', 'escalations']);

        return view('admin.customer-care.interaction-details', compact('interaction'));
    }

    /**
     * Display all support tickets
     */
    public function tickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'agent']);

        // Filter by agent
        if ($request->has('agent_id') && $request->agent_id !== '') {
            $query->where('agent_id', $request->agent_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(20);
        $agents = CustomerCare::where('is_active', true)->get();

        return view('admin.customer-care.tickets', compact('tickets', 'agents'));
    }

    /**
     * Display ticket details
     */
    public function showTicket(SupportTicket $ticket)
    {
        $ticket->load(['user', 'agent', 'escalations.escalatedBy']);

        return view('admin.customer-care.ticket-details', compact('ticket'));
    }

    /**
     * Display all escalations
     */
    public function escalations(Request $request)
    {
        $query = Escalation::with(['escalatedBy', 'supportTicket', 'customerInteraction']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('escalated_to_type') && $request->escalated_to_type !== '') {
            $query->where('escalated_to_type', $request->escalated_to_type);
        }

        $escalations = $query->latest()->paginate(20);

        return view('admin.customer-care.escalations', compact('escalations'));
    }

    /**
     * Display escalation details
     */
    public function showEscalation(Escalation $escalation)
    {
        $escalation->load([
            'escalatedBy',
            'supportTicket.user',
            'customerInteraction.user'
        ]);

        return view('admin.customer-care.escalation-details', compact('escalation'));
    }

    /**
     * Display customer interaction history
     */
    public function customerHistory(Patient $patient)
    {
        $patient->load([
            'customerInteractions.agent',
            'supportTickets.agent',
            'consultations.doctor'
        ]);

        return view('admin.customer-care.customer-history', compact('patient'));
    }

    /**
     * Display agent performance metrics
     */
    public function agentPerformance(Request $request)
    {
        $interactionService = app(CustomerInteractionService::class);
        $ticketService = app(SupportTicketService::class);
        $escalationService = app(EscalationService::class);

        $query = CustomerCare::query();

        if ($request->has('agent_id') && $request->agent_id !== '') {
            $query->where('id', $request->agent_id);
        }

        $agents = $query->where('is_active', true)->get();

        $performanceData = $agents->map(function($agent) use ($interactionService, $ticketService, $escalationService) {
            return [
                'agent' => $agent,
                'total_interactions' => CustomerInteraction::where('agent_id', $agent->id)->count(),
                'active_interactions' => CustomerInteraction::where('agent_id', $agent->id)->where('status', 'active')->count(),
                'resolved_tickets_today' => $ticketService->getResolvedTodayCount($agent->id),
                'pending_tickets' => $ticketService->getPendingTicketsCount($agent->id),
                'escalated_cases' => $escalationService->getEscalatedCasesCount($agent->id),
                'avg_response_time' => $interactionService->getAverageResponseTime($agent->id),
            ];
        });

        return view('admin.customer-care.agent-performance', compact('performanceData', 'agents'));
    }

    /**
     * Display frequent issues and escalations
     */
    public function frequentIssues()
    {
        // Most common ticket categories
        $ticketCategories = SupportTicket::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        // Most escalated issues
        $escalatedTickets = Escalation::whereNotNull('support_ticket_id')
            ->with('supportTicket')
            ->get()
            ->groupBy(function($escalation) {
                return $escalation->supportTicket->category ?? 'unknown';
            })
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc();

        // Agents with most escalations
        $agentEscalations = Escalation::selectRaw('escalated_by, COUNT(*) as count')
            ->groupBy('escalated_by')
            ->with('escalatedBy')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.customer-care.frequent-issues', compact(
            'ticketCategories',
            'escalatedTickets',
            'agentEscalations'
        ));
    }
}
