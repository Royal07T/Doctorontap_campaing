<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consultation Details - Doctor Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
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

            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::guard('doctor')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">Dr. {{ Auth::guard('doctor')->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::guard('doctor')->user()->specialization ?? 'Doctor' }}</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('doctor.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('doctor.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>My Consultations</span>
                </a>

                <a href="{{ route('doctor.bank-accounts') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Bank Accounts</span>
                </a>

                <a href="{{ route('doctor.payment-history') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Payment History</span>
                </a>

                <a href="{{ route('doctor.profile') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>

                <a href="{{ route('doctor.availability') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Availability</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>View Website</span>
                </a>

                <form method="POST" action="{{ route('doctor.logout') }}">
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
                            <h1 class="text-xl font-bold text-white">Consultation Details</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-6xl mx-auto">
                    <!-- Back Button -->
                    <div class="mb-6">
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Consultations
                        </a>
                    </div>

                    <!-- Header Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">{{ $consultation->reference }}</h1>
                                <p class="text-gray-600 mt-2">Consultation Details</p>
                            </div>
                            <div class="text-right space-y-2">
                                <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full 
                                    @if($consultation->status === 'completed') bg-green-100 text-green-800
                                    @elseif($consultation->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-800
                                    @elseif($consultation->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($consultation->status) }}
                                </span>
                                @if($consultation->status === 'completed')
                                    <div class="mt-2">
                                        <a href="{{ route('doctor.consultations') }}?open={{ $consultation->id }}" 
                                           class="inline-block px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-medium text-sm">
                                            {{ $consultation->hasTreatmentPlan() ? '📝 Edit Treatment Plan' : '➕ Fill Treatment Plan' }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Patient Information -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4">Patient Information</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Full Name</label>
                                        <p class="text-lg text-gray-900">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                                        <p class="text-lg text-gray-900">{{ $consultation->email }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Mobile</label>
                                        <p class="text-lg text-gray-900">{{ $consultation->mobile }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Age</label>
                                        <p class="text-lg text-gray-900">{{ $consultation->age }} years</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Gender</label>
                                        <p class="text-lg text-gray-900 capitalize">{{ $consultation->gender }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Consultation Mode</label>
                                        <p class="text-lg text-gray-900 capitalize">{{ str_replace('_', ' ', $consultation->consult_mode ?? 'N/A') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Details -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4">Medical Details</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Presenting Complaint</label>
                                        <p class="text-gray-900 whitespace-pre-line">{{ $consultation->problem ?? 'N/A' }}</p>
                                    </div>
                                    @if($consultation->symptoms)
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Symptoms</label>
                                        <p class="text-gray-900 whitespace-pre-line">{{ $consultation->symptoms }}</p>
                                    </div>
                                    @endif
                                    @if($consultation->severity)
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-600 mb-1">Severity</label>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($consultation->severity === 'severe') bg-red-100 text-red-800
                                            @elseif($consultation->severity === 'moderate') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst($consultation->severity) }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Treatment Plan (if exists) -->
                            @if($consultation->hasTreatmentPlan())
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                        <svg class="w-6 h-6 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Treatment Plan
                                    </h2>
                                </div>

                                @if($consultation->presenting_complaint)
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Presenting Complaint</label>
                                    <p class="text-gray-900 whitespace-pre-line">{{ $consultation->presenting_complaint }}</p>
                                </div>
                                @endif

                                @if($consultation->history_of_complaint)
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">History of Complaint</label>
                                    <p class="text-gray-900 whitespace-pre-line">{{ $consultation->history_of_complaint }}</p>
                                </div>
                                @endif

                                @if($consultation->diagnosis)
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Diagnosis</label>
                                    <p class="text-gray-900 whitespace-pre-line">{{ $consultation->diagnosis }}</p>
                                </div>
                                @endif

                                @if($consultation->treatment_plan)
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Treatment Plan</label>
                                    <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded">
                                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->treatment_plan }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-600 mb-2">Prescribed Medications</label>
                                    <div class="space-y-2">
                                        @foreach($consultation->prescribed_medications as $medication)
                                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                                            <p class="font-semibold text-gray-900">{{ $medication['name'] ?? 'N/A' }}</p>
                                            <p class="text-sm text-gray-600">Dosage: {{ $medication['dosage'] ?? 'N/A' }}</p>
                                            <p class="text-sm text-gray-600">Frequency: {{ $medication['frequency'] ?? 'N/A' }}</p>
                                            @if(isset($medication['duration']))
                                            <p class="text-sm text-gray-600">Duration: {{ $medication['duration'] }}</p>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @if($consultation->follow_up_instructions)
                                <div class="mb-4">
                                    <label class="block text-sm font-semibold text-gray-600 mb-1">Follow-up Instructions</label>
                                    <p class="text-gray-900 whitespace-pre-line">{{ $consultation->follow_up_instructions }}</p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Consultation Info -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-4">Consultation Info</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Date</label>
                                        <p class="text-sm font-medium text-gray-900">{{ $consultation->created_at->format('M d, Y H:i A') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Payment Status</label>
                                        @if($consultation->payment_status === 'paid')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                ✓ Paid
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ ucfirst($consultation->payment_status ?? 'Unpaid') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($consultation->canvasser)
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Canvasser</label>
                                        <p class="text-sm font-medium text-gray-900">{{ $consultation->canvasser->name }}</p>
                                    </div>
                                    @endif
                                    @if($consultation->nurse)
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Nurse</label>
                                        <p class="text-sm font-medium text-gray-900">{{ $consultation->nurse->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Payment Info -->
                            @if($consultation->payment)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-4">Payment Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Amount</label>
                                        <p class="text-lg font-bold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Payment Date</label>
                                        <p class="text-sm font-medium text-gray-900">{{ $consultation->payment->created_at->format('M d, Y H:i A') }}</p>
                                    </div>
                                    @if($consultation->payment->transaction_reference)
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Transaction Reference</label>
                                        <p class="text-sm font-medium text-gray-900 font-mono">{{ $consultation->payment->transaction_reference }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    @include('components.custom-alert-modal')
</body>
</html>
