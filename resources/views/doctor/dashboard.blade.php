@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('header-title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-6" x-data="{ darkMode: false }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center gap-2">
                Welcome back, Dr. {{ Auth::guard('doctor')->user()->last_name }}! <span class="text-2xl">👋</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">Here is what's happening with your clinical practice today.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg text-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Start Next Consult
            </a>
            <a href="{{ route('doctor.availability') }}" class="inline-flex items-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-all shadow-sm border border-gray-200 text-sm">
                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Update Availability
            </a>
        </div>
    </div>

    <!-- KYC/Compliance Banner -->
    @php
        $doctor = Auth::guard('doctor')->user();
        $isFullyVerified = $doctor->mdcn_certificate_verified && $doctor->is_approved;
        
        // Check what's missing
        $hasBasicInfo = $doctor->first_name && $doctor->last_name && $doctor->email && $doctor->phone && $doctor->specialization;
        $hasCertificate = $doctor->certificate_path || $doctor->certificate_data;
        $hasInsurance = $doctor->insurance_document;
        $isCertificateVerified = $doctor->mdcn_certificate_verified;
        $isApproved = $doctor->is_approved;
        
        // Count completed items
        $completedCount = 0;
        if ($hasBasicInfo) $completedCount++;
        if ($hasCertificate) $completedCount++;
        if ($hasInsurance) $completedCount++;
        if ($isCertificateVerified) $completedCount++;
        if ($isApproved) $completedCount++;
        
        $totalSteps = 5;
        $progressPercentage = ($completedCount / $totalSteps) * 100;
    @endphp
    
    @if($isFullyVerified)
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <span class="text-sm font-semibold text-emerald-900">Your profile is 100% compliant. KYC Verified.</span>
        </div>
        <a href="{{ route('doctor.profile') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-800 transition-colors">View Details</a>
    </div>
    @else
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6" x-data="{ expanded: false }">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-amber-900 mb-1">Action Required: Complete Profile Verification</h4>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-white rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-amber-900">{{ $completedCount }}/{{ $totalSteps }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Checklist -->
                <div class="ml-11 space-y-2" x-show="expanded" x-transition>
                    <div class="flex items-center gap-2 text-xs {{ $hasBasicInfo ? 'text-emerald-700' : 'text-gray-600' }}">
                        @if($hasBasicInfo)
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @endif
                        <span class="font-medium">Complete basic information (Name, Email, Phone, Specialization)</span>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs {{ $hasCertificate ? 'text-emerald-700' : 'text-gray-600' }}">
                        @if($hasCertificate)
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @endif
                        <span class="font-medium">Upload MDCN certificate (Licenses & KYC tab)</span>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs {{ $hasInsurance ? 'text-emerald-700' : 'text-gray-600' }}">
                        @if($hasInsurance)
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @endif
                        <span class="font-medium">Upload insurance documents (Professional liability insurance)</span>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs {{ $isCertificateVerified ? 'text-emerald-700' : 'text-gray-600' }}">
                        @if($isCertificateVerified)
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        @endif
                        <span class="font-medium">{{ $isCertificateVerified ? 'MDCN certificate verified by admin' : 'Awaiting admin verification of MDCN certificate' }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2 text-xs {{ $isApproved ? 'text-emerald-700' : 'text-gray-600' }}">
                        @if($isApproved)
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        @endif
                        <span class="font-medium">{{ $isApproved ? 'Account approved by admin' : 'Awaiting admin approval of your account' }}</span>
                    </div>
                    
                    @if(!$hasBasicInfo || !$hasCertificate || !$hasInsurance)
                    <div class="mt-3 p-3 bg-white rounded-lg border border-amber-200">
                        <p class="text-xs text-amber-900 font-medium mb-2">📋 <strong>What you need to do:</strong></p>
                        <ol class="text-xs text-gray-700 space-y-1 ml-4 list-decimal">
                            @if(!$hasBasicInfo)
                            <li>Go to Profile → Basic Info tab and fill in all required fields</li>
                            @endif
                            @if(!$hasCertificate)
                            <li>Go to Profile → Licenses & KYC tab and upload your MDCN certificate</li>
                            @endif
                            @if(!$hasInsurance)
                            <li>Upload your professional liability insurance document</li>
                            @endif
                            <li>Save your profile and wait for admin verification (usually 24-48 hours)</li>
                        </ol>
                    </div>
                    @else
                    <div class="mt-3 p-3 bg-white rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-900">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <strong>Great job!</strong> Your documents are uploaded. Our admin team is reviewing your profile. You'll receive an email notification once verified (usually within 24-48 hours).
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="flex flex-col gap-2">
                <a href="{{ route('doctor.profile') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-colors text-xs whitespace-nowrap shadow-sm">
                    Go to Profile
                </a>
                <button @click="expanded = !expanded" class="px-4 py-2 bg-white hover:bg-gray-50 text-amber-700 font-semibold rounded-lg transition-colors text-xs border border-amber-200">
                    <span x-text="expanded ? 'Hide Details' : 'Show Details'"></span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column (Stats & Priority) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Consultations -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                        </div>
                        @if($stats['consultations_growth'] != 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $stats['consultations_growth'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $stats['consultations_growth'] > 0 ? '+' : '' }}{{ $stats['consultations_growth'] }}%
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 font-medium mb-1">Total Consultations</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ number_format($stats['total_consultations'] ?? 0) }}</h3>
                </div>

                <!-- Total Earnings -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        @if($stats['earnings_growth'] != 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $stats['earnings_growth'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $stats['earnings_growth'] > 0 ? '+' : '' }}{{ $stats['earnings_growth'] }}%
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 font-medium mb-1">Total Earnings</p>
                    <h3 class="text-2xl md:text-3xl font-black text-gray-900">₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}</h3>
                </div>

                <!-- Patient Rating -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                        <span class="text-xs text-gray-500 font-semibold">Top 5%</span>
                    </div>
                    <p class="text-sm text-gray-500 font-medium mb-1">Patient Rating</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-gray-900">{{ number_format(Auth::guard('doctor')->user()->average_rating ?? 4.92, 2) }}</h3>
                        <span class="text-lg text-gray-400 font-medium">/ 5.0</span>
                    </div>
                </div>
            </div>

            <!-- Clinical Priority -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"></path>
                        </svg>
                        Clinical Priority
                    </h2>
                    <a href="{{ route('doctor.consultations') }}" class="text-sm font-bold text-purple-600 hover:text-purple-700 transition-colors">View All Schedule</a>
                </div>

                <div class="space-y-3">
                    @forelse($priorityConsultations ?? [] as $consultation)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors group">
                        <div class="flex items-center gap-4 flex-1">
                            <!-- Avatar -->
                            <div class="relative flex-shrink-0">
                                @if($consultation->patient && $consultation->patient->photo_url)
                                    <img src="{{ $consultation->patient->photo_url }}" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm">
                                @else
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg text-white bg-gradient-to-br from-blue-500 to-purple-600 border-2 border-white shadow-sm">
                                        {{ substr($consultation->first_name ?? 'P', 0, 1) }}
                                    </div>
                                @endif
                                @if($consultation->status === 'pending')
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-blue-500 border-2 border-white rounded-full"></span>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 text-sm mb-1">{{ $consultation->first_name }} {{ $consultation->last_name }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span>Scheduled for {{ $consultation->scheduled_at ? $consultation->scheduled_at->format('g:i A') : $consultation->created_at->format('g:i A') }}</span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="font-medium">Consultation Code: {{ $consultation->reference }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 ml-4">
                            @if($consultation->status === 'pending')
                                <span class="px-3 py-1.5 bg-red-50 text-red-600 rounded-full text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                                    Patient Waiting
                                </span>
                            @elseif($consultation->scheduled_at && $consultation->scheduled_at->diffInMinutes(now()) <= 60 && $consultation->scheduled_at->isFuture())
                                <span class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-full text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                                    In {{ $consultation->scheduled_at->diffInMinutes(now()) }} Minutes
                                </span>
                            @elseif($consultation->status === 'scheduled')
                                <span class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-full text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                                    Scheduled
                                </span>
                            @else
                                <span class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-full text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                                    {{ ucfirst($consultation->status) }}
                                </span>
                            @endif
                            
                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-purple-50 hover:border-purple-200 hover:text-purple-600 transition-all group-hover:border-purple-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">No priority consultations at the moment</p>
                        <p class="text-xs text-gray-400 mt-1">Your upcoming appointments will appear here</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            
            <!-- Doctor's Forum Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                        Doctor's Forum
                    </h2>
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </div>

                <div class="space-y-4">
                    @forelse($recentForumPosts ?? [] as $forumPost)
                    <!-- Forum Post -->
                    <a href="{{ route('doctor.forum.show', $forumPost->slug) }}" class="block group">
                        <div class="flex items-start gap-2 mb-2">
                            <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded" 
                                  style="background-color: {{ $forumPost->category->color ?? 'purple' }}20; color: {{ $forumPost->category->color ?? 'purple' }};">
                                {{ $forumPost->category->name ?? 'General' }}
                            </span>
                            <span class="text-[10px] text-gray-400 font-medium uppercase">{{ $forumPost->created_at->diffForHumans(null, true, true) }} AGO</span>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-purple-600 transition-colors cursor-pointer">
                            {{ \Illuminate\Support\Str::limit($forumPost->title, 60) }}
                        </h4>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            @if($forumPost->uniqueRepliers()->count() > 0)
                            <div class="flex items-center gap-1.5">
                                <div class="flex -space-x-2">
                                    @foreach($forumPost->uniqueRepliers() as $replier)
                                        @if($replier->photo_url)
                                            <img src="{{ $replier->photo_url }}" class="w-5 h-5 rounded-full border-2 border-white object-cover" alt="{{ $replier->name }}">
                                        @else
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 border-2 border-white flex items-center justify-center text-white text-[8px] font-bold">
                                                {{ substr($replier->name, 0, 1) }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <span class="font-medium">{{ $forumPost->replies_count }} {{ $forumPost->replies_count == 1 ? 'reply' : 'replies' }}</span>
                            </div>
                            @else
                                <span class="font-medium text-gray-400">No replies yet</span>
                            @endif
                            
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="font-medium">{{ number_format($forumPost->views_count) }}</span>
                            </div>
                        </div>
                    </a>

                    @if(!$loop->last)
                    <div class="border-t border-gray-100 my-3"></div>
                    @endif
                    @empty
                    <!-- Empty State -->
                    <div class="py-8 text-center">
                        <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 font-medium mb-1">No discussions yet</p>
                        <p class="text-xs text-gray-400">Be the first to start a topic!</p>
                    </div>
                    @endforelse

                    <a href="{{ route('doctor.forum.index') }}" class="block w-full mt-4 py-2.5 px-4 bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold rounded-xl transition-colors text-sm text-center">
                        Browse Forum
                    </a>
                </div>
            </div>

            <!-- Pending Payout Card -->
            <div :class="darkMode ? 'bg-gradient-to-br from-gray-900 to-gray-800' : 'bg-gradient-to-br from-purple-600 to-purple-700'" 
                 class="rounded-2xl p-6 text-white shadow-lg relative overflow-hidden transition-all duration-300">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <p :class="darkMode ? 'text-gray-400' : 'text-purple-200'" class="text-xs font-semibold mb-2 uppercase tracking-wider">Pending Payout</p>
                    <h3 class="text-4xl font-black mb-4">₦{{ number_format($stats['pending_earnings'] ?? 0, 0) }}</h3>
                    
                    <p :class="darkMode ? 'text-gray-500' : 'text-purple-200'" class="text-[11px] font-medium">
                        Scheduled for release on Friday, {{ now()->next('Friday')->format('M d') }}
                    </p>
                </div>
                
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" 
                        :class="darkMode ? 'bg-gray-700 hover:bg-gray-600' : 'bg-white hover:scale-105'" 
                        class="absolute bottom-5 right-5 w-11 h-11 rounded-full flex items-center justify-center shadow-lg transition-all">
                    <svg x-show="!darkMode" class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush
