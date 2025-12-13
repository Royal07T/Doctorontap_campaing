@extends('layouts.patient')

@section('title', 'New Consultation')

@section('content')
<div class="max-w-4xl mx-auto">
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
            <h3 class="text-lg font-bold text-gray-800 mb-4">Consultation Type</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative cursor-pointer">
                    <input type="radio" name="consultation_type" value="pay_later" class="peer hidden" {{ ($selectedType ?? 'pay_later') === 'pay_later' ? 'checked' : '' }}>
                    <div class="border-2 border-gray-300 rounded-lg p-5 hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-purple-50">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Consult Now, Pay Later</h4>
                            <svg class="w-5 h-5 text-purple-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Consult with a doctor first, pay after consultation</p>
                        <p class="text-lg font-bold text-purple-600">₦{{ number_format($payLaterFee, 2) }}</p>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="consultation_type" value="pay_now" class="peer hidden" {{ ($selectedType ?? 'pay_later') === 'pay_now' ? 'checked' : '' }}>
                    <div class="border-2 border-gray-300 rounded-lg p-5 hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-purple-50">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Pay Before Consultation</h4>
                            <svg class="w-5 h-5 text-purple-600 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Pay upfront to secure your consultation</p>
                        <p class="text-lg font-bold text-purple-600">₦{{ number_format($payNowFee, 2) }}</p>
                    </div>
                </label>
            </div>
            @error('consultation_type')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
                    <label for="consult_mode" class="block text-sm font-medium text-gray-700 mb-2">
                        Consultation Mode <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="consult_mode" value="voice" class="peer hidden" {{ old('consult_mode') === 'voice' ? 'checked' : '' }} required>
                            <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-purple-50">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                                <p class="font-semibold text-gray-800">Voice Call</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="consult_mode" value="video" class="peer hidden" {{ old('consult_mode') === 'video' ? 'checked' : '' }} required>
                            <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-purple-50">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <p class="font-semibold text-gray-800">Video Call</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="consult_mode" value="chat" class="peer hidden" {{ old('consult_mode') === 'chat' ? 'checked' : '' }} required>
                            <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-purple-500 transition-all peer-checked:border-purple-600 peer-checked:bg-purple-50">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 peer-checked:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="font-semibold text-gray-800">Chat</p>
                            </div>
                        </label>
                    </div>
                    @error('consult_mode')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
    // Form validation
    document.getElementById('consultationForm').addEventListener('submit', function(e) {
        const problem = document.getElementById('problem').value.trim();
        if (problem.length < 10) {
            e.preventDefault();
            alert('Please describe your problem in at least 10 characters.');
            return false;
        }
    });
</script>
@endpush
@endsection

