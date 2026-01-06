<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display customer care dashboard
     */
    public function index()
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        // Get statistics
        $stats = [
            'total_consultations' => Consultation::where('customer_care_id', $customerCare->id)->count(),
            'pending_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                  ->where('status', 'pending')->count(),
            'scheduled_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                     ->where('status', 'scheduled')->count(),
            'completed_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                    ->where('status', 'completed')->count(),
            'cancelled_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                                                    ->where('status', 'cancelled')->count(),
        ];

        // Get Customer Care Module statistics
        $interactionService = app(\App\Services\CustomerInteractionService::class);
        $ticketService = app(\App\Services\SupportTicketService::class);
        $escalationService = app(\App\Services\EscalationService::class);

        $customerCareStats = [
            'active_interactions' => \App\Models\CustomerInteraction::where('agent_id', $customerCare->id)
                ->where('status', 'active')->count(),
            'pending_tickets' => \App\Models\SupportTicket::where('agent_id', $customerCare->id)
                ->where('status', 'pending')->count(),
            'resolved_tickets_today' => $ticketService->getResolvedTodayCount($customerCare->id),
            'escalated_cases' => $escalationService->getEscalatedCasesCount($customerCare->id),
            'avg_response_time' => $interactionService->getAverageResponseTime($customerCare->id),
        ];

        // Get recent consultations
        $recentConsultations = Consultation::where('customer_care_id', $customerCare->id)
                                          ->with(['doctor', 'patient'])
                                          ->latest()
                                          ->limit(10)
                                          ->get();

        // Get recent interactions
        $recentInteractions = \App\Models\CustomerInteraction::where('agent_id', $customerCare->id)
            ->with(['user'])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent tickets
        $recentTickets = \App\Models\SupportTicket::where('agent_id', $customerCare->id)
            ->with(['user'])
            ->latest()
            ->limit(5)
            ->get();

        return view('customer-care.dashboard', compact(
            'stats',
            'customerCareStats',
            'recentConsultations',
            'recentInteractions',
            'recentTickets'
        ));
    }

    /**
     * Display consultations list
     */
    public function consultations(Request $request)
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        // Show all consultations, not just those assigned to this customer care agent
        $query = Consultation::with(['doctor', 'patient', 'payment', 'customerCare']);

        // Optional filter: Show only consultations assigned to this agent
        if ($request->has('my_consultations') && $request->my_consultations == '1') {
            $query->where('customer_care_id', $customerCare->id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $consultations = $query->latest()->paginate(20);

        return view('customer-care.consultations', compact('consultations'));
    }

    /**
     * Display consultation details
     */
    public function showConsultation($id)
    {
        // Allow viewing any consultation, not just those assigned to this agent
        $consultation = Consultation::with([
            'doctor', 
            'patient', 
            'payment', 
            'canvasser', 
            'nurse',
            'customerCare',
            'booking'
        ])->findOrFail($id);

        return view('customer-care.consultation-details', compact('consultation'));
    }
}
