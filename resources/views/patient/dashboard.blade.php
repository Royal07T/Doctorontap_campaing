<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Dashboard - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        [x-cloak] { display: none !important; }
        
        /* Carousel Animation */
        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-250px * var(--specialization-count, 18))); }
        }
        
        @keyframes scroll-symptoms {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-216px * var(--symptom-count, 13))); }
        }
        
        .animate-scroll {
            animation: scroll calc(var(--specialization-count, 18) * 2s) linear infinite;
        }
        
        .animate-scroll-symptoms {
            animation: scroll-symptoms calc(var(--symptom-count, 13) * 2s) linear infinite;
        }
        
        .animate-scroll:hover,
        .animate-scroll-symptoms:hover {
            animation-play-state: paused;
        }
        
        /* Symptom icon colors */
        .symptom-icon-menstruation { color: #D72638; }
        .symptom-icon-rashes { color: #F4A261; }
        .symptom-icon-skin-issues { color: #F4A261; }
        .symptom-icon-headache { color: #6D597A; }
        .symptom-icon-cough { color: #457B9D; }
        .symptom-icon-fever { color: #E63946; }
        .symptom-icon-stomach-pain { color: #2A9D8F; }
        .symptom-icon-back-pain { color: #264653; }
        .symptom-icon-eye-problems { color: #1D3557; }
        .symptom-icon-ear-pain { color: #8D99AE; }
        .symptom-icon-joint-pain { color: #588157; }
        .symptom-icon-chest-pain { color: #C1121F; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Sidebar Header -->
            <div class="purple-gradient p-5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-8 w-auto">
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User Info -->
            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    @if($patient->photo_url)
                        <img src="{{ $patient->photo_url }}" alt="{{ $patient->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-md">
                    @else
                        <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-md">
                        {{ substr($patient->name, 0, 1) }}
                    </div>
                    @endif
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ $patient->name }}</p>
                        <p class="text-xs text-gray-500">Patient</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2 overflow-y-auto flex-1" style="overflow-x: visible;">
                <a href="{{ route('patient.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('patient.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>My Consultations</span>
                </a>

                <a href="{{ route('patient.medical-records') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Medical Records</span>
                </a>

                <a href="{{ route('patient.payments') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Payments</span>
                </a>

                <a href="{{ route('patient.doctors') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Find Doctors</span>
                </a>

                @if($dependents->count() > 0)
                <a href="{{ route('patient.dependents') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Dependents</span>
                </a>
                @endif

                @if(strtolower($patient->gender) === 'female')
                <a href="#menstrual-cycle" onclick="document.getElementById('menstrual-cycle').scrollIntoView({behavior: 'smooth'}); return false;" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>Cycle Tracker</span>
                </a>
                @endif

                @if(strtolower($patient->gender) === 'male')
                <a href="#sexual-health" onclick="document.getElementById('sexual-health').scrollIntoView({behavior: 'smooth'}); return false;" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Health Tracker</span>
                </a>
                @endif

                <a href="{{ route('patient.profile') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                    <span>Profile</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <form method="POST" action="{{ route('patient.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">Dashboard</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Warm, Personal Welcome Section -->
                <div class="bg-gradient-to-br from-white via-purple-50/30 to-white rounded-2xl shadow-lg border border-purple-100/50 p-8 mb-8 relative overflow-hidden">
                    <!-- Decorative Background Elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-purple-200/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-200/10 rounded-full blur-3xl -ml-24 -mb-24"></div>
                    
                    <!-- CTA Button - Top Right Corner -->
                    <div class="absolute top-4 right-4 lg:top-6 lg:right-6 z-10">
                        <a href="{{ route('patient.doctors') }}" 
                           class="inline-flex items-center gap-2 px-5 py-3 lg:px-6 lg:py-3.5 purple-gradient hover:opacity-90 text-white rounded-xl font-bold text-sm lg:text-base transition-all shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-100">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            <span class="hidden sm:inline">Talk to a Doctor</span>
                            <span class="sm:hidden">Consult</span>
                            <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <!-- Content -->
                    <div class="relative pr-0 lg:pr-0">
                        <!-- Greeting -->
                        <div class="mb-6">
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                                Hello, {{ explode(' ', $patient->name)[0] }}! 
                                <span class="inline-block animate-bounce">👋</span>
                            </h1>
                            <p class="text-gray-600 text-base">How are you feeling today?</p>
                            </div>
                        
                        <!-- Mood Selector -->
                        <div class="mb-6" x-data="{ selectedMood: null }">
                            <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-3">Select Your Mood</p>
                            <div class="flex items-center gap-3 flex-wrap">
                                <button @click="selectedMood = 'good'" 
                                        :class="selectedMood === 'good' ? 'bg-emerald-500 text-white shadow-lg scale-105 border-2 border-emerald-600' : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-emerald-300 hover:bg-emerald-50'"
                                        class="flex items-center gap-2.5 px-5 py-3 rounded-xl font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd"/>
                                </svg>
                                    <span>Good</span>
                                </button>
                                <button @click="selectedMood = 'okay'" 
                                        :class="selectedMood === 'okay' ? 'bg-amber-500 text-white shadow-lg scale-105 border-2 border-amber-600' : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-amber-300 hover:bg-amber-50'"
                                        class="flex items-center gap-2.5 px-5 py-3 rounded-xl font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 10-2 0 1 1 0 002 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Okay</span>
                                </button>
                                <button @click="selectedMood = 'not_great'" 
                                        :class="selectedMood === 'not_great' ? 'bg-red-500 text-white shadow-lg scale-105 border-2 border-red-600' : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-red-300 hover:bg-red-50'"
                                        class="flex items-center gap-2.5 px-5 py-3 rounded-xl font-semibold text-sm transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-1 4a1 1 0 10-2 0 1 1 0 002 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Not great</span>
                                </button>
                        </div>
                    </div>

                        <!-- Quick Note -->
                        @php
                            $lastCheckup = $patient->consultations()
                                ->where('status', 'completed')
                                ->latest('consultation_completed_at')
                                ->first();
                            
                            $lastCheckupDate = $lastCheckup ? ($lastCheckup->consultation_completed_at ?? $lastCheckup->updated_at) : null;
                            $daysSinceCheckup = $lastCheckupDate ? now()->diffInDays($lastCheckupDate) : null;
                        @endphp
                        @if($lastCheckupDate)
                        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 mb-1">Quick Note</p>
                                    <p class="text-sm text-gray-700">
                                        Your last checkup was on 
                                        <span class="font-semibold text-gray-900">{{ $lastCheckupDate->format('M d, Y') }}</span>
                                        @if($lastCheckupDate->format('H:i') !== '00:00')
                                            at <span class="font-semibold text-gray-900">{{ $lastCheckupDate->format('g:i A') }}</span>
                                        @endif
                                        @if($daysSinceCheckup !== null && $daysSinceCheckup >= 0)
                                            <span class="text-gray-600">({{ $daysSinceCheckup }} {{ $daysSinceCheckup === 1 ? 'day' : 'days' }} ago)</span>
                                        @endif
                                    </p>
                        </div>
                    </div>
                            </div>
                        @endif
                        
                        <!-- Health Tips Section -->
                        <div class="mt-6 p-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 rounded-xl border border-emerald-100/50 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider mb-2">Health Tip</p>
                                    <p class="text-sm font-medium text-gray-800" id="healthTip">
                                        @php
                                            $healthTips = [
                                                "Staying hydrated can reduce your headaches.",
                                                "A 10-minute walk can boost your mood instantly.",
                                                "Deep breathing helps lower stress in minutes.",
                                                "Getting 7-8 hours of sleep improves your immune system.",
                                                "Eating slowly helps with better digestion.",
                                                "Regular handwashing prevents most infections.",
                                                "Stretching for 5 minutes reduces muscle tension.",
                                                "Drinking water before meals aids weight management.",
                                                "Taking breaks from screens reduces eye strain.",
                                                "A short walk after meals improves blood sugar.",
                                                "Laughing releases endorphins that reduce pain.",
                                                "Sunlight exposure helps regulate your sleep cycle.",
                                                "Chewing gum can help reduce stress and improve focus.",
                                                "Standing up every hour improves circulation.",
                                                "Green tea contains antioxidants that support heart health.",
                                                "Regular exercise can improve sexual health and performance.",
                                                "Open communication with your partner enhances intimacy.",
                                                "Stress management positively impacts sexual wellness.",
                                                "Adequate sleep is essential for healthy libido.",
                                                "Staying active improves blood flow and sexual function.",
                                                "Regular check-ups help maintain sexual health.",
                                                "Healthy eating supports overall sexual wellness.",
                                                "Reducing alcohol intake can improve sexual performance.",
                                                "Quitting smoking benefits sexual health significantly."
                                            ];
                                            echo $healthTips[array_rand($healthTips)];
                                        @endphp
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Specializations Carousel -->
                @if($specializations->count() > 0)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Find Doctors by Specialization
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Browse doctors by their area of expertise</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 overflow-hidden">
                        <div class="relative">
                            <div class="flex space-x-3 animate-scroll" style="--specialization-count: {{ $specializations->count() }};">
                                @foreach($specializations as $specialization)
                                    <a href="{{ route('patient.doctors-by-specialization', urlencode($specialization)) }}" 
                                       class="flex-shrink-0 w-[240px] bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl p-4 transition-all duration-300 hover:shadow-md group cursor-pointer border border-purple-100">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $specialization }}</p>
                                                <p class="text-xs text-purple-600 font-medium mt-0.5">View Doctors →</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                                <!-- Duplicate items for seamless loop -->
                                @foreach($specializations as $specialization)
                                    <a href="{{ route('patient.doctors-by-specialization', urlencode($specialization)) }}" 
                                       class="flex-shrink-0 w-[240px] bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl p-4 transition-all duration-300 hover:shadow-md group cursor-pointer border border-purple-100">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $specialization }}</p>
                                                <p class="text-xs text-purple-600 font-medium mt-0.5">View Doctors →</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4 text-center">Click any specialization to view available doctors</p>
                    </div>
                </div>
                @endif

                <!-- Consult Doctor by Symptoms Section -->
                @if(isset($symptoms) && count($symptoms) > 0)
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Consult Top Doctors Online
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Private online consultations with verified doctors in all specialists</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 overflow-hidden">
                        <div class="relative">
                            <div class="flex space-x-3 animate-scroll-symptoms" style="--symptom-count: {{ count($symptoms) }};">
                                @foreach($symptoms as $symptom)
                                    @php
                                        $symptomSlug = strtolower(str_replace(' ', '-', $symptom['name']));
                                        $symptomName = $symptom['name'];
                                        $symptomColor = $symptom['color'] ?? '#9333EA';
                                        $iconName = $symptom['icon'] ?? 'default';
                                    @endphp
                                    <a href="{{ route('patient.doctors-by-symptom', $symptomSlug) }}" 
                                       class="flex-shrink-0 w-[200px] bg-white hover:bg-gray-50 rounded-xl p-4 transition-all duration-300 hover:shadow-md group cursor-pointer border border-gray-200">
                                        <div class="flex flex-col items-center text-center space-y-3">
                                            <!-- Icon Container -->
                                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-sm" style="background-color: {{ $symptomColor }}15;">
                                        @if($iconName === 'menstruation-pregnancy')
                                            <!-- Period/Pregnancy Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Uterus shape -->
                                                <path d="M50 20 C35 20, 25 30, 25 45 C25 55, 30 65, 40 70 L40 80 C40 85, 45 90, 50 90 C55 90, 60 85, 60 80 L60 70 C70 65, 75 55, 75 45 C75 30, 65 20, 50 20 Z" fill="{{ $symptomColor }}" opacity="0.3"/>
                                                <path d="M50 20 C35 20, 25 30, 25 45 C25 55, 30 65, 40 70 L40 80 C40 85, 45 90, 50 90 C55 90, 60 85, 60 80 L60 70 C70 65, 75 55, 75 45 C75 30, 65 20, 50 20 Z" stroke="{{ $symptomColor }}" stroke-width="3" fill="none"/>
                                                <!-- Ovaries -->
                                                <circle cx="35" cy="40" r="5" fill="{{ $symptomColor }}"/>
                                                <circle cx="65" cy="40" r="5" fill="{{ $symptomColor }}"/>
                                                <!-- Drop at bottom -->
                                                <ellipse cx="50" cy="85" rx="8" ry="10" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                        @elseif($iconName === 'acne-skin')
                                            <!-- Acne/Skin Issues Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Face profile -->
                                                <path d="M30 30 Q30 20, 40 20 L60 20 Q70 20, 70 30 L70 50 Q70 60, 60 60 L50 70 L40 60 Q30 60, 30 50 Z" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                <!-- Acne spots -->
                                                <circle cx="45" cy="35" r="3" fill="{{ $symptomColor }}"/>
                                                <circle cx="55" cy="38" r="2.5" fill="{{ $symptomColor }}"/>
                                                <circle cx="50" cy="42" r="2" fill="{{ $symptomColor }}"/>
                                                <circle cx="48" cy="48" r="2.5" fill="{{ $symptomColor }}"/>
                                                <circle cx="52" cy="50" r="2" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                        @elseif($iconName === 'performance')
                                            <!-- Performance Issues Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Female symbol -->
                                                <circle cx="40" cy="40" r="15" fill="none" stroke="{{ $symptomColor }}" stroke-width="4"/>
                                                <path d="M40 55 L40 75" stroke="{{ $symptomColor }}" stroke-width="4" stroke-linecap="round"/>
                                                <path d="M30 65 L50 65" stroke="{{ $symptomColor }}" stroke-width="4" stroke-linecap="round"/>
                                                <!-- Male symbol -->
                                                <circle cx="60" cy="40" r="15" fill="none" stroke="#9333EA" stroke-width="4"/>
                                                <path d="M60 55 L60 75" stroke="#9333EA" stroke-width="4" stroke-linecap="round"/>
                                                <path d="M60 55 L70 45" stroke="#9333EA" stroke-width="4" stroke-linecap="round"/>
                                                    </svg>
                                        @elseif($iconName === 'cold-cough')
                                            <!-- Cold/Cough/Fever Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Person profile -->
                                                <circle cx="50" cy="35" r="12" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                <path d="M50 47 L50 70 Q50 75, 45 75 L35 75 Q30 75, 30 70 L30 60" fill="#87CEEB" stroke="#333" stroke-width="2"/>
                                                <!-- Hand to mouth -->
                                                <circle cx="60" cy="45" r="8" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                <!-- Cough lines -->
                                                <path d="M70 40 Q75 35, 80 40" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                <path d="M72 45 Q77 40, 82 45" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                <path d="M74 50 Q79 45, 84 50" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                    </svg>
                                        @elseif($iconName === 'child-sick')
                                            <!-- Child Not Feeling Well Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Baby head -->
                                                <circle cx="50" cy="40" r="20" fill="#FFA500" stroke="#333" stroke-width="2"/>
                                                <!-- Smile -->
                                                <path d="M40 45 Q50 50, 60 45" stroke="#333" stroke-width="2" fill="none" stroke-linecap="round"/>
                                                <!-- Eyes -->
                                                <circle cx="45" cy="38" r="2" fill="#333"/>
                                                <circle cx="55" cy="38" r="2" fill="#333"/>
                                                <!-- Body -->
                                                <ellipse cx="50" cy="70" rx="18" ry="20" fill="#9333EA" stroke="#333" stroke-width="2"/>
                                                <!-- Bib -->
                                                <path d="M40 65 L60 65 L58 75 L42 75 Z" fill="#FFA500" stroke="#333" stroke-width="1"/>
                                                    </svg>
                                        @elseif($iconName === 'depression-anxiety')
                                            <!-- Depression/Anxiety Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <!-- Person profile -->
                                                <circle cx="50" cy="35" r="12" fill="#FFA500" stroke="#333" stroke-width="2"/>
                                                <path d="M50 47 L50 70 Q50 75, 45 75 L35 75 Q30 75, 30 70 L30 60" fill="#9333EA" stroke="#333" stroke-width="2"/>
                                                <!-- Thought bubble -->
                                                <path d="M50 25 Q60 15, 70 20 Q75 25, 70 30 Q65 35, 60 30 Q55 25, 50 25" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="2"/>
                                                <circle cx="65" cy="25" r="8" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="2"/>
                                                <!-- Cross in bubble -->
                                                <path d="M62 22 L68 28 M68 22 L62 28" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                        @elseif($iconName === 'headache')
                                            <!-- Headache Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="50" cy="50" r="25" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                <path d="M35 45 Q50 40, 65 45" stroke="#333" stroke-width="2" fill="none"/>
                                                <circle cx="45" cy="48" r="2" fill="#333"/>
                                                <circle cx="55" cy="48" r="2" fill="#333"/>
                                                <path d="M30 35 Q25 30, 20 35" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                <path d="M80 35 Q85 30, 90 35" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                    </svg>
                                        @elseif($iconName === 'stomach-pain')
                                            <!-- Stomach Pain Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M50 30 Q40 35, 35 45 Q30 55, 35 65 Q40 75, 50 80 Q60 75, 65 65 Q70 55, 65 45 Q60 35, 50 30 Z" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="3"/>
                                                <circle cx="50" cy="55" r="3" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                        @elseif($iconName === 'back-pain')
                                            <!-- Back Pain Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="45" y="20" width="10" height="60" fill="#333" rx="2"/>
                                                <circle cx="50" cy="30" r="3" fill="{{ $symptomColor }}"/>
                                                <circle cx="50" cy="45" r="3" fill="{{ $symptomColor }}"/>
                                                <circle cx="50" cy="60" r="3" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                        @elseif($iconName === 'eye-problems')
                                            <!-- Eye Problems Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <ellipse cx="50" cy="50" rx="30" ry="20" fill="none" stroke="#333" stroke-width="3"/>
                                                <circle cx="50" cy="50" r="8" fill="#333"/>
                                                    </svg>
                                        @elseif($iconName === 'ear-pain')
                                            <!-- Ear Pain Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M30 30 Q25 40, 30 50 Q35 60, 30 70 Q25 75, 30 80 Q35 85, 40 80 Q45 75, 40 70 Q35 60, 40 50 Q45 40, 40 30 Q35 25, 30 30" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                <circle cx="35" cy="50" r="5" fill="{{ $symptomColor }}"/>
                                            </svg>
                                        @elseif($iconName === 'joint-pain')
                                            <!-- Joint Pain Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="30" cy="30" r="5" fill="{{ $symptomColor }}"/>
                                                <circle cx="50" cy="50" r="8" fill="{{ $symptomColor }}"/>
                                                <circle cx="70" cy="30" r="5" fill="{{ $symptomColor }}"/>
                                                <path d="M30 30 L50 50 L70 30" stroke="#333" stroke-width="3"/>
                                            </svg>
                                        @elseif($iconName === 'chest-pain')
                                            <!-- Chest Pain Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M50 30 Q40 40, 35 50 Q30 60, 35 70 Q40 80, 50 85 Q60 80, 65 70 Q70 60, 65 50 Q60 40, 50 30 Z" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="3"/>
                                                    </svg>
                                                @else
                                            <!-- Default Icon -->
                                            <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="50" cy="50" r="30" fill="{{ $symptomColor }}" opacity="0.2" stroke="{{ $symptomColor }}" stroke-width="3"/>
                                                <path d="M50 40 L50 60 M40 50 L60 50" stroke="{{ $symptomColor }}" stroke-width="4" stroke-linecap="round"/>
                                                    </svg>
                                                @endif
                                            </div>
                                    
                                    <!-- Symptom Name -->
                                            <div class="flex-1 w-full">
                                        <h4 class="font-semibold text-gray-900 text-xs mb-1">{{ $symptomName }}</h4>
                                        <p class="text-xs font-medium" style="color: {{ $symptomColor }};">Find Doctors →</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                                <!-- Duplicate items for seamless loop -->
                                @foreach($symptoms as $symptom)
                                    @php
                                        $symptomSlug = strtolower(str_replace(' ', '-', $symptom['name']));
                                        $symptomName = $symptom['name'];
                                        $symptomColor = $symptom['color'] ?? '#9333EA';
                                        $iconName = $symptom['icon'] ?? 'default';
                                    @endphp
                                    <a href="{{ route('patient.doctors-by-symptom', $symptomSlug) }}" 
                                       class="flex-shrink-0 w-[200px] bg-white hover:bg-gray-50 rounded-xl p-4 transition-all duration-300 hover:shadow-md group cursor-pointer border border-gray-200">
                                        <div class="flex flex-col items-center text-center space-y-3">
                                            <!-- Icon Container -->
                                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-sm" style="background-color: {{ $symptomColor }}15;">
                                                @if($iconName === 'menstruation-pregnancy')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M50 20 C35 20, 25 30, 25 45 C25 55, 30 65, 40 70 L40 80 C40 85, 45 90, 50 90 C55 90, 60 85, 60 80 L60 70 C70 65, 75 55, 75 45 C75 30, 65 20, 50 20 Z" fill="{{ $symptomColor }}" opacity="0.3"/>
                                                        <path d="M50 20 C35 20, 25 30, 25 45 C25 55, 30 65, 40 70 L40 80 C40 85, 45 90, 50 90 C55 90, 60 85, 60 80 L60 70 C70 65, 75 55, 75 45 C75 30, 65 20, 50 20 Z" stroke="{{ $symptomColor }}" stroke-width="3" fill="none"/>
                                                        <circle cx="35" cy="40" r="5" fill="{{ $symptomColor }}"/>
                                                        <circle cx="65" cy="40" r="5" fill="{{ $symptomColor }}"/>
                                                        <ellipse cx="50" cy="85" rx="8" ry="10" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                                @elseif($iconName === 'acne-skin')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M30 30 Q30 20, 40 20 L60 20 Q70 20, 70 30 L70 50 Q70 60, 60 60 L50 70 L40 60 Q30 60, 30 50 Z" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                        <circle cx="45" cy="35" r="3" fill="{{ $symptomColor }}"/>
                                                        <circle cx="55" cy="38" r="2.5" fill="{{ $symptomColor }}"/>
                                                        <circle cx="50" cy="42" r="2" fill="{{ $symptomColor }}"/>
                                                        <circle cx="48" cy="48" r="2.5" fill="{{ $symptomColor }}"/>
                                                        <circle cx="52" cy="50" r="2" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                                @elseif($iconName === 'performance')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="40" cy="40" r="15" fill="none" stroke="{{ $symptomColor }}" stroke-width="4"/>
                                                        <path d="M40 55 L40 75" stroke="{{ $symptomColor }}" stroke-width="4" stroke-linecap="round"/>
                                                        <path d="M30 65 L50 65" stroke="{{ $symptomColor }}" stroke-width="4" stroke-linecap="round"/>
                                                        <circle cx="60" cy="40" r="15" fill="none" stroke="#9333EA" stroke-width="4"/>
                                                        <path d="M60 55 L60 75" stroke="#9333EA" stroke-width="4" stroke-linecap="round"/>
                                                        <path d="M60 55 L70 45" stroke="#9333EA" stroke-width="4" stroke-linecap="round"/>
                                                    </svg>
                                                @elseif($iconName === 'cold-cough')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="50" cy="35" r="12" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                        <path d="M50 47 L50 70 Q50 75, 45 75 L35 75 Q30 75, 30 70 L30 60" fill="#87CEEB" stroke="#333" stroke-width="2"/>
                                                        <circle cx="60" cy="45" r="8" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                        <path d="M70 40 Q75 35, 80 40" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                        <path d="M72 45 Q77 40, 82 45" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                        <path d="M74 50 Q79 45, 84 50" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                    </svg>
                                                @elseif($iconName === 'child-sick')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="50" cy="40" r="20" fill="#FFA500" stroke="#333" stroke-width="2"/>
                                                        <path d="M40 45 Q50 50, 60 45" stroke="#333" stroke-width="2" fill="none" stroke-linecap="round"/>
                                                        <circle cx="45" cy="38" r="2" fill="#333"/>
                                                        <circle cx="55" cy="38" r="2" fill="#333"/>
                                                        <ellipse cx="50" cy="70" rx="18" ry="20" fill="#9333EA" stroke="#333" stroke-width="2"/>
                                                        <path d="M40 65 L60 65 L58 75 L42 75 Z" fill="#FFA500" stroke="#333" stroke-width="1"/>
                                                    </svg>
                                                @elseif($iconName === 'depression-anxiety')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="50" cy="35" r="12" fill="#FFA500" stroke="#333" stroke-width="2"/>
                                                        <path d="M50 47 L50 70 Q50 75, 45 75 L35 75 Q30 75, 30 70 L30 60" fill="#9333EA" stroke="#333" stroke-width="2"/>
                                                        <path d="M50 25 Q60 15, 70 20 Q75 25, 70 30 Q65 35, 60 30 Q55 25, 50 25" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="2"/>
                                                        <circle cx="65" cy="25" r="8" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="2"/>
                                                        <path d="M62 22 L68 28 M68 22 L62 28" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                @elseif($iconName === 'headache')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="50" cy="50" r="25" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                        <path d="M35 45 Q50 40, 65 45" stroke="#333" stroke-width="2" fill="none"/>
                                                        <circle cx="45" cy="48" r="2" fill="#333"/>
                                                        <circle cx="55" cy="48" r="2" fill="#333"/>
                                                        <path d="M30 35 Q25 30, 20 35" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                        <path d="M80 35 Q85 30, 90 35" stroke="{{ $symptomColor }}" stroke-width="3" fill="none" stroke-linecap="round"/>
                                                    </svg>
                                                @elseif($iconName === 'stomach-pain')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M50 30 Q40 35, 35 45 Q30 55, 35 65 Q40 75, 50 80 Q60 75, 65 65 Q70 55, 65 45 Q60 35, 50 30 Z" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="3"/>
                                                        <circle cx="50" cy="55" r="3" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                                @elseif($iconName === 'back-pain')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect x="45" y="20" width="10" height="60" fill="#333" rx="2"/>
                                                        <circle cx="50" cy="30" r="3" fill="{{ $symptomColor }}"/>
                                                        <circle cx="50" cy="45" r="3" fill="{{ $symptomColor }}"/>
                                                        <circle cx="50" cy="60" r="3" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                                @elseif($iconName === 'eye-problems')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <ellipse cx="50" cy="50" rx="30" ry="20" fill="none" stroke="#333" stroke-width="3"/>
                                                        <circle cx="50" cy="50" r="8" fill="#333"/>
                                                    </svg>
                                                @elseif($iconName === 'ear-pain')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M30 30 Q25 40, 30 50 Q35 60, 30 70 Q25 75, 30 80 Q35 85, 40 80 Q45 75, 40 70 Q35 60, 40 50 Q45 40, 40 30 Q35 25, 30 30" fill="#FFE5B4" stroke="#333" stroke-width="2"/>
                                                        <circle cx="35" cy="50" r="5" fill="{{ $symptomColor }}"/>
                                                    </svg>
                                                @elseif($iconName === 'joint-pain')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="30" cy="30" r="5" fill="{{ $symptomColor }}"/>
                                                        <circle cx="50" cy="50" r="8" fill="{{ $symptomColor }}"/>
                                                        <circle cx="70" cy="30" r="5" fill="{{ $symptomColor }}"/>
                                                        <path d="M30 30 L50 50 L70 30" stroke="#333" stroke-width="3"/>
                                                    </svg>
                                                @elseif($iconName === 'chest-pain')
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M50 30 Q40 40, 35 50 Q30 60, 35 70 Q40 80, 50 85 Q60 80, 65 70 Q70 60, 65 50 Q60 40, 50 30 Z" fill="{{ $symptomColor }}" opacity="0.3" stroke="{{ $symptomColor }}" stroke-width="3"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="50" cy="50" r="30" fill="{{ $symptomColor }}" opacity="0.2" stroke="{{ $symptomColor }}" stroke-width="3"/>
                                                        <path d="M50 40 L50 60 M40 50 L60 50" stroke="{{ $symptomColor }}" stroke-width="4" stroke-linecap="round"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            
                                            <!-- Symptom Name -->
                                            <div class="flex-1 w-full">
                                                <h4 class="font-semibold text-gray-900 text-xs mb-1">{{ $symptomName }}</h4>
                                                <p class="text-xs font-medium" style="color: {{ $symptomColor }};">Find Doctors →</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4 text-center">Click any symptom to find doctors who can help</p>
                    </div>
                </div>
                @endif

                <!-- Recent Consultations -->
                <div class="mb-8" x-data="{ consultationsOpen: false }">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Header (Clickable to toggle) -->
                        <button @click="consultationsOpen = !consultationsOpen" 
                                class="w-full flex items-center justify-between p-4 hover:bg-gray-50/50 transition-colors">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                    </div>
                                <div class="text-left flex-1 min-w-0">
                                    <h2 class="text-sm font-semibold text-gray-900">Recent Consultations</h2>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if($recentConsultations->count() > 0)
                                            {{ $recentConsultations->count() }} {{ $recentConsultations->count() === 1 ? 'consultation' : 'consultations' }}
                                        @else
                                            No consultations yet
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($recentConsultations->count() > 0)
                                <a href="{{ route('patient.consultations') }}" 
                                   @click.stop
                                   class="text-xs text-purple-600 hover:text-purple-700 font-medium transition-colors px-2 py-1 rounded hover:bg-purple-50">
                                    View All
                                </a>
                                @endif
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" 
                                     :class="{ 'rotate-180': consultationsOpen }" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </button>
                        
                        <!-- Dropdown Content -->
                        <div x-show="consultationsOpen" 
                             x-collapse
                             x-cloak
                             class="border-t border-gray-100">
                        @if($recentConsultations->count() > 0)
                                <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                                @foreach($recentConsultations as $consultation)
                                        <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                                           class="block p-3.5 hover:bg-gray-50/50 transition-all duration-200 group">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                                    <!-- Status Indicator -->
                                                    <div class="flex-shrink-0">
                                                        <div class="w-2 h-2 rounded-full 
                                                            @if($consultation->status === 'completed') bg-emerald-500
                                                            @elseif($consultation->status === 'pending') bg-amber-500
                                                            @elseif($consultation->status === 'scheduled') bg-blue-500
                                                            @elseif($consultation->status === 'cancelled') bg-red-500
                                                            @else bg-gray-400 @endif">
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Consultation Info -->
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2 mb-0.5">
                                                            <h3 class="text-xs font-semibold text-gray-900 truncate group-hover:text-purple-600 transition-colors">
                                                                {{ $consultation->reference }}
                                                            </h3>
                                                            @if($consultation->payment_status === 'paid')
                                                                <svg class="w-3 h-3 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                            @endif
                                                </div>
                                                        <div class="flex items-center gap-2 text-xs text-gray-600">
                                                            <span class="truncate">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</span>
                                                            <span class="text-gray-400">•</span>
                                                            <span class="text-gray-500 whitespace-nowrap">{{ $consultation->created_at->format('M d, Y') }}</span>
                                                </div>
                                            </div>
                                                </div>
                                                
                                                <!-- Status Badge -->
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-md 
                                                        @if($consultation->status === 'completed') bg-emerald-50 text-emerald-700 border border-emerald-200
                                                        @elseif($consultation->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200
                                                        @elseif($consultation->status === 'scheduled') bg-blue-50 text-blue-700 border border-blue-200
                                                        @elseif($consultation->status === 'cancelled') bg-red-50 text-red-700 border border-red-200
                                                        @else bg-gray-50 text-gray-700 border border-gray-200 @endif uppercase tracking-wide">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                                    <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-purple-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                            </div>
                                        </div>
                                        </a>
                                @endforeach
                            </div>
                        @else
                                <div class="text-center py-10 px-4">
                                    <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                    </div>
                                    <h3 class="text-xs font-semibold text-gray-700 mb-1">No Consultations Yet</h3>
                                    <p class="text-[10px] text-gray-500 mb-3">Start your first consultation to get medical advice</p>
                                    <a href="{{ route('consultation.index') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                        Start Consultation
                                </a>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>

                <!-- Menstrual Cycle Tracker (Female Patients Only) -->
                @if(strtolower($patient->gender) === 'female')
                <div id="menstrual-cycle" class="mb-8">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Menstrual Cycle Tracker</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Track your cycle and predictions</p>
                        </div>
                        <button onclick="document.getElementById('cycleModal').classList.remove('hidden')" 
                                class="px-4 py-2 purple-gradient hover:opacity-90 text-white rounded-lg text-xs font-semibold transition-all duration-200 flex items-center space-x-1.5 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Log Period</span>
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        @if($currentCycle)
                            <div class="p-4">
                                <!-- Current Cycle Status -->
                                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full @if($currentCycle->end_date) bg-emerald-500 @else bg-pink-500 @endif"></div>
                                    <div>
                                            <h3 class="text-xs font-semibold text-gray-900">Current Cycle</h3>
                                            <p class="text-[10px] text-gray-500">{{ $currentCycle->start_date->format('M d, Y') }}</p>
                                    </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-md @if($currentCycle->end_date) bg-emerald-50 text-emerald-700 border border-emerald-200 @else bg-pink-50 text-pink-700 border border-pink-200 @endif uppercase">
                                            @if($currentCycle->end_date) Completed @else Active @endif
                                        </span>
                                        <button onclick="deleteCycle({{ $currentCycle->id }})" 
                                                class="p-2 text-red-500 hover:text-white hover:bg-red-500 rounded-lg transition-all border border-red-200 hover:border-red-500" 
                                                title="Delete cycle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Cycle Stats Grid -->
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    <div class="bg-pink-50/50 rounded-lg p-2.5 border border-pink-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Start Date</p>
                                        <p class="text-xs font-semibold text-pink-700">{{ $currentCycle->start_date->format('M d') }}</p>
                                    </div>
                                    @if($currentCycle->end_date)
                                    <div class="bg-pink-50/50 rounded-lg p-2.5 border border-pink-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">End Date</p>
                                        <p class="text-xs font-semibold text-pink-700">{{ $currentCycle->end_date->format('M d') }}</p>
                                    </div>
                                    @endif
                                    @if($currentCycle->period_length)
                                    <div class="bg-pink-50/50 rounded-lg p-2.5 border border-pink-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Duration</p>
                                        <p class="text-xs font-semibold text-pink-700">{{ $currentCycle->period_length }} days</p>
                                    </div>
                                    @endif
                                    @if($currentCycle->flow_intensity)
                                    <div class="bg-pink-50/50 rounded-lg p-2.5 border border-pink-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Flow</p>
                                        <p class="text-xs font-semibold text-pink-700 capitalize">{{ $currentCycle->flow_intensity }}</p>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Predictions -->
                                <div class="space-y-2">
                                @if($nextPeriodPrediction)
                                    <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-2.5">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-2 h-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-medium text-blue-900">Next Period</p>
                                                <p class="text-xs text-blue-700 truncate">{{ $nextPeriodPrediction->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                    
                                    @if($nextOvulationPrediction)
                                    <div class="bg-purple-50/50 border border-purple-200 rounded-lg p-2.5">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-2 h-2 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-medium text-purple-900">Ovulation</p>
                                                <p class="text-xs text-purple-700 truncate">{{ $nextOvulationPrediction->format('M d, Y') }}</p>
                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if($fertileWindowStart && $fertileWindowEnd)
                                    <div class="bg-amber-50/50 border border-amber-200 rounded-lg p-2.5">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-2 h-2 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] font-medium text-amber-900">Fertile Window</p>
                                                <p class="text-xs text-amber-700 truncate">{{ $fertileWindowStart->format('M d') }} - {{ $fertileWindowEnd->format('M d') }}</p>
                                            </div>
                                        </div>
                            </div>
                        @endif
                                </div>
                            </div>
                            
                            @if($menstrualCycles->count() > 1)
                            <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/50">
                                <h4 class="text-[10px] font-semibold text-gray-700 uppercase tracking-wide mb-2">Recent Cycles</h4>
                                <div class="space-y-1.5">
                                @foreach($menstrualCycles->take(3) as $cycle)
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1 h-1 rounded-full bg-pink-400"></div>
                                                <span class="text-gray-700">{{ $cycle->start_date->format('M d, Y') }}</span>
                                        </div>
                                        <div class="text-right">
                                            @if($cycle->period_length)
                                                    <span class="text-gray-600">{{ $cycle->period_length }}d</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @else
                            <div class="text-center py-8 px-4">
                                <div class="w-6 h-6 bg-pink-50 rounded-full flex items-center justify-center mx-auto mb-2.5">
                                    <svg class="w-3 h-3 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs font-semibold text-gray-700 mb-1">Start Tracking Your Cycle</h3>
                                <p class="text-[10px] text-gray-500 mb-3">Log your period to track your menstrual cycle and get predictions</p>
                                <button onclick="document.getElementById('cycleModal').classList.remove('hidden')" 
                                        class="inline-flex items-center space-x-2 purple-gradient hover:opacity-90 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Log Your Period</span>
                                </button>
                            </div>
                        @endif
                    </div>
                            </div>
                        @endif
                        
                <!-- Sexual Health & Performance Tracker (Male Patients Only) -->
                @if(strtolower($patient->gender) === 'male')
                <div id="sexual-health" class="mb-8">
                    <div class="flex items-center justify-between mb-3">
                                        <div>
                            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Sexual Health & Performance Tracker</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Private tracking for your health</p>
                        </div>
                        <button onclick="document.getElementById('sexualHealthModal').classList.remove('hidden')" 
                                class="px-4 py-2 purple-gradient hover:opacity-90 text-white rounded-lg text-xs font-semibold transition-all duration-200 flex items-center space-x-1.5 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Log Entry</span>
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        @if($latestSexualHealthRecord)
                            <div class="p-4">
                                <!-- Current Record Status -->
                                <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                        <div>
                                            <h3 class="text-xs font-semibold text-gray-900">Latest Record</h3>
                                            <p class="text-[10px] text-gray-500">{{ $latestSexualHealthRecord->record_date->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-md bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                            Active
                                        </span>
                                        <button onclick="deleteSexualHealthRecord({{ $latestSexualHealthRecord->id }})" 
                                                class="p-2 text-red-500 hover:text-white hover:bg-red-500 rounded-lg transition-all border border-red-200 hover:border-red-500" 
                                                title="Delete record">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Health Stats Grid -->
                                <div class="grid grid-cols-2 gap-2 mb-3">
                                    @if($latestSexualHealthRecord->libido_level)
                                    <div class="bg-blue-50/50 rounded-lg p-2.5 border border-blue-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Libido Level</p>
                                        <p class="text-xs font-semibold text-blue-700 capitalize">{{ $latestSexualHealthRecord->libido_level }}</p>
                                    </div>
                                            @endif
                                    
                                    @if($latestSexualHealthRecord->erectile_health_score)
                                    <div class="bg-purple-50/50 rounded-lg p-2.5 border border-purple-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Erectile Health</p>
                                        <p class="text-xs font-semibold text-purple-700">{{ $latestSexualHealthRecord->erectile_health_score }}/10</p>
                                        </div>
                                            @endif
                                    
                                    @if($latestSexualHealthRecord->ejaculation_issues)
                                    <div class="bg-amber-50/50 rounded-lg p-2.5 border border-amber-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Ejaculation</p>
                                        <p class="text-xs font-semibold text-amber-700">Issues noted</p>
                                    </div>
                                            @endif
                                    
                                    @if($latestSexualHealthRecord->last_sti_test_date)
                                    <div class="bg-emerald-50/50 rounded-lg p-2.5 border border-emerald-100">
                                        <p class="text-[10px] text-gray-600 mb-0.5">Last STI Test</p>
                                        <p class="text-xs font-semibold text-emerald-700">{{ $latestSexualHealthRecord->last_sti_test_date->format('M d, Y') }}</p>
                                        </div>
                                    @endif
                                    </div>
                                
                                <!-- STI Test Reminder -->
                                @if($stiTestDue)
                                <div class="bg-red-50/50 border border-red-200 rounded-lg p-2.5 mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-2 h-2 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] font-medium text-red-900">STI Test Due</p>
                                            <p class="text-xs text-red-700">Time for your routine check-up</p>
                            </div>
                        </div>
                                </div>
                                @elseif($nextStiTestDate && $daysUntilStiTest > 0)
                                <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-2.5 mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-2 h-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] font-medium text-blue-900">Next STI Test</p>
                                            <p class="text-xs text-blue-700">{{ $nextStiTestDate->format('M d, Y') }}</p>
                                            <p class="text-[10px] text-blue-600 mt-0.5">In {{ $daysUntilStiTest }} {{ $daysUntilStiTest === 1 ? 'day' : 'days' }}</p>
                                        </div>
                    </div>
                </div>
                @endif
                            </div>

                            @if($sexualHealthRecords->count() > 1)
                            <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/50">
                                <h4 class="text-[10px] font-semibold text-gray-700 uppercase tracking-wide mb-2">Recent Records</h4>
                                <div class="space-y-1.5">
                                    @foreach($sexualHealthRecords->take(3) as $record)
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <div class="w-1 h-1 rounded-full bg-blue-400"></div>
                                                <span class="text-gray-700">{{ $record->record_date->format('M d, Y') }}</span>
                                            </div>
                                            <div class="text-right">
                                                @if($record->libido_level)
                                                    <span class="text-gray-600 capitalize text-[10px]">{{ $record->libido_level }}</span>
                                    @endif
                                </div>
                                </div>
                                    @endforeach
                            </div>
                        </div>
                            @endif
                        @else
                            <div class="text-center py-8 px-4">
                                <div class="w-6 h-6 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-2.5">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs font-semibold text-gray-700 mb-1">Start Tracking Your Health</h3>
                                <p class="text-[10px] text-gray-500 mb-3">Log your health metrics privately and securely</p>
                                <button onclick="document.getElementById('sexualHealthModal').classList.remove('hidden')" 
                                        class="inline-flex items-center space-x-2 purple-gradient hover:opacity-90 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Log Entry</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                        <!-- Dependents -->
                        @if($dependents->count() > 0)
                <div class="mb-8">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-gray-800">My Dependents</h3>
                                <a href="{{ route('patient.dependents') }}" class="text-sm text-purple-600 hover:text-purple-800 font-semibold">View All →</a>
                            </div>
                            <div class="space-y-3">
                                @foreach($dependents->take(3) as $dependent)
                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-blue-600 font-bold">{{ substr($dependent->name, 0, 1) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $dependent->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $dependent->age }} years old</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Menstrual Cycle Logging Modal -->
    @if(strtolower($patient->gender) === 'female')
    <div id="cycleModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
            <!-- Header with Gradient -->
            <div class="purple-gradient p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Log Menstrual Period</h3>
                            <p class="text-xs text-white/90 mt-0.5">Track your cycle for better prediction</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('cycleModal').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors p-1 hover:bg-white/10 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                </div>
                
            <!-- Form Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="cycleForm" onsubmit="submitCycle(event)">
                    @csrf
                    <div class="space-y-4">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-xs font-semibold text-gray-700 mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 transition-all" required>
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-xs font-semibold text-gray-700 mb-2">
                                End Date <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                            </label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 transition-all">
                            <p class="text-[10px] text-gray-500 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Leave blank if period is still ongoing
                            </p>
                        </div>
                        
                        <!-- Flow Intensity -->
                        <div>
                            <label for="flow_intensity" class="block text-xs font-semibold text-gray-700 mb-2">
                                Flow Intensity
                            </label>
                            <select name="flow_intensity" id="flow_intensity" 
                                    class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 transition-all bg-white">
                                <option value="">Select intensity</option>
                                <option value="light">Light</option>
                                <option value="moderate">Moderate</option>
                                <option value="heavy">Heavy</option>
                            </select>
                        </div>
                        
                        <!-- Symptoms -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-3">Symptoms <span class="text-gray-400 text-[10px] font-normal">(Optional)</span></label>
                            <div class="grid grid-cols-2 gap-2.5">
                                @foreach(['Cramps', 'Bloating', 'Headache', 'Mood changes', 'Fatigue', 'Back pain'] as $symptom)
                                    <label class="flex items-center gap-2 cursor-pointer p-2.5 rounded-lg border-2 border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all group">
                                        <input type="checkbox" name="symptoms[]" value="{{ strtolower(str_replace(' ', '_', $symptom)) }}" 
                                               class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                        <span class="text-xs font-medium text-gray-700 group-hover:text-purple-700">{{ $symptom }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-xs font-semibold text-gray-700 mb-2">
                                Notes <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                            </label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 resize-none transition-all"
                                      placeholder="Add any additional notes about your cycle..."></textarea>
                        </div>
                        
                        <!-- Spouse Number -->
                        <div>
                            <label for="spouse_number" class="block text-xs font-semibold text-gray-700 mb-2">
                                Spouse Phone Number <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                            </label>
                            <input type="tel" name="spouse_number" id="spouse_number" 
                                   value="{{ old('spouse_number', $latestSpouseNumber ?? '') }}"
                                   class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 transition-all"
                                   placeholder="e.g., +2348012345678 or 08012345678">
                            <p class="text-[10px] text-gray-500 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Add your spouse's number to receive fertility window notifications
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="button" onclick="document.getElementById('cycleModal').classList.add('hidden')" 
                                class="px-5 py-2.5 border-2 border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 purple-gradient hover:opacity-90 text-white rounded-lg text-xs font-semibold transition-all shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Save Period</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Sexual Health & Performance Logging Modal (Male Patients Only) -->
    @if(strtolower($patient->gender) === 'male')
    <div id="sexualHealthModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
            <!-- Header with Gradient -->
            <div class="purple-gradient p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Log Health Entry</h3>
                            <p class="text-xs text-white/90 mt-0.5">Private & secure health tracking</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('sexualHealthModal').classList.add('hidden')" class="text-white/80 hover:text-white transition-colors p-1 hover:bg-white/10 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Form Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="sexualHealthForm" onsubmit="submitSexualHealth(event)">
                    @csrf
                    <div class="space-y-4">
                        <!-- Record Date -->
                        <div>
                            <label for="record_date" class="block text-xs font-semibold text-gray-700 mb-2">
                                Record Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="record_date" id="record_date" 
                                   value="{{ old('record_date', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 transition-all" required>
                        </div>
                        
                        <!-- Libido Level -->
                        <div>
                            <label for="libido_level" class="block text-xs font-semibold text-gray-700 mb-2">
                                Libido Level
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex flex-col items-center p-3 rounded-lg border-2 border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all cursor-pointer group">
                                    <input type="radio" name="libido_level" value="low" class="text-purple-600 focus:ring-purple-500 mb-1">
                                    <span class="text-xs font-medium text-gray-700 group-hover:text-purple-700">Low</span>
                                </label>
                                <label class="flex flex-col items-center p-3 rounded-lg border-2 border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all cursor-pointer group">
                                    <input type="radio" name="libido_level" value="normal" class="text-purple-600 focus:ring-purple-500 mb-1">
                                    <span class="text-xs font-medium text-gray-700 group-hover:text-purple-700">Normal</span>
                                </label>
                                <label class="flex flex-col items-center p-3 rounded-lg border-2 border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all cursor-pointer group">
                                    <input type="radio" name="libido_level" value="high" class="text-purple-600 focus:ring-purple-500 mb-1">
                                    <span class="text-xs font-medium text-gray-700 group-hover:text-purple-700">High</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Erectile Health Score (Discreet) -->
                        <div>
                            <label for="erectile_health_score" class="block text-xs font-semibold text-gray-700 mb-2">
                                Erectile Health <span class="text-gray-400 text-[10px] font-normal">(Optional, 1-10 scale)</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="range" name="erectile_health_score" id="erectile_health_score" 
                                       min="1" max="10" value="5"
                                       class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600"
                                       oninput="document.getElementById('scoreDisplay').textContent = this.value">
                                <span id="scoreDisplay" class="text-sm font-semibold text-purple-600 w-8 text-center">5</span>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                This information is completely private
                            </p>
                        </div>
                        
                        <!-- Ejaculation Issues -->
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all">
                                <input type="checkbox" name="ejaculation_issues" value="1" 
                                       id="ejaculation_issues"
                                       class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-2 focus:ring-purple-500 cursor-pointer">
                                <span class="text-xs font-medium text-gray-700">Ejaculation Issues</span>
                            </label>
                            <textarea name="ejaculation_notes" id="ejaculation_notes" rows="2" 
                                      placeholder="Optional notes about ejaculation issues..."
                                      class="w-full text-xs rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2 px-3 resize-none transition-all mt-2"
                                      style="display: none;"></textarea>
                        </div>
                        
                        <!-- STI Test Date -->
                        <div>
                            <label for="last_sti_test_date" class="block text-xs font-semibold text-gray-700 mb-2">
                                Last STI Test Date <span class="text-gray-400 text-[10px] font-normal">(Optional)</span>
                            </label>
                            <input type="date" name="last_sti_test_date" id="last_sti_test_date" 
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 transition-all">
                            <p class="text-[10px] text-gray-500 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Recommended every 6 months for sexual health
                            </p>
                        </div>
                        
                        <!-- Additional Notes -->
                        <div>
                            <label for="notes" class="block text-xs font-semibold text-gray-700 mb-2">
                                Additional Notes <span class="text-gray-400 text-[10px] font-normal">(Optional, Private)</span>
                            </label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="w-full text-sm rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 py-2.5 px-4 resize-none transition-all"
                                      placeholder="Add any private notes about your health..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="button" onclick="document.getElementById('sexualHealthModal').classList.add('hidden')" 
                                class="px-5 py-2.5 border-2 border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2.5 purple-gradient hover:opacity-90 text-white rounded-lg text-xs font-semibold transition-all shadow-md hover:shadow-lg transform hover:scale-105 active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Save Entry</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function submitCycle(e) {
            e.preventDefault();
            const form = document.getElementById('cycleForm');
            const formData = new FormData(form);
            
            fetch('{{ route("patient.menstrual-cycle.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cycleModal').classList.add('hidden');
                    location.reload();
                } else {
                    CustomAlert.error(data.error || 'Failed to save cycle');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                CustomAlert.error('An error occurred. Please try again.');
            });
        }

        function deleteCycle(cycleId) {
            if (!confirm('Are you sure you want to delete this menstrual cycle? This action cannot be undone.')) {
                return;
            }

            const url = '{{ route("patient.menstrual-cycle.delete", ":id") }}'.replace(':id', cycleId);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    CustomAlert.error(data.error || 'Failed to delete cycle');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                CustomAlert.error('An error occurred. Please try again.');
            });
        }

        function submitSexualHealth(e) {
            e.preventDefault();
            const form = document.getElementById('sexualHealthForm');
            const formData = new FormData(form);
            
            fetch('{{ route("patient.sexual-health.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('sexualHealthModal').classList.add('hidden');
                    location.reload();
                } else {
                    CustomAlert.error(data.error || 'Failed to save record');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                CustomAlert.error('An error occurred. Please try again.');
            });
        }

        function deleteSexualHealthRecord(recordId) {
            if (!confirm('Are you sure you want to delete this health record? This action cannot be undone.')) {
                return;
            }

            const url = '{{ route("patient.sexual-health.delete", ":id") }}'.replace(':id', recordId);
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    CustomAlert.error(data.error || 'Failed to delete record');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                CustomAlert.error('An error occurred. Please try again.');
            });
        }

        // Health Tips Rotation
        document.addEventListener('DOMContentLoaded', function() {
            const healthTips = [
                "Staying hydrated can reduce your headaches.",
                "A 10-minute walk can boost your mood instantly.",
                "Deep breathing helps lower stress in minutes.",
                "Getting 7-8 hours of sleep improves your immune system.",
                "Eating slowly helps with better digestion.",
                "Regular handwashing prevents most infections.",
                "Stretching for 5 minutes reduces muscle tension.",
                "Drinking water before meals aids weight management.",
                "Taking breaks from screens reduces eye strain.",
                "A short walk after meals improves blood sugar.",
                "Laughing releases endorphins that reduce pain.",
                "Sunlight exposure helps regulate your sleep cycle.",
                "Chewing gum can help reduce stress and improve focus.",
                "Standing up every hour improves circulation.",
                "Green tea contains antioxidants that support heart health.",
                "Regular exercise can improve sexual health and performance.",
                "Open communication with your partner enhances intimacy.",
                "Stress management positively impacts sexual wellness.",
                "Adequate sleep is essential for healthy libido.",
                "Staying active improves blood flow and sexual function.",
                "Regular check-ups help maintain sexual health.",
                "Healthy eating supports overall sexual wellness.",
                "Reducing alcohol intake can improve sexual performance.",
                "Quitting smoking benefits sexual health significantly."
            ];
            
            const healthTipElement = document.getElementById('healthTip');
            if (healthTipElement) {
                let currentIndex = 0;
                
                // Add smooth transition styles
                healthTipElement.style.transition = 'opacity 0.5s ease-in-out, transform 0.5s ease-in-out';
                healthTipElement.style.willChange = 'opacity, transform';
                
                // Rotate tips every 20 seconds
                setInterval(function() {
                    currentIndex = (currentIndex + 1) % healthTips.length;
                    
                    // Fade out and slide up
                    healthTipElement.style.opacity = '0';
                    healthTipElement.style.transform = 'translateY(-10px)';
                    
                    setTimeout(function() {
                        // Update content
                        healthTipElement.textContent = healthTips[currentIndex];
                        
                        // Reset position for slide in
                        healthTipElement.style.transform = 'translateY(10px)';
                        
                        // Force reflow to ensure transform reset
                        healthTipElement.offsetHeight;
                        
                        // Fade in and slide to position
                        setTimeout(function() {
                            healthTipElement.style.opacity = '1';
                            healthTipElement.style.transform = 'translateY(0)';
                        }, 50);
                    }, 500);
                }, 20000);
            }
        });

        // Show/hide ejaculation notes textarea
        document.addEventListener('DOMContentLoaded', function() {
            const ejaculationCheckbox = document.getElementById('ejaculation_issues');
            const ejaculationNotes = document.getElementById('ejaculation_notes');
            
            if (ejaculationCheckbox && ejaculationNotes) {
                ejaculationCheckbox.addEventListener('change', function() {
                    ejaculationNotes.style.display = this.checked ? 'block' : 'none';
                });
            }
        });
    </script>
    @include('components.custom-alert-modal')
</body>
</html>
