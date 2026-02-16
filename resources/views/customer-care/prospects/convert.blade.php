@extends('layouts.customer-care')

@section('title', 'Convert Prospect - Customer Care')

@php
    $headerTitle = 'Convert Prospect to Patient';
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('customer-care.prospects.show', $prospect) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Prospect
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Convert Prospect to Patient</h1>
        <p class="text-sm text-gray-600 mt-1">This will create a real patient account and trigger onboarding communication</p>
    </div>

    <div class="bg-red-50 border-l-4 border-red-500 p-6 mb-6 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <h3 class="text-lg font-bold text-red-900 mb-2">Warning: Account Creation</h3>
                <p class="text-sm text-red-800 mb-3">This action will:</p>
                <ul class="list-disc list-inside text-sm text-red-800 space-y-1 mb-3">
                    <li>Create a real user account in the system</li>
                    <li>Create a patient profile</li>
                    <li>Send onboarding email to {{ $prospect->email ?? $prospect->mobile_number }}</li>
                    <li>Trigger account activation flow</li>
                    <li>Mark this prospect as "Converted"</li>
                </ul>
                <p class="text-sm font-semibold text-red-900">This cannot be undone. Please confirm before proceeding.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Prospect Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Name</p>
                <p class="text-sm font-semibold text-gray-900">{{ $prospect->full_name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Mobile</p>
                <p class="text-sm font-mono text-gray-900">{{ $prospect->mobile_number }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Email</p>
                <p class="text-sm text-gray-900">{{ $prospect->email ?? 'Will be generated' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Location</p>
                <p class="text-sm text-gray-900">{{ $prospect->location ?? '—' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('customer-care.prospects.process-conversion', $prospect) }}">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-start gap-3 mb-6">
                <input type="checkbox" name="confirm" id="confirm" required
                       class="mt-1 w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <label for="confirm" class="text-sm font-semibold text-gray-900">
                    I understand that this will create a real patient account and trigger onboarding communication. I confirm that I want to proceed.
                </label>
            </div>
            @error('confirm')
                <p class="text-xs text-red-600 mb-4">{{ $message }}</p>
            @enderror

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('customer-care.prospects.show', $prospect) }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                    Convert to Patient
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

