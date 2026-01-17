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
        
        /* Custom Scrollbar for hidden horizontal scroll but functional */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-900" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-100 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-50">
                <a href="{{ route('patient.dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('img/logo.png') }}" onerror="this.src='{{ asset('img/favicon.png') }}'" alt="DoctorOnTap" class="h-8 w-auto">
                    <span class="font-bold text-lg text-purple-700">DoctorOnTap<span class="text-xs align-top">TM</span></span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('patient.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 bg-purple-50 text-purple-700 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('patient.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Consultations</span>
                </a>

                <a href="{{ route('patient.medical-records') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Medical Records</span>
                </a>

                <a href="{{ route('patient.payments') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Payments</span>
                </a>

                <a href="{{ route('patient.doctors') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Find Doctors</span>
                </a>

                @if(strtolower($patient->gender) === 'female')
                <a href="#menstrual-cycle" onclick="document.getElementById('menstrual-cycle').scrollIntoView({behavior: 'smooth'}); return false;" class="flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-gray-900 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Cycle Tracker</span>
                </a>
                @endif
            </nav>

            <!-- User Profile (Bottom Sidebar) -->
            <div class="p-4 m-4 bg-gray-50 rounded-2xl flex items-center space-x-3">
                <div class="bg-orange-100 p-2 rounded-xl">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-gray-900 truncate">{{ $patient->name }}</h4>
                    <p class="text-xs text-gray-500 truncate">Patient ID: {{ $patient->id }}</p>
                </div>
                <form method="POST" action="{{ route('patient.logout') }}">
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-700" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
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

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center lg:hidden">
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none p-2 -ml-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                    
                    <h1 class="text-xl font-bold text-gray-900 hidden lg:block">Dashboard</h1>

                    <div class="flex items-center space-x-4 flex-1 lg:flex-none justify-end lg:justify-start lg:w-1/2">
                        <!-- Search Bar -->
                        <div class="relative w-full max-w-md hidden md:block">
                            <input type="text" placeholder="Search records, doctors..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                            </button>
                        </div>
                        
                        <!-- Date -->
                       <div class="hidden lg:block text-sm text-gray-500 font-medium border-l pl-4 border-gray-200">
                            {{ now()->format('l, M j, Y') }}
                       </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 p-6 lg:p-8 max-w-7xl mx-auto w-full">
                <!-- Greeting Section -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            Good morning, {{ explode(' ', $patient->name)[0] }}! <span class="animate-bounce">👋</span>
                        </h2>
                        <p class="text-gray-500 mt-1">Here is a summary of your health today.</p>
                    </div>
                    <div>
                        <a href="{{ route('patient.medical-records') }}" class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export History
                        </a>
                    </div>
                </div>

                <!-- Action Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Talk to a Doctor -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-pink-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center text-pink-500 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Talk to a Doctor</h3>
                            <p class="text-sm text-gray-500 mb-6">Connect with a certified GP or specialist in minutes.</p>
                            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-pink-400 hover:bg-pink-500 text-white rounded-lg font-medium transition-colors shadow-sm shadow-pink-200 w-full justify-center">
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
                            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-teal-400 hover:bg-teal-500 text-white rounded-lg font-medium transition-colors shadow-sm shadow-teal-200 w-full justify-center">
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
                            <div class="w-12 h-12 bg-sky-100 rounded-xl flex items-center justify-center text-sky-500 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Medical Coordination</h3>
                            <p class="text-sm text-gray-500 mb-6">Let us manage your appointments and pharmacy needs.</p>
                            <a href="{{ route('patient.medical-records') }}" class="inline-flex items-center px-6 py-2.5 bg-sky-400 hover:bg-sky-500 text-white rounded-lg font-medium transition-colors shadow-sm shadow-sky-200 w-full justify-center">
                                Manage Services
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Health Snapshot -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Snapshot Cards (Span 3 on Desktop) -->
                    <div class="lg:col-span-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Health Snapshot</h3>
                            <a href="{{ route('patient.medical-records') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">View All Trends</a>
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
                                    <span class="text-sm text-gray-500">Blood Pressure</span>
                                </div>
                                <div class="flex items-end space-x-2">
                                    @if(isset($latestVitals) && $latestVitals->blood_pressure)
                                        <span class="text-2xl font-bold text-gray-900">{{ $latestVitals->blood_pressure }}</span>
                                        <span class="text-xs text-green-500 font-medium bg-green-50 px-2 py-0.5 rounded-full mb-1">Normal</span>
                                    @else
                                        <span class="text-2xl font-bold text-gray-900">--/--</span>
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
                                    <span class="text-sm text-gray-500">Temperature</span>
                                </div>
                                <div class="flex items-end space-x-2">
                                    @if(isset($latestVitals) && $latestVitals->temperature)
                                        <span class="text-2xl font-bold text-gray-900">{{ $latestVitals->temperature }}°C</span>
                                        <span class="text-xs text-green-500 font-medium bg-green-50 px-2 py-0.5 rounded-full mb-1">Steady</span>
                                    @else
                                        <span class="text-2xl font-bold text-gray-900">--</span>
                                        <span class="text-xs text-gray-400 font-medium mb-1">No data</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Weight -->
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-500">Weight</span>
                                </div>
                                <div class="flex items-end space-x-2">
                                    @if(isset($latestVitals) && $latestVitals->weight)
                                        <span class="text-2xl font-bold text-gray-900">{{ $latestVitals->weight }} kg</span>
                                        <span class="text-xs text-red-500 font-medium bg-red-50 px-2 py-0.5 rounded-full mb-1">~</span>
                                    @else
                                        <span class="text-2xl font-bold text-gray-900">--</span>
                                        <span class="text-xs text-gray-400 font-medium mb-1">No data</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Appointments -->
                        <div class="mt-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">Upcoming Appointments</h3>
                                <a href="{{ route('patient.doctors') }}" class="text-sm font-bold text-purple-600 hover:text-purple-700 uppercase tracking-wide">Book New</a>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-50">
                                @forelse($upcomingConsultations as $consultation)
                                <div class="p-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex-shrink-0 text-center bg-gray-50 rounded-xl p-2 w-16">
                                        <span class="block text-xs font-bold text-gray-400 uppercase">{{ $consultation->created_at->format('M') }}</span>
                                        <span class="block text-xl font-bold text-purple-600">{{ $consultation->created_at->format('d') }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-base font-bold text-gray-900 truncate">{{ $consultation->doctor ? 'Dr. ' . $consultation->doctor->name : 'Pending Assignment' }}</h4>
                                        <div class="flex items-center text-sm text-gray-500 mt-1">
                                            <span>{{ $consultation->doctor->specialization ?? 'General Practice' }}</span>
                                            <span class="mx-2">•</span>
                                            <span>{{ ucfirst($consultation->type ?? 'Video Call') }}</span>
                                            <span class="mx-2">•</span>
                                            <span>{{ $consultation->created_at->format('h:i A') }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="p-2 text-gray-400 hover:text-purple-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                                @empty
                                <div class="p-8 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">No upcoming appointments</p>
                                    <a href="{{ route('patient.doctors') }}" class="text-purple-600 font-medium text-sm mt-2 hover:underline">Book a consultation</a>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Menstrual Cycle (Conditional) -->
                        @if(strtolower($patient->gender) === 'female')
                        <div class="mt-8" id="menstrual-cycle">
                             <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">Cycle Tracker</h3>
                            </div>
                             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 bg-gradient-to-br from-pink-50 to-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-pink-600">Next Period Prediction</p>
                                        @if($nextPeriodPrediction)
                                            <h4 class="text-2xl font-bold text-gray-900 mt-1">{{ $nextPeriodPrediction->format('M d') }}</h4>
                                            <p class="text-sm text-gray-500 mt-1">
                                                @if($nextPeriodPrediction->isToday())
                                                    Expected today
                                                @else
                                                    in {{ $nextPeriodPrediction->diffInDays(now()) }} days
                                                @endif
                                            </p>
                                        @else
                                            <h4 class="text-xl font-bold text-gray-900 mt-1">Not enough data</h4>
                                            <p class="text-sm text-gray-500 mt-1">Log your period to get predictions</p>
                                        @endif
                                    </div>
                                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                             </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Daily Health Tip -->
                        <div class="bg-purple-50 rounded-2xl p-6 border border-purple-100">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                <h3 class="text-sm font-bold text-gray-900">Daily Health Tip</h3>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed mb-4">
                                "Stress management positively impacts sexual wellness. Try spending 10 minutes today on focused breathing exercises."
                            </p>
                            <a href="#" class="text-xs font-bold text-purple-600 hover:text-purple-700">Read Full Article</a>
                        </div>

                        <!-- Quick Contacts -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 mb-4">Quick Contacts</h3>
                            <div class="space-y-4">
                                @forelse($quickContacts as $doctor)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $doctor->photo_url ?? asset('img/default-avatar.png') }}" class="w-10 h-10 rounded-full object-cover border border-gray-100" alt="{{ $doctor->name }}">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 line-clamp-1">{{ $doctor->name }}</h4>
                                            <p class="text-xs text-gray-500 line-clamp-1">{{ $doctor->specialization }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('patient.doctors') }}" class="text-gray-400 hover:text-purple-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </a>
                                </div>
                                @empty
                                <p class="text-xs text-gray-400">No doctors available yet.</p>
                                @endforelse
                                
                                <a href="{{ route('patient.doctors') }}" class="block w-full py-2 text-center text-sm font-bold text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors mt-4">
                                    Browse All Specialists
                                </a>
                            </div>
                        </div>

                        <!-- SOS/Emergency -->
                        <div class="bg-gray-900 rounded-2xl p-6 text-white text-center">
                            <h3 class="text-base font-bold mb-2">Need Emergency Help?</h3>
                            <p class="text-xs text-gray-400 mb-4 leading-relaxed">Our 24/7 hotline is available for immediate medical assistance.</p>
                            <button class="w-full py-3 bg-white text-gray-900 rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-gray-100 transition-colors">
                                <span class="text-red-500 text-lg">*</span> Call SOS Line
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
