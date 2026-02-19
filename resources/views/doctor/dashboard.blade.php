@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('header-title', 'Dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto" 
     x-data="dashboardData()">
    
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 mb-1">Welcome back, Dr. {{ Auth::guard('doctor')->user()->last_name }}</h1>
                <p class="text-sm text-gray-500">Practice overview for {{ now()->format('F j, Y') }}</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Start Consultation
                </a>
            </div>
        </div>

        <!-- KYC Banner (if needed) -->
        @php
            $doctor = Auth::guard('doctor')->user();
            $isFullyVerified = $doctor->mdcn_certificate_verified && $doctor->is_approved;
        @endphp
        
        @if(!$isFullyVerified)
        <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-medium text-amber-900">Complete your profile verification to start receiving consultations</span>
                </div>
                <a href="{{ route('doctor.profile') }}" class="text-sm font-semibold text-amber-700 hover:text-amber-800 whitespace-nowrap">Complete Now →</a>
            </div>
        </div>
        @endif
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Patient Queue -->
        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                @if($patientQueueCount > 0)
                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">{{ $patientQueueCount }}</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1.5">Patient Queue</p>
            <h3 class="text-2xl font-bold text-gray-900 mb-0.5">{{ $patientQueueCount }}</h3>
            <p class="text-xs text-gray-400">Waiting</p>
        </div>

        <!-- Total Consultations -->
        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-teal-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                @if($stats['consultations_growth'] != 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $stats['consultations_growth'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $stats['consultations_growth'] > 0 ? '+' : '' }}{{ $stats['consultations_growth'] }}%
                </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1.5">Total Consultations</p>
            <h3 class="text-2xl font-bold text-gray-900 mb-0.5">{{ number_format($stats['total_consultations'] ?? 0) }}</h3>
            <p class="text-xs text-gray-400">All time</p>
        </div>

        <!-- Total Earnings -->
        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                @if($stats['earnings_growth'] != 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $stats['earnings_growth'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $stats['earnings_growth'] > 0 ? '+' : '' }}{{ $stats['earnings_growth'] }}%
                </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1.5">Total Earnings</p>
            <h3 class="text-2xl font-bold text-gray-900 mb-0.5">₦{{ number_format($stats['total_earnings'] ?? 0, 0) }}</h3>
            <p class="text-xs text-gray-400">Lifetime</p>
        </div>

        <!-- Patient Rating -->
        <div class="bg-white rounded-lg p-5 border border-gray-200 shadow-sm hover:shadow transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 font-semibold">Top 5%</span>
            </div>
            <p class="text-xs text-gray-500 font-medium mb-1.5">Patient Rating</p>
            <div class="flex items-baseline gap-1 mb-0.5">
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format(Auth::guard('doctor')->user()->average_rating ?? 4.92, 1) }}</h3>
                <span class="text-sm text-gray-400">/ 5.0</span>
            </div>
            <p class="text-xs text-gray-400">Based on reviews</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left Column: Charts -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Consultation Trends -->
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Consultation Trends</h3>
                        <p class="text-xs text-gray-500">Last 30 days</p>
                    </div>
                </div>
                <div style="height: 200px;">
                    <canvas id="consultationTrendsChart"></canvas>
                </div>
            </div>

            <!-- Revenue Growth -->
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Revenue Growth</h3>
                        <p class="text-xs text-gray-500">Last 6 months</p>
                    </div>
                </div>
                <div style="height: 200px;">
                    <canvas id="revenueGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right Column: Next Consultation & Priority Queue -->
        <div class="space-y-6">
            <!-- Next Consultation -->
            @if($nextConsultation)
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Next Consultation</h3>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">
                        {{ $nextConsultation->status === 'pending' ? 'Waiting' : 'Scheduled' }}
                    </span>
                </div>
                
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    @if($nextConsultation->patient && $nextConsultation->patient->photo_url)
                        <img src="{{ $nextConsultation->patient->photo_url }}" class="w-12 h-12 rounded-full object-cover border border-gray-200">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-teal-500 flex items-center justify-center text-white font-semibold text-lg border border-gray-200">
                            {{ substr($nextConsultation->first_name ?? 'P', 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-900 mb-0.5 truncate">{{ $nextConsultation->first_name }} {{ $nextConsultation->last_name }}</h4>
                        <p class="text-xs text-gray-500 truncate">{{ $nextConsultation->reference }}</p>
                    </div>
                </div>

                @if($nextConsultation->scheduled_at)
                <div class="mb-4">
                    <div class="flex items-center gap-2 p-3 bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-600 font-medium truncate">{{ $nextConsultation->scheduled_at->format('M d, Y') }}</p>
                            <p class="text-sm font-bold text-blue-600" x-data="{ 
                                timeLeft: '{{ $nextConsultation->scheduled_at->diffForHumans() }}',
                                updateTime() {
                                    const scheduled = new Date('{{ $nextConsultation->scheduled_at->toIso8601String() }}');
                                    const now = new Date();
                                    const diff = scheduled - now;
                                    if (diff > 0) {
                                        const hours = Math.floor(diff / 3600000);
                                        const minutes = Math.floor((diff % 3600000) / 60000);
                                        this.timeLeft = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
                                    } else {
                                        this.timeLeft = 'Now';
                                    }
                                }
                            }" x-init="setInterval(() => updateTime(), 60000); updateTime()" x-text="timeLeft"></p>
                        </div>
                    </div>
                </div>
                @else
                <div class="mb-4 p-3 bg-red-50 rounded-lg">
                    <p class="text-sm font-semibold text-red-700">Patient is waiting now</p>
                </div>
                @endif

                <a href="{{ route('doctor.consultations.view', $nextConsultation->id) }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm">
                    Start Consultation
                </a>
            </div>
            @else
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-sm text-gray-500 font-medium">No upcoming consultations</p>
                </div>
            </div>
            @endif

            <!-- Priority Queue -->
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Priority Queue</h3>
                    <a href="{{ route('doctor.consultations') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">View All</a>
                </div>
                
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($priorityConsultations ?? [] as $consultation)
                    <a href="{{ route('doctor.consultations.view', $consultation->id) }}" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group">
                        <div class="flex items-center gap-3">
                            @if($consultation->patient && $consultation->patient->photo_url)
                                <img src="{{ $consultation->patient->photo_url }}" class="w-9 h-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-teal-500 flex items-center justify-center text-white font-semibold text-sm border border-gray-200 flex-shrink-0">
                                    {{ substr($consultation->first_name ?? 'P', 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $consultation->first_name }} {{ $consultation->last_name }}</h4>
                                <p class="text-xs text-gray-500 truncate">{{ $consultation->reference }}</p>
                            </div>
                            @if($consultation->status === 'pending')
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full whitespace-nowrap flex-shrink-0">Waiting</span>
                            @else
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full whitespace-nowrap flex-shrink-0">Scheduled</span>
                            @endif
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-500">No priority consultations</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function dashboardData() {
    return {
        // Dashboard data
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // Consultation Trends Line Chart
    const trendsCtx = document.getElementById('consultationTrendsChart');
    if (trendsCtx) {
        const trendsData = @json($consultationTrends);
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: trendsData.map(d => d.date),
                datasets: [{
                    label: 'Consultations',
                    data: trendsData.map(d => d.count),
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointBackgroundColor: '#2563EB',
                    pointHoverBackgroundColor: '#2563EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 12, weight: '500' },
                        bodyFont: { size: 12 },
                        cornerRadius: 6,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                            color: '#6B7280'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.04)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 },
                            color: '#6B7280',
                            maxRotation: 0
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Revenue Growth Bar Chart
    const revenueCtx = document.getElementById('revenueGrowthChart');
    if (revenueCtx) {
        const revenueData = @json($revenueGrowth);
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: revenueData.map(d => d.month),
                datasets: [{
                    label: 'Revenue',
                    data: revenueData.map(d => d.revenue),
                    backgroundColor: '#14B8A6',
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 12, weight: '500' },
                        bodyFont: { size: 12 },
                        cornerRadius: 6,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₦' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 11 },
                            color: '#6B7280',
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return '₦' + (value / 1000000).toFixed(1) + 'M';
                                } else if (value >= 1000) {
                                    return '₦' + (value / 1000).toFixed(0) + 'k';
                                }
                                return '₦' + value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.04)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 },
                            color: '#6B7280',
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

@endsection
