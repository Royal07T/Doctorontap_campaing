@extends('layouts.patient')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Greeting Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                Good morning, {{ explode(' ', $patient->name)[0] }}! <span class="animate-bounce">👋</span>
            </h2>
            <p class="text-gray-500 mt-1">Here is a summary of your health today.</p>
        </div>
        <div>
            <a href="{{ route('patient.medical-records') }}" class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export History
            </a>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Talk to a Doctor -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Talk to a Doctor</h3>
                <p class="text-sm text-gray-500 mb-6">Connect with a certified GP or specialist in minutes.</p>
                <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-purple-100 w-full justify-center">
                    Consult Now
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Hire a Caregiver -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-teal-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-teal-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Hire a Caregiver</h3>
                <p class="text-sm text-gray-500 mb-6">Professional nursing and home care services for your loved ones.</p>
                <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-teal-100 w-full justify-center">
                    Browse Caregivers
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Medical Coordination -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-sky-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-sky-100 rounded-xl flex items-center justify-center text-sky-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Medical Coordination</h3>
                <p class="text-sm text-gray-500 mb-6">Let us manage your appointments and pharmacy needs.</p>
                <a href="{{ route('patient.medical-records') }}" class="inline-flex items-center px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-sky-100 w-full justify-center">
                    Manage Services
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Health Snapshot & Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Snapshot Cards (Span 3 on Desktop) -->
        <div class="lg:col-span-3 space-y-8">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Health Snapshot</h3>
                    <a href="{{ route('patient.medical-records') }}" class="text-sm font-bold text-purple-600 hover:text-purple-700">View All Trends</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Blood Pressure -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold text-gray-400 uppercase tracking-wide">Pressure</span>
                        </div>
                        <div class="flex items-end space-x-2">
                            @if(isset($latestVitals) && $latestVitals->blood_pressure)
                                <span class="text-2xl font-bold text-gray-900">{{ $latestVitals->blood_pressure }}</span>
                                <span class="text-xs text-green-500 font-bold bg-green-50 px-2 py-0.5 rounded-full mb-1">Normal</span>
                            @else
                                <span class="text-2xl font-bold text-gray-300">--/--</span>
                                <span class="text-xs text-gray-400 font-medium mb-1">No data</span>
                            @endif
                        </div>
                    </div>

                    <!-- Temperature -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center text-orange-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold text-gray-400 uppercase tracking-wide">Temp</span>
                        </div>
                        <div class="flex items-end space-x-2">
                            @if(isset($latestVitals) && $latestVitals->temperature)
                                <span class="text-2xl font-bold text-gray-900">{{ $latestVitals->temperature }}°C</span>
                                <span class="text-xs text-green-500 font-bold bg-green-50 px-2 py-0.5 rounded-full mb-1">Steady</span>
                            @else
                                <span class="text-2xl font-bold text-gray-300">--</span>
                                <span class="text-xs text-gray-400 font-medium mb-1">No data</span>
                            @endif
                        </div>
                    </div>

                    <!-- Heart Rate -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold text-gray-400 uppercase tracking-wide">BPM</span>
                        </div>
                        <div class="flex items-end space-x-2">
                            @if(isset($latestVitals) && $latestVitals->heart_rate)
                                <span class="text-2xl font-bold text-gray-900">{{ $latestVitals->heart_rate }}</span>
                                <span class="text-xs text-green-500 font-bold bg-green-50 px-2 py-0.5 rounded-full mb-1">Stable</span>
                            @else
                                <span class="text-2xl font-bold text-gray-300">--</span>
                                <span class="text-xs text-gray-400 font-medium mb-1">No data</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Upcoming Appointments</h3>
                    <a href="{{ route('patient.doctors') }}" class="text-sm font-bold text-purple-600 hover:text-purple-700 uppercase tracking-wide">Book New</a>
                </div>
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-50 overflow-hidden">
                    @forelse($upcomingConsultations as $consultation)
                    <div class="p-5 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0 text-center bg-purple-50 rounded-xl p-2 w-16">
                            <span class="block text-[10px] font-bold text-purple-400 uppercase tracking-tighter">{{ $consultation->created_at->format('M') }}</span>
                            <span class="block text-xl font-black text-purple-700 leading-none">{{ $consultation->created_at->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-gray-900 truncate">
                                @if($consultation->doctor)
                                    {{ $consultation->doctor->name }}
                                @else
                                    Assigned Doctor Pending
                                @endif
                            </h4>
                            <div class="flex flex-wrap items-center text-xs text-gray-500 mt-1 gap-x-2 gap-y-1">
                                <span class="font-bold text-purple-600">{{ $consultation->doctor->specialization ?? 'General Practitioner' }}</span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span class="capitalize">{{ $consultation->type ?? 'Video Call' }}</span>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <span>{{ $consultation->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="p-2 text-gray-400 hover:text-purple-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    @empty
                    <div class="p-10 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-4">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 font-medium text-sm">No upcoming appointments</p>
                        <a href="{{ route('patient.doctors') }}" class="text-purple-600 font-bold text-xs mt-2 hover:underline uppercase tracking-wide">Book a consultation now</a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Menstrual Cycle (Conditional) -->
            @if(strtolower($patient->gender) === 'female')
            <div id="menstrual-cycle">
                 <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Health Trackers</h3>
                </div>
                 <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-0 overflow-hidden group relative">
                    <!-- Background Decoration -->
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 text-red-500 opacity-10 transition-transform group-hover:scale-125 duration-700">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </div>
                    
                    <div class="p-6 relative z-10">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <p class="text-[10px] font-black text-red-500 uppercase tracking-widest mb-1 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-ping"></span>
                                    Cycle Tracker
                                </p>
                                @if($nextPeriodPrediction)
                                    <h4 class="text-3xl font-black text-gray-900 tracking-tight">{{ $nextPeriodPrediction->format('M d') }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Next period starts 
                                        @if($nextPeriodPrediction->isToday())
                                            <span class="text-red-600 font-bold">today</span>
                                        @else
                                            in <span class="text-gray-900 font-bold">{{ $nextPeriodPrediction->diffInDays(now()) }} days</span>
                                        @endif
                                    </p>
                                @else
                                    <h4 class="text-xl font-bold text-gray-900 mt-1">Start Tracking</h4>
                                    <p class="text-xs text-gray-500 mt-1">Log your first period to get predictions.</p>
                                @endif
                            </div>
                            <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center text-red-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Mini Stats or Action -->
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('patient.doctors') }}" class="flex flex-col items-center justify-center p-3 bg-red-50/50 hover:bg-red-50 rounded-2xl transition-colors border border-red-100 group/item">
                                <span class="text-[10px] font-bold text-red-400 uppercase tracking-tighter mb-1">Log Daily Symptoms</span>
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </a>
                            <div class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-2xl border border-gray-100">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-1">Current Phase</span>
                                <span class="text-xs font-black text-gray-700">Follicular</span>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
            @endif
        </div>

        <!-- Right Side column (Tips/Contacts) -->
        <div class="space-y-6">
            <!-- Daily Health Tip -->
            <div class="bg-gray-900 rounded-2xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-20">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </div>
                <div class="relative z-10">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Daily Health Tip</h3>
                    <p class="text-sm font-medium leading-relaxed mb-6">
                        "Stress management positively impacts sexual wellness. Try spending 10 minutes today on focused breathing exercises."
                    </p>
                    <a href="#" class="text-xs font-bold text-purple-400 hover:text-purple-300 flex items-center gap-1 transition-colors">
                        Learn more
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Quick Contacts -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Quick Doctors</h3>
                <div class="space-y-4">
                    @forelse($quickContacts as $doctor)
                    <div class="flex items-center justify-between group cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                <img src="{{ $doctor->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->name).'&background=7B3DE9&color=fff' }}" class="w-full h-full object-cover" alt="{{ $doctor->name }}">
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 line-clamp-1" title="{{ $doctor->name }}">{{ $doctor->name }}</h4>
                                <p class="text-[10px] font-medium text-gray-500 truncate uppercase">{{ $doctor->specialization }}</p>
                            </div>
                        </div>
                         <a href="{{ route('patient.doctors') }}" class="text-gray-300 group-hover:text-purple-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No doctors available yet.</p>
                    @endforelse
                    
                    <a href="{{ route('patient.doctors') }}" class="block w-full py-2.5 text-center text-xs font-bold text-gray-600 border border-gray-100 rounded-xl hover:bg-gray-50 hover:text-purple-600 transition-all mt-4 uppercase tracking-wide">
                        View All Specialists
                    </a>
                </div>
            </div>

            <!-- SOS/Emergency -->
             <div class="bg-red-50 rounded-2xl p-6 border border-red-100 text-center">
                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-red-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-red-900 uppercase tracking-wide">Help & Support</h3>
                <p class="text-xs text-red-700 mt-1 mb-4">Need immediate medical assistance?</p>
                <button class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-red-100 transition-all">
                    Call SOS Line
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
