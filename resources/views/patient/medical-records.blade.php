@extends('layouts.patient')

@section('title', 'Health Passport')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600">Home</a>
                <span class="mx-2">/</span>
                <span class="text-purple-600">Medical Records</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Health Passport</h1>
            <p class="text-gray-500 text-sm mt-1">Your complete medical history and vital trends in one place.</p>
        </div>
        <div>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Records
            </button>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Total Records</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_records'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Last Visit</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $stats['last_consultation'] ? $stats['last_consultation']->format('M d') : '-' }}
                </h3>
                @if($stats['last_consultation'])
                    <p class="text-xs text-gray-500">{{ $stats['last_consultation']->format('Y') }}</p>
                @endif
            </div>
            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Vitals Logged</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_vital_signs'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Latest Vitals Dashboard -->
    @if($latestVitals)
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Active Vitals</h2>
            <span class="text-xs text-gray-500">Last updated: {{ $latestVitals->created_at->diffForHumans() }}</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Blood Pressure -->
            @if($latestVitals->blood_pressure)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </div>
                <span class="text-lg font-bold text-gray-900">{{ $latestVitals->blood_pressure }}</span>
                <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">Blood Pressure</span>
            </div>
            @endif

            <!-- Heart Rate -->
            @if($latestVitals->heart_rate)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-bold text-gray-900">{{ $latestVitals->heart_rate }}</span>
                    <span class="text-xs text-gray-500 font-medium">bpm</span>
                </div>
                <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">Heart Rate</span>
            </div>
            @endif

            <!-- Temperature -->
            @if($latestVitals->temperature)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-bold text-gray-900">{{ $latestVitals->temperature }}</span>
                    <span class="text-xs text-gray-500 font-medium">°C</span>
                </div>
                <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">Temperature</span>
            </div>
            @endif

             <!-- Weight -->
            @if($latestVitals->weight)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-bold text-gray-900">{{ $latestVitals->weight }}</span>
                    <span class="text-xs text-gray-500 font-medium">kg</span>
                </div>
                <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">Weight</span>
            </div>
            @endif
            
            <!-- Height -->
            @if($latestVitals->height)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-bold text-gray-900">{{ $latestVitals->height }}</span>
                    <span class="text-xs text-gray-500 font-medium">cm</span>
                </div>
                <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">Height</span>
            </div>
            @endif
             
             <!-- Oxygen -->
            @if($latestVitals->oxygen_saturation)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center">
                <div class="w-8 h-8 rounded-full bg-cyan-50 text-cyan-500 flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-bold text-gray-900">{{ $latestVitals->oxygen_saturation }}</span>
                    <span class="text-xs text-gray-500 font-medium">%</span>
                </div>
                <span class="text-[10px] uppercase font-bold text-gray-400 mt-1">SpO2</span>
            </div>
            @endif
        </div>
    </section>
    @endif

    <!-- Medical History Timeline -->
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-6">Medical History</h2>
        
        @if($medicalHistories->count() > 0)
            <div class="relative pl-6 space-y-8 before:absolute before:inset-y-0 before:left-[11px] before:w-[2px] before:bg-gray-200">
                @foreach($medicalHistories as $history)
                    <div class="relative" x-data="{ expanded: false }">
                        <!-- Timeline Dot -->
                        <div class="absolute -left-6 top-1 w-6 h-6 rounded-full border-4 border-white shadow-sm flex items-center justify-center bg-purple-600 z-10"></div>
                        
                        <!-- Content Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
                            <!-- Card Header -->
                            <div @click="expanded = !expanded" class="p-5 cursor-pointer flex items-start justify-between bg-white hover:bg-gray-50 transition-colors">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 class="text-base font-bold text-gray-900">Consultation #{{ $history->consultation->reference ?? 'REF' }}</h3>
                                        @if($history->is_latest)
                                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wide">Latest</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        <span class="font-medium text-gray-900">{{ $history->consultation_date->format('F d, Y') }}</span> 
                                        with <span class="text-purple-600 font-medium">Dr. {{ $history->consultation->doctor->name ?? 'Unknown' }}</span>
                                    </p>
                                </div>
                                <button class="text-gray-400 hover:text-purple-600 transition-colors">
                                    <svg class="w-5 h-5 transform transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Expanded Details -->
                            <div x-show="expanded" x-collapse x-cloak class="border-t border-gray-50 bg-gray-50/50">
                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @if($history->presenting_complaint)
                                    <div class="col-span-1 md:col-span-2">
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Presenting Complaint</h4>
                                        <div class="bg-white p-4 rounded-xl border border-gray-100 text-sm text-gray-800 leading-relaxed">
                                            {{ $history->presenting_complaint }}
                                        </div>
                                    </div>
                                    @endif

                                    @if($history->history_of_complaint)
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">History of Complaint</h4>
                                        <p class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-gray-100">{{ $history->history_of_complaint }}</p>
                                    </div>
                                    @endif

                                    @if($history->past_medical_history)
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Medical History</h4>
                                        <p class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-gray-100">{{ $history->past_medical_history }}</p>
                                    </div>
                                    @endif

                                    @if($history->allergies)
                                    <div class="col-span-1 md:col-span-2">
                                        <h4 class="text-xs font-bold text-red-400 uppercase tracking-wide mb-2 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> 
                                            Allergies
                                        </h4>
                                        <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-sm text-red-800 font-medium">
                                            {{ $history->allergies }}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($history->drug_history)
                                     <div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Drug History</h4>
                                        <p class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-gray-100">{{ $history->drug_history }}</p>
                                    </div>
                                    @endif

                                    @if($history->social_history)
                                     <div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Social History</h4>
                                        <p class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-gray-100">{{ $history->social_history }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $medicalHistories->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
                 <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No Medical Records Yet</h3>
                <p class="text-gray-500 max-w-sm mx-auto mb-6">Your medical history will appear here automatically after your consultations are completed.</p>
                <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-colors shadow-lg shadow-purple-200">
                    Book a Consultation
                </a>
            </div>
        @endif
    </section>

    <!-- Secure Notice -->
    <div class="bg-gray-50 rounded-xl p-4 flex items-center gap-3 border border-gray-200">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <p class="text-xs text-gray-500">Your medical data is encrypted and secure. Access is restricted to you and your authorized healthcare providers.</p>
    </div>
</div>
@endsection
