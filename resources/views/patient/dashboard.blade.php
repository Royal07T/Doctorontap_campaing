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
        
        /* Carousel Animation */
        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-250px * 7)); }
        }
        
        .animate-scroll {
            animation: scroll 30s linear infinite;
        }
        
        .animate-scroll:hover {
            animation-play-state: paused;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
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
            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr($patient->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ $patient->name }}</p>
                        <p class="text-xs text-gray-500">Patient</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
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

                <a href="{{ route('consultation.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Consultation</span>
                </a>

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
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
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
                        <span class="text-sm text-white hidden sm:inline">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Welcome Message -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome back, {{ $patient->first_name }}!</h2>
                    <p class="text-gray-600 mt-1">Here's an overview of your health records and consultations</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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
                            <div class="flex space-x-4 animate-scroll">
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

                <!-- Recent Activity Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Consultations -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-5 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-gray-800">Recent Consultations</h3>
                                <a href="{{ route('patient.consultations') }}" class="text-xs text-purple-600 hover:text-purple-800 font-semibold">View All →</a>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            @if($recentConsultations->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentConsultations as $consultation)
                                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900">{{ $consultation->reference }}</p>
                                                <p class="text-xs text-gray-600">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</p>
                                                <p class="text-xs text-gray-500 mt-1">{{ $consultation->created_at->format('M d, Y') }}</p>
                                            </div>
                                            <div class="flex flex-col items-end space-y-1">
                                                <span class="px-2 py-1 text-xs font-semibold rounded 
                                                    @if($consultation->status === 'completed') bg-emerald-100 text-emerald-800
                                                    @elseif($consultation->status === 'pending') bg-amber-100 text-amber-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                                @if($consultation->payment_status === 'paid')
                                                    <span class="text-xs text-emerald-600 font-medium">✓ Paid</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-500">No consultations yet</p>
                                    <a href="{{ route('consultation.index') }}" class="text-xs text-purple-600 hover:text-purple-800 mt-2 inline-block">Start your first consultation</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions & Account Status -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow-sm p-5">
                            <h3 class="text-base font-bold text-gray-800 mb-4">Quick Actions</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('consultation.index') }}" class="flex flex-col items-center justify-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group">
                                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">New Consultation</span>
                                </a>
                                <a href="{{ route('patient.consultations') }}" class="flex flex-col items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">View Consultations</span>
                                </a>
                                <a href="{{ route('patient.medical-records') }}" class="flex flex-col items-center justify-center p-4 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors group">
                                    <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">Medical Records</span>
                                </a>
                                <a href="{{ route('patient.profile') }}" class="flex flex-col items-center justify-center p-4 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors group">
                                    <div class="w-10 h-10 bg-amber-600 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">My Profile</span>
                                </a>
                            </div>
                        </div>

                        <!-- Account Status -->
                        <div class="bg-white rounded-lg shadow-sm p-5">
                            <h3 class="text-base font-bold text-gray-800 mb-4">Account Status</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Email Verification</span>
                                    @if($patient->is_verified)
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded">✓ Verified</span>
                                    @else
                                        <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded">Pending</span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Member Since</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $patient->created_at->format('M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dependents -->
                        @if($dependents->count() > 0)
                        <div class="bg-white rounded-lg shadow-sm p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-gray-800">My Dependents</h3>
                                <a href="{{ route('patient.dependents') }}" class="text-xs text-purple-600 hover:text-purple-800 font-semibold">View All →</a>
                            </div>
                            <div class="space-y-2">
                                @foreach($dependents->take(3) as $dependent)
                                    <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-bold text-sm">{{ substr($dependent->name, 0, 1) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $dependent->name }}</p>
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
</body>
</html>
