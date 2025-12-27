<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consultation Details - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="consultationPage()">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'consultations'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
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
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('admin.consultations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Consultations
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details Card -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Patient Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Patient Information
                            </h2>
                        <div class="flex items-center gap-2">
                            @if($consultation->is_multi_patient_booking && $consultation->booking)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700" title="Multi-Patient Booking">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Multi-Patient
                                </span>
                            @endif
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full font-mono text-xs font-semibold">
                            {{ $consultation->reference }}
                        </span>
                            </div>
                        </div>
                    </div>
                    
                    @if($consultation->is_multi_patient_booking && $consultation->booking)
                    <!-- Multi-Patient Booking Info -->
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="text-xs font-semibold text-blue-900 uppercase tracking-wide mb-2">Multi-Patient Booking Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <span class="text-xs font-medium text-gray-600">Booking Reference:</span>
                                <p class="text-xs text-gray-900 font-mono mt-0.5">{{ $consultation->booking->reference }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-600">Payer:</span>
                                <p class="text-xs text-gray-900 mt-0.5">{{ $consultation->booking->payer_name }}</p>
                                <p class="text-xs text-gray-600">{{ $consultation->booking->payer_email }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-600">Total Patients:</span>
                                <p class="text-xs text-gray-900 mt-0.5">{{ $consultation->booking->bookingPatients->count() }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-600">Total Amount:</span>
                                <p class="text-xs text-gray-900 font-semibold mt-0.5">₦{{ number_format($consultation->booking->total_adjusted_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Full Name</label>
                            <p class="text-sm text-gray-900">{{ $consultation->full_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</label>
                            <p class="text-sm text-gray-900">{{ $consultation->email ?: ($consultation->booking ? $consultation->booking->payer_email : 'N/A') }}</p>
                            @if($consultation->is_multi_patient_booking && !$consultation->email)
                                <p class="text-xs text-blue-600 mt-0.5">Using payer email (patient email not provided)</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Mobile</label>
                            <p class="text-sm text-gray-900">{{ $consultation->mobile }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Age</label>
                            <p class="text-sm text-gray-900">{{ $consultation->age }} years</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Gender</label>
                            <p class="text-sm text-gray-900 capitalize">{{ $consultation->gender }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consultation Mode</label>
                            <p class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $consultation->consult_mode) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Medical Details
                        </h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Problem Description</label>
                            <p class="text-sm text-gray-900 leading-relaxed">{{ $consultation->problem }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Severity</label>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $consultation->severity === 'mild' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $consultation->severity === 'moderate' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $consultation->severity === 'severe' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($consultation->severity) }}
                            </span>
                        </div>

                        @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Emergency Symptoms</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($consultation->emergency_symptoms as $symptom)
                                <span class="px-2 py-0.5 bg-red-50 text-red-700 rounded-lg text-xs border border-red-200">
                                    {{ $symptom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($consultation->medical_documents && count($consultation->medical_documents) > 0)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Medical Documents</label>
                                @if($consultation->doctor)
                                <button 
                                    @click="forwardDocuments()"
                                    :disabled="isForwarding"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                                    <svg x-show="!isForwarding" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <svg x-show="isForwarding" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isForwarding ? 'Forwarding...' : 'Forward to Doctor'"></span>
                                </button>
                                @endif
                            </div>
                            @if($consultation->documents_forwarded_at)
                            <div class="mb-2 p-2 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-xs text-green-700 flex items-center">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Documents forwarded to {{ $consultation->doctor->full_name }} on {{ $consultation->documents_forwarded_at->format('M d, Y g:i A') }}
                                </p>
                            </div>
                            @endif
                            <div class="space-y-2">
                                @foreach($consultation->medical_documents as $document)
                                <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg p-2.5">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-gray-900">{{ $document['original_name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($document['size'] / 1024, 2) }} KB</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('medical-document.download', ['consultation' => $consultation->id, 'filename' => $document['stored_name']]) }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Doctor Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Doctor Information
                            </h2>
                        @if($consultation->doctor)
                            <div class="flex gap-1.5">
                            @if($consultation->status === 'scheduled' || ($consultation->status === 'pending' && $consultation->doctor))
                            <button @click="queryDoctor()" 
                                    :disabled="isQuerying"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                                    <svg x-show="!isQuerying" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                    <svg x-show="isQuerying" class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                    <span x-text="isQuerying ? 'Sending...' : 'Query'"></span>
                            </button>
                            @endif
                            <button @click="showReassignModal = true" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                    Reassign
                            </button>
                        </div>
                        @endif
                        </div>
                    </div>
                    
                    @if($consultation->doctor)
                    <div class="flex items-start space-x-3">
                        @if($consultation->doctor->profile_image)
                        <img src="{{ asset('storage/' . $consultation->doctor->profile_image) }}" 
                             alt="{{ $consultation->doctor->full_name }}"
                             class="w-12 h-12 rounded-full object-cover">
                        @else
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <span class="text-lg text-purple-600 font-bold">
                                {{ substr($consultation->doctor->full_name, 0, 1) }}
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">{{ $consultation->doctor->full_name }}</h3>
                            <p class="text-xs text-gray-600">{{ $consultation->doctor->specialization }}</p>
                            @if($consultation->doctor->email)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $consultation->doctor->email }}</p>
                            @endif
                            @if($consultation->doctor->phone)
                            <p class="text-xs text-gray-500">{{ $consultation->doctor->phone }}</p>
                            @endif
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-gray-600">No specific doctor assigned - Any available doctor</p>
                    <button @click="showReassignModal = true" 
                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Assign Doctor
                    </button>
                    @endif
                </div>

                <!-- Payment Information -->
                @if($consultation->payment || $consultation->payment_status)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Payment Information
                        </h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Amount</label>
                            @if($consultation->payment)
                            <p class="text-sm font-bold text-gray-900">NGN {{ number_format($consultation->payment->amount, 2) }}</p>
                            @else
                                <p class="text-sm font-bold text-gray-500">Not Paid</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Payment Status</label>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $consultation->payment_status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $consultation->payment_status === 'unpaid' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $consultation->payment_status === 'pending' || $consultation->payment_status === 'pending_payment' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                {{ ucfirst($consultation->payment_status ?? 'unpaid') }}
                            </span>
                        </div>
                        @if($consultation->payment && $consultation->payment->transaction_id)
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Transaction ID</label>
                            <p class="text-xs text-gray-900 font-mono">{{ $consultation->payment->transaction_id }}</p>
                        </div>
                        @endif
                        @if($consultation->payment_completed_at)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Payment Completed At</label>
                            <p class="text-xs text-gray-900">{{ $consultation->payment_completed_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @elseif($consultation->payment && $consultation->payment->paid_at)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Paid At</label>
                            <p class="text-xs text-gray-900">{{ $consultation->payment->paid_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @endif
                        @if($consultation->payment && $consultation->payment->reference)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Payment Reference</label>
                            <p class="text-xs text-gray-900 font-mono">{{ $consultation->payment->reference }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Consultation Status
                        </h3>
                    </div>
                    
                    <div class="space-y-3">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Current Status</label>
                        <span class="inline-flex w-full justify-center px-3 py-2 rounded-lg text-xs font-semibold
                            {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $consultation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $consultation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ ucfirst($consultation->status) }}
                        </span>
                        
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mt-3">Change Status</label>
                        <div class="space-y-1.5">
                            @if($consultation->status !== 'pending')
                            <button @click="updateStatus('pending')" :disabled="isUpdating"
                                    class="w-full px-3 py-1.5 text-xs font-semibold bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition disabled:opacity-50">
                                Set to Pending
                            </button>
                            @endif
                            @if($consultation->status !== 'scheduled')
                            <button @click="updateStatus('scheduled')" :disabled="isUpdating"
                                    class="w-full px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                                Set to Scheduled
                            </button>
                            @endif
                            @if($consultation->status !== 'completed')
                            <button @click="updateStatus('completed')" :disabled="isUpdating"
                                    class="w-full px-3 py-1.5 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
                                Set to Completed
                            </button>
                            @endif
                            @if($consultation->status !== 'cancelled')
                            <button @click="updateStatus('cancelled')" :disabled="isUpdating"
                                    class="w-full px-3 py-1.5 text-xs font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50">
                                Set to Cancelled
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Payment Status
                        </h3>
                    </div>
                    
                    <span class="inline-flex w-full justify-center px-3 py-2 rounded-lg text-xs font-semibold mb-3
                        {{ $consultation->payment_status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $consultation->payment_status === 'unpaid' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $consultation->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ ucfirst($consultation->payment_status) }}
                    </span>

                    @if($consultation->payment_request_sent)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 mb-3">
                        <p class="text-xs text-blue-800">
                            <strong>Payment Request Sent</strong><br>
                            {{ $consultation->payment_request_sent_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif

                    @if($consultation->status === 'completed' && $consultation->payment_status !== 'paid')
                    <div class="space-y-2">
                        <!-- Send Payment Request Button -->
                        <button @click="sendPayment()" :disabled="isSending"
                                class="w-full px-3 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition disabled:opacity-50 flex items-center justify-center gap-1.5">
                            <svg x-show="!isSending" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <svg x-show="isSending" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSending ? 'Sending...' : '{{ $consultation->payment_request_sent ? 'Resend Payment Request' : 'Send Payment Request' }}'"></span>
                        </button>

                        <!-- Manual Payment Button -->
                        <div class="relative">
                            <div class="flex items-center justify-center my-1.5">
                                <div class="border-t border-gray-300 flex-grow"></div>
                                <span class="px-2 text-xs text-gray-500 font-medium">OR</span>
                                <div class="border-t border-gray-300 flex-grow"></div>
                            </div>
                            
                            <button @click="showManualPaymentModal = true" type="button"
                                    class="w-full px-3 py-2 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Mark as Paid (Manual)</span>
                            </button>
                            <p class="text-xs text-gray-500 mt-1.5 text-center">For bank transfer, cash, POS, or other offline payments</p>
                        </div>
                    </div>
                    @elseif($consultation->status === 'completed' && $consultation->payment_status === 'paid')
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5">
                        <p class="text-xs text-blue-800 text-center">
                            ✅ <strong>Payment Request Already Sent</strong><br>
                            No action needed. The patient has been notified.
                        </p>
                    </div>
                    @else
                    @if($consultation->status !== 'completed')
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-2.5">
                        <p class="text-xs text-gray-600 text-center">
                            💡 <strong>Note:</strong> Payment request will be available once the consultation status is marked as "Completed"
                        </p>
                    </div>
                    @endif
                    @endif
                </div>

                <!-- Treatment Plan Actions -->
                @if($consultation->hasTreatmentPlan())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Treatment Plan
                        </h3>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-2.5 mb-3">
                        <p class="text-xs text-green-800 flex items-center">
                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong>Treatment Plan Created</strong> on {{ $consultation->treatment_plan_created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    @if($consultation->payment_status === 'paid')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-2.5 mb-3">
                        <p class="text-xs text-green-800 flex items-center">
                            <svg class="w-3 h-3 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong>Payment Confirmed</strong> - Treatment plan will be sent to patient
                        </p>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2.5 mb-3">
                        <p class="text-xs text-yellow-800 flex items-center">
                            <svg class="w-3 h-3 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <strong>Payment Not Confirmed</strong> - Payment request will be sent instead
                        </p>
                    </div>
                    @endif

                    <button @click="forwardTreatmentPlan()" :disabled="isForwardingTP"
                            class="w-full px-3 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition disabled:opacity-50 flex items-center justify-center gap-1.5">
                        <svg x-show="!isForwardingTP" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <svg x-show="isForwardingTP" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isForwardingTP ? 'Sending...' : 'Forward to Patient'"></span>
                    </button>
                    
                    <p class="text-xs text-gray-500 mt-2 text-center">
                        @if($consultation->payment_status === 'paid')
                        <strong>Redundancy Measure:</strong> Will manually send <strong>treatment plan</strong> to patient (payment confirmed). Useful if patient paid before consultation or automatic email was not delivered.
                        @else
                        Will send <strong>payment request</strong> to patient (payment not confirmed - treatment plan will be sent automatically after payment)
                        @endif
                    </p>
                </div>
                @endif

                <!-- Timestamps -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Timestamps
                        </h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created</label>
                            <p class="text-xs text-gray-900">{{ $consultation->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Last Updated</label>
                            <p class="text-xs text-gray-900">{{ $consultation->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @if($consultation->consultation_completed_at)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Completed</label>
                            <p class="text-xs text-gray-900">{{ $consultation->consultation_completed_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div x-show="showMessageModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showMessageModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showMessageModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full"
                     :class="{
                         'bg-green-100': messageType === 'success',
                         'bg-red-100': messageType === 'error',
                         'bg-blue-100': messageType === 'info'
                     }">
                    <svg x-show="messageType === 'success'" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="messageType === 'error'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <svg x-show="messageType === 'info'" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center">
                    <h3 class="text-sm font-bold text-gray-900 mb-1.5" x-text="messageTitle"></h3>
                    <p class="text-xs text-gray-600 mb-4 leading-relaxed" x-text="messageText"></p>
                </div>

                <!-- Button -->
                <button @click="showMessageModal = false"
                        class="w-full px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showConfirmModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showConfirmModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showConfirmModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center mb-4">
                    <h3 class="text-sm font-bold text-gray-900 mb-1.5" x-text="confirmTitle"></h3>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="confirmText"></p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button @click="showConfirmModal = false"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button @click="executeConfirm()"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Payment Modal -->
    <div x-show="showManualPaymentModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showManualPaymentModal = false">
        <!-- Backdrop -->
        <div class="modal-backdrop fixed inset-0" @click="showManualPaymentModal = false"></div>

        <!-- Modal Content -->
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full relative z-10 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-5 py-3 rounded-t-xl">
                <h3 class="text-sm font-bold text-white">Mark Payment as Paid</h3>
                <p class="text-xs text-green-100 mt-0.5">Record offline/manual payment</p>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-3">
                <!-- Patient Info -->
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="text-xs text-gray-600">Patient</p>
                    <p class="text-xs font-semibold text-gray-900 mt-0.5">{{ $consultation->full_name }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">{{ $consultation->email }}</p>
                    @if($consultation->doctor)
                    <p class="text-xs text-gray-600 mt-1.5">Fee: <span class="font-bold text-green-600">₦{{ number_format($consultation->doctor->effective_consultation_fee, 2) }}</span></p>
                    @endif
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Payment Method *</label>
                    <select x-model="paymentMethod" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 transition">
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="POS">POS</option>
                        <option value="USSD">USSD</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Payment Reference -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Payment Reference (Optional)</label>
                    <input type="text" 
                           x-model="paymentReference"
                           placeholder="e.g., TXN123456, Receipt #789"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 transition">
                    <p class="text-xs text-gray-500 mt-0.5">Transaction ID, receipt number, or any reference</p>
                </div>

                <!-- Admin Notes -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Admin Notes (Optional)</label>
                    <textarea x-model="adminNotes"
                              rows="3"
                              placeholder="e.g., Patient paid via bank transfer on 2024-11-24. Confirmed by phone..."
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 resize-none transition"></textarea>
                    <p class="text-xs text-gray-500 mt-0.5">Internal note about this payment</p>
                </div>

                <!-- Warning -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex">
                        <svg class="w-4 h-4 text-yellow-600 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="text-xs text-yellow-800">
                            <p class="font-semibold">Important:</p>
                            <p class="mt-0.5">This will:</p>
                            <ul class="list-disc ml-4 mt-1 space-y-0.5">
                                <li>Mark consultation as PAID</li>
                                <li>Unlock treatment plan (if exists)</li>
                                <li>Send treatment plan email to patient</li>
                                <li>Record payment in system</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="px-5 pb-5">
                <div class="flex gap-2">
                    <button @click="showManualPaymentModal = false; paymentMethod = 'Bank Transfer'; paymentReference = ''; adminNotes = ''"
                            :disabled="isMarkingPaid"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition disabled:opacity-50">
                        Cancel
                    </button>
                    <button @click="markPaymentAsPaid()"
                            :disabled="isMarkingPaid"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 flex items-center justify-center gap-1.5">
                        <svg x-show="!isMarkingPaid" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg x-show="isMarkingPaid" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isMarkingPaid ? 'Processing...' : 'Confirm & Mark as Paid'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reassign Doctor Modal -->
    <div x-show="showReassignModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showReassignModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showReassignModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center mb-4">
                    <h3 class="text-sm font-bold text-gray-900 mb-1.5">Reassign Doctor</h3>
                    <p class="text-xs text-gray-600">
                        Current: <span class="font-semibold">{{ $consultation->doctor ? $consultation->doctor->full_name : 'No Doctor' }}</span>
                    </p>
                </div>

                <!-- Doctor Selection -->
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Select New Doctor</label>
                    <select x-model="selectedDoctorId" 
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-200 bg-white transition">
                        <option value="">-- Select a doctor --</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->full_name }} - {{ $doctor->specialization ?? 'General' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button @click="showReassignModal = false; selectedDoctorId = ''"
                            :disabled="isReassigning"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition disabled:opacity-50">
                        Cancel
                    </button>
                    <button @click="doReassignDoctor()"
                            :disabled="isReassigning || !selectedDoctorId"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition disabled:opacity-50 flex items-center justify-center gap-1.5">
                        <svg x-show="!isReassigning" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <svg x-show="isReassigning" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isReassigning ? 'Reassigning...' : 'Reassign'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function consultationPage() {
            return {
                pageLoading: false,
                sidebarOpen: false,
                showMessageModal: false,
                messageType: 'success',
                messageTitle: '',
                messageText: '',
                showConfirmModal: false,
                confirmTitle: '',
                confirmText: '',
                confirmCallback: null,
                isUpdating: false,
                isSending: false,
                isForwarding: false,
                isForwardingTP: false,
                showManualPaymentModal: false,
                paymentMethod: 'Bank Transfer',
                paymentReference: '',
                adminNotes: '',
                isMarkingPaid: false,
                showReassignModal: false,
                selectedDoctorId: '',
                isReassigning: false,
                isQuerying: false,
                
                showMessage(type, title, text) {
                    this.messageType = type;
                    this.messageTitle = title;
                    this.messageText = text;
                    this.showMessageModal = true;
                },
                
                showConfirm(title, text, callback) {
                    this.confirmTitle = title;
                    this.confirmText = text;
                    this.confirmCallback = callback;
                    this.showConfirmModal = true;
                },
                
                executeConfirm() {
                    this.showConfirmModal = false;
                    if (this.confirmCallback && typeof this.confirmCallback === 'function') {
                        this.confirmCallback();
                    }
                    this.confirmCallback = null;
                },
                
                async doUpdateStatus(newStatus) {
                    this.isUpdating = true;
                    try {
                        const response = await fetch('/admin/consultation/{{ $consultation->id }}/status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage('success', 'Success!', 'Status updated successfully');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to update status');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'Error updating status. Please try again.');
                    } finally {
                        this.isUpdating = false;
                    }
                },
                
                updateStatus(newStatus) {
                    this.showConfirm('Confirm Status Change', 'Are you sure you want to change the consultation status to ' + newStatus + '?', () => this.doUpdateStatus(newStatus));
                },
                
                async doSendPayment() {
                    this.isSending = true;
                    try {
                        const response = await fetch('/admin/consultation/{{ $consultation->id }}/send-payment', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage('success', 'Success!', data.message);
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to send payment request');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'Error sending payment request. Please try again.');
                    } finally {
                        this.isSending = false;
                    }
                },
                
                sendPayment() {
                    this.showConfirm('Send Payment Request', 'Send payment request email to {{ $consultation->email }}?', () => this.doSendPayment());
                },
                
                async markPaymentAsPaid() {
                    // Validate
                    if (!this.paymentMethod) {
                        this.showMessage('error', 'Error', 'Please select a payment method');
                        return;
                    }
                    
                    this.isMarkingPaid = true;
                    try {
                        const response = await fetch('/admin/consultation/{{ $consultation->id }}/mark-payment-paid', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                payment_method: this.paymentMethod,
                                payment_reference: this.paymentReference,
                                admin_notes: this.adminNotes
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showManualPaymentModal = false;
                            this.showMessage('success', 'Success!', data.message);
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to mark payment as paid');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'Error marking payment as paid. Please try again.');
                    } finally {
                        this.isMarkingPaid = false;
                    }
                },
                
                async doForwardDocuments() {
                    this.isForwarding = true;
                    try {
                        const response = await fetch('/admin/consultations/{{ $consultation->id }}/forward-documents', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage('success', 'Success!', 'Medical documents have been forwarded to the doctor successfully!');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to forward documents');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'An error occurred while forwarding documents. Please try again.');
                    } finally {
                        this.isForwarding = false;
                    }
                },
                
                forwardDocuments() {
                    this.showConfirm('Forward Documents', 'Forward medical documents to the doctor?', () => this.doForwardDocuments());
                },
                
                async doForwardTreatmentPlan() {
                    this.isForwardingTP = true;
                    try {
                        const response = await fetch('/admin/consultation/{{ $consultation->id }}/forward-treatment-plan', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage('success', 'Success!', data.message || 'Email has been sent to the patient successfully!');
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to forward email');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'An error occurred while sending the treatment plan. Please try again.');
                    } finally {
                        this.isForwardingTP = false;
                    }
                },
                
                forwardTreatmentPlan() {
                    @php
                        $recipientEmail = $consultation->email ?: ($consultation->patient && $consultation->patient->email ? $consultation->patient->email : ($consultation->booking ? $consultation->booking->payer_email : 'the patient'));
                        if ($consultation->payment_status === 'paid') {
                            $message = 'Send treatment plan to ' . $recipientEmail . '? (Payment confirmed)';
                        } else {
                            $message = 'Payment not confirmed. Send payment request to ' . $recipientEmail . ' instead? Treatment plan will be sent automatically after payment.';
                        }
                    @endphp
                    this.showConfirm('Forward to Patient', '{{ $message }}', () => this.doForwardTreatmentPlan());
                },
                
                async queryDoctor() {
                    this.showConfirm('Query Doctor', 'Send urgent delay query notification to Dr. {{ $consultation->doctor ? $consultation->doctor->full_name : "Doctor" }}? This will notify them that they are late for the appointment.', () => this.doQueryDoctor());
                },
                
                async doQueryDoctor() {
                    this.isQuerying = true;
                    try {
                        const response = await fetch('/admin/consultation/{{ $consultation->id }}/query-doctor', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.showMessage('success', 'Success!', data.message || 'Urgent delay query notification sent to doctor successfully');
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to send delay query notification');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'An error occurred while sending the delay query. Please try again.');
                    } finally {
                        this.isQuerying = false;
                    }
                },
                
                async doReassignDoctor() {
                    if (!this.selectedDoctorId) {
                        this.showMessage('error', 'Error', 'Please select a doctor');
                        return;
                    }
                    
                    this.isReassigning = true;
                    try {
                        const response = await fetch('/admin/consultation/{{ $consultation->id }}/reassign-doctor', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ doctor_id: this.selectedDoctorId })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.showReassignModal = false;
                            this.selectedDoctorId = '';
                            this.showMessage('success', 'Success!', data.message || 'Doctor reassigned successfully');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to reassign doctor');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'An error occurred while reassigning doctor. Please try again.');
                    } finally {
                        this.isReassigning = false;
                    }
                }
            }
        }
    </script>
            </main>
        </div>
    </div>
    @include('components.custom-alert-modal')
    @include('admin.shared.preloader')
</body>
</html>

