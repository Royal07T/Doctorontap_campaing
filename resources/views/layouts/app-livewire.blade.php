<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="DoctorOnTap">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DoctorOnTap">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#9333EA">
    <meta name="msapplication-TileColor" content="#9333EA">
    <meta name="msapplication-tap-highlight" content="no">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/pwa/icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/pwa/icon-192x192.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon.png') }}">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Resource Hints for Performance -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="{{ config('app.url') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-900" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @isset($sidebar)
            {{ $sidebar }}
        @else
            @yield('sidebar')
        @endisset

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            <!-- Top Header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 z-10 sticky top-0">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-primary focus:outline-none transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3 lg:hidden">
                            <img src="{{ asset('img/logo.png') }}" alt="DoctorOnTap" class="h-8 w-auto" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Doctor+On+Tap&background=7B3DE9&color=fff&size=128';">
                            <span class="text-xl font-bold text-gray-900">DoctorOnTap</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800 hidden lg:block">{{ $header ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center space-x-6">
                        <span class="text-sm font-medium text-gray-500">{{ now()->format('l, F j, Y') }}</span>
                        
                        <!-- User Dropdown (Placeholder if not present) -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-gray-700 hidden md:block">{{ auth()->user()->name ?? 'User' }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 border border-gray-100 z-50" style="display: none;">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log Out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
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

    <!-- Global Alert Toast -->
    <div 
        x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            init() {
                Livewire.on('alert', (event) => {
                    this.message = event.message;
                    this.type = event.type || 'success';
                    this.show = true;
                    setTimeout(() => { this.show = false }, 3000);
                });
            }
        }"
        x-show="show"
        x-transition
        class="fixed top-4 right-4 z-50 max-w-md"
        style="display: none;">
        <div 
            :class="{
                'bg-green-600': type === 'success',
                'bg-red-600': type === 'error',
                'bg-blue-600': type === 'info',
                'bg-yellow-600': type === 'warning'
            }"
            class="text-white px-6 py-4 rounded-lg shadow-xl flex items-center space-x-3">
            <!-- Success Icon -->
            <svg x-show="type === 'success'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <!-- Error Icon -->
            <svg x-show="type === 'error'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <!-- Info Icon -->
            <svg x-show="type === 'info'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <!-- Warning Icon -->
            <svg x-show="type === 'warning'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span x-text="message" class="flex-1"></span>
            <button @click="show = false" class="text-white hover:text-gray-200">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Livewire Scripts (must load before Alpine) -->
    @livewireScripts
    
    <!-- Vonage Video API SDK (OpenTok.js) - Load after Livewire and Alpine -->
    <!-- OpenTok.js is the official SDK for Vonage Video API (formerly TokBox) -->
    <script src="https://static.opentok.com/v2/js/opentok.min.js" 
            onerror="console.error('Failed to load OpenTok.js SDK from CDN. Please check your internet connection.')"></script>
    
    <!-- Vonage Conversations SDK - For chat consultations -->
    <script src="https://unpkg.com/@vonage/conversation-client@latest/dist/conversationClient.js" 
            onerror="console.error('Failed to load Vonage Conversations SDK from CDN. Please check your internet connection.')"></script>
    
    <!-- Page-specific scripts -->
    @stack('scripts')
    
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
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    // Unregister any existing service workers from different origins
                    const registrations = await navigator.serviceWorker.getRegistrations();
                    for (let registration of registrations) {
                        const currentOrigin = window.location.origin;
                        const swOrigin = new URL(registration.scope).origin;
                        if (swOrigin !== currentOrigin) {
                            console.log('Unregistering service worker from different origin:', swOrigin);
                            await registration.unregister();
                        }
                    }
                    
                    // Register service worker for current origin
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    console.log('ServiceWorker registered:', registration.scope);
                    
                    // Check for updates periodically
                    setInterval(() => {
                        registration.update();
                    }, 60000); // Check every minute
                } catch (error) {
                    console.log('ServiceWorker registration failed:', error);
                }
            });
            
            // Handle service worker updates
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });
        }
        
        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show custom install button (optional)
            const installButton = document.querySelector('#pwa-install-btn');
            if (installButton) {
                installButton.style.display = 'block';
                installButton.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        console.log(`User response to install prompt: ${outcome}`);
                        deferredPrompt = null;
                        installButton.style.display = 'none';
                    }
                });
            }
        });
        
        // Track if app is installed
        window.addEventListener('appinstalled', () => {
            console.log('PWA installed successfully');
            deferredPrompt = null;
        });
        
        // Detect if running as PWA
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            console.log('Running as PWA');
            document.body.classList.add('pwa-mode');
        }
    </script>
</body>
</html>

