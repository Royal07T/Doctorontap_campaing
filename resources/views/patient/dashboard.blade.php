@extends('layouts.patient')

@section('title', 'Patient Dashboard')


@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-8">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    @endif

    <!-- Welcome Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                @php
                    $hour = now()->hour;
                    if ($hour >= 5 && $hour < 12) {
                        $greeting = 'Good morning';
                        $emoji = '🌅';
                    } elseif ($hour >= 12 && $hour < 17) {
                        $greeting = 'Good afternoon';
                        $emoji = '☀️';
                    } elseif ($hour >= 17 && $hour < 21) {
                        $greeting = 'Good evening';
                        $emoji = '🌆';
                    } else {
                        $greeting = 'Good night';
                        $emoji = '🌙';
                    }
                @endphp
                {{ $greeting }}, {{ explode(' ', $patient->name)[0] }}! {{ $emoji }}
            </h2>
            <p class="text-gray-600 text-base">Here is a summary of your health today.</p>
        </div>
        <a href="{{ route('patient.dashboard.export-history') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-sm font-semibold text-purple-700 hover:bg-purple-50 hover:border-purple-300 transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export History
        </a>
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
                    <svg class="w-3 h-3 text-pink-600 absolute top-0.5 right-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Talk to a Doctor</h3>
            <p class="text-sm text-gray-700 mb-4 font-medium">Connect with a certified GP or specialist in minutes.</p>
            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-pink-600 text-white rounded-lg text-sm font-bold hover:bg-pink-700 transition-all shadow-lg hover:shadow-xl" style="background-color: #db2777 !important; color: #ffffff !important;">
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
            <p class="text-sm text-gray-700 mb-4 font-medium">Professional nursing and home care services for your loved ones.</p>
            <a href="{{ route('patient.caregivers') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-teal-600 text-white rounded-lg text-sm font-bold hover:bg-teal-700 transition-all shadow-lg hover:shadow-xl" style="background-color: #0d9488 !important; color: #ffffff !important;">
                Browse Caregivers
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <!-- Daily Health Tip (Purple) -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-purple-200 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Daily Health Tip</h3>
            <p class="text-sm text-gray-700 mb-4 font-medium">{{ \Illuminate\Support\Str::limit($dailyHealthTip, 80) }}</p>
            <a href="#" class="inline-flex items-center gap-2 px-5 py-3 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl" style="background-color: #9333ea !important; color: #ffffff !important;">
                Read More
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Specializations Carousel -->
    @if(!empty($specializationCarousel) && count($specializationCarousel) > 0)
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Consult by Specialization</h3>
            <a href="{{ route('patient.doctors') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">View All</a>
        </div>
        <div class="relative overflow-hidden">
            <style>
                @keyframes slideSpecialization {
                    0% {
                        transform: translateX(0);
                    }
                    100% {
                        transform: translateX(-50%);
                    }
                }
                .specialization-carousel {
                    display: flex;
                    gap: 0.75rem;
                    animation: slideSpecialization 35s linear infinite;
                    width: fit-content;
                }
                .specialization-carousel:hover {
                    animation-play-state: paused;
                }
                .specialization-carousel-wrapper {
                    overflow: hidden;
                    position: relative;
                }
                .specialization-carousel-wrapper::before,
                .specialization-carousel-wrapper::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    width: 100px;
                    height: 100%;
                    z-index: 10;
                    pointer-events: none;
                }
                .specialization-carousel-wrapper::before {
                    left: 0;
                    background: linear-gradient(to right, rgba(255,255,255,1), rgba(255,255,255,0));
                }
                .specialization-carousel-wrapper::after {
                    right: 0;
                    background: linear-gradient(to left, rgba(255,255,255,1), rgba(255,255,255,0));
                }
            </style>
            <div class="specialization-carousel-wrapper">
                <div class="specialization-carousel">
                    <!-- First set of specializations -->
                    @foreach($specializationCarousel as $spec)
                    <a href="{{ route('patient.doctors-by-specialization', urlencode($spec['specialization'])) }}" class="flex-shrink-0 inline-flex flex-col items-center gap-2 px-5 py-4 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-300 transition-all cursor-pointer min-w-[120px]">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            {!! medical_icon('specializations', $spec['icon'], ['class' => 'w-6 h-6 text-purple-600']) !!}
                        </div>
                        <span class="text-xs font-semibold text-gray-900 text-center whitespace-nowrap">{{ $spec['name'] }}</span>
                    </a>
                    @endforeach
                    <!-- Duplicate set for seamless loop -->
                    @foreach($specializationCarousel as $spec)
                    <a href="{{ route('patient.doctors-by-specialization', urlencode($spec['specialization'])) }}" class="flex-shrink-0 inline-flex flex-col items-center gap-2 px-5 py-4 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-300 transition-all cursor-pointer min-w-[120px]">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            {!! medical_icon('specializations', $spec['icon'], ['class' => 'w-6 h-6 text-purple-600']) !!}
                        </div>
                        <span class="text-xs font-semibold text-gray-900 text-center whitespace-nowrap">{{ $spec['name'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Health Snapshot and Medical Coordination Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Health Snapshot (Left - 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
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

        <!-- Medical Coordination and Second Opinion (Right - 1 column, split into 2) -->
        <div class="space-y-4">
            <!-- Medical Coordination Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-5 border border-blue-200 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-blue-200 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Medical Coordination</h3>
                <p class="text-xs text-gray-700 mb-3 font-medium">Manage appointments and pharmacy needs.</p>
                <a href="{{ route('patient.consultations') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-all shadow-lg hover:shadow-xl" style="background-color: #2563eb !important; color: #ffffff !important;">
                    Manage Services
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <!-- Second Opinion Card -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl p-5 border border-indigo-200 shadow-sm">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-indigo-200 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Second Opinion</h3>
                <p class="text-xs text-gray-700 mb-3 font-medium">Get expert medical review of your diagnosis.</p>
                <a href="{{ route('patient.doctors') }}?service=second_opinion" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-all shadow-lg hover:shadow-xl" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    Get Opinion
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Symptoms Carousel -->
    @if(!empty($symptomDoctors) && count($symptomDoctors) > 0)
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Consult by Symptoms</h3>
            <a href="{{ route('patient.doctors') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">View All</a>
        </div>
        <div class="relative overflow-hidden">
            <style>
                @keyframes slide {
                    0% {
                        transform: translateX(0);
                    }
                    100% {
                        transform: translateX(-50%);
                    }
                }
                .symptom-carousel {
                    display: flex;
                    gap: 0.75rem;
                    animation: slide 30s linear infinite;
                    width: fit-content;
                }
                .symptom-carousel:hover {
                    animation-play-state: paused;
                }
                .symptom-carousel-wrapper {
                    overflow: hidden;
                    position: relative;
                }
                .symptom-carousel-wrapper::before,
                .symptom-carousel-wrapper::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    width: 100px;
                    height: 100%;
                    z-index: 10;
                    pointer-events: none;
                }
                .symptom-carousel-wrapper::before {
                    left: 0;
                    background: linear-gradient(to right, rgba(255,255,255,1), rgba(255,255,255,0));
                }
                .symptom-carousel-wrapper::after {
                    right: 0;
                    background: linear-gradient(to left, rgba(255,255,255,1), rgba(255,255,255,0));
                }
            </style>
            <div class="symptom-carousel-wrapper">
                <div class="symptom-carousel">
                    <!-- First set of symptoms -->
                    @foreach($symptomDoctors as $symptomGroup)
                    <a href="{{ route('patient.doctors-by-symptom', $symptomGroup['symptom_slug']) }}" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-3 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-300 transition-all cursor-pointer">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            {!! medical_icon('symptoms', $symptomGroup['symptom_slug'], ['class' => 'w-4 h-4 text-purple-600']) !!}
                        </div>
                        <span class="text-sm font-semibold text-gray-900 whitespace-nowrap">{{ $symptomGroup['symptom'] }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @endforeach
                    <!-- Duplicate set for seamless loop -->
                    @foreach($symptomDoctors as $symptomGroup)
                    <a href="{{ route('patient.doctors-by-symptom', $symptomGroup['symptom_slug']) }}" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-3 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-300 transition-all cursor-pointer">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            {!! medical_icon('symptoms', $symptomGroup['symptom_slug'], ['class' => 'w-4 h-4 text-purple-600']) !!}
                        </div>
                        <span class="text-sm font-semibold text-gray-900 whitespace-nowrap">{{ $symptomGroup['symptom'] }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Appointments, Contacts, and Emergency Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Upcoming Appointments (Left) -->
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
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
        <div class="lg:col-span-1 bg-white rounded-2xl p-6 shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Contacts</h3>
            <div class="space-y-3">
                @forelse($quickContacts->take(2) as $doctor)
                <a href="{{ route('patient.doctors') }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors cursor-pointer">
                    <div class="w-12 h-12 rounded-lg bg-gray-200 overflow-hidden flex-shrink-0 border-2 border-gray-300">
                        @if($doctor->photo_url)
                            <img src="{{ $doctor->photo_url }}" class="w-full h-full object-cover" alt="{{ $doctor->name }}">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ substr($doctor->name, 0, 1) }}
                            </div>
                        @endif
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
        <div class="lg:col-span-1 bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 bg-red-200 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Need Emergency Help?</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">Our 24/7 hotline is available for immediate medical assistance.</p>
            <a href="tel:112" class="inline-flex items-center gap-2 w-full justify-center px-4 py-3 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Call SOS Line
            </a>
        </div>
    </div>

    <!-- Women's Health Tracker (Below Appointments) -->
    @if(strtolower($patient->gender) === 'female')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow">
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
