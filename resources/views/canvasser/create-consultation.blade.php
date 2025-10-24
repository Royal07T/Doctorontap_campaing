<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Consultation - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Sidebar Header -->
            <div class="purple-gradient p-5 flex items-center justify-between">
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
            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::guard('canvasser')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ Auth::guard('canvasser')->user()->name }}</p>
                        <p class="text-xs text-gray-500">Canvasser</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('canvasser.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('canvasser.patients') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>My Patients</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>View Website</span>
                </a>

                <form method="POST" action="{{ route('canvasser.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
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
                            <h1 class="text-xl font-bold text-white">Create Consultation</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('canvasser.patients') }}" class="text-white hover:text-purple-200 text-sm font-medium">
                            ← Back to Patients
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Patient Info Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Patient Information</h2>
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                            {{ $patient->has_consulted ? 'Has Consulted' : 'New Patient' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-semibold text-gray-900">{{ $patient->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-semibold text-gray-900">{{ $patient->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-semibold text-gray-900">{{ $patient->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Gender</p>
                            <p class="font-semibold text-gray-900">{{ ucfirst($patient->gender) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Age</p>
                            <p class="font-semibold text-gray-900">{{ $patient->age }} years</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Consultations</p>
                            <p class="font-semibold text-gray-900">{{ $patient->consultations_count }}</p>
                        </div>
                    </div>
                </div>

                <!-- Consultation Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Create New Consultation</h2>
                            <p class="text-gray-600 mt-1">Fill in the consultation details for {{ $patient->name }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('canvasser.patients.consultation.store', $patient->id) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <!-- Problem Description -->
                        <div>
                            <label for="problem" class="block text-sm font-semibold text-gray-700 mb-2">
                                Problem Description <span class="text-red-500">*</span>
                            </label>
                            <textarea id="problem" name="problem" rows="4" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
                                      placeholder="Describe the patient's medical problem or symptoms..."></textarea>
                        </div>

                        <!-- Medical Documents -->
                        <div>
                            <label for="medical_documents" class="block text-sm font-semibold text-gray-700 mb-2">
                                Medical Documents (Optional)
                            </label>
                            <input type="file" id="medical_documents" name="medical_documents[]" multiple
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                            <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max 5MB each)</p>
                        </div>

                        <!-- Severity Level -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Severity Level <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-purple-50">
                                    <input type="radio" name="severity" value="mild" class="text-purple-600 focus:ring-purple-500" required>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Mild</div>
                                        <div class="text-xs text-gray-500">Minor symptoms, not urgent</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-purple-50">
                                    <input type="radio" name="severity" value="moderate" class="text-purple-600 focus:ring-purple-500" required>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Moderate</div>
                                        <div class="text-xs text-gray-500">Noticeable symptoms, needs attention</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-purple-50">
                                    <input type="radio" name="severity" value="severe" class="text-purple-600 focus:ring-purple-500" required>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Severe</div>
                                        <div class="text-xs text-gray-500">Serious symptoms, urgent care needed</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Emergency Symptoms -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Emergency Symptoms (Check all that apply)
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="emergency_symptoms[]" value="chest_pain" class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Chest Pain</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="emergency_symptoms[]" value="difficulty_breathing" class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Difficulty Breathing</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="emergency_symptoms[]" value="severe_headache" class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Severe Headache</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="emergency_symptoms[]" value="loss_of_consciousness" class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Loss of Consciousness</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="emergency_symptoms[]" value="severe_bleeding" class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Severe Bleeding</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="emergency_symptoms[]" value="high_fever" class="text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">High Fever (>39°C)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Doctor Selection -->
                        <div>
                            <label for="doctor" class="block text-sm font-semibold text-gray-700 mb-2">
                                Preferred Doctor (Optional)
                            </label>
                            <select id="doctor" name="doctor"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                                <option value="">Let us assign the best available doctor</option>
                                @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}">
                                    Dr. {{ $doctor->name }} - {{ $doctor->specialization }} 
                                    (₦{{ number_format($doctor->consultation_fee, 0) }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Consultation Mode -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Consultation Mode <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-purple-50">
                                    <input type="radio" name="consult_mode" value="voice" class="text-purple-600 focus:ring-purple-500" required>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Voice Call</div>
                                        <div class="text-xs text-gray-500">Audio consultation</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-purple-50">
                                    <input type="radio" name="consult_mode" value="video" class="text-purple-600 focus:ring-purple-500" required>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Video Call</div>
                                        <div class="text-xs text-gray-500">Video consultation</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-purple-50">
                                    <input type="radio" name="consult_mode" value="chat" class="text-purple-600 focus:ring-purple-500" required>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">Chat</div>
                                        <div class="text-xs text-gray-500">Text-based consultation</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('canvasser.patients') }}" 
                               class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                                Create Consultation
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
