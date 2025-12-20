,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'DoctorOnTap') }} - Instant Medical Consultations</title>
    
    <!-- PWA Meta Tags -->
    <meta name="application-name" content="DoctorOnTap">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#7B3DE9">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 selection:bg-primary selection:text-white">
    <div class="relative min-h-screen overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-primary/20 blur-3xl filter"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] rounded-full bg-accent/20 blur-3xl filter"></div>
        </div>

        <!-- Navigation -->
        <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="{{ asset('img/logo.png') }}" alt="DoctorOnTap" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Doctor+On+Tap&background=7B3DE9&color=fff&size=128';">
                        <span class="ml-3 text-2xl font-bold text-gray-900">DoctorOnTap</span>
                    </div>
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-gray-600 hover:text-primary transition-colors font-medium">Features</a>
                        <a href="#how-it-works" class="text-gray-600 hover:text-primary transition-colors font-medium">How it Works</a>
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-900 hover:text-primary font-medium transition-colors">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-primary ml-4">Get Started</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button type="button" class="text-gray-500 hover:text-gray-900 focus:outline-none" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div class="hidden md:hidden glass absolute w-full border-t border-gray-100" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="#features" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Features</a>
                    <a href="#how-it-works" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">How it Works</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-primary hover:bg-gray-50">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-primary hover:bg-gray-50">Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl mb-6">
                        Healthcare at your <span class="text-primary">fingertips</span>
                    </h1>
                    <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500 mb-10">
                        Connect with certified doctors instantly. Secure, private, and convenient consultations from the comfort of your home.
                    </p>
                    <div class="flex justify-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-3">Book a Consultation</a>
                        @endif
                        <a href="#how-it-works" class="btn-secondary text-lg px-8 py-3">Learn More</a>
                    </div>
                </div>
                
                <!-- Hero Image/Illustration Placeholder -->
                <div class="mt-16 relative">
                    <div class="glass rounded-2xl p-4 shadow-2xl max-w-4xl mx-auto transform hover:scale-[1.01] transition-transform duration-500">
                        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Doctor Consultation" class="rounded-xl w-full h-auto object-cover shadow-sm">
                        
                        <!-- Floating Cards -->
                        <div class="absolute -bottom-6 -left-6 glass p-4 rounded-xl shadow-lg hidden md:block animate-bounce" style="animation-duration: 3s;">
                            <div class="flex items-center space-x-3">
                                <div class="bg-green-100 p-2 rounded-full">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Verified Doctors</p>
                                    <p class="text-xs text-gray-500">100% Certified</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="absolute -top-6 -right-6 glass p-4 rounded-xl shadow-lg hidden md:block animate-bounce" style="animation-duration: 4s;">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Instant Access</p>
                                    <p class="text-xs text-gray-500">24/7 Support</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="py-20 bg-white relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-primary font-semibold tracking-wide uppercase text-sm">Why Choose Us</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Better Healthcare Experience
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="glass p-8 rounded-2xl hover:shadow-xl transition-shadow duration-300">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-6 text-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Video Consultations</h3>
                        <p class="text-gray-500">High-quality video calls with doctors. Discuss your symptoms and get advice face-to-face.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="glass p-8 rounded-2xl hover:shadow-xl transition-shadow duration-300">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-6 text-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Digital Prescriptions</h3>
                        <p class="text-gray-500">Receive prescriptions digitally immediately after your consultation. Secure and easy to access.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="glass p-8 rounded-2xl hover:shadow-xl transition-shadow duration-300">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-6 text-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Secure Records</h3>
                        <p class="text-gray-500">Your medical history and data are encrypted and stored securely. Only you and your doctor have access.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center mb-4">
                            <img class="h-8 w-auto brightness-0 invert" src="{{ asset('img/logo.png') }}" alt="DoctorOnTap" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Doctor+On+Tap&background=fff&color=000&size=128';">
                            <span class="ml-3 text-xl font-bold">DoctorOnTap</span>
                        </div>
                        <p class="text-gray-400 max-w-sm">Making healthcare accessible, affordable, and convenient for everyone.</p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Find a Doctor</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Services</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Legal</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Cookie Policy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500">
                    <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scroll Effect Script -->
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('glass', 'shadow-sm');
            } else {
                navbar.classList.remove('glass', 'shadow-sm');
            }
        });
    </script>
</body>
</html>
