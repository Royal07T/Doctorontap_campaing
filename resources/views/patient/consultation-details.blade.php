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
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $consultation->reference }}</h1>
                    <p class="text-gray-600 mt-2">Consultation Details</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full 
                        @if($consultation->status === 'completed') bg-green-100 text-green-800
                        @elseif($consultation->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-800
                        @elseif($consultation->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($consultation->status) }}
                    </span>
                </div>
            </div>
        </div>

    <!-- Consultation Information -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Consultation Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-600 mb-2">Doctor</p>
                    @if($consultation->doctor)
                        <div class="flex items-center space-x-3">
                            @if($consultation->doctor->photo_url)
                                <img src="{{ $consultation->doctor->photo_url }}" alt="Dr. {{ $consultation->doctor->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-purple-200 shadow-md">
                            @else
                                <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold border-2 border-purple-200 shadow-md">
                                    {{ substr($consultation->doctor->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-800">
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
                        <p class="font-medium text-gray-800">N/A</p>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Date</p>
                    <p class="font-medium text-gray-800">{{ $consultation->created_at->format('M d, Y H:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Reference</p>
                    <p class="font-medium text-gray-800">{{ $consultation->reference }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Payment Status</p>
                    @if($consultation->payment_status === 'paid')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            ✓ Paid
                        </span>
                    @else
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ ucfirst($consultation->payment_status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        @if($consultation->payment)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Amount Paid</p>
                        <p class="text-2xl font-bold text-gray-800">₦{{ number_format($consultation->payment->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Payment Date</p>
                        <p class="font-medium text-gray-800">{{ $consultation->payment->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Transaction Reference</p>
                        <p class="font-medium text-gray-800">{{ $consultation->payment->transaction_reference ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Payment Method</p>
                        <p class="font-medium text-gray-800">{{ ucfirst($consultation->payment->payment_method ?? 'N/A') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Treatment Plan Section (Only visible if treatment plan exists and payment is paid) -->
        @if($consultation->payment_status === 'paid' && $consultation->hasTreatmentPlan())
            <div id="treatment-plan" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" style="scroll-margin-top: 100px;">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <svg class="w-6 h-6 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Treatment Plan
                    </h2>
                    @if($consultation->treatment_plan_created_at)
                        <span class="text-xs text-gray-500">Created: {{ $consultation->treatment_plan_created_at->format('M d, Y') }}</span>
                    @endif
                </div>

                <!-- Treatment Plan Content -->
                @if($consultation->treatment_plan)
                    <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded mb-4">
                        <div class="prose max-w-none">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->treatment_plan }}</p>
                        </div>
                    </div>
                @endif

                <!-- Prescribed Medications -->
                @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            Prescribed Medications
                        </h3>
                        <div class="space-y-3">
                            @foreach($consultation->prescribed_medications as $medication)
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <h4 class="font-semibold text-purple-900">{{ $medication['name'] ?? 'N/A' }}</h4>
                                            <p class="text-sm text-purple-700">Dosage: {{ $medication['dosage'] ?? 'N/A' }}</p>
                                        </div>
                                        <div class="text-sm text-purple-700">
                                            <p><span class="font-medium">Frequency:</span> {{ $medication['frequency'] ?? 'N/A' }}</p>
                                            <p><span class="font-medium">Duration:</span> {{ $medication['duration'] ?? 'N/A' }}</p>
                                            @if(isset($medication['instructions']) && !empty($medication['instructions']))
                                                <p class="mt-2"><span class="font-medium">Instructions:</span> {{ $medication['instructions'] }}</p>
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
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Follow-up Instructions
                        </h3>
                        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->follow_up_instructions }}</p>
                        </div>
                    </div>
                @endif

                <!-- Lifestyle Recommendations -->
                @if($consultation->lifestyle_recommendations)
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            Lifestyle Recommendations
                        </h3>
                        <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->lifestyle_recommendations }}</p>
                        </div>
                    </div>
                @endif

                <!-- Additional Notes -->
                @if($consultation->additional_notes)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Additional Notes
                        </h3>
                        <div class="bg-gray-50 border-l-4 border-gray-500 p-4 rounded">
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $consultation->additional_notes }}</p>
                        </div>
                    </div>
                @endif
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
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Actions</h2>
            <div class="space-y-3">
                @if($consultation->payment)
                    <a href="#" class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium text-gray-800">Download Receipt</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @endif
                
                <a href="{{ route('patient.medical-records') }}" class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="font-medium text-gray-800">View Medical Records</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Smooth scroll to treatment plan section if anchor is present
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#treatment-plan') {
            const element = document.getElementById('treatment-plan');
            if (element) {
                setTimeout(function() {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

