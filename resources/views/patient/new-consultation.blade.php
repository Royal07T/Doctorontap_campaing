@extends('layouts.patient')

@section('title', 'New Consultation')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            {{ session('error') }}
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div class="flex-1">
                <p class="font-semibold mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">New Consultation</h2>
                <p class="text-sm text-gray-600">Book a consultation with a healthcare professional</p>
            </div>
        </div>
    </div>

    <!-- Consultation Form -->
    <form method="POST" action="{{ route('patient.consultation.store') }}" enctype="multipart/form-data" id="consultationForm">
        @csrf

        <!-- Consultation Type Selection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Select Consultation Type</h3>
                    <p class="text-sm text-gray-600 mt-1">Choose how you want to pay for your consultation</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative cursor-pointer group">
                    <input type="radio" name="consultation_type" value="pay_later" class="peer sr-only" {{ old('consultation_type', $selectedType ?? 'pay_later') === 'pay_later' ? 'checked' : '' }} required>
                    <div class="border-2 border-gray-300 rounded-lg p-5 hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-purple-100 group-hover:shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center peer-checked:bg-purple-600 transition-colors">
                                    <svg class="w-6 h-6 text-purple-600 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-800 text-lg">Consult Now, Pay Later</h4>
                            </div>
                            <svg class="w-6 h-6 text-purple-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Consult with a doctor first, pay after consultation is completed</p>
                        <div class="flex items-center justify-between">
                            <p class="text-2xl font-bold text-purple-600">₦{{ number_format($payLaterFee, 2) }}</p>
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-semibold">Standard</span>
                        </div>
                    </div>
                </label>
                <label class="relative cursor-pointer group">
                    <input type="radio" name="consultation_type" value="pay_now" class="peer sr-only" {{ old('consultation_type', $selectedType ?? 'pay_later') === 'pay_now' ? 'checked' : '' }} required>
                    <div class="border-2 border-gray-300 rounded-lg p-5 hover:border-emerald-500 transition-all peer-checked:border-emerald-600 peer-checked:bg-gradient-to-br peer-checked:from-emerald-50 peer-checked:to-emerald-100 group-hover:shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center peer-checked:bg-emerald-600 transition-colors">
                                    <svg class="w-6 h-6 text-emerald-600 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-800 text-lg">Pay Before Consultation</h4>
                            </div>
                            <svg class="w-6 h-6 text-emerald-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">Pay upfront to secure your consultation slot</p>
                        <div class="flex items-center justify-between">
                            <p class="text-2xl font-bold text-emerald-600">₦{{ number_format($payNowFee, 2) }}</p>
                            @if($payNowFee < $payLaterFee)
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-semibold">
                                    Save ₦{{ number_format($payLaterFee - $payNowFee, 2) }}
                                </span>
                            @else
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-semibold">Upfront</span>
                            @endif
                        </div>
                    </div>
                </label>
            </div>
            @error('consultation_type')
                <p class="mt-3 text-sm text-red-600 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Patient Information (Read-only) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Your Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" value="{{ $patient->name }}" class="w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" value="{{ $patient->email }}" class="w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel" value="{{ $patient->phone }}" class="w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed" disabled>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Age</label>
                    <input type="text" value="{{ $patient->age }} years" class="w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed" disabled>
                </div>
            </div>
        </div>

        <!-- Medical Problem -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Medical Information</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="problem" class="block text-sm font-medium text-gray-700 mb-2">
                        Describe Your Problem <span class="text-red-500">*</span>
                    </label>
                    <textarea name="problem" id="problem" rows="4" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 @error('problem') border-red-500 @enderror" 
                              placeholder="Please describe your medical problem in detail (minimum 10 characters)" required>{{ old('problem') }}</textarea>
                    @error('problem')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="symptoms" class="block text-sm font-medium text-gray-700 mb-2">
                        Symptoms (Optional)
                    </label>
                    <textarea name="symptoms" id="symptoms" rows="3" 
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 @error('symptoms') border-red-500 @enderror" 
                              placeholder="List any symptoms you're experiencing">{{ old('symptoms') }}</textarea>
                    @error('symptoms')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="severity" class="block text-sm font-medium text-gray-700 mb-2">
                        Severity <span class="text-red-500">*</span>
                    </label>
                    <select name="severity" id="severity" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 @error('severity') border-red-500 @enderror" required>
                        <option value="">Select Severity</option>
                        <option value="mild" {{ old('severity') === 'mild' ? 'selected' : '' }}>Mild</option>
                        <option value="moderate" {{ old('severity') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                        <option value="severe" {{ old('severity') === 'severe' ? 'selected' : '' }}>Severe</option>
                    </select>
                    @error('severity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Symptoms (Check all that apply)</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach(['Chest pain', 'Difficulty breathing', 'Severe bleeding', 'Loss of consciousness', 'Severe allergic reaction', 'Severe burns'] as $symptom)
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="emergency_symptoms[]" value="{{ strtolower(str_replace(' ', '_', $symptom)) }}" 
                                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                       {{ in_array(strtolower(str_replace(' ', '_', $symptom)), old('emergency_symptoms', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $symptom }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="medical_documents" class="block text-sm font-medium text-gray-700 mb-2">
                        Medical Documents (Optional)
                    </label>
                    <input type="file" name="medical_documents[]" id="medical_documents" multiple
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 @error('medical_documents.*') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">You can upload multiple files (PDF, JPG, PNG, DOC, DOCX). Max 5MB per file.</p>
                    @error('medical_documents.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Doctor Selection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Doctor Selection (Optional)</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select a Doctor (Leave blank to be assigned automatically)
                    </label>
                    <select name="doctor_id" id="doctor_id" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                        <option value="">Auto-assign (Recommended)</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }} - {{ $doctor->specialization ?? 'General Practice' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="consult_mode" class="block text-sm font-bold text-gray-800 mb-3">
                        Select Consultation Mode <span class="text-red-500">*</span>
                    </label>
                    <p class="text-sm text-gray-600 mb-4">Choose how you want to communicate with your doctor</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="consult_mode" value="voice" class="peer sr-only" {{ old('consult_mode') === 'voice' ? 'checked' : '' }} required>
                            <div class="border-2 border-gray-300 rounded-lg p-5 text-center hover:border-blue-500 transition-all peer-checked:border-blue-600 peer-checked:bg-gradient-to-br peer-checked:from-blue-50 peer-checked:to-blue-100 group-hover:shadow-md">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 peer-checked:bg-blue-600 transition-colors">
                                    <svg class="w-6 h-6 text-blue-600 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-gray-800 text-lg mb-1">Voice Call</p>
                                <p class="text-xs text-gray-600">Audio consultation via phone</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="consult_mode" value="video" class="peer sr-only" {{ old('consult_mode') === 'video' ? 'checked' : '' }} required>
                            <div class="border-2 border-gray-300 rounded-lg p-5 text-center hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-purple-100 group-hover:shadow-md">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3 peer-checked:bg-purple-600 transition-colors">
                                    <svg class="w-6 h-6 text-purple-600 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-gray-800 text-lg mb-1">Video Call</p>
                                <p class="text-xs text-gray-600">Face-to-face video consultation</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="consult_mode" value="chat" class="peer sr-only" {{ old('consult_mode') === 'chat' ? 'checked' : '' }} required>
                            <div class="border-2 border-gray-300 rounded-lg p-5 text-center hover:border-emerald-500 transition-all peer-checked:border-emerald-600 peer-checked:bg-gradient-to-br peer-checked:from-emerald-50 peer-checked:to-emerald-100 group-hover:shadow-md">
                                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3 peer-checked:bg-emerald-600 transition-colors">
                                    <svg class="w-6 h-6 text-emerald-600 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-gray-800 text-lg mb-1">Text Chat</p>
                                <p class="text-xs text-gray-600">Messaging-based consultation</p>
                            </div>
                        </label>
                    </div>
                    @error('consult_mode')
                        <p class="mt-3 text-sm text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Consent -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Consent & Privacy</h3>
            
            <div class="space-y-4">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="informed_consent" value="1" 
                           class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500" required>
                    <span class="text-sm text-gray-700">
                        I understand and consent to the consultation process and agree to the terms of service. <span class="text-red-500">*</span>
                    </span>
                </label>
                @error('informed_consent')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="data_privacy" value="1" 
                           class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500" required>
                    <span class="text-sm text-gray-700">
                        I agree to the data privacy policy and understand how my information will be used. <span class="text-red-500">*</span>
                    </span>
                </label>
                @error('data_privacy')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <a href="{{ route('patient.dashboard') }}" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-purple-800 transition-all shadow-lg hover:shadow-xl">
                    Submit Consultation
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Form validation and sanitization
    document.getElementById('consultationForm').addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];

        // Validate problem description
        const problem = document.getElementById('problem').value.trim();
        if (problem.length < 10) {
            isValid = false;
            errors.push('Problem description must be at least 10 characters.');
        }
        if (problem.length > 500) {
            isValid = false;
            errors.push('Problem description cannot exceed 500 characters.');
        }
        
        // Validate symptoms (if provided)
        const symptoms = document.getElementById('symptoms').value.trim();
        if (symptoms.length > 1000) {
            isValid = false;
            errors.push('Symptoms description cannot exceed 1000 characters.');
        }

        // Validate severity
        const severity = document.getElementById('severity').value;
        if (!severity) {
            isValid = false;
            errors.push('Please select a severity level.');
        }

        // Validate consultation type (must be selected)
        const consultationType = document.querySelector('input[name="consultation_type"]:checked');
        if (!consultationType) {
            isValid = false;
            errors.push('Please select a consultation type (Pay Later or Pay Now).');
        }

        // Validate consultation mode (must be selected)
        const consultMode = document.querySelector('input[name="consult_mode"]:checked');
        if (!consultMode) {
            isValid = false;
            errors.push('Please select a consultation mode (Voice, Video, or Chat).');
        }

        // Validate consent checkboxes
        const informedConsent = document.querySelector('input[name="informed_consent"]:checked');
        const dataPrivacy = document.querySelector('input[name="data_privacy"]:checked');
        if (!informedConsent) {
            isValid = false;
            errors.push('You must accept the informed consent.');
        }
        if (!dataPrivacy) {
            isValid = false;
            errors.push('You must accept the data privacy policy.');
        }

        // Validate file uploads
        const fileInput = document.getElementById('medical_documents');
        if (fileInput.files.length > 0) {
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            
            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];
                
                if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    errors.push(`File "${file.name}" is not a valid file type. Only PDF, JPG, PNG, DOC, and DOCX files are allowed.`);
                }
                
                if (file.size > maxSize) {
                    isValid = false;
                    errors.push(`File "${file.name}" exceeds the maximum size of 5MB.`);
                }
            }
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }

        // Sanitize text inputs before submission
        document.getElementById('problem').value = sanitizeText(document.getElementById('problem').value);
        if (document.getElementById('symptoms').value) {
            document.getElementById('symptoms').value = sanitizeText(document.getElementById('symptoms').value);
        }
    });

    // Sanitize text input function
    function sanitizeText(text) {
        // Remove HTML tags
        const div = document.createElement('div');
        div.textContent = text;
        text = div.innerHTML;
        
        // Normalize whitespace
        text = text.replace(/\s+/g, ' ').trim();
        
        return text;
    }

    // Real-time character count for problem description
    const problemInput = document.getElementById('problem');
    if (problemInput) {
        const problemCount = document.createElement('div');
        problemCount.className = 'mt-1 text-xs text-gray-500';
        problemCount.id = 'problem-count';
        problemInput.parentNode.appendChild(problemCount);
        
        problemInput.addEventListener('input', function() {
            const length = this.value.length;
            problemCount.textContent = `${length}/500 characters`;
            if (length < 10) {
                problemCount.className = 'mt-1 text-xs text-red-500';
            } else if (length > 500) {
                problemCount.className = 'mt-1 text-xs text-red-500';
            } else {
                problemCount.className = 'mt-1 text-xs text-gray-500';
            }
        });
        
        // Initial count
        problemInput.dispatchEvent(new Event('input'));
    }

    // Real-time character count for symptoms
    const symptomsInput = document.getElementById('symptoms');
    if (symptomsInput) {
        const symptomsCount = document.createElement('div');
        symptomsCount.className = 'mt-1 text-xs text-gray-500';
        symptomsCount.id = 'symptoms-count';
        symptomsInput.parentNode.appendChild(symptomsCount);
        
        symptomsInput.addEventListener('input', function() {
            const length = this.value.length;
            symptomsCount.textContent = `${length}/1000 characters`;
            if (length > 1000) {
                symptomsCount.className = 'mt-1 text-xs text-red-500';
            } else {
                symptomsCount.className = 'mt-1 text-xs text-gray-500';
            }
        });
        
        // Initial count
        symptomsInput.dispatchEvent(new Event('input'));
    }
</script>
@endpush
@endsection

