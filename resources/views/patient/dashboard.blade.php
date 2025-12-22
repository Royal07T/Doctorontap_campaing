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
            100% { transform: translateX(calc(-250px * 12)); }
        }
        
        .animate-scroll {
            animation: scroll calc(var(--specialization-count, 18) * 2s) linear infinite;
        }
        
        .animate-scroll-symptoms {
            animation: scroll-symptoms 35s linear infinite;
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

                <a href="{{ route('patient.profile') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <!-- New Consultation Dropdown -->
                <div x-data="{ consultationMenuOpen: false }" class="relative" style="z-index: 100;">
                    <button type="button" @click="consultationMenuOpen = !consultationMenuOpen" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.consultation.new') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                        <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Consultation</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': consultationMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="consultationMenuOpen" 
                         x-cloak
                         @click.away="consultationMenuOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute left-0 right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden"
                         style="z-index: 9999;">
                        <a href="/patient/consultations/new/create?type=pay_later" 
                           class="block px-4 py-4 hover:bg-purple-50 transition-colors border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h4 class="font-semibold text-gray-800">Consult Now, Pay Later</h4>
                                    </div>
                                    <p class="text-xs text-gray-600 mb-2">Consult first, pay after service</p>
                                    <p class="text-sm font-bold text-purple-600">₦{{ number_format(\App\Models\Setting::get('consultation_fee_pay_later', \App\Models\Setting::get('pay_later_consultation_fee', 5000)), 2) }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                        <a href="/patient/consultations/new/create?type=pay_now" 
                           class="block px-4 py-4 hover:bg-purple-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h4 class="font-semibold text-gray-800">Pay Before Consultation</h4>
                                    </div>
                                    <p class="text-xs text-gray-600 mb-2">Pay upfront to secure consultation</p>
                                    <p class="text-sm font-bold text-purple-600">₦{{ number_format(\App\Models\Setting::get('consultation_fee_pay_now', \App\Models\Setting::get('pay_now_consultation_fee', 4500)), 2) }}</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

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
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
                    <!-- Total Consultations -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Consultations</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-emerald-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Completed</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Successful</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-amber-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Pending</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_consultations'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Awaiting</p>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Paid -->
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total Paid</p>
                                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['total_paid'], 0) }}</p>
                                <p class="text-xs text-gray-500 mt-1">All Time</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Specializations Carousel -->
                @if($specializations->count() > 0)
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Find Doctors by Specialization</h3>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6 overflow-hidden">
                        <div class="relative">
                            <div class="flex space-x-4 animate-scroll" style="--specialization-count: {{ $specializations->count() }};">
                                @foreach($specializations as $specialization)
                                    <a href="{{ route('patient.doctors-by-specialization', urlencode($specialization)) }}" 
                                       class="flex-shrink-0 w-[240px] bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-lg p-5 transition-all duration-300 hover:shadow-lg group cursor-pointer">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 truncate">{{ $specialization }}</p>
                                                <p class="text-xs text-purple-600 font-medium">View Doctors →</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                                <!-- Duplicate items for seamless loop -->
                                @foreach($specializations as $specialization)
                                    <a href="{{ route('patient.doctors-by-specialization', urlencode($specialization)) }}" 
                                       class="flex-shrink-0 w-[240px] bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-lg p-5 transition-all duration-300 hover:shadow-lg group cursor-pointer">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-900 truncate">{{ $specialization }}</p>
                                                <p class="text-xs text-purple-600 font-medium">View Doctors →</p>
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

                <!-- Find Doctor by Symptoms Carousel -->
                @if(isset($symptoms) && count($symptoms) > 0)
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Find a Doctor by Symptoms</h3>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 overflow-hidden">
                        <div class="relative">
                            <div class="flex space-x-4 animate-scroll-symptoms">
                                @foreach($symptoms as $symptom)
                                    @php
                                        $symptomSlug = strtolower(str_replace(' ', '-', $symptom['name']));
                                        $symptomName = $symptom['name'];
                                        
                                        // Map symptom names to CSS class names
                                        $classMap = [
                                            'Menstruation Flow' => 'menstruation',
                                            'Rashes' => 'rashes',
                                            'Skin Issues' => 'skin-issues',
                                            'Headache' => 'headache',
                                            'Cough' => 'cough',
                                            'Fever' => 'fever',
                                            'Stomach Pain' => 'stomach-pain',
                                            'Back Pain' => 'back-pain',
                                            'Eye Problems' => 'eye-problems',
                                            'Ear Pain' => 'ear-pain',
                                            'Joint Pain' => 'joint-pain',
                                            'Chest Pain' => 'chest-pain',
                                        ];
                                        $iconClass = 'symptom-icon-' . ($classMap[$symptomName] ?? 'default');
                                        
                                        // Map symptom names to colors for background
                                        $colorMap = [
                                            'Menstruation Flow' => '#D72638',
                                            'Rashes' => '#F4A261',
                                            'Skin Issues' => '#F4A261',
                                            'Headache' => '#6D597A',
                                            'Cough' => '#457B9D',
                                            'Fever' => '#E63946',
                                            'Stomach Pain' => '#2A9D8F',
                                            'Back Pain' => '#264653',
                                            'Eye Problems' => '#1D3557',
                                            'Ear Pain' => '#8D99AE',
                                            'Joint Pain' => '#588157',
                                            'Chest Pain' => '#C1121F',
                                        ];
                                        $symptomColor = $colorMap[$symptomName] ?? '#6B7280';
                                    @endphp
                                    <a href="{{ route('patient.doctors-by-symptom', $symptomSlug) }}" 
                                       class="flex-shrink-0 w-[240px] bg-white hover:bg-gray-50 rounded-lg p-5 transition-all duration-300 hover:shadow-lg group cursor-pointer border border-gray-200">
                                        <div class="flex flex-col items-center text-center space-y-3">
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform" style="background-color: {{ $symptomColor }}20;">
                                                @if($symptomName === 'Menstruation Flow')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C12 2 6 10 6 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-4-6-11.31-6-11.31z"/>
                                                    </svg>
                                                @elseif($symptomName === 'Rashes' || $symptomName === 'Skin Issues')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"/>
                                                        <circle cx="8" cy="8" r="1.5" fill="currentColor"/>
                                                        <circle cx="16" cy="8" r="1.5" fill="currentColor"/>
                                                        <circle cx="8" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="16" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="8" cy="16" r="1.5" fill="currentColor"/>
                                                        <circle cx="16" cy="16" r="1.5" fill="currentColor"/>
                                                        <circle cx="12" cy="10" r="1.5" fill="currentColor"/>
                                                        <circle cx="12" cy="14" r="1.5" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Headache')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h8M8 12h8M8 16h4"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l2 2M3 10l2-2M21 6l-2 2M21 10l-2-2"/>
                                                    </svg>
                                                @elseif($symptomName === 'Cough')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8h-1.5M21 12h-1.5M21 16h-1.5"/>
                                                    </svg>
                                                @elseif($symptomName === 'Fever')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2"/>
                                                    </svg>
                                                @elseif($symptomName === 'Stomach Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h8"/>
                                                        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Back Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M8 6l4-4 4 4M8 18l4 4 4-4"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8h12M6 12h12M6 16h12"/>
                                                        <circle cx="12" cy="8" r="1" fill="currentColor"/>
                                                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                                                        <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Eye Problems')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                @elseif($symptomName === 'Ear Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5c-1.5 0-3 1-3.5 2.5C4 8.5 4.5 10 5.5 11v7c0 1.1.9 2 2 2s2-.9 2-2v-7c1-.5 1.5-2 1.5-3.5C11 6 9.5 5 8 5z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 5c-1.5 0-3 1-3.5 2.5C12 8.5 12.5 10 13.5 11v7c0 1.1.9 2 2 2s2-.9 2-2v-7c1-.5 1.5-2 1.5-3.5C19 6 17.5 5 16 5z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"/>
                                                    </svg>
                                                @elseif($symptomName === 'Joint Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 12h8M8 17h8"/>
                                                        <circle cx="6" cy="7" r="1.5" fill="currentColor"/>
                                                        <circle cx="6" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="6" cy="17" r="1.5" fill="currentColor"/>
                                                        <circle cx="18" cy="7" r="1.5" fill="currentColor"/>
                                                        <circle cx="18" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="18" cy="17" r="1.5" fill="currentColor"/>
                                                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Chest Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1 w-full">
                                                <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $symptomName }}</h4>
                                                <p class="text-xs font-medium {{ $iconClass }}">Find Doctors →</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                                <!-- Duplicate items for seamless loop -->
                                @foreach($symptoms as $symptom)
                                    @php
                                        $symptomSlug = strtolower(str_replace(' ', '-', $symptom['name']));
                                        $symptomName = $symptom['name'];
                                        
                                        // Map symptom names to CSS class names
                                        $classMap = [
                                            'Menstruation Flow' => 'menstruation',
                                            'Rashes' => 'rashes',
                                            'Skin Issues' => 'skin-issues',
                                            'Headache' => 'headache',
                                            'Cough' => 'cough',
                                            'Fever' => 'fever',
                                            'Stomach Pain' => 'stomach-pain',
                                            'Back Pain' => 'back-pain',
                                            'Eye Problems' => 'eye-problems',
                                            'Ear Pain' => 'ear-pain',
                                            'Joint Pain' => 'joint-pain',
                                            'Chest Pain' => 'chest-pain',
                                        ];
                                        $iconClass = 'symptom-icon-' . ($classMap[$symptomName] ?? 'default');
                                        
                                        // Map symptom names to colors for background
                                        $colorMap = [
                                            'Menstruation Flow' => '#D72638',
                                            'Rashes' => '#F4A261',
                                            'Skin Issues' => '#F4A261',
                                            'Headache' => '#6D597A',
                                            'Cough' => '#457B9D',
                                            'Fever' => '#E63946',
                                            'Stomach Pain' => '#2A9D8F',
                                            'Back Pain' => '#264653',
                                            'Eye Problems' => '#1D3557',
                                            'Ear Pain' => '#8D99AE',
                                            'Joint Pain' => '#588157',
                                            'Chest Pain' => '#C1121F',
                                        ];
                                        $symptomColor = $colorMap[$symptomName] ?? '#6B7280';
                                    @endphp
                                    <a href="{{ route('patient.doctors-by-symptom', $symptomSlug) }}" 
                                       class="flex-shrink-0 w-[240px] bg-white hover:bg-gray-50 rounded-lg p-5 transition-all duration-300 hover:shadow-lg group cursor-pointer border border-gray-200">
                                        <div class="flex flex-col items-center text-center space-y-3">
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform" style="background-color: {{ $symptomColor }}20;">
                                                @if($symptomName === 'Menstruation Flow')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C12 2 6 10 6 14c0 3.31 2.69 6 6 6s6-2.69 6-6c0-4-6-11.31-6-11.31z"/>
                                                    </svg>
                                                @elseif($symptomName === 'Rashes' || $symptomName === 'Skin Issues')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"/>
                                                        <circle cx="8" cy="8" r="1.5" fill="currentColor"/>
                                                        <circle cx="16" cy="8" r="1.5" fill="currentColor"/>
                                                        <circle cx="8" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="16" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="8" cy="16" r="1.5" fill="currentColor"/>
                                                        <circle cx="16" cy="16" r="1.5" fill="currentColor"/>
                                                        <circle cx="12" cy="10" r="1.5" fill="currentColor"/>
                                                        <circle cx="12" cy="14" r="1.5" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Headache')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h8M8 12h8M8 16h4"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l2 2M3 10l2-2M21 6l-2 2M21 10l-2-2"/>
                                                    </svg>
                                                @elseif($symptomName === 'Cough')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8h-1.5M21 12h-1.5M21 16h-1.5"/>
                                                    </svg>
                                                @elseif($symptomName === 'Fever')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2m0 16v2"/>
                                                    </svg>
                                                @elseif($symptomName === 'Stomach Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h8"/>
                                                        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Back Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M8 6l4-4 4 4M8 18l4 4 4-4"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8h12M6 12h12M6 16h12"/>
                                                        <circle cx="12" cy="8" r="1" fill="currentColor"/>
                                                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                                                        <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Eye Problems')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                @elseif($symptomName === 'Ear Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5c-1.5 0-3 1-3.5 2.5C4 8.5 4.5 10 5.5 11v7c0 1.1.9 2 2 2s2-.9 2-2v-7c1-.5 1.5-2 1.5-3.5C11 6 9.5 5 8 5z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 5c-1.5 0-3 1-3.5 2.5C12 8.5 12.5 10 13.5 11v7c0 1.1.9 2 2 2s2-.9 2-2v-7c1-.5 1.5-2 1.5-3.5C19 6 17.5 5 16 5z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7c-.6 0-1 .4-1 1s.4 1 1 1 1-.4 1-1-.4-1-1-1z"/>
                                                    </svg>
                                                @elseif($symptomName === 'Joint Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 12h8M8 17h8"/>
                                                        <circle cx="6" cy="7" r="1.5" fill="currentColor"/>
                                                        <circle cx="6" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="6" cy="17" r="1.5" fill="currentColor"/>
                                                        <circle cx="18" cy="7" r="1.5" fill="currentColor"/>
                                                        <circle cx="18" cy="12" r="1.5" fill="currentColor"/>
                                                        <circle cx="18" cy="17" r="1.5" fill="currentColor"/>
                                                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                                    </svg>
                                                @elseif($symptomName === 'Chest Pain')
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1 w-full">
                                                <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $symptomName }}</h4>
                                                <p class="text-xs font-medium {{ $iconClass }}">Find Doctors →</p>
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
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-800">Recent Consultations</h2>
                        <a href="{{ route('patient.consultations') }}" class="text-sm text-purple-600 hover:text-purple-800 font-semibold">View All →</a>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        @if($recentConsultations->count() > 0)
                            <div class="divide-y divide-gray-200">
                                @foreach($recentConsultations as $consultation)
                                    <div class="p-5 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-base font-bold text-gray-900">{{ $consultation->reference }}</h3>
                                                    <p class="text-sm text-gray-600 mt-0.5">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ $consultation->created_at->format('M d, Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                                    @if($consultation->status === 'completed') bg-emerald-100 text-emerald-800
                                                    @elseif($consultation->status === 'pending') bg-amber-100 text-amber-800
                                                    @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-800
                                                    @elseif($consultation->status === 'cancelled') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                                @if($consultation->payment_status === 'paid')
                                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">✓ Paid</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Consultations Yet</h3>
                                <p class="text-sm text-gray-500 mb-4">You haven't had any consultations yet.</p>
                                <a href="{{ route('consultation.index') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    Start Your First Consultation
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Menstrual Cycle Tracker (Female Patients Only) -->
                @if(strtolower($patient->gender) === 'female')
                <div id="menstrual-cycle" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-800">Menstrual Cycle Tracker</h2>
                        <button onclick="document.getElementById('cycleModal').classList.remove('hidden')" 
                                class="px-6 py-3 purple-gradient hover:opacity-90 text-white rounded-lg font-semibold transition-colors flex items-center space-x-2 shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Log Your Period</span>
                        </button>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        @if($currentCycle)
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900">Current Cycle</h3>
                                        <p class="text-sm text-gray-600">Started: {{ $currentCycle->start_date->format('M d, Y') }}</p>
                                    </div>
                                    @if($currentCycle->end_date)
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">
                                            Completed
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-pink-100 text-pink-800 text-xs font-semibold rounded-full">
                                            Active
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div class="bg-pink-50 rounded-lg p-4">
                                        <p class="text-xs text-gray-600 mb-1">Start Date</p>
                                        <p class="text-lg font-bold text-pink-600">{{ $currentCycle->start_date->format('M d') }}</p>
                                    </div>
                                    @if($currentCycle->end_date)
                                    <div class="bg-pink-50 rounded-lg p-4">
                                        <p class="text-xs text-gray-600 mb-1">End Date</p>
                                        <p class="text-lg font-bold text-pink-600">{{ $currentCycle->end_date->format('M d') }}</p>
                                    </div>
                                    @endif
                                    @if($currentCycle->period_length)
                                    <div class="bg-pink-50 rounded-lg p-4">
                                        <p class="text-xs text-gray-600 mb-1">Duration</p>
                                        <p class="text-lg font-bold text-pink-600">{{ $currentCycle->period_length }} days</p>
                                    </div>
                                    @endif
                                    @if($currentCycle->flow_intensity)
                                    <div class="bg-pink-50 rounded-lg p-4">
                                        <p class="text-xs text-gray-600 mb-1">Flow</p>
                                        <p class="text-lg font-bold text-pink-600 capitalize">{{ $currentCycle->flow_intensity }}</p>
                                    </div>
                                    @endif
                                </div>
                                
                                @if($nextPeriodPrediction)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-900">Next Period Prediction</p>
                                            <p class="text-sm text-blue-700">{{ $nextPeriodPrediction->format('l, F d, Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Start Tracking Your Cycle</h3>
                                <p class="text-sm text-gray-500 mb-4">Log your period to track your menstrual cycle and predict your next period.</p>
                                <button onclick="document.getElementById('cycleModal').classList.remove('hidden')" 
                                        class="inline-block purple-gradient hover:opacity-90 text-white px-8 py-3 rounded-lg font-semibold transition shadow-md hover:shadow-lg flex items-center space-x-2 mx-auto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Log Your Period</span>
                                </button>
                            </div>
                        @endif
                        
                        @if($menstrualCycles->count() > 0)
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="text-sm font-semibold text-gray-800 mb-4">Recent Cycles</h4>
                            <div class="space-y-3">
                                @foreach($menstrualCycles->take(3) as $cycle)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $cycle->start_date->format('M d, Y') }}</p>
                                            @if($cycle->end_date)
                                                <p class="text-xs text-gray-600">Ended: {{ $cycle->end_date->format('M d, Y') }}</p>
                                            @else
                                                <p class="text-xs text-pink-600">Active</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            @if($cycle->period_length)
                                                <p class="text-sm font-semibold text-gray-900">{{ $cycle->period_length }} days</p>
                                            @endif
                                            @if($cycle->cycle_length)
                                                <p class="text-xs text-gray-600">{{ $cycle->cycle_length }} day cycle</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Account Status & Dependents -->
                <div class="grid grid-cols-1 gap-6">

                    <!-- Account Status & Dependents -->
                    <div class="space-y-6">
                        <!-- Account Status -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                            <h3 class="text-base font-bold text-gray-800 mb-4">Account Information</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Email Verification</span>
                                    @if($patient->is_verified)
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">✓ Verified</span>
                                    @else
                                        <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full">Pending</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm text-gray-600">Member Since</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $patient->created_at->format('M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dependents -->
                        @if($dependents->count() > 0)
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
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Menstrual Cycle Logging Modal -->
    @if(strtolower($patient->gender) === 'female')
    <div id="cycleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Log Menstrual Period</h3>
                    <button onclick="document.getElementById('cycleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form id="cycleForm" onsubmit="submitCycle(event)">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200" required>
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                End Date (Optional)
                            </label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200">
                            <p class="text-xs text-gray-500 mt-1">Leave blank if period is still ongoing</p>
                        </div>
                        
                        <div>
                            <label for="flow_intensity" class="block text-sm font-medium text-gray-700 mb-2">
                                Flow Intensity
                            </label>
                            <select name="flow_intensity" id="flow_intensity" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200">
                                <option value="">Select intensity</option>
                                <option value="light">Light</option>
                                <option value="moderate">Moderate</option>
                                <option value="heavy">Heavy</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms (Optional)</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['Cramps', 'Bloating', 'Headache', 'Mood changes', 'Fatigue', 'Back pain'] as $symptom)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="symptoms[]" value="{{ strtolower(str_replace(' ', '_', $symptom)) }}" 
                                               class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                                        <span class="text-sm text-gray-700">{{ $symptom }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="notes" id="notes" rows="3" 
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-200"
                                      placeholder="Add any additional notes..."></textarea>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" onclick="document.getElementById('cycleModal').classList.add('hidden')" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-medium transition-colors">
                            Save
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
    </script>
    @include('components.custom-alert-modal')
</body>
</html>
