<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCare\StoreInteractionRequest;
use App\Http\Requests\CustomerCare\AddNoteRequest;
use App\Models\CustomerInteraction;
use App\Models\Patient;
use App\Services\CustomerInteractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InteractionsController extends Controller
{
    protected $interactionService;

    public function __construct(CustomerInteractionService $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    /**
     * Display a listing of interactions
     */
    public function index(Request $request)
    {
        $agent = Auth::guard('customer_care')->user();

        $query = CustomerInteraction::where('agent_id', $agent->id)
            ->with(['user', 'notes']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by channel
        if ($request->has('channel') && $request->channel !== '') {
            $query->where('channel', $request->channel);
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

        return view('customer-care.interactions.index', compact('interactions'));
    }

    /**
     * Show the form for creating a new interaction
     */
    public function create(Request $request)
    {
        $query = Patient::query();
        
        // Search filter for customer names
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $patients = $query->orderBy('name')->limit(100)->get();
        $searchTerm = $request->get('search', '');
        
        return view('customer-care.interactions.create', compact('patients', 'searchTerm'));
    }

    /**
     * Store a newly created interaction
     */
    public function store(StoreInteractionRequest $request)
    {
        $interaction = $this->interactionService->createInteraction($request->validated());

        return redirect()
            ->route('customer-care.interactions.show', $interaction)
            ->with('success', 'Interaction created successfully.');
    }

    /**
     * Display the specified interaction
     */
    public function show(CustomerInteraction $interaction)
    {
        $user = Auth::guard('customer_care')->user();
        
        // Customer care agents can view all interactions
        // Only check if user is authenticated
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        $interaction->load(['user', 'agent', 'notes.creator', 'escalations']);

        // Get related data for context
        $relatedInteractions = CustomerInteraction::where('user_id', $interaction->user_id)
            ->where('id', '!=', $interaction->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'summary', 'status', 'channel', 'created_at']);
        
        $relatedTickets = \App\Models\SupportTicket::where('user_id', $interaction->user_id)
            ->latest()
            ->limit(5)
            ->get(['id', 'ticket_number', 'subject', 'status', 'created_at']);
        
        $relatedConsultations = \App\Models\Consultation::where('patient_id', $interaction->user_id)
            ->latest()
            ->limit(5)
            ->get(['id', 'reference', 'status', 'created_at']);

        return view('customer-care.interactions.show', compact(
            'interaction',
            'relatedInteractions',
            'relatedTickets',
            'relatedConsultations'
        ));
    }

    /**
     * End an interaction
     */
    public function end(CustomerInteraction $interaction)
    {
        Gate::authorize('update', $interaction);

        $this->interactionService->endInteraction($interaction);

        return redirect()
            ->route('customer-care.interactions.show', $interaction)
            ->with('success', 'Interaction ended successfully.');
    }

    /**
     * Add a note to an interaction
     */
    public function addNote(AddNoteRequest $request, CustomerInteraction $interaction)
    {
        Gate::authorize('update', $interaction);

        $this->interactionService->addNote(
            $interaction,
            $request->note,
            $request->boolean('is_internal', true)
        );

        return redirect()
            ->route('customer-care.interactions.show', $interaction)
            ->with('success', 'Note added successfully.');
    }
}
