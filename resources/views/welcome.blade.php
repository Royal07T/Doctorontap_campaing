<!DOCTYPE html>
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
<body class="font-sans antialiased text-text-main bg-surface selection:bg-primary selection:text-white">
    <div class="relative min-h-screen overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-[-10%] right-[-5%] w-[600px] h-[600px] rounded-full bg-primary/10 blur-3xl filter opacity-70"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[600px] h-[600px] rounded-full bg-accent/10 blur-3xl filter opacity-70"></div>
            <div class="absolute top-[20%] left-[10%] w-[300px] h-[300px] rounded-full bg-purple-200/20 blur-2xl filter opacity-40"></div>
        </div>

        <!-- Navigation -->
        <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="{{ asset('img/logo.png') }}" alt="DoctorOnTap" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Doctor+On+Tap&background=7B3DE9&color=fff&size=128&font-size=0.33';">
                        <span class="ml-3 text-2xl font-bold text-gray-900 font-heading tracking-tight">DoctorOnTap</span>
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
        <div class="relative pt-32 pb-20 lg:pt-40 lg:pb-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-4xl tracking-tight font-extrabold text-text-main font-heading sm:text-5xl md:text-6xl mb-6 leading-tight">
                        Healthcare at your <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary to-accent">fingertips</span>
                    </h1>
                    <p class="mt-4 max-w-2xl mx-auto text-xl text-text-muted mb-10 leading-relaxed">
                        Connect with certified doctors instantly. Secure, private, and convenient consultations from the comfort of your home.
                    </p>
                    <div class="flex justify-center gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-3 shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-shadow">Book a Consultation</a>
                        @endif
                        <a href="#how-it-works" class="bg-white text-text-main border border-gray-200 hover:bg-gray-50 hover:border-gray-300 font-semibold rounded-lg text-lg px-8 py-3 transition-all">Learn More</a>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="mt-16 relative">
                    <div class="glass rounded-2xl p-2 bg-white/50 backdrop-blur-xl border border-white/60 shadow-2xl max-w-5xl mx-auto">
                        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Doctor Consultation" class="rounded-xl w-full h-auto object-cover shadow-sm">
                    </div>
                </div>

                <!-- Trust Indicators -->
                <div class="mt-12 pt-8 border-t border-gray-200/60 w-full max-w-5xl mx-auto">
                    <p class="text-center text-sm font-semibold text-text-muted uppercase tracking-wider mb-6">Trusted by patients, compliant with global standards</p>
                    <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16 opacity-70 grayscale hover:grayscale-0 transition-all duration-300">
                        <div class="flex items-center gap-2">
                            <svg class="h-8 w-8 text-text-main" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                            <span class="font-bold text-lg">HIPAA Compliant</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-8 w-8 text-text-main" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span class="font-bold text-lg">SSL Secure</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-8 w-8 text-text-main" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <span class="font-bold text-lg">Verified Doctors</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-8 w-8 text-text-main" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><path d="M12 6v6l4 2"/></svg>
                            <span class="font-bold text-lg">24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="py-24 bg-white relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-accent/5 blur-3xl"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-accent font-bold tracking-wide uppercase text-sm mb-2">Why Choose DoctorOnTap</h2>
                    <p class="text-3xl leading-tight font-heading font-bold text-text-main sm:text-4xl">
                        A Better Healthcare Experience
                    </p>
                    <p class="mt-4 max-w-2xl mx-auto text-lg text-text-muted">
                        We've reimagined the doctor-patient relationship to be more personal, accessible, and effective.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-purple-50 rounded-xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold font-heading text-text-main mb-3">Video Consultations</h3>
                        <p class="text-text-muted leading-relaxed">High-quality video calls with doctors. Discuss your symptoms and get professional medical advice face-to-face, anytime.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center mb-6 text-accent group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold font-heading text-text-main mb-3">Digital Prescriptions</h3>
                        <p class="text-text-muted leading-relaxed">Receive prescriptions digitally immediately after your consultation. Sent directly to your pharmacy of choice.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-teal-50 rounded-xl flex items-center justify-center mb-6 text-teal-600 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold font-heading text-text-main mb-3">Secure Records</h3>
                        <p class="text-text-muted leading-relaxed">Your medical history and data are encrypted with bank-level security. Only you and your authorized doctor have access.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="py-20 bg-surface border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-extrabold font-heading text-primary mb-2">10k+</div>
                        <div class="text-sm font-medium text-text-muted uppercase tracking-wide">Patients Served</div>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold font-heading text-accent mb-2">500+</div>
                        <div class="text-sm font-medium text-text-muted uppercase tracking-wide">Verified Doctors</div>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold font-heading text-primary mb-2">98%</div>
                        <div class="text-sm font-medium text-text-muted uppercase tracking-wide">Satisfaction Rate</div>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold font-heading text-accent mb-2">15m</div>
                        <div class="text-sm font-medium text-text-muted uppercase tracking-wide">Avg. Wait Time</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="py-20 relative overflow-hidden">
            <div class="absolute inset-0 bg-primary/5"></div>
            <div class="max-w-4xl mx-auto px-4 relative z-10 text-center">
                <h2 class="text-3xl font-heading font-bold text-text-main mb-6">Ready to prioritize your health?</h2>
                <p class="text-xl text-text-muted mb-10">Join thousands of users who trust DoctorOnTap for their healthcare needs.</p>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-primary text-lg px-10 py-4 shadow-xl hover:scale-105 transition-transform">Get Started Now</a>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center mb-6">
                            <img class="h-8 w-auto brightness-0 invert opacity-90" src="{{ asset('img/logo.png') }}" alt="DoctorOnTap" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Doctor+On+Tap&background=fff&color=000&size=128&font-size=0.33';">
                            <span class="ml-3 text-xl font-bold font-heading tracking-tight">DoctorOnTap</span>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed">Making healthcare accessible, affordable, and convenient for everyone. Your health, your schedule.</p>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold font-heading mb-6 text-gray-100">Platform</h4>
                        <ul class="space-y-4 text-gray-400 text-sm">
                            <li><a href="#" class="hover:text-primary-light transition-colors">How it Works</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Find a Doctor</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Pricing</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold font-heading mb-6 text-gray-100">Company</h4>
                        <ul class="space-y-4 text-gray-400 text-sm">
                            <li><a href="#" class="hover:text-primary-light transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Blog</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold font-heading mb-6 text-gray-100">Legal</h4>
                        <ul class="space-y-4 text-gray-400 text-sm">
                            <li><a href="#" class="hover:text-primary-light transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">Cookie Policy</a></li>
                            <li><a href="#" class="hover:text-primary-light transition-colors">HIPAA Compliance</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                    <p>© {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <!-- Social placeholders -->
                        <a href="#" class="hover:text-white transition-colors"><span class="sr-only">Twitter</span><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg></a>
                        <a href="#" class="hover:text-white transition-colors"><span class="sr-only">LinkedIn</span><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"/></svg></a>
                    </div>
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
