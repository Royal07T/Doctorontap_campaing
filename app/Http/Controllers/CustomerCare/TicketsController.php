<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCare\StoreTicketRequest;
use App\Models\SupportTicket;
use App\Models\Patient;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    protected $ticketService;

    public function __construct(SupportTicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of tickets
     */
    public function index(Request $request)
    {
        $agent = Auth::guard('customer_care')->user();

        $query = SupportTicket::where('agent_id', $agent->id)
            ->with(['user', 'agent']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
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

        return view('customer-care.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        return view('customer-care.tickets.create', compact('patients'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->createTicket($request->validated());

        return redirect()
            ->route('customer-care.tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    /**
     * Display the specified ticket
     */
    public function show(SupportTicket $ticket)
    {
        $user = Auth::guard('customer_care')->user();
        
        // Customer care agents can view all tickets
        // Only check if user is authenticated
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->load(['user', 'agent', 'escalations.escalatedBy']);

        return view('customer-care.tickets.show', compact('ticket'));
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $user = Auth::guard('customer_care')->user();
        
        // Customer care agents can update all tickets
        // Only check if user is authenticated
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:open,pending,resolved,escalated',
        ]);

        $this->ticketService->updateTicketStatus($ticket, $request->status);

        return redirect()
            ->route('customer-care.tickets.show', $ticket)
            ->with('success', 'Ticket status updated successfully.');
    }
}
