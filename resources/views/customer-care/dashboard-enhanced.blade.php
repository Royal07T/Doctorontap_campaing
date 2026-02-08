@extends('layouts.customer-care')

@section('title', 'Customer Care Dashboard - Enhanced')

@php
    $headerTitle = 'Elite Control Center';
@endphp

@push('styles')
<style>
    /* Keyboard shortcut hints */
    .shortcut-hint {
        display: inline-block;
        padding: 2px 6px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 11px;
        font-family: monospace;
        color: #64748b;
    }
    
    /* Activity pulse animation */
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .pulse-dot {
        animation: pulse-dot 2s ease-in-out infinite;
    }
    
    /* Chart container */
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    /* Team status colors */
    .status-available { color: #10b981; }
    .status-busy { color: #f59e0b; }
    .status-on-call { color: #ef4444; }
    .status-break { color: #6b7280; }
    .status-offline { color: #94a3b8; }
</style>
@endpush

@section('content')
<div x-data="dashboardApp()" x-init="init()" @keydown.window="handleKeyPress($event)">
    
    <!-- Quick Action Modal (Ctrl+K) -->
    <div x-show="showQuickActions" 
         x-cloak
         class="fixed inset-0 z-50 flex items-start justify-center pt-20 bg-slate-900/60 backdrop-blur-sm"
         @click.self="showQuickActions = false"
         @keydown.escape.window="showQuickActions = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden animate-slide-up">
            <div class="p-6 border-b border-slate-100">
                <input type="text" 
                       x-ref="quickSearch"
                       x-model="quickSearchQuery"
                       @input="filterQuickActions()"
                       placeholder="Type to search or use shortcuts..." 
                       class="w-full px-4 py-3 text-lg border-0 focus:ring-0 outline-none">
            </div>
            <div class="max-h-96 overflow-y-auto">
                <template x-for="action in filteredQuickActions" :key="action.name">
                    <div @click="executeQuickAction(action)" 
                         class="px-6 py-4 hover:bg-purple-50 cursor-pointer flex items-center justify-between border-b border-slate-50 last:border-0">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl" x-text="action.icon"></span>
                            <div>
                                <div class="font-bold text-slate-800" x-text="action.name"></div>
                                <div class="text-xs text-slate-500" x-text="action.description"></div>
                            </div>
                        </div>
                        <span class="shortcut-hint" x-text="action.shortcut"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Success/Error Notifications -->
    <div x-show="notification.show"
         x-transition
         class="fixed top-4 right-4 z-50 max-w-md">
        <div :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'" 
             class="rounded-xl shadow-2xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold" x-text="notification.message"></span>
                </div>
                <button @click="notification.show = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts Help -->
    <div class="mb-4 bg-white rounded-xl shadow-sm p-3 flex items-center justify-between">
        <div class="flex items-center space-x-6 text-xs">
            <span class="text-slate-600 font-medium">Keyboard Shortcuts:</span>
            <span><span class="shortcut-hint">Ctrl+K</span> Quick Actions</span>
            <span><span class="shortcut-hint">Ctrl+S</span> Search</span>
            <span><span class="shortcut-hint">Ctrl+N</span> New Ticket</span>
            <span><span class="shortcut-hint">Ctrl+R</span> Refresh</span>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-slate-500">Auto-refresh:</span>
            <span class="pulse-dot w-2 h-2 bg-green-500 rounded-full"></span>
            <span class="text-xs font-medium text-green-600">Active</span>
        </div>
    </div>

    <!-- Main Stats Cards with Trending -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach([
            ['title' => 'Total Consultations', 'value' => $stats['total_consultations'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'purple', 'trend' => '+12%'],
            ['title' => 'Pending Queue', 'value' => $stats['pending_consultations'], 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber', 'trend' => '-5%'],
            ['title' => 'Scheduled Today', 'value' => $stats['scheduled_consultations'], 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'blue', 'trend' => '+8%'],
            ['title' => 'Completed', 'value' => $stats['completed_consultations'], 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'trend' => '+15%']
        ] as $stat)
        <div class="clean-card p-6 border-l-4 border-l-{{ $stat['color'] }}-600 hover:shadow-lg transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-{{ $stat['color'] }}-50 rounded-xl group-hover:bg-{{ $stat['color'] }}-600 transition-colors">
                    <svg class="w-6 h-6 text-{{ $stat['color'] }}-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ str_starts_with($stat['trend'], '+') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $stat['trend'] }}
                </span>
            </div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">{{ $stat['title'] }}</p>
                <p class="text-3xl font-black text-slate-800">{{ $stat['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Performance Metrics Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Performance Score -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center justify-between">
                <span>Performance Score</span>
                <span class="text-2xl font-black text-{{ $performanceMetrics['performance_score'] >= 80 ? 'green' : ($performanceMetrics['performance_score'] >= 60 ? 'amber' : 'red') }}-600">
                    {{ $performanceMetrics['performance_score'] }}
                </span>
            </h3>
            <div class="relative pt-1">
                <div class="overflow-hidden h-4 text-xs flex rounded-full bg-slate-100">
                    <div style="width:{{ $performanceMetrics['performance_score'] }}%" 
                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-purple-600 to-indigo-600"></div>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-2xl font-black text-slate-800">{{ $performanceMetrics['first_contact_resolution'] }}%</p>
                    <p class="text-xs text-slate-500 font-medium">First Contact Resolution</p>
                </div>
                <div>
                    <p class="text-2xl font-black text-slate-800">{{ $performanceMetrics['avg_handle_time'] }}m</p>
                    <p class="text-xs text-slate-500 font-medium">Avg Handle Time</p>
                </div>
            </div>
        </div>

        <!-- Today's Progress -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Today's Progress</h3>
            <div class="mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-xs font-medium text-slate-600">Cases Handled</span>
                    <span class="text-xs font-bold text-slate-800">{{ $performanceMetrics['cases_today'] }} / {{ $performanceMetrics['target_cases'] }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3">
                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ min(($performanceMetrics['cases_today'] / $performanceMetrics['target_cases']) * 100, 100) }}%"></div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4">
                <div class="text-center p-3 bg-green-50 rounded-xl">
                    <p class="text-lg font-black text-green-600">{{ $kpiMetrics['today']['consultations'] }}</p>
                    <p class="text-xs text-slate-600">Consultations</p>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-xl">
                    <p class="text-lg font-black text-blue-600">{{ $kpiMetrics['today']['tickets_resolved'] }}</p>
                    <p class="text-xs text-slate-600">Resolved</p>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-xl">
                    <p class="text-lg font-black text-purple-600">{{ $kpiMetrics['today']['interactions'] }}</p>
                    <p class="text-xs text-slate-600">Interactions</p>
                </div>
            </div>
        </div>

        <!-- SLA Compliance -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">SLA Compliance</h3>
            <div class="flex items-center justify-center mb-4">
                <div class="relative">
                    <svg class="transform -rotate-90" width="120" height="120">
                        <circle cx="60" cy="60" r="50" stroke="#f1f5f9" stroke-width="10" fill="none"/>
                        <circle cx="60" cy="60" r="50" 
                                stroke="{{ $kpiMetrics['sla_compliance'] >= 90 ? '#10b981' : ($kpiMetrics['sla_compliance'] >= 75 ? '#f59e0b' : '#ef4444') }}" 
                                stroke-width="10" 
                                fill="none"
                                stroke-dasharray="{{ (float)$kpiMetrics['sla_compliance'] * 3.14 }}, 314"
                                stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-3xl font-black text-slate-800">{{ $kpiMetrics['sla_compliance'] }}%</span>
                    </div>
                </div>
            </div>
            <p class="text-center text-xs text-slate-500">Target: >95% compliance</p>
        </div>
    </div>

    <!-- Smart Queue Management & Team Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Smart Queue -->
        <div class="lg:col-span-2 clean-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-800">Smart Queue Management</h3>
                <button @click="refreshQueue()" class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-bold hover:bg-purple-700">
                    Refresh Queue
                </button>
            </div>
            
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="text-center p-4 bg-red-50 rounded-xl border-2 border-red-200">
                    <p class="text-2xl font-black text-red-600">{{ $queueData['high_priority'] }}</p>
                    <p class="text-xs font-bold text-red-700 uppercase">High Priority</p>
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-xl">
                    <p class="text-2xl font-black text-amber-600">{{ $queueData['pending'] }}</p>
                    <p class="text-xs font-bold text-amber-700 uppercase">Waiting</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-xl">
                    <p class="text-2xl font-black text-blue-600">{{ $queueData['scheduled'] }}</p>
                    <p class="text-xs font-bold text-blue-700 uppercase">Scheduled</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <p class="text-2xl font-black text-green-600">{{ $queueData['avg_wait_time'] }}m</p>
                    <p class="text-xs font-bold text-green-700 uppercase">Avg Wait</p>
                </div>
            </div>

            @if($queueData['longest_waiting'])
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-red-800">⚠️ Longest Waiting Patient</p>
                        <p class="text-xs text-red-600 mt-1">
                            Reference: {{ $queueData['longest_waiting']->reference }} - 
                            Waiting: {{ $queueData['longest_waiting']->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <a href="{{ route('customer-care.consultations.show', $queueData['longest_waiting']->id) }}" 
                       class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700">
                        Handle Now
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Team Status Widget -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center justify-between">
                <span>Team Status</span>
                <span class="text-xs text-green-600 font-medium">{{ $teamStatus->where('status', '!=', 'offline')->count() }} Online</span>
            </h3>
            <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                @foreach($teamStatus as $agent)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr($agent->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $agent->name }}</p>
                            <p class="text-xs text-slate-500">{{ $agent->active_cases }} active cases</p>
                        </div>
                    </div>
                    <span class="status-{{ strtolower(str_replace(' ', '-', $agent->status ?? 'available')) }} text-lg">
                        {{ $agent->status === 'available' ? '🟢' : ($agent->status === 'busy' ? '🟡' : ($agent->status === 'on_call' ? '🔴' : '⚪')) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Hourly Distribution Chart -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Peak Hours Analysis (Last 7 Days)</h3>
            <div class="chart-container">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Consultation Status Distribution</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Real-time Activity Feed & Recent Items -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Activity Feed -->
        <div class="lg:col-span-2 clean-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center">
                    <span class="pulse-dot w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Live Activity Feed
                </h3>
                <span class="text-xs text-slate-500" x-text="'Updated ' + lastUpdateTime"></span>
            </div>
            <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar" id="activityFeed">
                @foreach($activityFeed as $activity)
                <div class="flex items-start space-x-3 p-3 hover:bg-slate-50 rounded-lg transition cursor-pointer" 
                     onclick="window.location='{{ $activity['url'] }}'">
                    <span class="text-2xl">{{ $activity['icon'] }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-800">{{ $activity['message'] }}</p>
                        <p class="text-xs text-slate-500">{{ $activity['detail'] }}</p>
                    </div>
                    <span class="text-xs text-slate-400">{{ $activity['time']->diffForHumans(null, true) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="clean-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Quick Stats</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-600">Today vs Yesterday</span>
                    <span class="text-sm font-bold {{ $kpiMetrics['today']['consultations'] > $kpiMetrics['yesterday']['consultations'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $kpiMetrics['today']['consultations'] > $kpiMetrics['yesterday']['consultations'] ? '+' : '' }}
                        {{ $kpiMetrics['today']['consultations'] - $kpiMetrics['yesterday']['consultations'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-600">This Week vs Last Week</span>
                    <span class="text-sm font-bold {{ $kpiMetrics['week']['consultations'] > $kpiMetrics['week']['last_week'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $kpiMetrics['week']['consultations'] > $kpiMetrics['week']['last_week'] ? '+' : '' }}
                        {{ $kpiMetrics['week']['consultations'] - $kpiMetrics['week']['last_week'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t">
                    <span class="text-xs text-slate-600">Customer Satisfaction</span>
                    <span class="text-sm font-bold text-purple-600">⭐ {{ $performanceMetrics['customer_satisfaction'] }}/5.0</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
function dashboardApp() {
    return {
        showQuickActions: false,
        quickSearchQuery: '',
        filteredQuickActions: [],
        lastUpdateTime: 'just now',
        notification: {
            show: false,
            type: 'success',
            message: ''
        },
        // Chart instances
        hourlyChart: null,
        statusChart: null,
        quickActions: [
            { name: 'Search Patients', icon: '🔍', shortcut: 'Ctrl+S', action: 'search', description: 'Quick patient search' },
            { name: 'New Ticket', icon: '🎫', shortcut: 'Ctrl+N', action: 'new-ticket', description: 'Create support ticket' },
            { name: 'Send SMS', icon: '📱', shortcut: 'Ctrl+M', action: 'sms', description: 'Send bulk SMS' },
            { name: 'Send Email', icon: '📧', shortcut: 'Ctrl+E', action: 'email', description: 'Send bulk email' },
            { name: 'View Queue', icon: '📋', shortcut: 'Ctrl+Q', action: 'queue', description: 'View consultation queue' },
            { name: 'Team Chat', icon: '💬', shortcut: 'Ctrl+T', action: 'team', description: 'Team collaboration' },
            { name: 'Reports', icon: '📊', shortcut: 'Ctrl+P', action: 'reports', description: 'Generate reports' },
            { name: 'Refresh', icon: '🔄', shortcut: 'Ctrl+R', action: 'refresh', description: 'Refresh dashboard' },
        ],
        
        init() {
            this.filteredQuickActions = this.quickActions;
            
            // Wait for DOM to be ready before initializing charts
            this.$nextTick(() => {
                setTimeout(() => {
                    this.initCharts();
                }, 100);
            });
            
            this.startRealTimeUpdates();
            
            // Focus quick search when modal opens
            this.$watch('showQuickActions', value => {
                if (value) {
                    setTimeout(() => this.$refs.quickSearch.focus(), 100);
                }
            });
        },
        
        handleKeyPress(e) {
            // Ctrl+K - Quick Actions
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                this.showQuickActions = !this.showQuickActions;
            }
            // Ctrl+R - Refresh
            else if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                this.refreshDashboard();
            }
            // Ctrl+N - New Ticket
            else if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = '{{ route("customer-care.tickets.create") }}';
            }
            // Ctrl+S - Search
            else if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                document.getElementById('patientSearch')?.focus();
            }
        },
        
        filterQuickActions() {
            const query = this.quickSearchQuery.toLowerCase();
            this.filteredQuickActions = this.quickActions.filter(action => 
                action.name.toLowerCase().includes(query) || 
                action.description.toLowerCase().includes(query)
            );
        },
        
        executeQuickAction(action) {
            this.showQuickActions = false;
            
            const routes = {
                'search': '#patientSearch',
                'new-ticket': '{{ route("customer-care.tickets.create") }}',
                'sms': '{{ route("customer-care.bulk-sms.index") }}',
                'email': '{{ route("customer-care.bulk-email.index") }}',
                'queue': '{{ route("customer-care.consultations") }}',
                'reports': '#',
                'refresh': () => this.refreshDashboard(),
            };
            
            const route = routes[action.action];
            if (typeof route === 'function') {
                route();
            } else if (route.startsWith('#')) {
                document.querySelector(route)?.focus();
            } else {
                window.location.href = route;
            }
        },
        
        showNotification(type, message) {
            this.notification = { show: true, type, message };
            setTimeout(() => this.notification.show = false, 5000);
        },
        
        refreshDashboard() {
            this.showNotification('success', 'Dashboard refreshed successfully!');
            window.location.reload();
        },
        
        refreshQueue() {
            this.showNotification('success', 'Queue data refreshed!');
            this.fetchRealtimeStats();
        },
        
        initCharts() {
            try {
                // Destroy existing charts if they exist
                if (this.hourlyChart) {
                    this.hourlyChart.destroy();
                    this.hourlyChart = null;
                }
                if (this.statusChart) {
                    this.statusChart.destroy();
                    this.statusChart = null;
                }
                
                // Hourly Distribution Chart
                const hourlyCtx = document.getElementById('hourlyChart');
                if (hourlyCtx && hourlyCtx.getContext) {
                    this.hourlyChart = new Chart(hourlyCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: Array.from({length: 24}, (_, i) => i + ':00'),
                            datasets: [{
                                label: 'Consultations',
                                data: @json($kpiMetrics['hourly_distribution']),
                                borderColor: 'rgb(147, 51, 234)',
                                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                } else {
                    console.warn('Hourly chart canvas not found');
                }
                
                // Status Distribution Chart
                const statusCtx = document.getElementById('statusChart');
                const statusData = @json($kpiMetrics['status_distribution']);
                if (statusCtx && statusCtx.getContext) {
                    this.statusChart = new Chart(statusCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                            datasets: [{
                                data: Object.values(statusData),
                                backgroundColor: [
                                    'rgb(147, 51, 234)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(245, 158, 11)',
                                    'rgb(239, 68, 68)',
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                } else {
                    console.warn('Status chart canvas not found');
                }
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        },
        
        startRealTimeUpdates() {
            // Fetch updates every 30 seconds
            setInterval(() => {
                this.fetchRealtimeActivity();
                this.fetchRealtimeStats();
            }, 30000);
        },
        
        async fetchRealtimeActivity() {
            try {
                const response = await fetch('{{ route("customer-care.dashboard.realtime-activity") }}');
                const data = await response.json();
                if (data.success) {
                    this.updateActivityFeed(data.activities);
                    this.lastUpdateTime = 'just now';
                }
            } catch (error) {
                console.error('Failed to fetch activity:', error);
            }
        },
        
        async fetchRealtimeStats() {
            try {
                const response = await fetch('{{ route("customer-care.dashboard.realtime-stats") }}');
                const data = await response.json();
                if (data.success) {
                    // Update stats dynamically without page reload
                    console.log('Stats updated:', data.stats);
                }
            } catch (error) {
                console.error('Failed to fetch stats:', error);
            }
        },
        
        updateActivityFeed(activities) {
            // Update activity feed with new items
            // This would prepend new activities to the feed
        }
    }
}
</script>
@endpush

