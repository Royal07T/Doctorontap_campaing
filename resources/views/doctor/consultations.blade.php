@extends('layouts.doctor')

@section('title', 'My Consultations')
@section('header-title', 'My Consultations')

@push('x-data-extra')
, isGlobalUpdating: false
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-4 md:p-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.dashboard') }}" class="hover:text-indigo-600 transition-colors">Home</a>
                <span class="mx-2">/</span>
                <span class="text-indigo-600 font-medium">My Consultations</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">My Consultations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and review all patient consultations</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Consultations -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Total Consults</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['total'] }}</h3>
                    <p class="text-xs text-indigo-600 font-bold mt-1">All time</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-amber-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pending</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['pending'] }}</h3>
                    <p class="text-xs text-amber-600 font-bold mt-1">Needs attention</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 border border-amber-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-emerald-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Completed</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['completed'] }}</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">{{ $stats['paid'] }} paid</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 border border-emerald-100">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Earnings -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-2xl shadow-lg border border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider mb-2">Total Earnings</p>
                    <h3 class="text-3xl font-black text-white">₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}</h3>
                    <p class="text-xs text-emerald-100 font-medium mt-1">{{ $stats['paid'] }} paid</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-white border border-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Compact Filter Bar -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('doctor.consultations') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search by patient name, email, or reference..."
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select name="payment_status" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">All Payments</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filter
                    </button>
                    @if(request('search') || request('payment_status'))
                    <a href="{{ route('doctor.consultations') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-gray-100 px-6">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <a href="{{ route('doctor.consultations') }}"
                   class="{{ !request('status') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    All
                </a>
                <a href="{{ route('doctor.consultations', ['status' => 'pending']) }}"
                   class="{{ request('status') == 'pending' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Pending
                </a>
                <a href="{{ route('doctor.consultations', ['status' => 'scheduled']) }}"
                   class="{{ request('status') == 'scheduled' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Scheduled
                </a>
                <a href="{{ route('doctor.consultations', ['status' => 'completed']) }}"
                   class="{{ request('status') == 'completed' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Completed
                </a>
                <a href="{{ route('doctor.consultations', ['status' => 'cancelled']) }}"
                   class="{{ request('status') == 'cancelled' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Cancelled
                </a>
            </nav>
        </div>

        <!-- Pagination Info -->
        @if($consultations->total() > 0)
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
            <p class="text-xs text-gray-500">
                Showing <span class="font-bold text-gray-900">{{ $consultations->firstItem() }}</span> - <span class="font-bold text-gray-900">{{ $consultations->lastItem() }}</span> of <span class="font-bold text-gray-900">{{ $consultations->total() }}</span> consultations
            </p>
        </div>
        @endif

        <!-- Consultations List -->
        @if($consultations->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($consultations as $consultation)
                <div class="p-6 hover:bg-indigo-50/30 transition-all duration-200 group" x-data="{ showActions: false }">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Patient Info -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border-2 border-white shadow-sm flex-shrink-0 ring-2 ring-transparent group-hover:ring-indigo-200 transition-all">
                                <div class="w-full h-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-lg font-bold">
                                    {{ substr($consultation->full_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">
                                    {{ $consultation->full_name }}
                                </h4>
                                <p class="text-xs text-gray-500 truncate font-mono">{{ $consultation->reference }}</p>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate">{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('M d, Y') : $consultation->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('h:i A') : 'Time Pending' }}</span>
                            </div>
                        </div>

                        <!-- Status & Payment Badges -->
                        <div class="flex-shrink-0 flex items-center gap-2">
                            <!-- Status Badge -->
                            @if($consultation->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-widest border border-emerald-200 shadow-sm">
                                    ✓ Completed
                                </span>
                            @elseif($consultation->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 uppercase tracking-widest border border-amber-200 shadow-sm">
                                    ⏳ Pending
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold bg-red-100 text-red-700 uppercase tracking-wider border border-red-200 animate-pulse">
                                    Patient Waiting
                                </span>
                            @elseif($consultation->status === 'scheduled')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-widest border border-indigo-200 shadow-sm">
                                    📅 Scheduled
                                </span>
                                @if($consultation->scheduled_at && $consultation->scheduled_at->isFuture() && $consultation->scheduled_at->diffInMinutes(now()) <= 60)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold bg-blue-100 text-blue-700 uppercase tracking-wider border border-blue-200">
                                        Starts in {{ $consultation->scheduled_at->diffInMinutes(now()) }}m
                                    </span>
                                @endif
                            @elseif($consultation->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 uppercase tracking-widest border border-rose-200 shadow-sm">
                                    ✕ Cancelled
                                </span>
                            @endif

                            <!-- Payment Status Badge -->
                            @if($consultation->payment_status === 'paid')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider border border-emerald-200">
                                    ✓ Paid
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold bg-rose-100 text-rose-700 uppercase tracking-wider border border-rose-200">
                                    Unpaid
                                </span>
                            @endif
                        </div>

                        <!-- Quick Actions (Visible on Hover) -->
                        <div class="flex-shrink-0 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </a>
                            @if($consultation->status === 'pending' || $consultation->status === 'scheduled')
                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Start
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($consultations->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $consultations->withQueryString()->links() }}
            </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="p-16 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-indigo-50 to-purple-50 mb-6 border-4 border-indigo-100">
                    <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">No Consultations Found</h3>
                <p class="text-sm text-gray-600 mb-6 max-w-md mx-auto leading-relaxed">
                    @if(request('status'))
                        You don't have any <span class="font-semibold text-indigo-600">{{ request('status') }}</span> consultations at this time. 
                        New consultations will appear here as patients book appointments.
                    @else
                        You don't have any consultations yet. New patient consultations will appear here once they book appointments with you.
                    @endif
                </p>
                @if(request('status'))
                    <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        View All Consultations
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <x-system-preloader x-show="isGlobalUpdating" message="Updating Consultation Status..." subtext="Notifying patient and admin of the change." />
@endpush
