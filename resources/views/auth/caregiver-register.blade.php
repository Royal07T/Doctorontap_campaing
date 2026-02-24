<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caregiver Registration - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .progress-step { transition: all 0.3s ease; }
        .progress-line { transition: width 0.5s ease; }
        [x-cloak] { display: none !important; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('[data-section]');
            const progressSteps = document.querySelectorAll('[data-progress-step]');
            const progressBar = document.getElementById('progress-bar');
            let currentStep = 0;
            function updateProgress(step) {
                currentStep = step;
                const percentage = ((step + 1) / 4) * 100;
                progressBar.style.width = percentage + '%';
                const progressText = document.getElementById('progress-text');
                if (progressText) progressText.textContent = 'Step ' + (step + 1) + ' of 4';
                progressSteps.forEach(function(stepEl, index) {
                    const circle = stepEl.querySelector('.step-circle');
                    const label = stepEl.querySelector('.step-label');
                    if (index < step) {
                        circle.className = 'step-circle w-8 h-8 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-lg transform scale-110';
                        circle.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                        if (label) label.className = 'step-label text-xs font-semibold text-white mt-2 hidden sm:block';
                    } else if (index === step) {
                        circle.className = 'step-circle w-8 h-8 rounded-full flex items-center justify-center bg-white text-purple-600 font-bold shadow-lg ring-4 ring-white/50 transform scale-110';
                        circle.textContent = index + 1;
                        if (label) label.className = 'step-label text-xs font-semibold text-white mt-2 hidden sm:block';
                    } else {
                        circle.className = 'step-circle w-8 h-8 rounded-full flex items-center justify-center bg-white/20 text-white/70 font-bold border-2 border-white/30';
                        circle.textContent = index + 1;
                        if (label) label.className = 'step-label text-xs font-medium text-white/80 mt-2 hidden sm:block';
                    }
                });
            }
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const sectionIndex = parseInt(entry.target.dataset.section);
                        updateProgress(sectionIndex);
                    }
                });
            }, { root: null, rootMargin: '-50% 0px -50% 0px', threshold: 0 });
            sections.forEach(function(s) { observer.observe(s); });
            updateProgress(0);
            document.querySelectorAll('input, select, textarea').forEach(function(input) {
                input.addEventListener('focus', function() {
                    const section = this.closest('[data-section]');
                    if (section) updateProgress(parseInt(section.dataset.section));
                });
            });
        });
        function togglePasswordVisibility(inputId, buttonId) {
            var el = document.getElementById(inputId);
            var open = document.getElementById(buttonId + '-open');
            var closed = document.getElementById(buttonId + '-closed');
            if (el.type === 'password') {
                el.type = 'text';
                if (open) open.classList.add('hidden');
                if (closed) closed.classList.remove('hidden');
            } else {
                el.type = 'password';
                if (open) open.classList.remove('hidden');
                if (closed) closed.classList.add('hidden');
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            var stateSelect = document.getElementById('state_id');
            var citySelect = document.getElementById('city_id');
            var oldCityId = @json(old('city_id'));
            if (stateSelect && citySelect) {
                stateSelect.addEventListener('change', function() {
                    var stateId = this.value;
                    citySelect.innerHTML = '<option value="">Loading cities...</option>';
                    citySelect.disabled = true;
                    if (!stateId) {
                        citySelect.innerHTML = '<option value="">Select state first</option>';
                        citySelect.disabled = false;
                        return;
                    }
                    fetch('{{ url("caregiver/cities") }}/' + stateId)
                        .then(function(r) { return r.json(); })
                        .then(function(cities) {
                            citySelect.innerHTML = '<option value="">Select your city</option>';
                            cities.forEach(function(c) {
                                var opt = document.createElement('option');
                                opt.value = c.id;
                                opt.textContent = c.name;
                                if (oldCityId && c.id == oldCityId) opt.selected = true;
                                citySelect.appendChild(opt);
                            });
                            citySelect.disabled = false;
                        })
                        .catch(function() {
                            citySelect.innerHTML = '<option value="">Error loading cities</option>';
                            citySelect.disabled = false;
                        });
                });
                if (stateSelect.value) stateSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</head>
<body class="bg-gradient-to-br from-purple-50 via-white to-purple-50 min-h-screen" x-data="{ isSubmitting: false }">
    <!-- Header -->
    <div class="bg-purple-600 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-10 w-auto">
                </div>
                <a href="{{ route('care_giver.login') }}" class="text-sm text-white hover:text-purple-100 font-medium transition-colors">
                    Already registered? <span class="underline">Sign in</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="sticky top-0 z-50 bg-gradient-to-r from-purple-600 via-purple-500 to-purple-600 border-b border-purple-700 shadow-lg">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="relative mb-8">
                <div class="absolute top-4 left-0 w-full h-0.5 bg-white/20"></div>
                <div id="progress-bar" class="absolute top-4 left-0 h-0.5 bg-gradient-to-r from-green-400 via-white to-white transition-all duration-500 ease-out shadow-lg" style="width: 25%;"></div>
                <div class="relative flex justify-between">
                    <div data-progress-step="0" class="flex flex-col items-center progress-step">
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg ring-4 ring-purple-200 transform scale-110">1</div>
                        <span class="step-label text-xs font-semibold text-white mt-2 hidden sm:block">Personal Info</span>
                    </div>
                    <div data-progress-step="1" class="flex flex-col items-center progress-step relative">
                        <div class="step-line absolute top-4 right-full w-full h-0.5 bg-gray-300"></div>
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">2</div>
                        <span class="step-label text-xs font-medium text-white/80 mt-2 hidden sm:block">Professional</span>
                    </div>
                    <div data-progress-step="2" class="flex flex-col items-center progress-step relative">
                        <div class="step-line absolute top-4 right-full w-full h-0.5 bg-gray-300"></div>
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">3</div>
                        <span class="step-label text-xs font-medium text-white/80 mt-2 hidden sm:block">Address & Docs</span>
                    </div>
                    <div data-progress-step="3" class="flex flex-col items-center progress-step relative">
                        <div class="step-line absolute top-4 right-full w-full h-0.5 bg-gray-300"></div>
                        <div class="step-circle w-8 h-8 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 font-bold">4</div>
                        <span class="step-label text-xs font-medium text-white/80 mt-2 hidden sm:block">Security</span>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm text-white/90">
                    <span id="progress-text" class="font-semibold text-white">Step 1 of 4</span>
                    <span class="hidden sm:inline"> - Complete all sections to register</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <div class="text-center mb-10">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">Welcome to DoctorOnTap!</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Join our network of caregivers. Complete the registration form below to apply. Once approved, you can support patients and their families.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-purple-100 overflow-hidden">
            <form method="POST" action="{{ route('caregiver.register.submit') }}" enctype="multipart/form-data" class="divide-y divide-gray-100" @submit="isSubmitting = true">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-8 mt-8 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
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
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Personal Information</h3>
                            <p class="text-sm text-gray-500">Let's start with your basic details</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required minlength="2" maxlength="255" placeholder="e.g., Jane"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('first_name') border-red-500 @enderror">
                            @error('first_name')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required minlength="2" maxlength="255" placeholder="e.g., Doe"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('last_name') border-red-500 @enderror">
                            @error('last_name')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="e.g., jane@example.com"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('email') border-red-500 @enderror">
                            @error('email')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required maxlength="20" placeholder="e.g., 08012345678"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('phone') border-red-500 @enderror">
                            @error('phone')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('date_of_birth') border-red-500 @enderror">
                            @error('date_of_birth')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">Gender <span class="text-red-500">*</span></label>
                            <select id="gender" name="gender" required class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('gender') border-red-500 @enderror">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Professional Details -->
                <div class="p-6 lg:p-8 bg-purple-50/30" data-section="1">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Professional Details</h3>
                            <p class="text-sm text-gray-500">Your qualifications and experience</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">Role Applied For <span class="text-red-500">*</span></label>
                            <select id="role" name="role" required class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('role') border-red-500 @enderror">
                                <option value="">Select role</option>
                                <option value="Registered Nurse" {{ old('role') == 'Registered Nurse' ? 'selected' : '' }}>Registered Nurse</option>
                                <option value="Auxiliary Nurse" {{ old('role') == 'Auxiliary Nurse' ? 'selected' : '' }}>Auxiliary Nurse</option>
                                <option value="Caregiver" {{ old('role') == 'Caregiver' ? 'selected' : '' }}>Caregiver</option>
                                <option value="Medical Assistant" {{ old('role') == 'Medical Assistant' ? 'selected' : '' }}>Medical Assistant</option>
                            </select>
                            @error('role')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="experience_years" class="block text-sm font-semibold text-gray-700 mb-2">Years of Experience <span class="text-red-500">*</span></label>
                            <input type="number" id="experience_years" name="experience_years" value="{{ old('experience_years') }}" required min="0" placeholder="e.g., 5"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('experience_years') border-red-500 @enderror">
                            @error('experience_years')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="license_number" class="block text-sm font-semibold text-gray-700 mb-2">License Number</label>
                            <input type="text" id="license_number" name="license_number" value="{{ old('license_number') }}" placeholder="Optional"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('license_number') border-red-500 @enderror">
                            @error('license_number')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">Short Bio / Summary</label>
                            <textarea id="bio" name="bio" rows="3" placeholder="Briefly describe your experience and skills..."
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all resize-none @error('bio') border-red-500 @enderror">{{ old('bio') }}</textarea>
                            @error('bio')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Address & Documents -->
                <div class="p-6 lg:p-8" data-section="2">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Address & Documents</h3>
                            <p class="text-sm text-gray-500">Where you're based and optional documents</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">Street Address <span class="text-red-500">*</span></label>
                            <input type="text" id="address" name="address" value="{{ old('address') }}" required placeholder="Street, area, landmark"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('address') border-red-500 @enderror">
                            @error('address')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="state_id" class="block text-sm font-semibold text-gray-700 mb-2">State <span class="text-red-500">*</span></label>
                            <select id="state_id" name="state_id" required class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('state_id') border-red-500 @enderror">
                                <option value="">Select your state</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}" {{ old('state_id') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                @endforeach
                            </select>
                            @error('state_id')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="city_id" class="block text-sm font-semibold text-gray-700 mb-2">City <span class="text-red-500">*</span></label>
                            <select id="city_id" name="city_id" required disabled class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('city_id') border-red-500 @enderror">
                                <option value="">Select state first</option>
                            </select>
                            @error('city_id')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="profile_photo" class="block text-sm font-semibold text-gray-700 mb-2">Profile Photo</label>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="mt-1.5 text-xs text-gray-500">Optional. Max 2MB. JPG, PNG.</p>
                        </div>
                        <div>
                            <label for="cv_file" class="block text-sm font-semibold text-gray-700 mb-2">CV (PDF/DOC)</label>
                            <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="mt-1.5 text-xs text-gray-500">Optional. Max 5MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Account Security -->
                <div class="p-6 lg:p-8 bg-gray-50" data-section="3">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Account Security</h3>
                            <p class="text-sm text-gray-500">Create a secure password for your account</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required minlength="8" placeholder="Minimum 8 characters" autocomplete="new-password"
                                    class="w-full px-4 py-3 pr-12 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all @error('password') border-red-500 @enderror">
                                <button type="button" onclick="togglePasswordVisibility('password', 'password-eye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1" aria-label="Toggle password">
                                    <svg id="password-eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg id="password-eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('password')<p class="mt-2 text-xs text-red-500 flex items-center"><svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Re-enter password" autocomplete="new-password"
                                    class="w-full px-4 py-3 pr-12 text-sm border border-gray-300 rounded-xl focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-all">
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'password-confirmation-eye')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1" aria-label="Toggle password">
                                    <svg id="password-confirmation-eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg id="password-confirmation-eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="p-6 lg:p-8 bg-gray-50">
                    <div class="text-center mb-4">
                        <p class="text-sm font-medium text-gray-700">Almost there! Review your information and submit.</p>
                        <p class="text-xs text-gray-500 mt-1">We'll review your application and get back to you after approval.</p>
                    </div>
                    <button type="submit" :disabled="isSubmitting"
                        class="w-full px-8 py-4 bg-purple-600 text-white font-bold text-lg rounded-xl hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isSubmitting">Submit Application →</span>
                        <span x-show="isSubmitting" x-cloak class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-8 text-center text-sm text-gray-600">
            <p>Once we receive your application, we'll review and approve your account so you can start supporting patients. 💜</p>
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

    <x-system-preloader x-show="isSubmitting" message="Submitting your application..." />
</body>
</html>
