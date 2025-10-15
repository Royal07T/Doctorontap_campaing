<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Walk-In Vital Check - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="walkInVitals()">
    <!-- Top Navigation Bar -->
    <nav class="purple-gradient shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto">
                    <span class="ml-3 text-white text-lg font-bold">Walk-In Vital Check</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white text-sm">{{ Auth::guard('nurse')->user()->name }}</span>
                    <a href="{{ route('nurse.dashboard') }}" 
                       class="text-white hover:text-purple-200 text-sm font-medium px-3 py-2 rounded-md hover:bg-purple-700 transition-colors">
                        ← Back to Dashboard
                    </a>
                    <form method="POST" action="{{ route('nurse.logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="text-white hover:text-red-200 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-600 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="bg-gray-100 p-6 min-h-screen">
                <!-- Success Message -->
                <div x-show="successMessage" 
                     x-transition
                     class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span x-text="successMessage"></span>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" 
                     x-transition
                     class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                </div>

                <!-- Walk-In Vital Check Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Quick Vital Check</h2>
                        <p class="text-gray-600">For walk-in patients at events (e.g., fun fairs, health camps)</p>
                    </div>

                    <form @submit.prevent="submitForm">
                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-purple-500">
                                📋 Personal Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           x-model="formData.first_name" 
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           x-model="formData.last_name" 
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           x-model="formData.email" 
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="example@email.com">
                                </div>

                                <!-- Phone Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" 
                                           x-model="formData.phone" 
                                           required
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 08012345678">
                                </div>

                                <!-- Age -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Age <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" 
                                           x-model="formData.age" 
                                           required
                                           min="1"
                                           max="150"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 35">
                                </div>

                                <!-- Gender -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Gender <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   x-model="formData.gender" 
                                                   value="male" 
                                                   required
                                                   class="mr-2 text-purple-600 focus:ring-purple-500">
                                            <span>Male</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   x-model="formData.gender" 
                                                   value="female" 
                                                   required
                                                   class="mr-2 text-purple-600 focus:ring-purple-500">
                                            <span>Female</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vital Signs Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-purple-500">
                                ❤️ Vital Signs
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Blood Pressure -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Blood Pressure
                                    </label>
                                    <input type="text" 
                                           x-model="formData.blood_pressure" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 120/80">
                                    <p class="text-xs text-gray-500 mt-1">Format: systolic/diastolic (mmHg)</p>
                                </div>

                                <!-- Heart Rate -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Heart Rate
                                    </label>
                                    <input type="number" 
                                           x-model="formData.heart_rate" 
                                           min="0"
                                           max="300"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 72">
                                    <p class="text-xs text-gray-500 mt-1">Beats per minute (bpm)</p>
                                </div>

                                <!-- Oxygen Saturation -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Oxygen Saturation
                                    </label>
                                    <input type="number" 
                                           x-model="formData.oxygen_saturation" 
                                           min="0"
                                           max="100"
                                           step="0.1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 98">
                                    <p class="text-xs text-gray-500 mt-1">Percentage (%)</p>
                                </div>

                                <!-- Temperature -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Temperature
                                    </label>
                                    <input type="number" 
                                           x-model="formData.temperature" 
                                           min="30"
                                           max="45"
                                           step="0.1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 37.0">
                                    <p class="text-xs text-gray-500 mt-1">Celsius (°C)</p>
                                </div>

                                <!-- Respiratory Rate -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Respiratory Rate
                                    </label>
                                    <input type="number" 
                                           x-model="formData.respiratory_rate" 
                                           min="0"
                                           max="100"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 16">
                                    <p class="text-xs text-gray-500 mt-1">Breaths per minute</p>
                                </div>

                                <!-- Blood Sugar -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Blood Sugar
                                    </label>
                                    <input type="number" 
                                           x-model="formData.blood_sugar" 
                                           min="0"
                                           max="1000"
                                           step="0.1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 95">
                                    <p class="text-xs text-gray-500 mt-1">mg/dL</p>
                                </div>

                                <!-- Height -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Height
                                    </label>
                                    <input type="number" 
                                           x-model="formData.height" 
                                           min="0"
                                           max="300"
                                           step="0.1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 170">
                                    <p class="text-xs text-gray-500 mt-1">Centimeters (cm)</p>
                                </div>

                                <!-- Weight -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Weight
                                    </label>
                                    <input type="number" 
                                           x-model="formData.weight" 
                                           min="0"
                                           max="500"
                                           step="0.1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="e.g., 70">
                                    <p class="text-xs text-gray-500 mt-1">Kilograms (kg)</p>
                                </div>

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes/Observations
                                    </label>
                                    <textarea x-model="formData.notes" 
                                              rows="3"
                                              maxlength="1000"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                              placeholder="Any observations or comments..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('nurse.dashboard') }}" 
                               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    :disabled="loading"
                                    class="px-6 py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                                <span x-show="!loading">📧 Send Report to Email</span>
                                <span x-show="loading">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
    </main>

    <script>
        function walkInVitals() {
            return {
                sidebarOpen: false,
                loading: false,
                successMessage: '',
                errorMessage: '',
                formData: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                    age: '',
                    gender: '',
                    blood_pressure: '',
                    heart_rate: '',
                    oxygen_saturation: '',
                    temperature: '',
                    respiratory_rate: '',
                    blood_sugar: '',
                    height: '',
                    weight: '',
                    notes: ''
                },

                async submitForm() {
                    this.loading = true;
                    this.successMessage = '';
                    this.errorMessage = '';

                    try {
                        const response = await fetch('{{ route("nurse.walk-in-vitals.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.successMessage = data.message;
                            this.resetForm();
                            
                            // Scroll to top to see success message
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            // Auto-hide success message after 10 seconds
                            setTimeout(() => {
                                this.successMessage = '';
                            }, 10000);
                        } else {
                            this.errorMessage = data.message || 'Failed to send report. Please try again.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error. Please check your connection and try again.';
                        console.error('Error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                resetForm() {
                    this.formData = {
                        first_name: '',
                        last_name: '',
                        email: '',
                        phone: '',
                        age: '',
                        gender: '',
                        blood_pressure: '',
                        heart_rate: '',
                        oxygen_saturation: '',
                        temperature: '',
                        respiratory_rate: '',
                        blood_sugar: '',
                        height: '',
                        weight: '',
                        notes: ''
                    };
                }
            }
        }
    </script>
</body>
</html>

