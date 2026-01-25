@extends('layouts.') {{-- or whatever your layout file is named --}}

@section('sidebar')
    {{-- Optional: You can leave sidebar empty for auth pages --}}
@endsection

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Join as a Caregiver
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Or <a href="{{ route('care_giver.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                sign in to your account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form method="POST" action="{{ route('caregiver.register.submit') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Personal Information --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Personal Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Please provide your valid personal details.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" required value="{{ old('phone') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input id="dob" name="date_of_birth" type="date" required value="{{ old('date_of_birth') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
                        <select id="gender" name="gender" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="male" @selected(old('gender') == 'male')>Male</option>
                            <option value="female" @selected(old('gender') == 'female')>Female</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Professional Details --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Professional Details</h3>
                    <p class="mt-1 text-sm text-gray-500">Your qualifications and experience.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role Applied For</label>
                        <select id="role" name="role" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="Registered Nurse" @selected(old('role')=='Registered Nurse')>Registered Nurse</option>
                            <option value="Auxiliary Nurse" @selected(old('role')=='Auxiliary Nurse')>Auxiliary Nurse</option>
                            <option value="Caregiver" @selected(old('role')=='Caregiver')>Caregiver</option>
                            <option value="Medical Assistant" @selected(old('role')=='Medical Assistant')>Medical Assistant</option>
                        </select>
                    </div>

                    <div>
                        <label for="experience" class="block text-sm font-medium text-gray-700">Years of Experience</label>
                        <input id="experience" name="experience_years" type="number" min="0" required value="{{ old('experience_years') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="license" class="block text-sm font-medium text-gray-700">License Number</label>
                        <input id="license" name="license_number" type="text" value="{{ old('license_number') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700">Short Bio / Summary</label>
                        <textarea id="bio" name="bio" rows="3" placeholder="Briefly describe your experience and skills..." class="mt-1 block w-full sm:text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('bio') }}</textarea>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Address --}}
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Address</h3>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Street Address</label>
                        <input id="address"
