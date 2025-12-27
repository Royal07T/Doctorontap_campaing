@extends('layouts.patient')

@section('title', 'Consultation Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('patient.consultations') }}" class="text-purple-600 hover:text-purple-800 font-medium">
            ← Back to Consultations
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-lg font-bold text-gray-900 mb-1">{{ $consultation->reference }}</h1>
                <p class="text-xs text-gray-500">Consultation Details</p>
            </div>
            <div class="text-right">
                @if($consultation->status === 'completed')
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Completed</span>
                @elseif($consultation->status === 'pending')
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Pending</span>
                @elseif($consultation->status === 'scheduled')
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">Scheduled</span>
                @elseif($consultation->status === 'cancelled')
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Cancelled</span>
                @else
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ ucfirst($consultation->status) }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Consultation Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Consultation Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Doctor</p>
                @if($consultation->doctor)
                    <div class="flex items-center space-x-2.5">
                        @if($consultation->doctor->photo_url)
                            <img src="{{ $consultation->doctor->photo_url }}" alt="Dr. {{ $consultation->doctor->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-purple-200">
                        @else
                            <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-semibold text-sm border-2 border-purple-200">
                                {{ substr($consultation->doctor->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-xs font-semibold text-gray-900">
                                @php
                                    $name = trim($consultation->doctor->name);
                                    $nameLower = strtolower($name);
                                    $hasDrPrefix = preg_match('/^dr\.?\s*/i', $nameLower);
                                @endphp
                                {{ $hasDrPrefix ? $name : 'Dr. ' . $name }}
                            </p>
                            @if($consultation->doctor->specialization)
                                <p class="text-xs text-gray-500">{{ $consultation->doctor->specialization }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-900">N/A</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Reference</p>
                <p class="text-xs text-gray-900 font-mono">{{ $consultation->reference }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Created Date</p>
                <p class="text-xs text-gray-900">{{ $consultation->created_at->format('M d, Y H:i A') }}</p>
            </div>
            @if($consultation->scheduled_at)
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Scheduled Date & Time</p>
                <p class="text-xs text-gray-900 font-semibold">{{ $consultation->scheduled_at->format('M d, Y H:i A') }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Status</p>
                @if($consultation->status === 'completed')
                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Completed</span>
                @elseif($consultation->status === 'pending')
                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Pending</span>
                @elseif($consultation->status === 'scheduled')
                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-700">Scheduled</span>
                @else
                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ ucfirst($consultation->status) }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Status</p>
                @if($consultation->payment_status === 'paid')
                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">✓ Paid</span>
                @else
                    <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-amber-100 text-amber-700">{{ ucfirst($consultation->payment_status) }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Consultation Mode</p>
                <p class="text-xs text-gray-900">
                    @if($consultation->consult_mode === 'voice')
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Voice Call
                        </span>
                    @elseif($consultation->consult_mode === 'video')
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Video Call
                        </span>
                    @elseif($consultation->consult_mode === 'chat')
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Text Chat
                        </span>
                    @else
                        {{ ucfirst($consultation->consult_mode ?? 'N/A') }}
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Severity</p>
                <p class="text-xs text-gray-900">
                    @if($consultation->severity === 'mild')
                        <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">🟢 Mild</span>
                    @elseif($consultation->severity === 'moderate')
                        <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-amber-100 text-amber-700">🟡 Moderate</span>
                    @elseif($consultation->severity === 'severe')
                        <span class="px-2 py-0.5 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-700">🔴 Severe</span>
                    @else
                        {{ ucfirst($consultation->severity ?? 'N/A') }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Patient Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Patient Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Full Name</p>
                <p class="text-xs text-gray-900">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Age</p>
                <p class="text-xs text-gray-900">{{ $consultation->age ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Gender</p>
                <p class="text-xs text-gray-900">{{ ucfirst($consultation->gender ?? 'N/A') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Email</p>
                <p class="text-xs text-gray-900">{{ $consultation->email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Mobile</p>
                <p class="text-xs text-gray-900">{{ $consultation->mobile ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Medical Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Medical Information</h2>
        <div class="space-y-4">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Problem Description</p>
                <p class="text-xs text-gray-900 leading-relaxed">{{ $consultation->problem ?? 'N/A' }}</p>
            </div>
            
            @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Emergency Symptoms</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($consultation->emergency_symptoms as $symptom)
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ ucfirst(str_replace('_', ' ', $symptom)) }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($consultation->medical_documents && count($consultation->medical_documents) > 0)
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Medical Documents</p>
                <div class="space-y-2">
                    @foreach($consultation->medical_documents as $doc)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-xs text-gray-900">{{ $doc['original_name'] ?? 'Document' }}</span>
                                <span class="text-xs text-gray-500">({{ number_format(($doc['size'] ?? 0) / 1024, 2) }} KB)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

        <!-- Payment Information -->
        @if($consultation->payment)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Payment Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Amount Paid</p>
                        <p class="text-lg font-bold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Date</p>
                        <p class="text-xs text-gray-900">{{ $consultation->payment->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Transaction Reference</p>
                        <p class="text-xs text-gray-900 font-mono">{{ $consultation->payment->transaction_reference ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Method</p>
                        <p class="text-xs text-gray-900">{{ ucfirst($consultation->payment->payment_method ?? 'N/A') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Treatment Plan Section (Only visible if treatment plan exists and payment is paid) -->
        @if($consultation->payment_status === 'paid' && $consultation->hasTreatmentPlan())
            <div id="treatment-plan" x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6" style="scroll-margin-top: 100px;">
                <!-- Treatment Plan Card Header -->
                <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-3 h-3 rounded-full bg-teal-500"></div>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 flex items-center">
                                    <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Treatment Plan
                                </h2>
                                @if($consultation->treatment_plan_created_at)
                                    <p class="text-xs text-gray-500 mt-0.5">Created: {{ $consultation->treatment_plan_created_at->format('M d, Y') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                 :class="{ 'rotate-180': open }" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Treatment Plan Dropdown Content -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     x-cloak
                     class="border-t border-gray-100 bg-gray-50"
                     style="display: none;">
                    <div class="p-5 space-y-5">

                        <!-- Treatment Plan Content -->
                        @if($consultation->treatment_plan)
                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 mb-2 uppercase tracking-wide">Treatment Plan</h3>
                                <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded">
                                    <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->treatment_plan }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Prescribed Medications -->
                        @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                    Prescribed Medications
                                </h3>
                                <div class="space-y-2.5">
                                    @foreach($consultation->prescribed_medications as $medication)
                                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                                <div>
                                                    <h4 class="text-xs font-semibold text-purple-900 mb-1">{{ $medication['name'] ?? 'N/A' }}</h4>
                                                    <p class="text-xs text-purple-700">Dosage: {{ $medication['dosage'] ?? 'N/A' }}</p>
                                                </div>
                                                <div class="text-xs text-purple-700">
                                                    <p><span class="font-medium">Frequency:</span> {{ $medication['frequency'] ?? 'N/A' }}</p>
                                                    <p><span class="font-medium">Duration:</span> {{ $medication['duration'] ?? 'N/A' }}</p>
                                                    @if(isset($medication['instructions']) && !empty($medication['instructions']))
                                                        <p class="mt-1.5"><span class="font-medium">Instructions:</span> {{ $medication['instructions'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Follow-up Instructions -->
                        @if($consultation->follow_up_instructions)
                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 mb-2 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Follow-up Instructions
                                </h3>
                                <div class="bg-orange-50 border-l-4 border-orange-500 p-3 rounded">
                                    <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->follow_up_instructions }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Lifestyle Recommendations -->
                        @if($consultation->lifestyle_recommendations)
                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 mb-2 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    Lifestyle Recommendations
                                </h3>
                                <div class="bg-teal-50 border-l-4 border-teal-500 p-3 rounded">
                                    <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->lifestyle_recommendations }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Additional Notes -->
                        @if($consultation->additional_notes)
                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 mb-2 uppercase tracking-wide flex items-center">
                                    <svg class="w-4 h-4 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Additional Notes
                                </h3>
                                <div class="bg-gray-50 border-l-4 border-gray-500 p-3 rounded">
                                    <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->additional_notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($consultation->status === 'completed' && $consultation->payment_status === 'paid' && !$consultation->hasTreatmentPlan())
            <!-- Treatment Plan Not Available -->
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Treatment Plan Not Available</h3>
                        <p class="mt-2 text-sm text-yellow-700">
                            Your treatment plan has not been created yet. Please check back later or contact support if you have any questions.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($consultation->status === 'completed' && $consultation->requiresPaymentForTreatmentPlan())
            <!-- Payment Required -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Payment Required</h3>
                        <p class="mt-2 text-sm text-blue-700">
                            Payment is required to access your treatment plan. Please complete payment to view your treatment plan.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Actions</h2>
            <div class="space-y-2.5">
                @if($consultation->payment)
                    <a href="{{ route('patient.consultation.receipt', $consultation->id) }}" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-600 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="text-xs font-medium text-gray-900">Download Receipt</span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endif
                
                <a href="{{ route('patient.medical-records') }}" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-600 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-xs font-medium text-gray-900">View Medical Records</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Smooth scroll to treatment plan section and open dropdown if anchor is present
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#treatment-plan') {
            const element = document.getElementById('treatment-plan');
            if (element) {
                setTimeout(function() {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Open the dropdown
                    if (typeof Alpine !== 'undefined') {
                        const component = Alpine.$data(element);
                        if (component && typeof component.open !== 'undefined') {
                            component.open = true;
                        } else {
                            // Fallback: trigger click on button
                            const button = element.querySelector('button');
                            if (button) {
                                button.click();
                            }
                        }
                    } else {
                        // Fallback: trigger click on button
                        const button = element.querySelector('button');
                        if (button) {
                            button.click();
                        }
                    }
                    
                    // Add a highlight effect
                    element.style.transition = 'box-shadow 0.3s ease';
                    element.style.boxShadow = '0 0 0 4px rgba(20, 184, 166, 0.3)';
                    setTimeout(function() {
                        element.style.boxShadow = '';
                    }, 2000);
                }, 100);
            }
        }
    });
</script>
@endpush
@endsection

