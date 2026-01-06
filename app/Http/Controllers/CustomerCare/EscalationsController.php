<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCare\EscalateRequest;
use App\Models\Escalation;
use App\Models\SupportTicket;
use App\Models\CustomerInteraction;
use App\Models\AdminUser;
use App\Models\Doctor;
use App\Services\EscalationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EscalationsController extends Controller
{
    protected $escalationService;

    public function __construct(EscalationService $escalationService)
    {
        $this->escalationService = $escalationService;
    }

    /**
     * Display a listing of escalations
     */
    public function index(Request $request)
    {
        $agent = Auth::guard('customer_care')->user();

        $query = Escalation::where('escalated_by', $agent->id)
            ->with(['escalatedBy', 'supportTicket', 'customerInteraction']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('escalated_to_type') && $request->escalated_to_type !== '') {
            $query->where('escalated_to_type', $request->escalated_to_type);
        }

        $escalations = $query->latest()->paginate(20);

        return view('customer-care.escalations.index', compact('escalations'));
    }

    /**
     * Show the form for escalating a ticket
     */
    public function createFromTicket(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $admins = AdminUser::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();

        return view('customer-care.escalations.create-from-ticket', compact('ticket', 'admins', 'doctors'));
    }

    /**
     * Show the form for escalating an interaction
     */
    public function createFromInteraction(CustomerInteraction $interaction)
    {
        $this->authorize('view', $interaction);

        $admins = AdminUser::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();

        return view('customer-care.escalations.create-from-interaction', compact('interaction', 'admins', 'doctors'));
    }

    /**
     * Escalate a support ticket
     */
    public function escalateTicket(EscalateRequest $request, SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $escalation = $this->escalationService->escalateTicket($ticket, $request->validated());

        return redirect()
            ->route('customer-care.escalations.show', $escalation)
            ->with('success', 'Ticket escalated successfully.');
    }

    /**
     * Escalate a customer interaction
     */
    public function escalateInteraction(EscalateRequest $request, CustomerInteraction $interaction)
    {
        $this->authorize('update', $interaction);

        $escalation = $this->escalationService->escalateInteraction($interaction, $request->validated());

        return redirect()
            ->route('customer-care.escalations.show', $escalation)
            ->with('success', 'Interaction escalated successfully.');
    }

    /**
     * Display the specified escalation
     */
    public function show(Escalation $escalation)
    {
        $this->authorize('view', $escalation);

        $escalation->load([
            'escalatedBy',
            'supportTicket.user',
            'customerInteraction.user'
        ]);

        return view('customer-care.escalations.show', compact('escalation'));
    }
}
