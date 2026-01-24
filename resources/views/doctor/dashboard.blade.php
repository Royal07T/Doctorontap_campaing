@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('header-title', 'Doctor Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-4 md:p-6">
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-emerald-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm font-medium text-emerald-700">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-800 rounded-2xl shadow-xl p-6 md:p-8 lg:p-10 relative overflow-hidden" style="background: linear-gradient(135deg, #4F46E5 0%, #4338CA 50%, #3730A3 100%);">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full blur-3xl -mr-36 -mt-36"></div>
        <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/10 rounded-full blur-3xl -ml-28 -mb-28"></div>
        
        <div class="relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Main Welcome Content -->
                <div class="lg:col-span-2">
                    <div class="mb-6">
                        <h1 class="text-2xl md:text-3xl lg:text-4xl font-black mb-3 leading-tight" style="color: #FFFFFF; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            Welcome back, Dr. {{ Auth::guard('doctor')->user()->name }}! 👨‍⚕️
                        </h1>
                        <p class="text-sm md:text-base leading-relaxed max-w-3xl" style="color: #E0E7FF;">
                            Here is what's happening with your clinical practice today. Manage consultations, review patient cases, and stay on top of your schedule.
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-white hover:bg-gray-50 text-indigo-700 rounded-xl font-bold text-sm transition-all shadow-lg hover:shadow-xl uppercase tracking-tight">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span>View Consultations</span>
                        </a>
                        <a href="{{ route('doctor.profile') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/20 border-2 border-white/40 rounded-xl font-bold text-sm transition-all uppercase tracking-tight backdrop-blur-sm" style="color: #FFFFFF;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Update Profile</span>
                        </a>
                    </div>
                </div>
                
                <!-- Earnings Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white/15 backdrop-blur-md p-6 rounded-2xl border-2 border-white/30 shadow-2xl h-full flex flex-col justify-center">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-bold uppercase tracking-widest" style="color: #E0E7FF;">Total Earnings</p>
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #FFFFFF;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl md:text-4xl lg:text-5xl font-black mb-2 break-words" style="color: #FFFFFF; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            ₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}
                        </p>
                        <p class="text-xs md:text-sm" style="color: #E0E7FF;">
                            From <span class="font-bold" style="color: #FFFFFF;">{{ $stats['paid_consultations'] ?? 0 }}</span> paid consultations
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Consultations -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Total Consults</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['total_consultations'] ?? 0 }}</h3>
                    <p class="text-xs text-emerald-600 font-bold mt-1">+{{ $stats['completed_consultations'] ?? 0 }} completed</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Today -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-amber-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pending Today</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['pending_consultations'] ?? 0 }}</h3>
                    <p class="text-xs text-amber-600 font-bold mt-1">Needs attention</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 border border-amber-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-emerald-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Completed</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['completed_consultations'] ?? 0 }}</h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">{{ $stats['paid_consultations'] ?? 0 }} paid</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 border border-emerald-100">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Earnings (Mobile Visible) -->
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 rounded-2xl shadow-lg border border-indigo-500 lg:hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider mb-2">Total Earnings</p>
                    <h3 class="text-3xl font-black text-white">₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}</h3>
                    <p class="text-xs text-indigo-100 font-medium mt-1">{{ $stats['paid_consultations'] ?? 0 }} paid</p>
                </div>
                <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-white border border-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Scheduled (Desktop Only) -->
        <div class="hidden lg:block bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-blue-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Scheduled</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['scheduled_consultations'] ?? 0 }}</h3>
                    <p class="text-xs text-blue-600 font-bold mt-1">Upcoming sessions</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 border border-blue-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Consultations -->
    @if($recentConsultations->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 p-6 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900 uppercase tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Recent Consultations
                </h2>
                <p class="text-xs text-gray-500 mt-1">Latest patient cases and consultations</p>
            </div>
            <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition-all shadow-sm shadow-indigo-100 uppercase tracking-widest">
                View All
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($recentConsultations as $consultation)
            <div x-data="{ open: false }" class="hover:bg-gray-50/50 transition-colors">
                <!-- Consultation Header -->
                <button @click="open = !open" class="w-full text-left p-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-inset">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <!-- Status Indicator -->
                            <div class="flex-shrink-0">
                                @if($consultation->status === 'completed')
                                    <div class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm"></div>
                                @elseif($consultation->status === 'pending')
                                    <div class="w-3 h-3 rounded-full bg-amber-500 shadow-sm animate-pulse"></div>
                                @elseif($consultation->status === 'scheduled')
                                    <div class="w-3 h-3 rounded-full bg-blue-500 shadow-sm"></div>
                                @elseif($consultation->status === 'cancelled')
                                    <div class="w-3 h-3 rounded-full bg-rose-500 shadow-sm"></div>
                                @else
                                    <div class="w-3 h-3 rounded-full bg-gray-400 shadow-sm"></div>
                                @endif
                            </div>

                            <!-- Patient Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $consultation->reference ?? 'N/A' }}</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest
                                        @if($consultation->status === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-100
                                        @elseif($consultation->status === 'pending') bg-amber-50 text-amber-700 border border-amber-100
                                        @elseif($consultation->status === 'scheduled') bg-blue-50 text-blue-700 border border-blue-100
                                        @elseif($consultation->status === 'cancelled') bg-rose-50 text-rose-700 border border-rose-100
                                        @else bg-gray-50 text-gray-700 border border-gray-100 @endif">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                    @if($consultation->payment_status === 'paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-widest">✓ Paid</span>
                                    @elseif($consultation->payment_status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase tracking-tight">Payment Pending</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">{{ $consultation->first_name }} {{ $consultation->last_name }}</span>
                                    <span class="mx-1.5">•</span>
                                    <span>{{ $consultation->created_at->format('M d, Y, h:i A') }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Expand Icon -->
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                 :class="{ 'rotate-180': open }" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Expanded Details -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     x-cloak
                     class="px-6 pb-6 pt-2"
                     style="display: none;">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Patient</p>
                                <p class="text-sm text-gray-900 font-semibold">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Date & Time</p>
                                <p class="text-sm text-gray-900">{{ $consultation->created_at->format('M d, Y H:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Severity</p>
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full
                                    {{ $consultation->severity == 'mild' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $consultation->severity == 'moderate' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                                    {{ $consultation->severity == 'severe' ? 'bg-rose-100 text-rose-700 border border-rose-200' : '' }}">
                                    {{ ucfirst($consultation->severity ?? 'N/A') }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Chief Complaint</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ Str::limit($consultation->problem, 200) }}</p>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Payment Status</p>
                                @if($consultation->payment_status == 'paid')
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Paid
                                        </span>
                                        @if($consultation->payment)
                                            <span class="text-sm text-gray-700 font-bold">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                                        @endif
                                    </div>
                                @elseif($consultation->payment_status == 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Not Required</span>
                                @endif
                            </div>

                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" 
                               class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition-all shadow-sm shadow-indigo-100 uppercase tracking-widest">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 border-2 border-gray-100">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No Recent Consultations</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
            You don't have any recent consultations yet. New patient cases will appear here.
        </p>
        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-100 text-sm uppercase tracking-widest">
            View All Consultations
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush
