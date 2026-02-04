@extends('layouts.patient')

@section('title', 'Patient Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Welcome Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">
                Good morning, {{ explode(' ', $patient->name)[0] }}! 👋
            </h2>
            <p class="text-gray-600 mt-1">Here is a summary of your health today.</p>
        </div>
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export History
        </button>
    </div>

    <!-- Three Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Talk to a Doctor (Pink) -->
        <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-2xl p-6 border border-pink-200 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-pink-200 rounded-xl flex items-center justify-center relative">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <svg class="w-3 h-3 text-pink-600 absolute top-1 right-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Talk to a Doctor</h3>
            <p class="text-sm text-gray-600 mb-4">Connect with a certified GP or specialist in minutes.</p>
            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-pink-600 text-white rounded-lg text-sm font-medium hover:bg-pink-700 transition-colors">
                Consult Now
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <!-- Hire a Caregiver (Teal) -->
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-2xl p-6 border border-teal-200 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-teal-200 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Hire a Caregiver</h3>
            <p class="text-sm text-gray-600 mb-4">Professional nursing and home care services for your loved ones.</p>
            <a href="{{ route('patient.caregivers') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-medium hover:bg-teal-700 transition-colors">
                Browse Caregivers
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <!-- Medical Coordination (Blue) -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Medical Coordination</h3>
            <p class="text-sm text-gray-600 mb-4">Let us manage your appointments and pharmacy needs.</p>
            <a href="{{ route('patient.consultations') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                Manage Services
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Health Snapshot and Daily Tip Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Health Snapshot (Left - 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Health Snapshot</h3>
                <a href="{{ route('patient.medical-records') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">View All Trends</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Blood Pressure -->
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-500">Blood Pressure</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $latestVitals->blood_pressure ?? '120/80' }}</p>
                    <p class="text-xs text-green-600 mt-1">~Normal</p>
                </div>

                <!-- Temperature -->
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-500">Temperature</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $latestVitals->temperature ?? '36.6' }}°C</p>
                    <p class="text-xs text-green-600 mt-1">—Steady</p>
                </div>

                <!-- Weight -->
                <div class="bg-white p-4 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-500">Weight</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $latestVitals->weight ?? '64.5' }} kg</p>
                    <p class="text-xs text-red-600 mt-1">~+0.5kg</p>
                </div>
            </div>
        </div>

        <!-- Daily Health Tip (Right - 1 column) -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 shadow-sm text-white">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold">Daily Health Tip</h3>
            </div>
            <p class="text-sm leading-relaxed mb-4">{{ $dailyHealthTip }}</p>
            <a href="#" class="text-sm font-medium underline hover:no-underline">Read Full Article</a>
        </div>
    </div>

    <!-- Appointments, Contacts, and Emergency Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Upcoming Appointments (Left) -->
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Upcoming Appointments</h3>
                <a href="{{ route('patient.doctors') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">BOOK NEW</a>
            </div>
            <div class="space-y-4">
                @forelse($upcomingConsultations->take(2) as $consultation)
                @php
                    $appointmentDate = $consultation->scheduled_at ?? $consultation->created_at;
                    $appointmentTime = $consultation->scheduled_at ? $consultation->scheduled_at->format('h:i A') : ($consultation->created_at->format('h:i A'));
                @endphp
                <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-lg bg-purple-50 flex flex-col items-center justify-center text-purple-600 border border-purple-100">
                            <span class="text-xs font-bold">{{ $appointmentDate->format('M') }}</span>
                            <span class="text-lg font-bold leading-none">{{ $appointmentDate->format('j') }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            @if($consultation->doctor)
                                Dr. {{ $consultation->doctor->name }}
                            @else
                                {{ $consultation->problem ?? 'Routine Checkup' }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $consultation->doctor->specialization ?? 'General Practitioner' }} • {{ $consultation->consultation_mode ?? 'Video Call' }} • {{ $appointmentTime }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <p class="text-sm">No upcoming appointments</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Contacts (Middle) -->
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Contacts</h3>
            <div class="space-y-3">
                @forelse($quickContacts->take(2) as $doctor)
                <a href="{{ route('patient.doctors') }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer">
                    <div class="w-10 h-10 rounded-lg bg-gray-200 overflow-hidden flex-shrink-0">
                        <img src="{{ $doctor->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->name).'&background=9333EA&color=fff' }}" class="w-full h-full object-cover" alt="{{ $doctor->name }}">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $doctor->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $doctor->specialization ?? 'General Physician' }} • {{ $doctor->is_available ? 'Online' : 'Offline' }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </a>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <p class="text-sm">No contacts available</p>
                </div>
                @endforelse
                <a href="{{ route('patient.doctors') }}" class="block w-full text-center py-2 text-sm font-medium text-gray-600 hover:text-purple-600 border border-gray-200 rounded-lg hover:border-purple-200 transition-colors">
                    Browse All Specialists
                </a>
            </div>
        </div>

        <!-- Emergency Help (Right) -->
        <div class="lg:col-span-1 bg-gradient-to-br from-blue-900 to-blue-800 rounded-2xl p-6 shadow-sm text-white">
            <h3 class="text-lg font-bold mb-2">Need Emergency Help?</h3>
            <p class="text-sm text-blue-100 mb-4">Our 24/7 hotline is available for immediate medical assistance.</p>
            <a href="tel:112" class="inline-flex items-center gap-2 w-full justify-center px-4 py-3 bg-white text-blue-900 rounded-lg text-sm font-bold hover:bg-blue-50 transition-colors">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Call SOS Line
            </a>
        </div>
    </div>

    <!-- Women's Health Tracker (Below Appointments) -->
    @if(strtolower($patient->gender) === 'female')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
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
    </div>
    @endif
</div>
@endsection
