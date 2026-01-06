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
    public function create()
    {
        $patients = Patient::orderBy('name')->get();
        return view('customer-care.interactions.create', compact('patients'));
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
        $this->authorize('view', $interaction);

        $interaction->load(['user', 'agent', 'notes.creator']);

        return view('customer-care.interactions.show', compact('interaction'));
    }

    /**
     * End an interaction
     */
    public function end(CustomerInteraction $interaction)
    {
        $this->authorize('update', $interaction);

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
        $this->authorize('update', $interaction);

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
