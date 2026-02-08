<?php

namespace App\Http\Controllers\CustomerCare;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\CustomerCare;
use App\Models\CustomerInteraction;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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

        // Get KPI metrics for charts
        $kpiMetrics = $this->getKPIMetrics($customerCare->id);
        
        // Get queue management data
        $queueData = $this->getQueueData();
        
        // Get team status
        $teamStatus = $this->getTeamStatus();
        
        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($customerCare->id);
        
        // Get activity feed
        $activityFeed = $this->getActivityFeed($customerCare->id);

        return view('customer-care.dashboard-enhanced', compact(
            'stats',
            'customerCareStats',
            'recentConsultations',
            'recentInteractions',
            'recentTickets',
            'kpiMetrics',
            'queueData',
            'teamStatus',
            'performanceMetrics',
            'activityFeed'
        ));
    }
    
    /**
     * Get KPI metrics for dashboard charts
     */
    private function getKPIMetrics($agentId)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        
        return [
            'today' => [
                'consultations' => Consultation::where('customer_care_id', $agentId)
                    ->whereDate('created_at', $today)->count(),
                'tickets_resolved' => SupportTicket::where('agent_id', $agentId)
                    ->where('status', 'resolved')
                    ->whereDate('updated_at', $today)->count(),
                'interactions' => CustomerInteraction::where('agent_id', $agentId)
                    ->whereDate('created_at', $today)->count(),
            ],
            'yesterday' => [
                'consultations' => Consultation::where('customer_care_id', $agentId)
                    ->whereDate('created_at', $yesterday)->count(),
                'tickets_resolved' => SupportTicket::where('agent_id', $agentId)
                    ->where('status', 'resolved')
                    ->whereDate('updated_at', $yesterday)->count(),
            ],
            'week' => [
                'consultations' => Consultation::where('customer_care_id', $agentId)
                    ->whereBetween('created_at', [$thisWeek, now()])->count(),
                'last_week' => Consultation::where('customer_care_id', $agentId)
                    ->whereBetween('created_at', [$lastWeek, $thisWeek])->count(),
            ],
            'hourly_distribution' => $this->getHourlyDistribution($agentId),
            'status_distribution' => $this->getStatusDistribution($agentId),
            'sla_compliance' => $this->getSLACompliance($agentId),
        ];
    }
    
    /**
     * Get hourly distribution for peak hours chart
     */
    private function getHourlyDistribution($agentId)
    {
        $data = Consultation::where('customer_care_id', $agentId)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
            
        $hours = range(0, 23);
        $distribution = [];
        foreach ($hours as $hour) {
            $distribution[] = $data[$hour] ?? 0;
        }
        
        return $distribution;
    }
    
    /**
     * Get status distribution for pie chart
     */
    private function getStatusDistribution($agentId)
    {
        return Consultation::where('customer_care_id', $agentId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }
    
    /**
     * Get SLA compliance percentage
     */
    private function getSLACompliance($agentId)
    {
        $total = SupportTicket::where('agent_id', $agentId)
            ->whereNotNull('resolved_at')
            ->count();
            
        if ($total === 0) return 100;
        
        $compliant = SupportTicket::where('agent_id', $agentId)
            ->whereNotNull('resolved_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, resolved_at) <= 240') // 4 hours SLA
            ->count();
            
        return round(($compliant / $total) * 100, 1);
    }
    
    /**
     * Get queue management data
     */
    private function getQueueData()
    {
        // High priority = pending for more than 1 hour
        $oneHourAgo = Carbon::now()->subHour();
        
        return [
            'pending' => Consultation::where('status', 'pending')->count(),
            'high_priority' => Consultation::where('status', 'pending')
                ->where('created_at', '<', $oneHourAgo)->count(),
            'scheduled' => Consultation::where('status', 'scheduled')
                ->whereDate('scheduled_at', '>=', now())->count(),
            'longest_waiting' => Consultation::where('status', 'pending')
                ->orderBy('created_at', 'asc')
                ->first(),
            'avg_wait_time' => $this->calculateAverageWaitTime(),
        ];
    }
    
    /**
     * Calculate average wait time
     */
    private function calculateAverageWaitTime()
    {
        $avgMinutes = Consultation::where('status', 'completed')
            ->whereNotNull('started_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, started_at)) as avg_wait'))
            ->value('avg_wait');
            
        return round($avgMinutes ?? 0);
    }
    
    /**
     * Get team status
     */
    private function getTeamStatus()
    {
        $agents = CustomerCare::select([
                'id', 
                'name', 
                'last_activity_at',
                'last_login_at',
                DB::raw('(SELECT COUNT(*) FROM consultations WHERE customer_care_id = customer_cares.id AND status IN ("pending", "in_progress")) as active_cases')
            ])
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();
            
        // Add computed status based on last_activity_at
        $agents->each(function($agent) {
            $minutesSinceActivity = $agent->last_activity_at 
                ? Carbon::parse($agent->last_activity_at)->diffInMinutes(now()) 
                : 9999;
                
            if ($minutesSinceActivity < 5) {
                $agent->status = 'available';
            } elseif ($minutesSinceActivity < 30) {
                $agent->status = 'busy';
            } else {
                $agent->status = 'offline';
            }
            
            // If they have 5+ active cases, mark as busy
            if ($agent->active_cases >= 5) {
                $agent->status = 'busy';
            }
        });
        
        // Sort by status (available first, then busy, then offline)
        return $agents->sortBy(function($agent) {
            $order = ['available' => 1, 'busy' => 2, 'on_call' => 3, 'break' => 4, 'offline' => 5];
            return $order[$agent->status] ?? 99;
        })->values();
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics($agentId)
    {
        $totalResolved = SupportTicket::where('agent_id', $agentId)
            ->where('status', 'resolved')->count();
            
        // First contact resolution = resolved tickets (assuming all are first contact without escalation field)
        $firstContactResolution = $totalResolved;
            
        return [
            'performance_score' => $this->calculatePerformanceScore($agentId),
            'first_contact_resolution' => $totalResolved > 0 
                ? round(($firstContactResolution / $totalResolved) * 100, 1) 
                : 0,
            'avg_handle_time' => $this->calculateAvgHandleTime($agentId),
            'customer_satisfaction' => $this->getCustomerSatisfactionScore($agentId),
            'cases_today' => Consultation::where('customer_care_id', $agentId)
                ->whereDate('created_at', today())->count(),
            'target_cases' => 20, // Daily target
        ];
    }
    
    /**
     * Calculate performance score (0-100)
     */
    private function calculatePerformanceScore($agentId)
    {
        $slaCompliance = $this->getSLACompliance($agentId);
        $totalTickets = SupportTicket::where('agent_id', $agentId)->count();
        $resolvedTickets = SupportTicket::where('agent_id', $agentId)
            ->where('status', 'resolved')->count();
            
        $resolutionRate = $totalTickets > 0 
            ? ($resolvedTickets / $totalTickets) * 100 
            : 0;
            
        // Weighted average: SLA (40%) + Resolution Rate (40%) + Activity (20%)
        $score = ($slaCompliance * 0.4) + ($resolutionRate * 0.4) + (min($totalTickets, 50) * 0.4);
        
        return round(min($score, 100), 1);
    }
    
    /**
     * Calculate average handle time in minutes
     */
    private function calculateAvgHandleTime($agentId)
    {
        $avgMinutes = CustomerInteraction::where('agent_id', $agentId)
            ->whereNotNull('ended_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_time'))
            ->value('avg_time');
            
        return round($avgMinutes ?? 0);
    }
    
    /**
     * Get customer satisfaction score
     */
    private function getCustomerSatisfactionScore($agentId)
    {
        // This would come from a reviews/ratings table
        // For now, return a placeholder
        return 4.5;
    }
    
    /**
     * Get activity feed
     */
    private function getActivityFeed($agentId, $limit = 20)
    {
        $activities = [];
        
        // Recent consultations
        $consultations = Consultation::where('customer_care_id', $agentId)
            ->with('patient')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($consultation) {
                return [
                    'type' => 'consultation',
                    'icon' => '📋',
                    'color' => 'blue',
                    'message' => "New consultation #{$consultation->reference} assigned",
                    'detail' => $consultation->patient->name ?? 'Unknown',
                    'time' => $consultation->created_at,
                    'url' => route('customer-care.consultations.show', $consultation->id),
                ];
            });
            
        // Recent tickets
        $tickets = SupportTicket::where('agent_id', $agentId)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($ticket) {
                $statusColors = [
                    'resolved' => 'green',
                    'pending' => 'yellow',
                    'escalated' => 'red',
                ];
                return [
                    'type' => 'ticket',
                    'icon' => $ticket->status === 'resolved' ? '✅' : '🎫',
                    'color' => $statusColors[$ticket->status] ?? 'gray',
                    'message' => "Ticket #{$ticket->id} {$ticket->status}",
                    'detail' => $ticket->subject ?? 'No subject',
                    'time' => $ticket->updated_at,
                    'url' => route('customer-care.tickets.show', $ticket->id),
                ];
            });
            
        // Merge and sort by time
        $activities = collect($consultations)->concat($tickets)
            ->sortByDesc('time')
            ->take($limit)
            ->values();
            
        return $activities;
    }
    
    /**
     * API: Get real-time activity feed
     */
    public function getRealtimeActivity(Request $request)
    {
        $customerCare = Auth::guard('customer_care')->user();
        $since = $request->get('since', now()->subMinutes(5));
        
        $activities = $this->getActivityFeed($customerCare->id, 10);
        
        return response()->json([
            'success' => true,
            'activities' => $activities,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    
    /**
     * API: Get real-time stats
     */
    public function getRealtimeStats(Request $request)
    {
        $customerCare = Auth::guard('customer_care')->user();
        
        // Count online agents (active in last 15 minutes)
        $onlineThreshold = now()->subMinutes(15);
        $teamOnline = CustomerCare::where('is_active', true)
            ->where(function($query) use ($onlineThreshold) {
                $query->where('last_activity_at', '>=', $onlineThreshold)
                      ->orWhereHas('consultations', function($q) {
                          $q->whereIn('status', ['pending', 'in_progress']);
                      });
            })
            ->count();
        
        $stats = [
            'pending_consultations' => Consultation::where('customer_care_id', $customerCare->id)
                ->where('status', 'pending')->count(),
            'active_tickets' => SupportTicket::where('agent_id', $customerCare->id)
                ->whereIn('status', ['pending', 'in_progress'])->count(),
            'team_online' => $teamOnline,
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'timestamp' => now()->toIso8601String(),
        ]);
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
