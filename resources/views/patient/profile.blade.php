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

