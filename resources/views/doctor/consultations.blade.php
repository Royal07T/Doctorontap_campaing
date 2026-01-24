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

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 px-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
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
                <div class="p-6 hover:bg-gray-50/50 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Patient Info -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border-2 border-white shadow-sm flex-shrink-0">
                                <div class="w-full h-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-lg font-bold">
                                    {{ substr($consultation->full_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">
                                    {{ $consultation->full_name }}
                                </h4>
                                <p class="text-xs text-gray-500 truncate">{{ $consultation->reference }}</p>
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

                        <!-- Status Badge -->
                        <div class="flex-shrink-0">
                            @if($consultation->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-widest border border-emerald-100">
                                    Completed
                                </span>
                            @elseif($consultation->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 uppercase tracking-widest border border-amber-100">
                                    Pending
                                </span>
                            @elseif($consultation->status === 'scheduled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-widest border border-indigo-100">
                                    Scheduled
                                </span>
                            @elseif($consultation->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 uppercase tracking-widest border border-rose-100">
                                    Cancelled
                                </span>
                            @endif
                        </div>

                        <!-- Payment Status -->
                        <div class="flex-shrink-0">
                            @if($consultation->payment_status === 'paid')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-widest border border-emerald-100">
                                    ✓ Paid
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 uppercase tracking-tight border border-rose-100">
                                    Unpaid
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center gap-2">
                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-indigo-100 uppercase tracking-widest">
                                View Details
                            </a>
                            
                            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
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
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 border-2 border-gray-100">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No Consultations Found</h3>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request('status'))
                        You don't have any {{ request('status') }} consultations yet.
                    @else
                        You don't have any consultations yet. New patient consultations will appear here.
                    @endif
                </p>
                @if(request('status'))
                    <a href="{{ route('doctor.consultations') }}" class="text-indigo-600 font-bold hover:underline text-sm uppercase tracking-widest">
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
