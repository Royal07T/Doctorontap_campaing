<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consultation Details - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            <a href="{{ route('admin.consultations') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Consultations
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details Card -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Patient Information -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Patient Information</h2>
                        <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded-lg font-mono text-sm font-bold">
                            {{ $consultation->reference }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Full Name</label>
                            <p class="text-lg text-gray-900">{{ $consultation->full_name }}</p>
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
                            <p class="text-lg text-gray-900 capitalize">{{ str_replace('_', ' ', $consultation->consult_mode) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Details -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Medical Details</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Problem Description</label>
                            <p class="text-base text-gray-900 leading-relaxed">{{ $consultation->problem }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Severity</label>
                            <span class="inline-flex px-4 py-2 rounded-full text-sm font-semibold
                                {{ $consultation->severity === 'mild' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $consultation->severity === 'moderate' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $consultation->severity === 'severe' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($consultation->severity) }}
                            </span>
                        </div>

                        @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-2">Emergency Symptoms</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($consultation->emergency_symptoms as $symptom)
                                <span class="px-3 py-1 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
                                    {{ $symptom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($consultation->medical_documents && count($consultation->medical_documents) > 0)
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-semibold text-gray-600">📎 Medical Documents</label>
                                @if($consultation->doctor)
                                <button 
                                    @click="forwardDocuments()"
                                    :disabled="isForwarding"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-md disabled:opacity-50">
                                    <svg x-show="!isForwarding" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <svg x-show="isForwarding" class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isForwarding ? 'Forwarding...' : 'Forward to Doctor'"></span>
                                </button>
                                @endif
                            </div>
                            @if($consultation->documents_forwarded_at)
                            <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-sm text-green-700 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Documents forwarded to {{ $consultation->doctor->full_name }} on {{ $consultation->documents_forwarded_at->format('M d, Y g:i A') }}
                                </p>
                            </div>
                            @endif
                            <div class="space-y-2">
                                @foreach($consultation->medical_documents as $document)
                                <div class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $document['original_name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($document['size'] / 1024, 2) }} KB</p>
                                        </div>
                                    </div>
                                    <a href="{{ asset('storage/' . $document['path']) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Doctor Information</h2>
                    
                    @if($consultation->doctor)
                    <div class="flex items-start space-x-4">
                        @if($consultation->doctor->profile_image)
                        <img src="{{ asset('storage/' . $consultation->doctor->profile_image) }}" 
                             alt="{{ $consultation->doctor->full_name }}"
                             class="w-20 h-20 rounded-full object-cover">
                        @else
                        <div class="w-20 h-20 rounded-full bg-purple-100 flex items-center justify-center">
                            <span class="text-3xl text-purple-600 font-bold">
                                {{ substr($consultation->doctor->full_name, 0, 1) }}
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">{{ $consultation->doctor->full_name }}</h3>
                            <p class="text-gray-600">{{ $consultation->doctor->specialization }}</p>
                            @if($consultation->doctor->email)
                            <p class="text-sm text-gray-500 mt-1">📧 {{ $consultation->doctor->email }}</p>
                            @endif
                            @if($consultation->doctor->phone)
                            <p class="text-sm text-gray-500">📱 {{ $consultation->doctor->phone }}</p>
                            @endif
                        </div>
                    </div>
                    @else
                    <p class="text-gray-600">No specific doctor assigned - Any available doctor</p>
                    @endif
                </div>

                <!-- Payment Information -->
                @if($consultation->payment)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Payment Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Amount</label>
                            <p class="text-xl font-bold text-gray-900">NGN {{ number_format($consultation->payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Status</label>
                            <span class="inline-flex px-4 py-2 rounded-full text-sm font-semibold
                                {{ $consultation->payment->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $consultation->payment->status === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $consultation->payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ ucfirst($consultation->payment->status) }}
                            </span>
                        </div>
                        @if($consultation->payment->transaction_id)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Transaction ID</label>
                            <p class="text-base text-gray-900 font-mono">{{ $consultation->payment->transaction_id }}</p>
                        </div>
                        @endif
                        @if($consultation->payment->paid_at)
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Paid At</label>
                            <p class="text-base text-gray-900">{{ $consultation->payment->paid_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Consultation Status</h3>
                    
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-600">Current Status</label>
                        <span class="inline-flex w-full justify-center px-4 py-3 rounded-lg text-sm font-semibold
                            {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $consultation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $consultation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($consultation->status) }}
                        </span>
                        
                        <label class="block text-sm font-semibold text-gray-600 mt-4">Change Status</label>
                        <div class="space-y-2">
                            @if($consultation->status !== 'pending')
                            <button @click="updateStatus('pending')" :disabled="isUpdating"
                                    class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors disabled:opacity-50">
                                Set to Pending
                            </button>
                            @endif
                            @if($consultation->status !== 'scheduled')
                            <button @click="updateStatus('scheduled')" :disabled="isUpdating"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                                Set to Scheduled
                            </button>
                            @endif
                            @if($consultation->status !== 'completed')
                            <button @click="updateStatus('completed')" :disabled="isUpdating"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                                Set to Completed
                            </button>
                            @endif
                            @if($consultation->status !== 'cancelled')
                            <button @click="updateStatus('cancelled')" :disabled="isUpdating"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                                Set to Cancelled
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Actions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Payment Status</h3>
                    
                    <span class="inline-flex w-full justify-center px-4 py-3 rounded-lg text-sm font-semibold mb-4
                        {{ $consultation->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $consultation->payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $consultation->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                        {{ ucfirst($consultation->payment_status) }}
                    </span>

                    @if($consultation->payment_request_sent)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-blue-800">
                            <strong>Payment Request Sent</strong><br>
                            {{ $consultation->payment_request_sent_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif

                    @if($consultation->status === 'completed' && $consultation->payment_status !== 'paid')
                    <button @click="sendPayment()" :disabled="isSending"
                            class="w-full px-4 py-3 purple-gradient text-white rounded-lg hover:shadow-lg transition-all disabled:opacity-50 font-semibold flex items-center justify-center">
                        <svg x-show="!isSending" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <svg x-show="isSending" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSending ? 'Sending...' : '{{ $consultation->payment_request_sent ? 'Resend Payment Request' : 'Send Payment Request' }}'"></span>
                    </button>
                    @elseif($consultation->status === 'completed' && $consultation->payment_status === 'paid')
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800 text-center">
                            ✅ <strong>Payment Request Already Sent</strong><br>
                            No action needed. The patient has been notified.
                        </p>
                    </div>
                    @else
                    @if($consultation->status !== 'completed')
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 text-center">
                            💡 <strong>Note:</strong> Payment request will be available once the consultation status is marked as "Completed"
                        </p>
                    </div>
                    @endif
                    @endif
                </div>

                <!-- Treatment Plan Actions -->
                @if($consultation->hasTreatmentPlan())
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Treatment Plan</h3>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                        <p class="text-sm text-green-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong>Treatment Plan Created</strong> on {{ $consultation->treatment_plan_created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    <button @click="forwardTreatmentPlan()" :disabled="isForwardingTP"
                            class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 hover:shadow-lg transition-all disabled:opacity-50 font-semibold flex items-center justify-center">
                        <svg x-show="!isForwardingTP" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <svg x-show="isForwardingTP" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isForwardingTP ? 'Sending...' : 'Forward Treatment Plan to Patient'"></span>
                    </button>
                    
                    <p class="text-xs text-gray-500 mt-3 text-center">
                        Click to manually send the treatment plan to the patient's email as a redundancy measure
                    </p>
                </div>
                @endif

                <!-- Timestamps -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Timestamps</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Created</label>
                            <p class="text-sm text-gray-900">{{ $consultation->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Last Updated</label>
                            <p class="text-sm text-gray-900">{{ $consultation->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @if($consultation->consultation_completed_at)
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Completed</label>
                            <p class="text-sm text-gray-900">{{ $consultation->consultation_completed_at->format('M d, Y h:i A') }}</p>
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
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full"
                     :class="{
                         'bg-green-100': messageType === 'success',
                         'bg-red-100': messageType === 'error',
                         'bg-blue-100': messageType === 'info'
                     }">
                    <svg x-show="messageType === 'success'" class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="messageType === 'error'" class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <svg x-show="messageType === 'info'" class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="messageTitle"></h3>
                    <p class="text-gray-600 mb-6" x-text="messageText"></p>
                </div>

                <!-- Button -->
                <button @click="showMessageModal = false"
                        class="w-full px-6 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg transition-all">
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
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="confirmTitle"></h3>
                    <p class="text-gray-600" x-text="confirmText"></p>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-3">
                    <button @click="showConfirmModal = false"
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button @click="executeConfirm()"
                            class="flex-1 px-6 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                        Confirm
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
                            this.showMessage('success', 'Success!', 'Treatment plan has been sent to the patient\'s email successfully!');
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to forward treatment plan');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Error', 'An error occurred while sending the treatment plan. Please try again.');
                    } finally {
                        this.isForwardingTP = false;
                    }
                },
                
                forwardTreatmentPlan() {
                    this.showConfirm('Forward Treatment Plan', 'Send treatment plan to {{ $consultation->email }}?', () => this.doForwardTreatmentPlan());
                }
            }
        }
    </script>
            </main>
        </div>
    </div>
</body>
</html>

