<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Dashboard') - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        [x-cloak] { display: none !important; }
        
        /* Ensure sidebar navigation is scrollable */
        .sidebar-nav {
            scrollbar-width: thin;
            scrollbar-color: #9333EA #f3f4f6;
        }
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: #f3f4f6;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background-color: #9333EA;
            border-radius: 3px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background-color: #7E22CE;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               style="height: 100vh; max-height: 100vh; display: flex; flex-direction: column;">
            <!-- Sidebar Header -->
            <div class="purple-gradient p-5 flex items-center justify-between flex-shrink-0" style="flex-shrink: 0;">
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
            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100 flex-shrink-0" style="flex-shrink: 0;">
                <div class="flex items-center space-x-3">
                    @php
                        $patient = Auth::guard('patient')->user();
                    @endphp
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
            <nav class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-2 sidebar-nav" style="min-height: 0; flex: 1 1 auto; -webkit-overflow-scrolling: touch;">
                <a href="{{ route('patient.dashboard') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.dashboard') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('patient.consultations') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.consultations') || request()->routeIs('patient.consultation.view') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>My Consultations</span>
                </a>

                <a href="{{ route('patient.medical-records') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.medical-records') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Medical Records</span>
                </a>

                <a href="{{ route('patient.payments') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.payments') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Payments</span>
                </a>

                <a href="{{ route('patient.doctors') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.doctors') || request()->routeIs('patient.doctors-by-specialization') || request()->routeIs('patient.doctors-by-symptom') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Find Doctors</span>
                </a>

                <a href="{{ route('patient.caregivers') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.caregivers') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Find Caregivers</span>
                </a>

                @if(Auth::guard('patient')->user()->dependents()->count() > 0)
                <a href="{{ route('patient.dependents') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.dependents') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Dependents</span>
                </a>
                @endif

                <a href="{{ route('patient.support-tickets.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.support-tickets.*') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Customer Care</span>
                </a>

                <a href="{{ route('patient.profile') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.profile') ? 'text-white purple-gradient' : 'text-gray-700 hover:bg-purple-50 hover:text-purple-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>

                @if(strtolower(Auth::guard('patient')->user()->gender) === 'female')
                <a href="{{ route('patient.cycle-tracker') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all {{ request()->routeIs('patient.cycle-tracker') ? 'bg-pink-50 text-pink-600' : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>Menstrual Cycle</span>
                </a>
                @endif
            </nav>
            
            <!-- Fixed Bottom Section (Logout) -->
            <div class="flex-shrink-0 border-t border-gray-200 p-4 bg-white">
                <form method="POST" action="{{ route('patient.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
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

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left: Logo -->
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <h1 class="text-xl font-bold text-gray-900">DoctorOnTap™</h1>
                        </div>
                    </div>
                    
                    <!-- Center: Search Bar -->
                    <div class="hidden md:flex flex-1 max-w-md mx-8">
                        <div class="relative w-full">
                            <input type="text" placeholder="Search records, doctors..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Right: Notifications, Dark Mode, Date -->
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        
                        <!-- Dark Mode Toggle -->
                        <button class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>
                        
                        <!-- Date -->
                        <span class="text-sm font-medium text-gray-700">{{ now()->format('l, M d, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
@include('components.custom-alert-modal')

    <!-- Global Page Loader -->
    <x-system-preloader x-show="pageLoading" message="Loading page..." />
    
    <script>
        // Global page loading handler
        (function() {
            function initPageLoader() {
                const body = document.body;
                let alpineData = null;
                
                // Try to get Alpine data
                if (typeof Alpine !== 'undefined' && Alpine.$data) {
                    alpineData = Alpine.$data(body);
                }
                
                if (alpineData && typeof alpineData.pageLoading !== 'undefined') {
                    // Show loader on link clicks
                    document.addEventListener('click', function(e) {
                        const link = e.target.closest('a[href]');
                        if (link && link.href && !link.href.startsWith('javascript:') && !link.href.startsWith('#')) {
                            const href = link.getAttribute('href');
                            if (href && !href.startsWith('#') && !link.hasAttribute('data-no-loader')) {
                                alpineData.pageLoading = true;
                            }
                        }
                    });
                    
                    // Hide loader when page is fully loaded
                    if (document.readyState === 'complete') {
                        alpineData.pageLoading = false;
                    } else {
                        window.addEventListener('load', function() {
                            alpineData.pageLoading = false;
                        });
                    }
                    
                    // Hide loader on popstate (back/forward navigation)
                    window.addEventListener('popstate', function() {
                        alpineData.pageLoading = false;
                    });
                } else {
                    // Retry if Alpine not ready
                    setTimeout(initPageLoader, 100);
                }
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPageLoader);
            } else {
                setTimeout(initPageLoader, 100);
            }
        })();
    </script>
</body>
</html>
