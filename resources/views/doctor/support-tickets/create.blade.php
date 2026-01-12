@extends('layouts.doctor')

@section('title', 'Create Support Ticket')
@section('header-title', 'Create Support Ticket')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('doctor.support-tickets.index') }}" class="text-purple-600 hover:text-purple-700 font-semibold flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Tickets
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Create Support Ticket</h1>
        <p class="text-sm text-gray-600 mt-1">We're here to help! Please provide details about your issue.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('doctor.support-tickets.store') }}">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Select a category</option>
                    <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Billing & Payment</option>
                    <option value="appointment" {{ old('category') == 'appointment' ? 'selected' : '' }}>Appointment & Consultation</option>
                    <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical Support</option>
                    <option value="medical" {{ old('category') == 'medical' ? 'selected' : '' }}>Medical Inquiry</option>
                </select>
                @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="low" {{ old('priority', 'medium') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ old('priority', 'medium') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ old('priority', 'medium') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                @error('priority')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" required value="{{ old('subject') }}" 
                       placeholder="Brief description of your issue..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('subject')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description <span class="text-red-500">*</span></label>
                <textarea name="description" required rows="8" 
                          placeholder="Please provide detailed information about your issue..." 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('doctor.support-tickets.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-semibold">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 purple-gradient text-white rounded-lg hover:opacity-90 font-semibold">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

