@extends('layouts.patient')

@section('title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Profile Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-5 uppercase tracking-wide">Personal Information</h2>
            
            <form method="POST" action="{{ route('patient.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Photo Upload Section -->
                    <div class="bg-gray-50 rounded-lg p-4 border-2 border-dashed border-gray-300 mb-5">
                        <h3 class="text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wide">Profile Photo</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($patient->photo_url)
                                    <img src="{{ $patient->photo_url }}" alt="Profile Photo" class="w-16 h-16 rounded-full object-cover border-3 border-purple-200">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center border-3 border-purple-200">
                                        <span class="text-xl font-bold text-purple-600">{{ substr($patient->name, 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label for="photo" class="block text-xs font-medium text-gray-700 mb-1.5">Upload Photo</label>
                                <input type="file" name="photo" id="photo" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF. Max size: 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-xs font-medium text-gray-700 mb-1.5">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $patient->name) }}" 
                               class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email (Read Only) -->
                    <div class="mb-4">
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" id="email" value="{{ $patient->email }}" 
                               class="w-full text-sm rounded-lg border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed" disabled>
                        <p class="mt-1 text-xs text-gray-500">Email address cannot be changed</p>
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label for="phone" class="block text-xs font-medium text-gray-700 mb-1.5">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $patient->phone) }}" 
                               class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 @error('phone') border-red-500 @enderror" required>
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div class="mb-4">
                        <label for="gender" class="block text-xs font-medium text-gray-700 mb-1.5">Gender</label>
                        <select name="gender" id="gender" 
                                class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @if(strtolower($patient->gender) === 'female' || old('gender') === 'female')
                        <div class="mt-2 p-3 bg-pink-50 border-l-4 border-pink-500 rounded">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 text-pink-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-xs font-medium text-pink-900">Menstrual Cycle Tracker Available</p>
                                    <p class="text-xs text-pink-700 mt-1">As a female patient, you can track your menstrual cycle on your <a href="{{ route('patient.dashboard') }}" class="underline font-semibold">Dashboard</a>. You can log your periods and get predictions for your next cycle.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Date of Birth -->
                    <div class="mb-5">
                        <label for="date_of_birth" class="block text-xs font-medium text-gray-700 mb-1.5">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" 
                               value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}" 
                               class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="purple-gradient hover:opacity-90 text-white px-5 py-2.5 text-sm font-medium rounded-lg transition">
                            Update Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>

    <!-- Medical Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-5 uppercase tracking-wide">Medical Information</h2>
        
        <form method="POST" action="{{ route('patient.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Blood Group and Genotype -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="blood_group" class="block text-xs font-medium text-gray-700 mb-1.5">Blood Group</label>
                        <select name="blood_group" id="blood_group" 
                                class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">Select Blood Group</option>
                            <option value="A+" {{ old('blood_group', $patient->blood_group) === 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_group', $patient->blood_group) === 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_group', $patient->blood_group) === 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_group', $patient->blood_group) === 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_group', $patient->blood_group) === 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_group', $patient->blood_group) === 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_group', $patient->blood_group) === 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_group', $patient->blood_group) === 'O-' ? 'selected' : '' }}>O-</option>
                            <option value="Unknown" {{ old('blood_group', $patient->blood_group) === 'Unknown' ? 'selected' : '' }}>Unknown</option>
                        </select>
                    </div>

                    <div>
                        <label for="genotype" class="block text-xs font-medium text-gray-700 mb-1.5">Genotype</label>
                        <select name="genotype" id="genotype" 
                                class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">Select Genotype</option>
                            <option value="AA" {{ old('genotype', $patient->genotype) === 'AA' ? 'selected' : '' }}>AA</option>
                            <option value="AS" {{ old('genotype', $patient->genotype) === 'AS' ? 'selected' : '' }}>AS</option>
                            <option value="AC" {{ old('genotype', $patient->genotype) === 'AC' ? 'selected' : '' }}>AC</option>
                            <option value="SS" {{ old('genotype', $patient->genotype) === 'SS' ? 'selected' : '' }}>SS</option>
                            <option value="SC" {{ old('genotype', $patient->genotype) === 'SC' ? 'selected' : '' }}>SC</option>
                            <option value="CC" {{ old('genotype', $patient->genotype) === 'CC' ? 'selected' : '' }}>CC</option>
                            <option value="Unknown" {{ old('genotype', $patient->genotype) === 'Unknown' ? 'selected' : '' }}>Unknown</option>
                        </select>
                    </div>
                </div>

                <!-- Height and Weight -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="height" class="block text-xs font-medium text-gray-700 mb-1.5">Height (cm)</label>
                        <input type="text" name="height" id="height" value="{{ old('height', $patient->height) }}" 
                               placeholder="e.g., 175"
                               class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    </div>

                    <div>
                        <label for="weight" class="block text-xs font-medium text-gray-700 mb-1.5">Weight (kg)</label>
                        <input type="text" name="weight" id="weight" value="{{ old('weight', $patient->weight) }}" 
                               placeholder="e.g., 70"
                               class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    </div>
                </div>

                <!-- Allergies -->
                <div>
                    <label for="allergies" class="block text-xs font-medium text-gray-700 mb-1.5">Allergies</label>
                    <textarea name="allergies" id="allergies" rows="3" 
                              placeholder="List any known allergies (e.g., Penicillin, Peanuts, Latex)"
                              class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('allergies', $patient->allergies) }}</textarea>
                </div>

                <!-- Chronic Conditions -->
                <div>
                    <label for="chronic_conditions" class="block text-xs font-medium text-gray-700 mb-1.5">Chronic Conditions</label>
                    <textarea name="chronic_conditions" id="chronic_conditions" rows="3" 
                              placeholder="List any chronic medical conditions (e.g., Diabetes, Hypertension, Asthma)"
                              class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('chronic_conditions', $patient->chronic_conditions) }}</textarea>
                </div>

                <!-- Current Medications -->
                <div>
                    <label for="current_medications" class="block text-xs font-medium text-gray-700 mb-1.5">Current Medications</label>
                    <textarea name="current_medications" id="current_medications" rows="3" 
                              placeholder="List medications you are currently taking"
                              class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('current_medications', $patient->current_medications) }}</textarea>
                </div>

                <!-- Surgical History -->
                <div>
                    <label for="surgical_history" class="block text-xs font-medium text-gray-700 mb-1.5">Surgical History</label>
                    <textarea name="surgical_history" id="surgical_history" rows="3" 
                              placeholder="List any past surgeries or procedures"
                              class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('surgical_history', $patient->surgical_history) }}</textarea>
                </div>

                <!-- Family Medical History -->
                <div>
                    <label for="family_medical_history" class="block text-xs font-medium text-gray-700 mb-1.5">Family Medical History</label>
                    <textarea name="family_medical_history" id="family_medical_history" rows="3" 
                              placeholder="List significant medical conditions in your family"
                              class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('family_medical_history', $patient->family_medical_history) }}</textarea>
                </div>

                <!-- Emergency Contact -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-xs font-semibold text-gray-900 mb-4 uppercase tracking-wide">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="emergency_contact_name" class="block text-xs font-medium text-gray-700 mb-1.5">Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                   value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" 
                                   placeholder="Full Name"
                                   class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                        </div>

                        <div>
                            <label for="emergency_contact_phone" class="block text-xs font-medium text-gray-700 mb-1.5">Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" 
                                   value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" 
                                   placeholder="Phone Number"
                                   class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                        </div>

                        <div>
                            <label for="emergency_contact_relationship" class="block text-xs font-medium text-gray-700 mb-1.5">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" 
                                   value="{{ old('emergency_contact_relationship', $patient->emergency_contact_relationship) }}" 
                                   placeholder="e.g., Spouse, Parent, Sibling"
                                   class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                        </div>
                    </div>
                </div>

                <!-- Medical Notes -->
                <div>
                    <label for="medical_notes" class="block text-xs font-medium text-gray-700 mb-1.5">Additional Medical Notes</label>
                    <textarea name="medical_notes" id="medical_notes" rows="4" 
                              placeholder="Any additional medical information you'd like to share with your doctors"
                              class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('medical_notes', $patient->medical_notes) }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="purple-gradient hover:opacity-90 text-white px-5 py-2.5 text-sm font-medium rounded-lg transition">
                        Update Medical Information
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-5 uppercase tracking-wide">Account Information</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2.5 border-b border-gray-200">
                    <div>
                        <p class="text-xs font-semibold text-gray-900">Email Verification</p>
                        <p class="text-xs text-gray-500 mt-0.5">Confirm your email address for account security</p>
                    </div>
                    @if($patient->is_verified)
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">✓ Verified</span>
                    @else
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">Pending</span>
                    @endif
                </div>

                <div class="flex justify-between items-center py-2.5 border-b border-gray-200">
                    <div>
                        <p class="text-xs font-semibold text-gray-900">Account Created</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $patient->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center py-2.5">
                    <div>
                        <p class="text-xs font-semibold text-gray-900">Total Consultations</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $patient->consultations_count ?? 0 }} consultations completed</p>
                    </div>
                </div>
            </div>
        </div>

    <!-- Privacy & Security -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-5 uppercase tracking-wide">Privacy & Security</h2>
            
            <div class="space-y-3">
                <a href="#" class="flex justify-between items-center py-2.5 hover:bg-gray-50 rounded px-3 transition">
                    <div>
                        <p class="text-xs font-semibold text-gray-900">Change Password</p>
                        <p class="text-xs text-gray-500 mt-0.5">Update your password regularly for security</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <div class="py-3 px-3 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-purple-700">
                                Your medical information is encrypted and stored securely. We never share your data without your explicit consent.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

