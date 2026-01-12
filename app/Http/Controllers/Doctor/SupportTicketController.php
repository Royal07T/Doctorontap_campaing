<?php

namespace App\Http\Controllers\Doctor;

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
     * Display a listing of doctor's support tickets
     */
    public function index(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();

        $query = SupportTicket::where('user_type', 'doctor')
            ->where('doctor_id', $doctor->id)
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

        return view('doctor.support-tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return view('doctor.support-tickets.create');
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

        $doctor = Auth::guard('doctor')->user();

        $ticket = $this->ticketService->createTicket([
            'user_type' => 'doctor',
            'doctor_id' => $doctor->id,
            'user_id' => $doctor->id, // For compatibility
            'category' => $request->category,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
        ]);

        return redirect()
            ->route('doctor.support-tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully. Ticket Number: ' . $ticket->ticket_number);
    }

    /**
     * Display the specified ticket
     */
    public function show(SupportTicket $supportTicket)
    {
        $doctor = Auth::guard('doctor')->user();

        // Ensure the ticket belongs to this doctor
        if ($supportTicket->user_type !== 'doctor' || $supportTicket->doctor_id !== $doctor->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        $supportTicket->load(['agent', 'escalations.escalatedBy']);

        return view('doctor.support-tickets.show', compact('supportTicket'));
    }
}
