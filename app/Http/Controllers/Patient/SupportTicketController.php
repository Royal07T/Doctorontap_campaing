<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    protected $ticketService;

    public function __construct(SupportTicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of patient's support tickets
     */
    public function index(Request $request)
    {
        $patient = Auth::guard('patient')->user();

        $query = SupportTicket::where('user_type', 'patient')
            ->where('user_id', $patient->id)
            ->with(['agent']);

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
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(15);

        return view('patient.support-tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return view('patient.support-tickets.create');
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:billing,appointment,technical,medical',
            'subject' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10|max:2000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $patient = Auth::guard('patient')->user();

        $ticket = $this->ticketService->createTicket([
            'user_type' => 'patient',
            'user_id' => $patient->id,
            'category' => $request->category,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
        ]);

        return redirect()
            ->route('patient.support-tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully. Ticket Number: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified ticket
     */
    public function show(SupportTicket $supportTicket)
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the ticket belongs to this patient
        if ($supportTicket->user_type !== 'patient' || $supportTicket->user_id !== $patient->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        $supportTicket->load(['agent', 'escalations.escalatedBy']);

        return view('patient.support-tickets.show', compact('supportTicket'));
    }
}
