@extends('layouts.patient')

@section('title', 'Patient Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- 1. Patient Identity Hero Banner -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Patient Mini Avatar & Basic Info -->
            <div class="flex-shrink-0">
                @if($patient->photo_url)
                    <img src="{{ $patient->photo_url }}" alt="{{ $patient->name }}" class="w-24 h-24 rounded-2xl object-cover border-4 border-gray-50 shadow-sm">
                @else
                    <div class="w-24 h-24 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 text-3xl font-bold border-4 border-white shadow-sm">
                        {{ substr($patient->name, 0, 1) }}
                    </div>
                @endif
            </div>

            <div class="flex-1 text-center md:text-left space-y-2">
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ $patient->name }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></span>
                        Status: Active
                    </span>
                </div>
                
                <div class="flex flex-wrap justify-center md:justify-start items-center gap-x-6 gap-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="font-semibold text-gray-400">Sex:</span>
                        <span class="text-gray-900 capitalize">{{ $patient->gender ?? 'Not Set' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-semibold text-gray-400">Age:</span>
                        <span class="text-gray-900">
                            @if($patient->date_of_birth)
                                {{ $patient->date_of_birth->age }} Years
                            @else
                                --
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-semibold text-gray-400">Blood Group:</span>
                        <span class="text-gray-900">{{ $patient->blood_group ?? '--' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-semibold text-gray-400">Genotype:</span>
                        <span class="text-gray-900">{{ $patient->genotype ?? '--' }}</span>
                    </div>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-col gap-2 w-full md:w-auto">
                <a href="{{ route('patient.profile') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                    Edit Health Profile
                </a>
                <a href="{{ route('patient.medical-records') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none shadow-sm transition-colors">
                    Medical History
                </a>
            </div>
        </div>
        
        <!-- Quick Stats Footer -->
        <div class="bg-gray-50 border-t border-gray-100 px-6 py-3 flex flex-wrap justify-center md:justify-start gap-6 text-xs font-medium text-gray-500">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Last Consultation: <span class="text-gray-900">{{ $patient->last_consultation_at ? \Carbon\Carbon::parse($patient->last_consultation_at)->format('d M Y') : 'None recorded' }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Total Records: <span class="text-gray-900">{{ $stats['total_consultations'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Health Overview & Metrics Tracker -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Vitals Grid -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Recent Health Metrics
                    </h3>
                    <span class="text-xs text-gray-500 italic">Last Updated: {{ isset($latestVitals) ? \Carbon\Carbon::parse($latestVitals->created_at)->diffForHumans() : 'No data' }}</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Blood Pressure -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors group">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Blood Pressure</p>
                        <div class="flex items-baseline justify-between">
                            <h4 class="text-2xl font-black text-gray-900">
                                {{ $latestVitals->blood_pressure ?? '--/--' }}
                                <span class="text-xs font-normal text-gray-500 ml-1">mmHg</span>
                            </h4>
                            @if(isset($latestVitals->blood_pressure))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-green-50 text-green-700 rounded-full border border-green-100">STABLE</span>
                            @endif
                        </div>
                    </div>

                    <!-- Heart Rate -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors group">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Heart Rate</p>
                        <div class="flex items-baseline justify-between">
                            <h4 class="text-2xl font-black text-gray-900">
                                {{ $latestVitals->heart_rate ?? '--' }}
                                <span class="text-xs font-normal text-gray-500 ml-1">BPM</span>
                            </h4>
                            @if(isset($latestVitals->heart_rate))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-green-50 text-green-700 rounded-full border border-green-100">NORMAL</span>
                            @endif
                        </div>
                    </div>

                    <!-- Temperature -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors group">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Body Temp</p>
                        <div class="flex items-baseline justify-between">
                            <h4 class="text-2xl font-black text-gray-900">
                                {{ $latestVitals->temperature ?? '--' }}
                                <span class="text-xs font-normal text-gray-500 ml-1">°C</span>
                            </h4>
                            @if(isset($latestVitals->temperature))
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-green-50 text-green-700 rounded-full border border-green-100">IDEAL</span>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <!-- Menstrual Cycle (Conditional) -->
            @if(strtolower($patient->gender) === 'female')
            <section class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-100 p-5 bg-gray-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Women's Health TRACKER</h3>
                        <p class="text-xs text-gray-500">Menstrual Cycle & Predictions</p>
                    </div>
                    <a href="{{ route('patient.cycle-tracker') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm transition-all hover:shadow-md">
                        Full Tracker & Logs
                    </a>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:divide-x divide-gray-100">
                        <!-- Left: Status & Prediction -->
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500 flex-shrink-0">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">CURRENT PHASE</p>
                                    <h4 class="text-2xl font-black text-gray-900">Follicular Phase</h4>
                                    <p class="text-sm text-gray-500 mt-1 italic">Body is preparing for ovulation.</p>
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 font-medium">Next Period Expected:</span>
                                    <span class="font-black text-gray-900">{{ $nextPeriodPrediction ? $nextPeriodPrediction->format('d M Y') : 'Track to see' }}</span>
                                </div>
                                <div class="mt-2 text-[11px] text-gray-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    Approx. {{ $nextPeriodPrediction ? abs(round($nextPeriodPrediction->diffInDays(now(), false))) : '--' }} days remaining
                                </div>
                            </div>
                        </div>

                        <!-- Right: Parameters -->
                        <div class="md:pl-8 space-y-4">
                            <h5 class="text-sm font-bold text-gray-900 mb-3">Cycle Parameters</h5>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                                    <span class="text-sm text-gray-500">Average Cycle Length</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $averageCycleLength ?? 28 }} Days</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-50">
                                    <span class="text-sm text-gray-500">Period Duration</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $averagePeriodLength ?? 5 }} Days</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm text-gray-500">Last Period Start</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $menstrualCycles->isNotEmpty() ? $menstrualCycles->first()->start_date->format('d M Y') : '--' }}</span>
                                </div>
                            </div>
                            
                            <a href="{{ route('patient.cycle-tracker') }}" class="w-full flex items-center justify-center gap-2 py-2.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl text-xs font-bold hover:bg-rose-100 transition-colors uppercase tracking-widest">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Log Daily Symptoms
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            <!-- Men's Health (Conditional) -->
            @if(strtolower($patient->gender) === 'male')
            <section class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-100 p-5 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Men's Health TRACKER</h3>
                        <p class="text-xs text-gray-500">Sexual Wellness & Screenings</p>
                    </div>
                    @if($stiTestDue)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            Screening Recommended
                        </span>
                    @endif
                </div>

                <div class="p-6">
                    <div class="bg-indigo-50/50 rounded-2xl p-5 border border-indigo-100 flex flex-col md:flex-row items-center gap-6">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100 flex-shrink-0">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h4 class="text-lg font-black text-gray-900">STI Screening Status</h4>
                            <p class="text-sm text-gray-600 mt-1">Regular screening is key to maintaining sexual health and wellness.</p>
                            
                            <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-4 text-xs font-bold uppercase tracking-wider">
                                <div class="flex items-center gap-2 text-gray-500">
                                    Last Test: <span class="text-gray-900">{{ $latestSexualHealthRecord && $latestSexualHealthRecord->last_sti_test_date ? $latestSexualHealthRecord->last_sti_test_date->format('d M Y') : 'No record' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-indigo-600">
                                    Next Due: <span class="text-gray-900">{{ $nextStiTestDate ? $nextStiTestDate->format('M Y') : 'Consult MD' }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('patient.doctors', ['specialization' => 'Urology']) }}" class="w-full md:w-auto px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all uppercase tracking-widest text-center">
                            Book Screening
                        </a>
                    </div>
                </div>
            </section>
            @endif

            <!-- Upcoming Appointments -->
            <section>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Appointments & Reminders
                    </h3>
                    <a href="{{ route('patient.consultations') }}" class="text-xs font-bold text-indigo-600 hover:underline">Manage All</a>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 divide-y divide-gray-100">
                    @forelse($upcomingConsultations as $consultation)
                    <div class="p-5 flex items-center gap-4 hover:bg-gray-50/50 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex flex-col items-center justify-center text-indigo-600">
                            <span class="text-[10px] font-bold uppercase">{{ $consultation->created_at->format('M') }}</span>
                            <span class="text-lg font-black leading-none">{{ $consultation->created_at->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">
                                @if($consultation->doctor)
                                    Dr. {{ $consultation->doctor->name }}
                                @else
                                    Assigned Doctor Pending
                                @endif
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[11px] font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">{{ $consultation->doctor->specialization ?? 'GP' }}</span>
                                <span class="text-[11px] text-gray-400 capitalize">{{ $consultation->type ?? 'Video Consultation' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors bg-gray-50 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center bg-gray-50/30 rounded-2xl">
                        <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">No scheduled appointments</p>
                        <a href="{{ route('patient.doctors') }}" class="mt-3 inline-flex items-center text-xs font-bold text-indigo-600 uppercase tracking-widest hover:underline">
                            Book a Consultation
                        </a>
                    </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- Right Side column (Services & Support) -->
        <div class="space-y-6">
            <!-- 3. Health Services (Action Cards) -->
            <section class="space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest px-1">Health Services</h3>
                
                <!-- Talk to a Doctor -->
                <div class="group relative bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-all overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-8 -mt-8 opacity-50 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mb-4 shadow-sm border border-indigo-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-1">Talk to a Doctor</h4>
                        <p class="text-xs text-gray-500 mb-5 leading-relaxed">Instantly connect with certified medical specialists online.</p>
                        <a href="{{ route('patient.doctors') }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-100/50 hover:bg-indigo-700 transition-all uppercase tracking-widest">
                            Consult Now
                        </a>
                    </div>
                </div>

                <!-- Hire a Caregiver -->
                <div class="group relative bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-emerald-200 transition-all overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 opacity-50 transition-transform group-hover:scale-110"></div>
                    <div class="relative z-10">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mb-4 shadow-sm border border-emerald-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-1">Hire a Caregiver</h4>
                        <p class="text-xs text-gray-500 mb-5 leading-relaxed">Professional home nursing and care services for your family.</p>
                        <a href="{{ route('patient.doctors') }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-100/50 hover:bg-emerald-700 transition-all uppercase tracking-widest">
                            Find Caregivers
                        </a>
                    </div>
                </div>
            </section>

            <!-- Quick Contacts Section -->
            <section class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
                 <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">Quick Contact</h3>
                 <div class="space-y-4">
                    @forelse($quickContacts as $doctor)
                    <a href="{{ route('patient.doctors') }}" class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl hover:bg-indigo-50 transition-colors border border-gray-100 group cursor-pointer">
                        <div class="w-10 h-10 rounded-xl bg-gray-200 border-2 border-white overflow-hidden flex-shrink-0">
                            <img src="{{ $doctor->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->name).'&background=4F46E5&color=fff' }}" class="w-full h-full object-cover" alt="{{ $doctor->name }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-gray-900 truncate">{{ $doctor->name }}</h4>
                            <p class="text-[10px] text-indigo-600 uppercase font-bold tracking-tight">{{ $doctor->specialization }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                    @empty
                    <p class="text-xs text-gray-400 italic">No preferred doctors yet.</p>
                    @endforelse
                    
                    <a href="{{ route('patient.doctors') }}" class="block w-full text-center text-xs font-bold text-gray-500 hover:text-indigo-600 py-2.5 border border-gray-100 rounded-xl hover:bg-gray-50 transition-all mt-2 uppercase tracking-widest">
                        Browse All Specialists
                    </a>
                 </div>
            </section>

            <!-- SOS/Support -->
            <section class="bg-rose-50 rounded-2xl p-6 border border-rose-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-rose-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-900 uppercase tracking-tight">Need Support?</h4>
                        <p class="text-[11px] text-rose-700">Medical emergency or assistance</p>
                    </div>
                </div>
                <button class="w-full flex items-center justify-center gap-2 py-3 bg-white border-2 border-rose-500 text-rose-600 rounded-xl text-xs font-black shadow-lg shadow-rose-100 hover:bg-rose-500 hover:text-white transition-all uppercase tracking-widest">
                    Call Emergency Line
                </button>
            </section>
        </div>
    </div>
</div>
@endsection
