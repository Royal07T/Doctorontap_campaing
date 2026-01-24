@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('header-title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center gap-2">
                Welcome back, Dr. {{ Auth::guard('doctor')->user()->last_name }}! <span class="text-2xl">👋</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">Here is what's happening with your clinical practice today.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors shadow-lg shadow-indigo-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Start Next Consult
            </a>
            <a href="{{ route('doctor.availability') }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-colors">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Update Availability
            </a>
        </div>
    </div>

    <!-- KYC/Compliance Banner -->
    @if(Auth::guard('doctor')->user()->is_profile_complete)
    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="text-sm font-medium text-emerald-800">Your profile is 100% compliant. KYC Verified.</span>
        </div>
        <a href="{{ route('doctor.profile') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">View Details</a>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <span class="text-sm font-medium text-amber-800">Action Required: Complete your profile verification.</span>
        </div>
        <a href="{{ route('doctor.profile') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800">Complete Now</a>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Left Column (Stats & Priority) -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Consultations -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                            </svg>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2 py-1 rounded-full">+12%</span>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Total Consultations</p>
                    <h3 class="text-3xl font-black text-gray-900 mt-1">{{ number_format($stats['total_consultations'] ?? 0) }}</h3>
                </div>

                <!-- Total Earnings -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2 py-1 rounded-full">+8%</span>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Total Earnings</p>
                    <h3 class="text-3xl font-black text-gray-900 mt-1">₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}</h3>
                </div>

                <!-- Patient Rating -->
                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                        <span class="text-xs text-gray-400 font-medium">Top 5%</span>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Patient Rating</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <h3 class="text-3xl font-black text-gray-900">{{ number_format(Auth::guard('doctor')->user()->average_rating ?? 5.0, 1) }}</h3>
                        <span class="text-lg text-gray-400 font-medium">/ 5.0</span>
                    </div>
                </div>
            </div>

            <!-- Clinical Priority -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                        Clinical Priority
                    </h2>
                    <a href="{{ route('doctor.consultations') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View All Schedule</a>
                </div>

                <div class="space-y-4">
                    @forelse($recentConsultations->take(3) as $consultation)
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between hover:border-indigo-100 transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Avatar Placeholder -->
                            <div class="w-12 h-12 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                @if($consultation->patient && $consultation->patient->photo_url)
                                    <img src="{{ $consultation->patient->photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold bg-gray-50">
                                        {{ substr($consultation->first_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm">{{ $consultation->first_name }} {{ $consultation->last_name }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-gray-500">
                                        Scheduled for {{ $consultation->created_at->format('h:i A') }}
                                    </p>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <p class="text-xs text-gray-400">Code: {{ $consultation->reference }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                @if($consultation->status === 'completed') bg-gray-100 text-gray-600
                                @elseif($consultation->status === 'pending' || $consultation->status === 'scheduled') bg-indigo-50 text-indigo-600
                                @else bg-gray-50 text-gray-500 @endif">
                                @if($consultation->status === 'pending') Patient Waiting
                                @elseif($consultation->status === 'scheduled') Scheduled
                                @else {{ ucfirst($consultation->status) }} @endif
                            </span>
                            
                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white p-8 rounded-xl border border-gray-100 text-center">
                        <p class="text-gray-500 text-sm">No upcoming appointments scheduled.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            


            <!-- Pending Payout Card -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <!-- Decor -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                
                <div class="relative z-10">
                    <p class="text-xs font-medium text-indigo-100 mb-1">Pending Payout</p>
                    <h3 class="text-3xl font-black mb-4">₦{{ number_format($stats['pending_earnings'] ?? 0, 2) }}</h3>
                    
                    <p class="text-[10px] text-indigo-200">Scheduled for release on Friday, {{ now()->next('Friday')->format('M d') }}</p>
                </div>
                
                <!-- Action Icon -->
                <div class="absolute bottom-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg cursor-pointer hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush
