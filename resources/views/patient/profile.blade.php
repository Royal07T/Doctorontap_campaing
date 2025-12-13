@extends('layouts.patient')

@section('title', 'My Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
            <p class="text-gray-600 mt-2">Manage your personal information and account settings</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Profile Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Personal Information</h2>
            
            <form method="POST" action="{{ route('patient.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $patient->name) }}" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email (Read Only) -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" value="{{ $patient->email }}" 
                               class="w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed" disabled>
                        <p class="mt-1 text-xs text-gray-500">Email address cannot be changed</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $patient->phone) }}" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('phone') border-red-500 @enderror" required>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <select name="gender" id="gender" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" 
                               value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                            Update Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Account Information</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b">
                    <div>
                        <p class="font-medium text-gray-800">Email Verification</p>
                        <p class="text-sm text-gray-500">Confirm your email address for account security</p>
                    </div>
                    @if($patient->is_verified)
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">✓ Verified</span>
                    @else
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">Pending</span>
                    @endif
                </div>

                <div class="flex justify-between items-center py-3 border-b">
                    <div>
                        <p class="font-medium text-gray-800">Account Created</p>
                        <p class="text-sm text-gray-500">{{ $patient->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center py-3">
                    <div>
                        <p class="font-medium text-gray-800">Total Consultations</p>
                        <p class="text-sm text-gray-500">{{ $patient->consultations_count ?? 0 }} consultations completed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy & Security -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Privacy & Security</h2>
            
            <div class="space-y-4">
                <a href="#" class="flex justify-between items-center py-3 hover:bg-gray-50 rounded px-3 transition">
                    <div>
                        <p class="font-medium text-gray-800">Change Password</p>
                        <p class="text-sm text-gray-500">Update your password regularly for security</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <div class="py-3 px-3 bg-blue-50 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
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

