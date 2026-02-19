<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor Dashboard') - DoctorOnTap</title>
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
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" @if(View::hasSection('x-data-custom')) x-data="@yield('x-data-custom')" @else x-data="{ sidebarOpen: false, pageLoading: false @stack('x-data-extra') }" @endif>
    <div class="flex h-screen overflow-hidden">
        @include('doctor.partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg border-b border-purple-700 z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">@yield('header-title', 'Doctor Dashboard')</h1>
                        </div>
                    </div>
                    
                    <!-- Right: Notifications, Settings, Date -->
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        
                        <!-- Settings Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="p-2.5 bg-white/20 hover:bg-white/30 text-white rounded-2xl transition-all duration-300 border border-white/30 hover:border-white/50 group relative shadow-lg hover:shadow-xl backdrop-blur-sm">
                                <svg class="w-5 h-5 transform group-hover:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-50"
                                 style="display: none;">
                                <a href="{{ route('doctor.settings') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">Profile</span>
                                </a>
                                <a href="{{ route('doctor.settings') }}#preferences" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                    <span class="font-medium">Preferences</span>
                                </a>
                                <a href="{{ route('doctor.settings') }}#security" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span class="font-medium">Security</span>
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('doctor.logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span class="font-medium">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Date -->
                        <span class="hidden md:block text-sm font-medium text-white">{{ now()->format('l, M d, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    
    <!-- Global Page Loader -->
    <x-system-preloader x-show="pageLoading" message="Loading page..." />
    
    <script>
        // Global page loading handler
        (function() {
            // Show loader on initial page load
            if (document.readyState === 'loading') {
                // Page is still loading
            }
            
            // Wait for Alpine to initialize
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

