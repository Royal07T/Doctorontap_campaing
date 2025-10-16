<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DoctorOnTap - Speak with a Doctor Today</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        html {
            scroll-behavior: smooth;
        }
        
        body {
            background: #f9fafb;
            min-height: 100vh;
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        [x-cloak] {
            display: none !important;
        }
        
        .hero-section {
            position: relative;
            min-height: 600px;
            overflow: hidden;
        }
        
        .hero-bg-image {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 2s ease-in-out;
        }
        
        .hero-bg-image.active {
            opacity: 1;
        }
        
        .hero-bg-1 {
            background-image: url('{{ asset('img/hero/national-cancer-institute-NFvdKIhxYlU-unsplash.jpg') }}');
        }
        
        .hero-bg-2 {
            background-image: url('{{ asset('img/hero/vitaly-gariev-c1ZGaJTOnJs-unsplash.jpg') }}');
        }
        
        .hero-bg-3 {
            background-image: url('{{ asset('img/hero/national-cancer-institute-nR2C9AVzfHY-unsplash.jpg') }}');
        }
        
        .hero-bg-4 {
            background-image: url('{{ asset('img/hero/vitaly-gariev-2DzoG3upSu4-unsplash.jpg') }}');
        }
        
        .hero-bg-5 {
            background-image: url('{{ asset('img/hero/mufid-majnun-AShIzTVQoEo-unsplash.jpg') }}');
        }
        
        .hero-bg-6 {
            background-image: url('{{ asset('img/hero/vitaly-gariev-RtFPJ2HlTYI-unsplash.jpg') }}');
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(123, 61, 233, 0.4) 100%);
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
        }
        
        .purple-gradient {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
        }
        
        .star-rating {
            color: #fbbf24;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 purple-gradient shadow-lg">
        <div class="container mx-auto px-5 py-3 md:py-4">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center group">
                    <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap Logo" class="h-7 sm:h-8 md:h-10 w-auto transition-transform group-hover:scale-105">
                </a>
                <div class="flex items-center gap-4 md:gap-8 text-white">
                    <!-- Email -->
                    <a href="mailto:inquiries@doctorontap.com.ng" class="hover:text-purple-200 transition-colors">
                        <div class="text-right">
                            <p class="text-xs md:text-sm font-medium opacity-90">Email</p>
                            <p class="text-xs md:text-base font-semibold">inquiries@doctorontap.com.ng</p>
                        </div>
                    </a>
                    <!-- WhatsApp -->
                    <a href="https://wa.me/2348177777122" target="_blank" class="hover:text-purple-200 transition-colors">
                        <div class="text-right">
                            <p class="text-xs md:text-sm font-medium opacity-90">Whatsapp number</p>
                            <p class="text-xs md:text-base font-semibold">08177777122</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Rotating Background Images -->
    <section class="hero-section">
        <!-- Background Images -->
        <div class="hero-bg-image hero-bg-1 active"></div>
        <div class="hero-bg-image hero-bg-2"></div>
        <div class="hero-bg-image hero-bg-3"></div>
        <div class="hero-bg-image hero-bg-4"></div>
        <div class="hero-bg-image hero-bg-5"></div>
        <div class="hero-bg-image hero-bg-6"></div>
        
        <!-- Overlay -->
        <div class="hero-overlay"></div>
        
        <!-- Hero Content -->
        <div class="hero-content">
            <div class="container mx-auto max-w-5xl px-5 py-16 md:py-24">
                <div class="text-center text-white">
                    <!-- Headline -->
                    <h1 class="text-4xl md:text-6xl font-bold mb-4" style="text-shadow: 3px 3px 10px rgba(0,0,0,0.8);">
                        Talk to a Doctor Now,<br>
                        <span style="color: #fbbf24; text-shadow: 3px 3px 10px rgba(0,0,0,0.8);">Pay Later</span>
            </h1>
                    
                    <!-- Subheadline -->
                    <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">
                        Instant access to verified doctors with no upfront fees.<br>
                        <span style="color: #fbbf24; text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">Consult now and pay only after your appointment.</span>
                    </p>
                    
                    <!-- Social Proof - Customer Reviews Carousel -->
                    <div class="mb-8 max-w-3xl mx-auto" x-data="reviewCarousel()">
                        <div class="relative">
                            <!-- Review Cards -->
                            <div class="overflow-hidden">
                                <div class="flex transition-transform duration-500 ease-in-out" :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                                    <!-- Review 1 -->
                                    <div class="w-full flex-shrink-0 px-4">
                                        <div class="bg-gradient-to-br from-white/20 to-purple-100/20 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border-2 border-white/30 hover:border-white/50 transition-all duration-300">
                                            <div class="flex justify-center mb-4">
                                                <span class="star-rating text-3xl drop-shadow-lg">★★★★★</span>
                                            </div>
                                            <h3 class="text-white text-xl font-bold text-center mb-4" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.5);">What people say about us on Google</h3>
                                            <p class="text-white text-lg mb-5 leading-relaxed text-center font-medium" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.5);">"Thank you for your help. The Doctor that was assigned to me was very helpful. She asked me to do some test and told me not to worry. Now my mind is at peace. If you're always of hospital or need second thought on your health contact doctorontap. The prices are very very low. I will be going to the hospital once they have one. Thank you once again."</p>
                                            <div class="flex items-center justify-center gap-3">
                                                <img src="{{ asset('img/testimony/Nancy Audu-War.jpg') }}" alt="Nancy Audu-War" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-lg">
                                                <p class="text-white text-base font-bold" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Nancy Audu-War</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Review 2 -->
                                    <div class="w-full flex-shrink-0 px-4">
                                        <div class="bg-gradient-to-br from-white/20 to-purple-100/20 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border-2 border-white/30 hover:border-white/50 transition-all duration-300">
                                            <div class="flex justify-center mb-4">
                                                <span class="star-rating text-3xl drop-shadow-lg">★★★★★</span>
                                            </div>
                                            <h3 class="text-white text-xl font-bold text-center mb-4" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.5);">What people say about us on Google</h3>
                                            <p class="text-white text-lg mb-5 leading-relaxed text-center font-medium" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.5);">"I Love them 😍😍 I appreciate their doctor they were willing to talk to me i felt as if i was talking to somebody i know ✅✅✅"</p>
                                            <div class="flex items-center justify-center gap-3">
                                                <img src="{{ asset('img/testimony/Otabor Theodora.jpg') }}" alt="Otabor Theodora" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-lg">
                                                <p class="text-white text-base font-bold" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Otabor Theodora</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Review 3 -->
                                    <div class="w-full flex-shrink-0 px-4">
                                        <div class="bg-gradient-to-br from-white/20 to-purple-100/20 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border-2 border-white/30 hover:border-white/50 transition-all duration-300">
                                            <div class="flex justify-center mb-4">
                                                <span class="star-rating text-3xl drop-shadow-lg">★★★★★</span>
                                            </div>
                                            <h3 class="text-white text-xl font-bold text-center mb-4" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.5);">What people say about us on Google</h3>
                                            <p class="text-white text-lg mb-5 leading-relaxed text-center font-medium" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.5);">"When DoctorOnTap ads prompted on my instagram, i was curious as to how true it was. I contacted them and i must say, their services is cool and impressive."</p>
                                            <div class="flex items-center justify-center gap-3">
                                                <img src="{{ asset('img/testimony/odunayo muibat.jpeg') }}" alt="Odunayo Muibat" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-lg">
                                                <p class="text-white text-base font-bold" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Odunayo Muibat</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Navigation Arrows -->
                            <button @click="prevSlide()" class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/50 backdrop-blur-md text-white rounded-full p-3 shadow-2xl transition-all hover:scale-110 border-2 border-white/40">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button @click="nextSlide()" class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/50 backdrop-blur-md text-white rounded-full p-3 shadow-2xl transition-all hover:scale-110 border-2 border-white/40">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Dots Navigation -->
                        <div class="flex justify-center gap-3 mt-8">
                            <button @click="goToSlide(0)" class="h-2 rounded-full transition-all shadow-lg" :class="currentSlide === 0 ? 'bg-white w-12' : 'bg-white/40 w-8 hover:bg-white/60'"></button>
                            <button @click="goToSlide(1)" class="h-2 rounded-full transition-all shadow-lg" :class="currentSlide === 1 ? 'bg-white w-12' : 'bg-white/40 w-8 hover:bg-white/60'"></button>
                            <button @click="goToSlide(2)" class="h-2 rounded-full transition-all shadow-lg" :class="currentSlide === 2 ? 'bg-white w-12' : 'bg-white/40 w-8 hover:bg-white/60'"></button>
                        </div>
                    </div>
                    
                    <!-- CTA Button -->
                    <a href="#consultation-form" class="inline-block px-8 py-4 text-xl font-bold text-white rounded-full purple-gradient hover:shadow-2xl hover:scale-105 transition-all">
                        Talk to a Doctor Now →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gradient-to-br from-gray-50 via-purple-50 to-blue-50 py-16">
        <div class="container mx-auto max-w-4xl px-5">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 rounded-full mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                Frequently Asked Questions
            </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Get instant answers to common questions about Consult now, pay later
                </p>
            </div>
            
            <!-- FAQ Accordion -->
            <div class="space-y-3" x-data="{ openFaq: 1 }">
                <!-- FAQ 1 -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-200">
                    <button @click="openFaq = openFaq === 1 ? null : 1" 
                            class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                What does 'Pay After' mean?
                    </h3>
                        </div>
                        <svg :class="openFaq === 1 ? 'rotate-180' : ''" 
                             class="w-5 h-5 text-gray-500 transition-transform duration-300" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 1" 
                         x-collapse
                         class="px-6 pb-5 pt-0">
                        <div class="pl-14 text-gray-600 leading-relaxed">
                            You speak with a doctor first and only pay after your consultation is complete. No upfront fees required. This ensures you get the care you need without financial barriers.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-200">
                    <button @click="openFaq = openFaq === 2 ? null : 2" 
                            class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                How do I pay after the consultation?
                    </h3>
                        </div>
                        <svg :class="openFaq === 2 ? 'rotate-180' : ''" 
                             class="w-5 h-5 text-gray-500 transition-transform duration-300" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 2" 
                         x-collapse
                         class="px-6 pb-5 pt-0">
                        <div class="pl-14 text-gray-600 leading-relaxed">
                            After your consultation, you'll receive a payment link via email or SMS. We accept all major payment methods including mobile money, bank transfers, and debit/credit cards. Payment is simple, secure, and only takes a few minutes.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 3 -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-200">
                    <button @click="openFaq = openFaq === 3 ? null : 3" 
                            class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Is this service safe and private?
                    </h3>
                        </div>
                        <svg :class="openFaq === 3 ? 'rotate-180' : ''" 
                             class="w-5 h-5 text-gray-500 transition-transform duration-300" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 3" 
                         x-collapse
                         class="px-6 pb-5 pt-0">
                        <div class="pl-14 text-gray-600 leading-relaxed">
                            Absolutely! Your privacy and security are our top priorities. All your medical data is encrypted using industry-standard protocols. Your information is handled under strict medical confidentiality policies and will never be shared without your explicit consent. We comply with all healthcare privacy regulations.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 4 -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-200">
                    <button @click="openFaq = openFaq === 4 ? null : 4" 
                            class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Can I choose my doctor and consultation mode?
                    </h3>
                        </div>
                        <svg :class="openFaq === 4 ? 'rotate-180' : ''" 
                             class="w-5 h-5 text-gray-500 transition-transform duration-300" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 4" 
                         x-collapse
                         class="px-6 pb-5 pt-0">
                        <div class="pl-14 text-gray-600 leading-relaxed">
                            Yes! You have full control over your consultation experience. Browse our list of qualified doctors, read their profiles and specializations, then select the one that best fits your needs. You can also choose your preferred consultation mode: voice call, video call, or text chat - whatever makes you most comfortable.
                        </div>
                    </div>
                </div>

                <!-- FAQ 5 - Additional -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-200">
                    <button @click="openFaq = openFaq === 5 ? null : 5" 
                            class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                How quickly can I speak with a doctor?
                            </h3>
                        </div>
                        <svg :class="openFaq === 5 ? 'rotate-180' : ''" 
                             class="w-5 h-5 text-gray-500 transition-transform duration-300" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 5" 
                         x-collapse
                         class="px-6 pb-5 pt-0">
                        <div class="pl-14 text-gray-600 leading-relaxed">
                            Most consultations are scheduled within 24 hours of your request. For urgent matters, our available doctors can often connect with you the same day. Simply submit your consultation form, and we'll match you with an appropriate healthcare professional as quickly as possible.
                        </div>
                    </div>
                </div>

                <!-- FAQ 6 - Additional -->
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-200">
                    <button @click="openFaq = openFaq === 6 ? null : 6" 
                            class="w-full text-left px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 bg-violet-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Can I upload medical documents or test results?
                            </h3>
                        </div>
                        <svg :class="openFaq === 6 ? 'rotate-180' : ''" 
                             class="w-5 h-5 text-gray-500 transition-transform duration-300" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="openFaq === 6" 
                         x-collapse
                         class="px-6 pb-5 pt-0">
                        <div class="pl-14 text-gray-600 leading-relaxed">
                            Yes! When submitting your consultation request, you can attach relevant medical documents, lab results, x-rays, or prescription history. This helps our doctors provide you with more accurate and informed medical advice. Supported formats include PDF, JPG, PNG, and common document formats.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Still Have Questions CTA -->
            <div class="mt-12 text-center">
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-8 shadow-xl">
                    <h3 class="text-2xl font-bold text-white mb-3">
                        Still Have Questions?
                    </h3>
                    <p class="text-purple-100 mb-6 max-w-xl mx-auto">
                        Our support team is ready to help you with any concerns or inquiries
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="tel:08177777122" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-purple-700 font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Call Us Now
                        </a>
                        <a href="mailto:inquiries@doctorontap.com.ng" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-purple-700 text-white font-semibold rounded-lg hover:bg-purple-800 hover:shadow-lg hover:scale-105 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Consultation Form Section -->
    <div id="consultation-form" class="container mx-auto max-w-5xl px-5 py-12" x-data="consultationForm()">
        <!-- Form Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-600 to-blue-600 rounded-2xl mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                Book Your Consultation
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Complete the form below to connect with a qualified healthcare professional
            </p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-3xl mx-auto">
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold shadow-md">1</div>
                    <span class="text-xs font-medium text-gray-700 mt-2 hidden sm:block">Personal</span>
                </div>
                <div class="flex-1 h-1 bg-gradient-to-r from-purple-600 to-blue-500 mx-2"></div>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold shadow-md">2</div>
                    <span class="text-xs font-medium text-gray-700 mt-2 hidden sm:block">Medical</span>
                </div>
                <div class="flex-1 h-1 bg-gradient-to-r from-blue-500 to-emerald-500 mx-2"></div>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white font-bold shadow-md">3</div>
                    <span class="text-xs font-medium text-gray-700 mt-2 hidden sm:block">Preferences</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 md:p-8">
            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                
                <!-- PERSONAL DETAILS BLOCK -->
                <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-200 p-5">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Step 1: Personal Details</h3>
                            <p class="text-xs text-gray-600">Tell us about yourself</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                    <div>
                            <label for="first_name" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            First Name *
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            x-model="formData.first_name"
                            required
                                placeholder="Enter your first name"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                        >
                            <p x-show="errors.first_name" class="text-red-500 text-xs mt-1" x-text="errors.first_name"></p>
                    </div>

                        <!-- Last Name -->
                    <div>
                            <label for="last_name" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            Last Name *
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            x-model="formData.last_name"
                            required
                                placeholder="Enter your last name"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                        >
                            <p x-show="errors.last_name" class="text-red-500 text-xs mt-1" x-text="errors.last_name"></p>
                    </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                                Gender *
                            </label>
                            <select 
                                id="gender" 
                                x-model="formData.gender"
                                required
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 bg-white transition-colors"
                            >
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p x-show="errors.gender" class="text-red-500 text-xs mt-1" x-text="errors.gender"></p>
                        </div>

                        <!-- Age -->
                        <div>
                            <label for="age" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                                Age *
                            </label>
                            <input 
                                type="number" 
                                id="age" 
                                x-model="formData.age"
                                min="1"
                                max="120"
                                required
                                placeholder="Enter your age"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                            >
                            <p x-show="errors.age" class="text-red-500 text-xs mt-1" x-text="errors.age"></p>
                        </div>

                        <!-- Mobile Number -->
                        <div>
                            <label for="mobile" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                                Mobile Number (WhatsApp) *
                            </label>
                            <input 
                                type="tel" 
                                id="mobile" 
                                x-model="formData.mobile"
                                required
                                placeholder="+234 XXX XXX XXXX"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                            >
                            <p x-show="errors.mobile" class="text-red-500 text-xs mt-1" x-text="errors.mobile"></p>
                </div>

                <!-- Email -->
                <div>
                            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                        Email Address *
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        x-model="formData.email"
                        required
                                placeholder="your.email@example.com"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                    >
                            <p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email"></p>
                        </div>
                    </div>
                </div>

                <!-- TRIAGE BLOCK -->
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200 p-5">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Step 2: Medical Information</h3>
                            <p class="text-xs text-gray-600">Help us understand your health concern</p>
                        </div>
                    </div>
                    
                    <!-- What's the problem -->
                    <div class="mb-4">
                        <label for="problem" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            What's the problem right now? *
                        </label>
                        <input 
                            type="text" 
                            id="problem" 
                            x-model="formData.problem"
                            required
                            placeholder="Brief description of your main concern"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-colors"
                        >
                        <p x-show="errors.problem" class="text-red-500 text-xs mt-1" x-text="errors.problem"></p>
                    </div>

                    <!-- Medical Documents Upload -->
                    <div class="mb-4">
                        <label for="medical_documents" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            Upload Medical Documents (Optional)
                        </label>
                        <p class="text-xs text-gray-600 mb-2">
                            Upload test results, lab reports, X-rays, or prescriptions (PDF, JPG, PNG, DOC - Max 5MB)
                        </p>
                        <input 
                            type="file" 
                            id="medical_documents" 
                            name="medical_documents[]"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            @change="handleFileUpload($event)"
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors"
                        >
                        <p x-show="errors.medical_documents" class="text-red-500 text-xs mt-1" x-text="errors.medical_documents"></p>
                        <div x-show="uploadedFiles.length > 0" class="mt-2">
                            <p class="text-xs font-semibold text-gray-700 mb-1.5">Selected files:</p>
                            <ul class="space-y-1">
                                <template x-for="(file, index) in uploadedFiles" :key="index">
                                    <li class="flex items-center justify-between text-xs bg-white px-3 py-2 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span x-text="file.name" class="truncate max-w-xs"></span>
                                            <span class="text-gray-500" x-text="'(' + (file.size / 1024).toFixed(0) + ' KB)'"></span>
                                        </span>
                                        <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700 ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- How bad is it -->
                    <div class="mb-4">
                        <label for="severity" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                            How bad is it? *
                        </label>
                        <select 
                            id="severity" 
                            x-model="formData.severity"
                            required
                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white transition-colors"
                        >
                            <option value="">Select Severity</option>
                            <option value="mild">🟢 Mild - Not urgent</option>
                            <option value="moderate">🟡 Moderate - Needs attention</option>
                            <option value="severe">🔴 Severe - Urgent care needed</option>
                        </select>
                        <p x-show="errors.severity" class="text-red-500 text-xs mt-1" x-text="errors.severity"></p>
                    </div>

                    <!-- Emergency Symptoms -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Are you experiencing any of these symptoms now?
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="chest_pain" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Chest pain or pressure</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="cough" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Persistent cough</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="breathing" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Severe difficulty breathing</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="consciousness" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Loss of consciousness / fainting</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="bleeding" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Uncontrolled heavy bleeding</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="weakness" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Sudden weakness, numbness</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="speech" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Slurred speech</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="seizure" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Seizure now or ongoing</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="abdominal" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Severe abdominal pain</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="fever" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Very high fever</span>
                            </label>
                            <label class="flex items-start gap-2 p-2 bg-white border border-gray-200 hover:border-blue-300 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" x-model="formData.emergency_symptoms" value="pregnancy_bleeding" class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">Heavy vaginal bleeding in pregnancy</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- DOCTOR'S CHOICE BLOCK -->
                <div class="bg-gradient-to-br from-emerald-50 to-white rounded-xl border border-emerald-200 p-5">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Step 3: Doctor & Consultation Preferences</h3>
                            <p class="text-xs text-gray-600">Choose your preferred doctor and mode</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Doctor Preference -->
                        <div>
                            <label for="doctor" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                                Doctor Preference
                            </label>
                            <select 
                                id="doctor" 
                                x-model="formData.doctor"
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 bg-white transition-colors"
                            >
                                <option value="">Any Available Doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">
                                        {{ $doctor->name }}@if($doctor->specialization) - {{ $doctor->specialization }}@endif - NGN {{ number_format($doctor->consultation_fee, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <p x-show="errors.doctor" class="text-red-500 text-xs mt-1" x-text="errors.doctor"></p>
                        </div>

                        <!-- Consult Mode -->
                <div>
                            <label for="consult_mode" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                                Consultation Mode *
                    </label>
                            <select 
                                id="consult_mode" 
                                x-model="formData.consult_mode"
                        required
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 bg-white transition-colors"
                            >
                                <option value="">Select Consult Mode</option>
                                <option value="voice">📞 Voice Call</option>
                                <option value="video">📹 Video Call</option>
                                <option value="chat">💬 Text Chat</option>
                            </select>
                            <p x-show="errors.consult_mode" class="text-red-500 text-xs mt-1" x-text="errors.consult_mode"></p>
                        </div>
                    </div>
                </div>

                <!-- CONSENT BLOCK -->
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Consent & Privacy</h3>
                            <p class="text-xs text-gray-600">Please review and accept our policies</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <!-- Informed Consent -->
                        <label class="flex items-start gap-3 p-3 bg-white border border-gray-200 hover:border-purple-300 hover:bg-purple-50 rounded-lg cursor-pointer transition-colors">
                            <input type="checkbox" x-model="formData.informed_consent" required class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                            <span class="text-sm">
                                I have read and agree to the 
                                <a href="#" class="text-purple-600 hover:underline font-semibold">Informed Consent</a> *
                            </span>
                        </label>

                        <!-- Data & Privacy -->
                        <label class="flex items-start gap-3 p-3 bg-white border border-gray-200 hover:border-purple-300 hover:bg-purple-50 rounded-lg cursor-pointer transition-colors">
                            <input type="checkbox" x-model="formData.data_privacy" required class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                            <span class="text-sm">
                                I have read and agree to the 
                                <a href="#" class="text-purple-600 hover:underline font-semibold">Data & Privacy Policy</a> *
                            </span>
                    </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="w-full py-4 px-6 text-base font-semibold text-white rounded-xl transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl hover:scale-[1.02] purple-gradient flex items-center justify-center gap-2"
                    >
                        <svg x-show="isSubmitting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="isSubmitting ? 'Submitting Your Request...' : 'Submit Consultation Request'"></span>
                    </button>
                    
                    <!-- Privacy Assurance -->
                    <div class="mt-5 p-4 bg-gradient-to-r from-purple-50 via-blue-50 to-purple-50 border border-purple-200 rounded-xl">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-purple-800 mb-1 text-sm">Your Privacy & Security Guaranteed</h4>
                                <p class="text-gray-700 text-xs leading-relaxed">
                                    Your personal and health information is encrypted and stored securely following Nigeria Data Protection Regulations (NDPR). We never share your data without explicit consent.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- What Happens Next -->
                    <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-bold text-gray-800 mb-3 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            What Happens Next?
                        </h4>
                        <ol class="space-y-2 text-xs text-gray-600">
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                <span>You'll receive instant confirmation via email and SMS</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                <span>Our team will review and assign an appropriate doctor</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                <span>The doctor will contact you within 24 hours to schedule your consultation</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                <span>After consultation, you'll receive a payment link - pay only after care!</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </form>
        </div>

        <!-- Success Modal -->
        <div x-show="showSuccessModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="showSuccessModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="modal-backdrop fixed inset-0" @click="showSuccessModal = false"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 transform transition-all"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90">
                    
                    <!-- Success Icon -->
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-green-100">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3" x-text="modalTitle"></h3>
                        <p class="text-gray-600 mb-4 leading-relaxed" x-text="modalMessage"></p>
                        
                        <div x-show="consultationReference" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-blue-800 font-semibold mb-1">Your Reference Number:</p>
                            <p class="text-xl font-bold text-blue-900" x-text="consultationReference"></p>
                            <p class="text-xs text-blue-600 mt-2">Please save this reference for your records</p>
                        </div>
                        
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6 text-left">
                            <p class="text-sm text-purple-900 font-semibold mb-2">📧 Check Your Email</p>
                            <p class="text-xs text-purple-700">
                                We've sent a confirmation email with all your booking details and a payment link (if applicable).
                            </p>
                        </div>
                    </div>

                    <!-- Button -->
                    <button @click="showSuccessModal = false"
                            class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg transition-all transform hover:scale-105">
                        Got it, Thanks! ✨
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div x-show="showErrorModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="showErrorModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="modal-backdrop fixed inset-0" @click="showErrorModal = false"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 transform transition-all"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90">
                    
                    <!-- Error Icon -->
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-red-100">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3" x-text="modalTitle"></h3>
                        <p class="text-gray-600 mb-6 leading-relaxed" x-text="modalMessage"></p>
                        
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6 text-left">
                            <p class="text-sm text-orange-900 font-semibold mb-2">💡 Need Help?</p>
                            <p class="text-xs text-orange-700 mb-2">
                                If the problem persists, please contact us:
                            </p>
                            <p class="text-xs text-orange-700">
                                📧 Email: inquiries@doctorontap.com.ng<br>
                                📱 Phone: 08177777122
                            </p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex space-x-3">
                        <button @click="showErrorModal = false"
                                class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-300 transition-all">
                            Close
                        </button>
                        <button @click="showErrorModal = false; window.scrollTo({ top: document.getElementById('consultation-form').offsetTop - 100, behavior: 'smooth' })"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all transform hover:scale-105">
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-purple-900 text-white py-12 mt-16">
        <div class="container mx-auto px-5">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
                <!-- Company Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-8 sm:h-10 md:h-12 w-auto">
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-purple-300">Caring For you, Just Like Family</h3>
                    <p class="text-gray-300 text-sm leading-relaxed mb-4">
                        Speak to a doctor in minutes, hire a caregiver, buy prescribed medication, and get best support for healthcare abroad from anywhere and at anytime.
                    </p>
                    <div class="flex gap-3">
                        <a href="#" class="w-9 h-9 bg-purple-600 hover:bg-purple-700 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-9 h-9 bg-purple-600 hover:bg-purple-700 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-9 h-9 bg-purple-600 hover:bg-purple-700 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                    </div>
            </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-bold mb-4 text-purple-300">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="#consultation-form" class="text-gray-300 hover:text-purple-400 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Book Consultation
                            </a>
                        </li>
                        <li>
                            <a href="#faq" class="text-gray-300 hover:text-purple-400 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                FAQs
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-purple-400 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Privacy Policy
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-gray-300 hover:text-purple-400 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                Terms of Service
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-bold mb-4 text-purple-300">Get In Touch</h3>
                    <div class="space-y-3 text-sm">
                        <a href="mailto:{{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }}" class="flex items-start gap-3 text-gray-300 hover:text-purple-400 transition-colors group">
                            <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-white text-xs uppercase tracking-wide">Email</div>
                                <div class="break-all">{{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }}</div>
                            </div>
                        </a>

                        <a href="tel:08177777122" class="flex items-start gap-3 text-gray-300 hover:text-purple-400 transition-colors group">
                            <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-white text-xs uppercase tracking-wide">Nigeria</div>
                                <div>08177777122</div>
                            </div>
                        </a>

                        <a href="tel:+16178333519" class="flex items-start gap-3 text-gray-300 hover:text-purple-400 transition-colors group">
                            <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-white text-xs uppercase tracking-wide">USA</div>
                                <div>+1 (617) 833-3519</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Office Locations -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10 pt-8 border-t border-gray-700">
                <!-- Nigeria Office -->
                <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-purple-500 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-white mb-1 flex items-center gap-2">
                                🇳🇬 Nigeria Office
                            </h4>
                            <p class="text-gray-300 text-sm leading-relaxed">
                                Suite D21, Plot 228, P.O.W. Mafemi Crescent, Utako, Abuja
                            </p>
                        </div>
                    </div>
                </div>

                <!-- USA Office -->
                <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-purple-500 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-white mb-1 flex items-center gap-2">
                                🇺🇸 USA Office
                            </h4>
                            <p class="text-gray-300 text-sm leading-relaxed">
                                596 Metropolitan Avenue, Hyde Park, Boston, MA 02136
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Map Section -->
            <div class="mb-8">
                <h3 class="text-xl font-bold mb-4 text-center text-purple-300">Find Us - Abuja Office</h3>
                <div class="rounded-xl overflow-hidden shadow-2xl border-2 border-purple-500">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3939.9876543210!2d7.4567890!3d9.0678910!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDQnMDQuNCJOIDfCsDI3JzI0LjQiRQ!5e0!3m2!1sen!2sng!4v1234567890"
                        width="100%" 
                        height="300" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="DoctorOnTap Abuja Office Location">
                    </iframe>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="pt-6 border-t border-gray-700">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-gray-400 text-sm">
                        © 2025 <span class="font-semibold text-purple-300">DoctorOnTap</span>. All rights reserved.
                    </p>
                    <div class="flex items-center gap-4 text-sm text-gray-400">
                        <a href="#" class="hover:text-purple-400 transition-colors">Privacy Policy</a>
                        <span class="text-gray-600">•</span>
                        <a href="#" class="hover:text-purple-400 transition-colors">Terms of Service</a>
                        <span class="text-gray-600">•</span>
                        <a href="#" class="hover:text-purple-400 transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
        </footer>

    <script>
        // Hero Background Image Rotator
        document.addEventListener('DOMContentLoaded', function() {
            const bgImages = document.querySelectorAll('.hero-bg-image');
            let currentIndex = 0;
            
            function rotateBackground() {
                bgImages[currentIndex].classList.remove('active');
                currentIndex = (currentIndex + 1) % bgImages.length;
                bgImages[currentIndex].classList.add('active');
            }
            
            setInterval(rotateBackground, 5000);
        });
    
        // Review Carousel Component
        function reviewCarousel() {
            return {
                currentSlide: 0,
                totalSlides: 3,
                autoSlideInterval: null,
                
                init() {
                    this.startAutoSlide();
                },
                
                nextSlide() {
                    this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                    this.resetAutoSlide();
                },
                
                prevSlide() {
                    this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                    this.resetAutoSlide();
                },
                
                goToSlide(index) {
                    this.currentSlide = index;
                    this.resetAutoSlide();
                },
                
                startAutoSlide() {
                    this.autoSlideInterval = setInterval(() => {
                        this.nextSlide();
                    }, 5000); // Auto-slide every 5 seconds
                },
                
                resetAutoSlide() {
                    clearInterval(this.autoSlideInterval);
                    this.startAutoSlide();
                }
            }
        }
    
        function consultationForm() {
            return {
                formData: {
                    first_name: '',
                    last_name: '',
                    gender: '',
                    age: '',
                    mobile: '',
                    email: '',
                    problem: '',
                    severity: '',
                    emergency_symptoms: [],
                    doctor: '',
                    consult_mode: '',
                    informed_consent: false,
                    data_privacy: false
                },
                uploadedFiles: [],
                errors: {},
                successMessage: '',
                isSubmitting: false,
                showSuccessModal: false,
                showErrorModal: false,
                modalTitle: '',
                modalMessage: '',
                consultationReference: '',

                handleFileUpload(event) {
                    const files = Array.from(event.target.files);
                    this.uploadedFiles = files;
                },

                removeFile(index) {
                    this.uploadedFiles.splice(index, 1);
                    // Clear the input
                    const fileInput = document.getElementById('medical_documents');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                },

                async submitForm() {
                    this.isSubmitting = true;
                    this.errors = {};
                    this.successMessage = '';

                    try {
                        // Use FormData to support file uploads
                        const formData = new FormData();
                        
                        // Append all form fields
                        for (const key in this.formData) {
                            if (Array.isArray(this.formData[key])) {
                                this.formData[key].forEach(value => {
                                    formData.append(`${key}[]`, value);
                                });
                            } else {
                                formData.append(key, this.formData[key]);
                            }
                        }
                        
                        // Append files
                        this.uploadedFiles.forEach(file => {
                            formData.append('medical_documents[]', file);
                        });

                        const response = await fetch('/submit', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Show success modal
                            this.modalTitle = 'Booking Confirmed! 🎉';
                            this.modalMessage = data.message || 'Your consultation has been booked successfully. We will contact you shortly via WhatsApp.';
                            this.consultationReference = data.consultation_reference || '';
                            this.showSuccessModal = true;
                            this.resetForm();
                        } else {
                            if (data.errors) {
                                this.errors = data.errors;
                                window.scrollTo({ top: document.getElementById('consultation-form').offsetTop - 100, behavior: 'smooth' });
                            } else {
                                this.displayErrorModal('Submission Failed', data.message || 'Please check your information and try again.');
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.displayErrorModal('Connection Error', 'An error occurred while submitting your booking. Please check your internet connection and try again.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                displayErrorModal(title, message) {
                    this.modalTitle = title;
                    this.modalMessage = message;
                    this.showErrorModal = true;
                },

                async initializePayment(paymentData) {
                    try {
                        const response = await fetch('/payment/initialize', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(paymentData)
                        });

                        const data = await response.json();

                        if (data.success && data.checkout_url) {
                            // Redirect to Korapay checkout
                            window.location.href = data.checkout_url;
                        } else {
                            this.displayErrorModal('Payment Initialization Failed', data.message || 'Unable to initialize payment. Please try again.');
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Payment Error:', error);
                        this.displayErrorModal('Payment System Error', 'An error occurred with the payment system. Please try again later.');
                        this.isSubmitting = false;
                    }
                },

                resetForm() {
                    this.formData = {
                        first_name: '',
                        last_name: '',
                        gender: '',
                        age: '',
                        mobile: '',
                        email: '',
                        problem: '',
                        severity: '',
                        emergency_symptoms: [],
                        doctor: '',
                        consult_mode: '',
                        informed_consent: false,
                        data_privacy: false
                    };
                    this.uploadedFiles = [];
                    // Clear file input
                    const fileInput = document.getElementById('medical_documents');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                }
            }
        }
    </script>

</body>
</html>
