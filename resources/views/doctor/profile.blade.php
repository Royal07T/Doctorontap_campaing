@extends('layouts.doctor')

@section('title', 'Profile Settings')
@section('header-title', 'Profile Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-lg font-bold text-gray-900">Profile Settings</h1>
        <p class="text-xs text-gray-500 mt-1">Update your profile information and photo</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Photo Upload Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Profile Photo</h2>
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if($doctor->photo_url)
                        <img src="{{ $doctor->photo_url }}" alt="Profile Photo" class="w-16 h-16 rounded-full object-cover border-3 border-purple-200">
                    @else
                        <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center border-3 border-purple-200">
                            <span class="text-xl font-bold text-purple-600">{{ substr($doctor->name, 0, 1) }}</span>
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

        <!-- Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-medium text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $doctor->first_name) }}" required
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('first_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-medium text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $doctor->last_name) }}" required
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('last_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-xs font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $doctor->name) }}" required
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $doctor->email) }}" required
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $doctor->phone) }}" required
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="gender" class="block text-xs font-medium text-gray-700 mb-1.5">Gender</label>
                    <select name="gender" id="gender" class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $doctor->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $doctor->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Professional Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="specialization" class="block text-xs font-medium text-gray-700 mb-1.5">Specialization</label>
                    <input type="text" name="specialization" id="specialization" value="{{ old('specialization', $doctor->specialization) }}"
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('specialization')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="block text-xs font-medium text-gray-700 mb-1.5">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $doctor->location) }}"
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('location')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="experience" class="block text-xs font-medium text-gray-700 mb-1.5">Experience</label>
                    <input type="text" name="experience" id="experience" value="{{ old('experience', $doctor->experience) }}"
                           placeholder="e.g., 10 years"
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('experience')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="languages" class="block text-xs font-medium text-gray-700 mb-1.5">Languages</label>
                    <input type="text" name="languages" id="languages" value="{{ old('languages', $doctor->languages) }}"
                           placeholder="e.g., English, French"
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('languages')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="place_of_work" class="block text-xs font-medium text-gray-700 mb-1.5">Place of Work</label>
                    <input type="text" name="place_of_work" id="place_of_work" value="{{ old('place_of_work', $doctor->place_of_work) }}"
                           placeholder="e.g., General Hospital, Private Clinic"
                           class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                    @error('place_of_work')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Bio Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Biography</h2>
            <div>
                <label for="bio" class="block text-xs font-medium text-gray-700 mb-1.5">About Me</label>
                <textarea name="bio" id="bio" rows="6" maxlength="2000"
                          placeholder="Tell patients about your background, education, and expertise..."
                          class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">{{ old('bio', $doctor->bio) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Maximum 2000 characters</p>
                @error('bio')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('doctor.dashboard') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 purple-gradient hover:opacity-90 text-white text-sm font-medium rounded-lg transition">
                Save Changes
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush

