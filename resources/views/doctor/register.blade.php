<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor Registration - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
        }
        .progress-step {
            transition: all 0.3s ease;
        }
        .progress-line {
            transition: width 0.5s ease;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('[data-section]');
            const progressSteps = document.querySelectorAll('[data-progress-step]');
            const progressBar = document.getElementById('progress-bar');
            
            let currentStep = 0;
            
            function updateProgress(step) {
                currentStep = step;
                
                // Update progress bar width
                const percentage = ((step + 1) / 4) * 100;
                progressBar.style.width = percentage + '%';
                
                // Update progress text
                const progressText = document.getElementById('progress-text');
                if (progressText) {
                    progressText.textContent = `Step ${step + 1} of 4`;
                }
                
                // Update step indicators
                progressSteps.forEach((stepEl, index) => {
                    const circle = stepEl.querySelector('.step-circle');
                    const label = stepEl.querySelector('.step-label');
                    const line = stepEl.querySelector('.step-line');
                    
                    if (index < step) {
                        // Completed
                        circle.className = 'step-circle w-8 h-8 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-lg transform scale-110';
                        circle.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                        if (label) label.className = 'step-label text-xs font-semibold text-green-600 mt-2';
                        if (line) line.className = 'step-line absolute top-4 left-full w-full h-0.5 bg-green-500';
                    } else if (index === step) {
                        // Current
                        circle.className = 'step-circle w-8 h-8 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg ring-4 ring-purple-200 transform scale-110';
                        circle.textContent = index + 1;
                        if (label) label.className = 'step-label text-xs font-semibold text-purple-600 mt-2';
                        if (line && index < 3) line.className = 'step-line absolute top-4 left-full w-full h-0.5 bg-gray-300';
                    } else {
                        // Upcoming
                        circle.className = 'step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold';
                        circle.textContent = index + 1;
                        if (label) label.className = 'step-label text-xs font-medium text-gray-400 mt-2';
                        if (line && index < 3) line.className = 'step-line absolute top-4 left-full w-full h-0.5 bg-gray-300';
                    }
                });
            }
            
            // Intersection Observer to track which section is in view
            const observerOptions = {
                root: null,
                rootMargin: '-50% 0px -50% 0px',
                threshold: 0
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const sectionIndex = parseInt(entry.target.dataset.section);
                        updateProgress(sectionIndex);
                    }
                });
            }, observerOptions);
            
            sections.forEach(section => observer.observe(section));
            
            // Initialize first step
            updateProgress(0);
            
            // Also update on input focus
            const allInputs = document.querySelectorAll('input, select, textarea');
            allInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    const section = this.closest('[data-section]');
                    if (section) {
                        const sectionIndex = parseInt(section.dataset.section);
                        updateProgress(sectionIndex);
                    }
                });
            });
        });
    </script>
</head>
<body class="bg-gradient-to-br from-purple-50 via-white to-purple-50 min-h-screen">
    <!-- Header -->
    <div class="bg-purple-600 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-10 w-auto">
                </div>
                <a href="{{ route('doctor.login') }}" class="text-sm text-white hover:text-purple-100 font-medium transition-colors">
                    Already registered? <span class="underline">Sign in</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <!-- Progress Bar Line -->
            <div class="relative mb-8">
                <div class="absolute top-4 left-0 w-full h-0.5 bg-gray-200"></div>
                <div id="progress-bar" class="absolute top-4 left-0 h-0.5 bg-gradient-to-r from-green-500 via-purple-500 to-purple-600 transition-all duration-500 ease-out" style="width: 25%;"></div>
                
                <!-- Steps -->
                <div class="relative flex justify-between">
                    <!-- Step 1 -->
                    <div data-progress-step="0" class="flex flex-col items-center progress-step">
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg ring-4 ring-purple-200 transform scale-110">
                            1
                        </div>
                        <span class="step-label text-xs font-semibold text-purple-600 mt-2 hidden sm:block">Personal Info</span>
                    </div>
                    
                    <!-- Step 2 -->
                    <div data-progress-step="1" class="flex flex-col items-center progress-step relative">
                        <div class="step-line absolute top-4 right-full w-full h-0.5 bg-gray-300"></div>
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">
                            2
                        </div>
                        <span class="step-label text-xs font-medium text-gray-400 mt-2 hidden sm:block">Professional</span>
                    </div>
                    
                    <!-- Step 3 -->
                    <div data-progress-step="2" class="flex flex-col items-center progress-step relative">
                        <div class="step-line absolute top-4 right-full w-full h-0.5 bg-gray-300"></div>
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">
                            3
                        </div>
                        <span class="step-label text-xs font-medium text-gray-400 mt-2 hidden sm:block">Documents</span>
                    </div>
                    
                    <!-- Step 4 -->
                    <div data-progress-step="3" class="flex flex-col items-center progress-step relative">
                        <div class="step-line absolute top-4 right-full w-full h-0.5 bg-gray-300"></div>
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">
                            4
                        </div>
                        <span class="step-label text-xs font-medium text-gray-400 mt-2 hidden sm:block">Security</span>
                    </div>
                </div>
            </div>
            
            <!-- Progress Text -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    <span id="progress-text" class="font-semibold text-purple-600">Step 1 of 4</span>
                    <span class="hidden sm:inline"> - Complete all sections to register</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <!-- Welcome Section -->
        <div class="text-center mb-10">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">
                Welcome to DoctorOnTap! 🎉
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                We are super excited to have you join our network. Complete the registration form below to get started with providing excellent healthcare services.
            </p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-purple-100 overflow-hidden">
            <form method="POST" action="{{ route('doctor.register.post') }}" enctype="multipart/form-data" class="divide-y divide-gray-100">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-8 mt-8 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-semibold text-red-800">Please correct the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Section 1: Personal Information -->
                <div class="p-6 lg:p-8" data-section="0">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Personal Information</h3>
                            <p class="text-sm text-gray-500">Let's start with your basic details</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                A. First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   required
                                   minlength="2"
                                   maxlength="255"
                                   pattern="[a-zA-Z\s'\-]+"
                                   placeholder="e.g., Glory"
                                   title="First name should only contain letters, spaces, hyphens, or apostrophes"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                B. Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   required
                                   minlength="2"
                                   maxlength="255"
                                   pattern="[a-zA-Z\s'\-]+"
                                   placeholder="e.g., Iniabasi"
                                   title="Last name should only contain letters, spaces, hyphens, or apostrophes"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">
                                C. Gender <span class="text-red-500">*</span>
                            </label>
                            <select id="gender"
                                    name="gender"
                                    required
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('gender') border-red-500 @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                D. Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   required
                                   minlength="10"
                                   maxlength="20"
                                   pattern="[0-9+\s\-\(\)]+"
                                   placeholder="e.g., 09067726381"
                                   title="Please enter a valid phone number (at least 10 digits)"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                E. Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   maxlength="255"
                                   placeholder="e.g., gloryiniabasi2000@gmail.com"
                                   title="Please enter a valid email address"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Professional Details -->
                <div class="p-6 lg:p-8 bg-purple-50/30" data-section="1">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Professional Details</h3>
                            <p class="text-sm text-gray-500">Tell us about your medical expertise</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Specialization -->
                        <div class="md:col-span-2">
                            <label for="specialization" class="block text-sm font-semibold text-gray-700 mb-2">
                                F. Specialty <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="specialization"
                                   name="specialization"
                                   value="{{ old('specialization') }}"
                                   required
                                   placeholder="e.g., General Doctor, ENT Specialist"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('specialization') border-red-500 @enderror">
                            @error('specialization')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Years of Experience -->
                        <div>
                            <label for="experience" class="block text-sm font-semibold text-gray-700 mb-2">
                                G. Years of Experience <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="experience"
                                   name="experience"
                                   value="{{ old('experience') }}"
                                   required
                                   placeholder="e.g., 2 years"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('experience') border-red-500 @enderror">
                            @error('experience')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Consultation Fee -->
                        <div>
                            <label for="consultation_fee" class="block text-sm font-semibold text-gray-700 mb-2">
                                H. Consultation Fee (₦) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   id="consultation_fee"
                                   name="consultation_fee"
                                   value="{{ old('consultation_fee') }}"
                                   required
                                   step="0.01"
                                   min="0"
                                   placeholder="e.g., 5000"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('consultation_fee') border-red-500 @enderror">
                            <p class="mt-1.5 text-xs text-gray-500">Your suggested fee. Admin may adjust during approval.</p>
                            @error('consultation_fee')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Present Place of Work -->
                        <div class="md:col-span-2">
                            <label for="place_of_work" class="block text-sm font-semibold text-gray-700 mb-2">
                                I. Present Place of Work <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="place_of_work"
                                   name="place_of_work"
                                   value="{{ old('place_of_work') }}"
                                   required
                                   placeholder="e.g., Capitol hill hospital, Warri, Delta state"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('place_of_work') border-red-500 @enderror">
                            @error('place_of_work')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                                J. Your Role <span class="text-red-500">*</span>
                            </label>
                            <select id="role"
                                    name="role"
                                    required
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('role') border-red-500 @enderror">
                                <option value="">Select Role</option>
                                <option value="clinical" {{ old('role') == 'clinical' ? 'selected' : '' }}>Clinical</option>
                                <option value="non-clinical" {{ old('role') == 'non-clinical' ? 'selected' : '' }}>Non-Clinical</option>
                            </select>
                            @error('role')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div>
                            <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                                L. Location <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="location"
                                   name="location"
                                   value="{{ old('location') }}"
                                   required
                                   placeholder="e.g., Warri, Delta state"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('location') border-red-500 @enderror">
                            @error('location')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- MDCN License -->
                        <div class="md:col-span-2">
                            <label for="mdcn_license_current" class="block text-sm font-semibold text-gray-700 mb-2">
                                M. Are you up to date with your MDCN license? <span class="text-red-500">*</span>
                            </label>
                            <select id="mdcn_license_current"
                                    name="mdcn_license_current"
                                    required
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('mdcn_license_current') border-red-500 @enderror">
                                <option value="">Select Status</option>
                                <option value="yes" {{ old('mdcn_license_current') == 'yes' ? 'selected' : '' }}>✓ Yes, up to date</option>
                                <option value="processing" {{ old('mdcn_license_current') == 'processing' ? 'selected' : '' }}>⏳ Still processing/Awaiting update</option>
                                <option value="no" {{ old('mdcn_license_current') == 'no' ? 'selected' : '' }}>✗ No</option>
                            </select>
                            <p class="mt-1.5 text-xs text-purple-600 font-medium">⚠️ To practice on DoctorOnTap, your MDCN license must be up to date for KYC purposes.</p>
                            @error('mdcn_license_current')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Languages Spoken -->
                        <div>
                            <label for="languages" class="block text-sm font-semibold text-gray-700 mb-2">
                                N. Languages Spoken <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="languages"
                                   name="languages"
                                   value="{{ old('languages') }}"
                                   required
                                   placeholder="e.g., English"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('languages') border-red-500 @enderror">
                            @error('languages')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Days of Availability -->
                        <div>
                            <label for="days_of_availability" class="block text-sm font-semibold text-gray-700 mb-2">
                                O. Days of Availability <span class="text-red-500">*</span>
                            </label>
                            <textarea id="days_of_availability"
                                      name="days_of_availability"
                                      required
                                      rows="3"
                                      placeholder="e.g., Two weeks of day shifts and one week of night shift, in that order."
                                      class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all resize-none @error('days_of_availability') border-red-500 @enderror">{{ old('days_of_availability') }}</textarea>
                            <p class="mt-1.5 text-xs text-gray-500">Describe your availability schedule</p>
                            @error('days_of_availability')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Documentation -->
                <div class="p-6 lg:p-8" data-section="2">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Documentation (Optional)</h3>
                            <p class="text-sm text-gray-500">Upload your credentials for verification</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-5 mb-5">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 mb-2">📌 Important Notes:</h4>
                                <ul class="text-xs text-blue-800 space-y-1.5">
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span>Upload your <strong>MDCN license</strong> or any available medical credentials</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span><strong>For KYC purposes:</strong> Your MDCN license must be up to date to practice on DoctorOnTap</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span><strong>Security Notice:</strong> We do NOT accept submissions by email, WhatsApp, or social media</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span>If your license is still processing, you can upload it later after admin approval</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span><strong>Accepted formats:</strong> PDF, JPG, PNG (Max 5MB)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-purple-400 transition-all">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <label for="certificate" class="block text-sm font-semibold text-gray-700 mb-1">
                            Upload MDCN License or Medical Certificate
                        </label>
                        <p class="text-xs text-gray-500 mb-3">Click to browse or drag and drop</p>
                        <input type="file"
                               id="certificate"
                               name="certificate"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                        @error('certificate')
                            <p class="mt-2 text-xs text-red-500 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Section 4: Account Security -->
                <div class="p-6 lg:p-8 bg-gray-50" data-section="3">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Account Security</h3>
                            <p class="text-sm text-gray-500">Create a secure password for your account</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   placeholder="Minimum 8 characters (uppercase, lowercase, number)"
                                   title="Password must be at least 8 characters and contain uppercase, lowercase, and number"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="mt-2 text-xs text-red-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   minlength="8"
                                   placeholder="Re-enter password"
                                   title="Please confirm your password"
                                   class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="p-6 lg:p-8 bg-gray-50">
                    <div class="text-center mb-4">
                        <p class="text-sm font-medium text-gray-700">✨ Almost there! Review your information and submit.</p>
                        <p class="text-xs text-gray-500 mt-1">We'll send you a verification email after registration.</p>
                    </div>
                    <button type="submit"
                            class="w-full px-8 py-4 bg-purple-600 text-white font-bold text-lg rounded-xl hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 shadow-lg hover:shadow-xl transition-all">
                        Complete Registration →
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Note -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>Once we receive your information, we'll review and approve your account to start sending patients your way. 🩺</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-purple-600 border-t border-purple-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center">
            <p class="text-xs text-white">
                &copy; {{ date('Y') }} DoctorOnTap. All rights reserved. | 
                <a href="#" class="hover:text-purple-200 transition-colors">Privacy Policy</a> | 
                <a href="#" class="hover:text-purple-200 transition-colors">Terms of Service</a>
            </p>
        </div>
    </div>
</body>
</html>
