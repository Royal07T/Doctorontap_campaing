<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $agent->name }} - Agent Activities - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false, activeTab: 'overview' }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'customer-cares'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Agent Activities'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('admin.customer-cares') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white rounded-lg hover:bg-gray-50 transition border border-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Customer Care
                    </a>
                </div>

                <!-- Agent Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                {{ substr($agent->name, 0, 1) }}
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">{{ $agent->name }}</h1>
                                <p class="text-sm text-gray-600">{{ $agent->email }}</p>
                                @if($agent->phone)
                                <p class="text-xs text-gray-500">{{ $agent->phone }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-2">
                                    @if($agent->is_active)
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">Active</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Member Since</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $agent->created_at->format('M d, Y') }}</p>
                            @if($agent->last_login_at)
                            <p class="text-xs text-gray-500 mt-1">Last Login</p>
                            <p class="text-xs text-gray-600">{{ $agent->last_login_at->format('M d, Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Interactions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Interactions</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_interactions'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="text-emerald-600 font-semibold">{{ $stats['resolved_interactions'] }}</span> resolved
                                </p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Tickets</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tickets'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="text-amber-600 font-semibold">{{ $stats['open_tickets'] }}</span> open,
                                    <span class="text-emerald-600 font-semibold">{{ $stats['resolved_tickets'] }}</span> resolved
                                </p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Escalations -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Total Escalations</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_escalations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span class="text-amber-600 font-semibold">{{ $stats['pending_escalations'] }}</span> pending,
                                    <span class="text-emerald-600 font-semibold">{{ $stats['resolved_escalations'] }}</span> resolved
                                </p>
                            </div>
                            <div class="bg-orange-50 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Performance -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-emerald-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1">Performance</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['resolved_tickets_today'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Resolved today</p>
                                @if($stats['avg_response_time'])
                                <p class="text-xs text-gray-500 mt-1">
                                    Avg: {{ number_format($stats['avg_response_time'], 1) }}s
                                </p>
                                @endif
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'overview'" 
                                    :class="activeTab === 'overview' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors">
                                Overview
                            </button>
                            <button @click="activeTab = 'interactions'" 
                                    :class="activeTab === 'interactions' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors">
                                Interactions ({{ $interactions->total() }})
                            </button>
                            <button @click="activeTab = 'tickets'" 
                                    :class="activeTab === 'tickets' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors">
                                Tickets ({{ $tickets->total() }})
                            </button>
                            <button @click="activeTab = 'escalations'" 
                                    :class="activeTab === 'escalations' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-3 text-sm font-semibold border-b-2 transition-colors">
                                Escalations ({{ $escalations->total() }})
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div x-show="activeTab === 'overview'" x-cloak>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Recent Activity (Last 7 Days)</h3>
                            
                            <!-- Recent Interactions -->
                            @if($recentInteractions->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Recent Interactions
                                </h4>
                                <div class="space-y-2">
                                    @foreach($recentInteractions as $interaction)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $interaction->user->name ?? 'Unknown' }}</p>
                                                <p class="text-xs text-gray-600 truncate">{{ \Illuminate\Support\Str::limit($interaction->summary, 60) }}</p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($interaction->status === 'active') bg-blue-100 text-blue-800
                                                    @elseif($interaction->status === 'resolved') bg-emerald-100 text-emerald-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($interaction->status) }}
                                                </span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $interaction->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Recent Tickets -->
                            @if($recentTickets->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Recent Tickets
                                </h4>
                                <div class="space-y-2">
                                    @foreach($recentTickets as $ticket)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number }} - {{ $ticket->subject }}</p>
                                                <p class="text-xs text-gray-600">{{ $ticket->user->name ?? 'Unknown' }}</p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($ticket->status === 'open') bg-yellow-100 text-yellow-800
                                                    @elseif($ticket->status === 'resolved') bg-emerald-100 text-emerald-800
                                                    @elseif($ticket->status === 'pending') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($ticket->status) }}
                                                </span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $ticket->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Recent Escalations -->
                            @if($recentEscalations->count() > 0)
                            <div>
                                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    Recent Escalations
                                </h4>
                                <div class="space-y-2">
                                    @foreach($recentEscalations as $escalation)
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if($escalation->supportTicket)
                                                        Ticket: {{ $escalation->supportTicket->ticket_number }}
                                                    @elseif($escalation->customerInteraction)
                                                        Interaction: {{ \Illuminate\Support\Str::limit($escalation->customerInteraction->summary, 40) }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($escalation->reason, 60) }}</p>
                                            </div>
                                            <div class="text-right ml-4">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($escalation->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($escalation->status === 'resolved') bg-emerald-100 text-emerald-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($escalation->status) }}
                                                </span>
                                                <p class="text-xs text-gray-500 mt-1">{{ $escalation->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($recentInteractions->count() === 0 && $recentTickets->count() === 0 && $recentEscalations->count() === 0)
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500">No recent activity in the last 7 days</p>
                            </div>
                            @endif
                        </div>

                        <!-- Interactions Tab -->
                        <div x-show="activeTab === 'interactions'" x-cloak>
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">All Interactions</h3>
                            </div>
                            @if($interactions->count() > 0)
                            <div class="space-y-3">
                                @foreach($interactions as $interaction)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-sm transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <p class="text-sm font-semibold text-gray-900">{{ $interaction->user->name ?? 'Unknown' }}</p>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($interaction->status === 'active') bg-blue-100 text-blue-800
                                                    @elseif($interaction->status === 'resolved') bg-emerald-100 text-emerald-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($interaction->status) }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    {{ ucfirst($interaction->channel) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-700 mb-2">{{ $interaction->summary }}</p>
                                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                                <span>Started: {{ $interaction->started_at ? $interaction->started_at->format('M d, Y H:i') : 'N/A' }}</span>
                                                @if($interaction->ended_at)
                                                <span>Ended: {{ $interaction->ended_at->format('M d, Y H:i') }}</span>
                                                @endif
                                                @if($interaction->duration_seconds)
                                                <span>Duration: {{ round($interaction->duration_seconds / 60, 1) }} min</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.customer-care-oversight.interactions.show', $interaction) }}" 
                                           class="ml-4 px-3 py-1.5 text-xs font-semibold text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $interactions->links() }}
                            </div>
                            @else
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500">No interactions found</p>
                            </div>
                            @endif
                        </div>

                        <!-- Tickets Tab -->
                        <div x-show="activeTab === 'tickets'" x-cloak>
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">All Tickets</h3>
                            </div>
                            @if($tickets->count() > 0)
                            <div class="space-y-3">
                                @foreach($tickets as $ticket)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-sm transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <p class="text-sm font-semibold text-gray-900">{{ $ticket->ticket_number }}</p>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($ticket->status === 'open') bg-yellow-100 text-yellow-800
                                                    @elseif($ticket->status === 'resolved') bg-emerald-100 text-emerald-800
                                                    @elseif($ticket->status === 'pending') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($ticket->status) }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    {{ ucfirst($ticket->category) }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($ticket->priority === 'urgent') bg-red-100 text-red-800
                                                    @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800
                                                    @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900 mb-1">{{ $ticket->subject }}</p>
                                            <p class="text-sm text-gray-700 mb-2">{{ \Illuminate\Support\Str::limit($ticket->description, 100) }}</p>
                                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                                <span>Customer: {{ $ticket->user->name ?? 'Unknown' }}</span>
                                                <span>Created: {{ $ticket->created_at->format('M d, Y H:i') }}</span>
                                                @if($ticket->resolved_at)
                                                <span>Resolved: {{ $ticket->resolved_at->format('M d, Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.customer-care-oversight.tickets.show', $ticket) }}" 
                                           class="ml-4 px-3 py-1.5 text-xs font-semibold text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $tickets->links() }}
                            </div>
                            @else
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500">No tickets found</p>
                            </div>
                            @endif
                        </div>

                        <!-- Escalations Tab -->
                        <div x-show="activeTab === 'escalations'" x-cloak>
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">All Escalations</h3>
                            </div>
                            @if($escalations->count() > 0)
                            <div class="space-y-3">
                                @foreach($escalations as $escalation)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-sm transition">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($escalation->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($escalation->status === 'resolved') bg-emerald-100 text-emerald-800
                                                    @elseif($escalation->status === 'in_progress') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $escalation->status)) }}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    To: {{ ucfirst($escalation->escalated_to_type) }}
                                                </span>
                                            </div>
                                            @if($escalation->supportTicket)
                                            <p class="text-sm font-medium text-gray-900 mb-1">
                                                Ticket: {{ $escalation->supportTicket->ticket_number }} - {{ $escalation->supportTicket->subject }}
                                            </p>
                                            <p class="text-xs text-gray-600 mb-2">Customer: {{ $escalation->supportTicket->user->name ?? 'Unknown' }}</p>
                                            @elseif($escalation->customerInteraction)
                                            <p class="text-sm font-medium text-gray-900 mb-1">Interaction</p>
                                            <p class="text-xs text-gray-600 mb-2">Customer: {{ $escalation->customerInteraction->user->name ?? 'Unknown' }}</p>
                                            @endif
                                            <p class="text-sm text-gray-700 mb-2">{{ \Illuminate\Support\Str::limit($escalation->reason, 100) }}</p>
                                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                                <span>Created: {{ $escalation->created_at->format('M d, Y H:i') }}</span>
                                                @if($escalation->resolved_at)
                                                <span>Resolved: {{ $escalation->resolved_at->format('M d, Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.customer-care-oversight.escalations.show', $escalation) }}" 
                                           class="ml-4 px-3 py-1.5 text-xs font-semibold text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $escalations->links() }}
                            </div>
                            @else
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500">No escalations found</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

