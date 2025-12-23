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
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" @if(View::hasSection('x-data-custom')) x-data="@yield('x-data-custom')" @else x-data="{ sidebarOpen: false, pageLoading: false @stack('x-data-extra') }" @endif>
    <div class="flex h-screen overflow-hidden">
        @include('doctor.partials.sidebar')

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
                            <h1 class="text-xl font-bold text-white">@yield('header-title', 'Doctor Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
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

