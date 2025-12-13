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
                    <p class="text-sm text-gray-600 mb-1">Doctor</p>
                    <p class="font-medium text-gray-800">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</p>
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

        <!-- Medical Notes (Only visible if consultation is completed) -->
        @if($consultation->status === 'completed')
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Medical Privacy Notice</h3>
                        <p class="mt-2 text-sm text-blue-700">
                            Your detailed medical notes and treatment plan are available in the Medical Records section for your privacy and security.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('patient.medical-records') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                View Medical Records →
                            </a>
                        </div>
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
@endsection

