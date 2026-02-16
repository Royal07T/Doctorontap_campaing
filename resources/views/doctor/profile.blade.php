@extends('layouts.doctor')

@section('title', 'Profile & Credentials')
@section('header-title', 'Profile & Credentials')

@push('x-data-extra')
, activeTab: 'basic-info'
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 md:px-6">
    <div class="max-w-6xl mx-auto">
        <!-- Profile Header Card (LinkedIn-style) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-32"></div>
            <div class="px-8 pb-8 -mt-16">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div class="flex items-end gap-6">
                        <!-- Avatar -->
                        <div class="relative">
                            @if($doctor->photo_url)
                                <img src="{{ $doctor->photo_url }}" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center border-4 border-white shadow-lg">
                                    <span class="text-4xl font-bold text-indigo-600">{{ substr($doctor->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <label for="avatar-upload-header" class="absolute bottom-0 right-0 w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-indigo-700 transition-colors shadow-lg border-2 border-white">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </label>
                            <input type="file" id="avatar-upload-header" name="photo" accept="image/*" class="hidden">
                        </div>
                        
                        <!-- Name & Info -->
                        <div class="pb-4">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $doctor->name }}</h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                @if($doctor->specialization)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span class="font-semibold">{{ $doctor->specialization }}</span>
                                </div>
                                @endif
                                @if($doctor->location)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $doctor->location }}</span>
                                </div>
                                @endif
                                @if($doctor->experience)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $doctor->experience }} years experience</span>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Verification Badges -->
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                @if($doctor->is_approved)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified Doctor
                                </span>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Pending Verification
                                </span>
                                @endif
                                @if($doctor->insurance_document)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Insurance Verified
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KYC Completion Progress -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">KYC Completion</h3>
                @php
                    $kycItems = [
                        'profile' => $doctor->first_name && $doctor->last_name && $doctor->email && $doctor->phone,
                        'specialization' => !empty($doctor->specialization),
                        'mdcn' => true, // Assuming MDCN is always verified if shown
                        'insurance' => !empty($doctor->insurance_document),
                    ];
                    $kycComplete = count(array_filter($kycItems));
                    $kycTotal = count($kycItems);
                    $kycPercentage = ($kycComplete / $kycTotal) * 100;
                @endphp
                <span class="text-sm font-bold text-indigo-600">{{ $kycComplete }}/{{ $kycTotal }} Complete</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-3 rounded-full transition-all duration-500" style="width: {{ $kycPercentage }}%"></div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="flex items-center gap-2">
                    @if($kycItems['profile'])
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    @endif
                    <span class="text-xs font-semibold {{ $kycItems['profile'] ? 'text-gray-900' : 'text-gray-500' }}">Profile Info</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($kycItems['specialization'])
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    @endif
                    <span class="text-xs font-semibold {{ $kycItems['specialization'] ? 'text-gray-900' : 'text-gray-500' }}">Specialization</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($kycItems['mdcn'])
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    @endif
                    <span class="text-xs font-semibold {{ $kycItems['mdcn'] ? 'text-gray-900' : 'text-gray-500' }}">MDCN License</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($kycItems['insurance'])
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    @endif
                    <span class="text-xs font-semibold {{ $kycItems['insurance'] ? 'text-gray-900' : 'text-gray-500' }}">Insurance</span>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-lg">
                <div class="flex">
                    <svg class="h-5 w-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex -mb-px min-w-max">
                    <button @click="activeTab = 'basic-info'"
                            :class="activeTab === 'basic-info' ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            class="px-6 py-4 border-b-2 font-semibold text-sm transition-all duration-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Basic Info
                        </span>
                    </button>
                    <button @click="activeTab = 'professional'"
                            :class="activeTab === 'professional' ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            class="px-6 py-4 border-b-2 font-semibold text-sm transition-all duration-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Professional Details
                        </span>
                    </button>
                    <button @click="activeTab = 'licenses'"
                            :class="activeTab === 'licenses' ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            class="px-6 py-4 border-b-2 font-semibold text-sm transition-all duration-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Licenses & KYC
                        </span>
                    </button>
                    <button @click="activeTab = 'bank'"
                            :class="activeTab === 'bank' ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            class="px-6 py-4 border-b-2 font-semibold text-sm transition-all duration-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Bank Accounts
                        </span>
                    </button>
                </nav>
            </div>
        </div>

        <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data">
            @csrf

            <!-- Basic Info Tab -->
            <div x-show="activeTab === 'basic-info'" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="flex items-start gap-2 mb-6">
                    <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Basic Information</h3>
                        <p class="text-sm text-gray-600 mt-1">Your personal details and contact information</p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-start gap-8 mb-8">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="relative">
                            @if($doctor->photo_url)
                                <img src="{{ $doctor->photo_url }}" alt="Profile Photo" class="w-32 h-32 rounded-full object-cover border-4 border-gray-100">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center border-4 border-gray-100">
                                    <span class="text-4xl font-bold text-indigo-600">{{ substr($doctor->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <label for="avatar-upload" class="absolute bottom-0 right-0 w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-indigo-700 transition-colors shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </label>
                            <input type="file" id="avatar-upload" name="photo" accept="image/*" class="hidden">
                        </div>
                        <p class="text-xs text-gray-500 mt-3 text-center">JPG, PNG or GIF<br>Max 2MB</p>
                    </div>

                    <!-- Form Fields -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-5 w-full">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">First Name *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $doctor->first_name) }}" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Last Name *</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $doctor->last_name) }}" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email', $doctor->email) }}" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Phone Number *</label>
                            <input type="tel" name="phone" value="{{ old('phone', $doctor->phone) }}" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Gender</label>
                            <select name="gender" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Select</option>
                                <option value="Female" {{ old('gender', $doctor->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Male" {{ old('gender', $doctor->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Other" {{ old('gender', $doctor->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Languages</label>
                            <input type="text" name="languages" value="{{ old('languages', $doctor->languages) }}"
                                   placeholder="English, Yoruba, Hausa"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Details Tab -->
            <div x-show="activeTab === 'professional'" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="flex items-start gap-2 mb-6">
                    <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Professional Information</h3>
                        <p class="text-sm text-gray-600 mt-1">Your medical specialization and experience</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Specialization</label>
                        <input type="text" name="specialization" value="{{ old('specialization', $doctor->specialization) }}"
                               placeholder="Dermatologist"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Experience (Years)</label>
                        <input type="number" name="experience" value="{{ old('experience', (int)$doctor->experience) }}"
                               placeholder="8"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Location</label>
                        <input type="text" name="location" value="{{ old('location', $doctor->location) }}"
                               placeholder="Abuja, Nigeria"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div class="col-span-1 md:col-span-3">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Biography</label>
                        <textarea name="bio" rows="5" maxlength="1000" x-data="{ charCount: {{ strlen(old('bio', $doctor->bio ?? '')) }} }" 
                                  @input="charCount = $event.target.value.length"
                                  placeholder="Compassionate and detail-oriented medical doctor dedicated to providing accurate diagnosis, effective treatment, and patient-centered care. Committed to improving health outcomes through professionalism, empathy, and evidence-based practice."
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('bio', $doctor->bio) }}</textarea>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-xs text-gray-500">Write a professional bio to help patients understand your expertise</p>
                            <p class="text-xs font-semibold" :class="charCount > 900 ? 'text-amber-600' : 'text-gray-500'">
                                <span x-text="charCount">0</span>/1000 characters
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Service Capabilities Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="flex items-start gap-2 mb-6">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Service Capabilities</h3>
                            <p class="text-sm text-gray-600 mt-1">Configure the types of services you can provide</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Second Opinion Capability -->
                        <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <input type="hidden" name="can_provide_second_opinion" value="0">
                            <input type="checkbox" 
                                   id="can_provide_second_opinion" 
                                   name="can_provide_second_opinion" 
                                   value="1"
                                   {{ old('can_provide_second_opinion', $doctor->can_provide_second_opinion ?? true) ? 'checked' : '' }}
                                   class="mt-1 rounded text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <label for="can_provide_second_opinion" class="block text-sm font-bold text-gray-900 cursor-pointer">
                                    Provide Second Opinion Services
                                </label>
                                <p class="text-xs text-gray-600 mt-1">Enable this to allow patients to request second opinions from you. You'll review existing medical results, diagnoses, or treatment plans.</p>
                            </div>
                        </div>
                        
                        <!-- International Doctor Status -->
                        <div class="flex items-start gap-3 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <input type="hidden" name="is_international" value="0">
                            <input type="checkbox" 
                                   id="is_international" 
                                   name="is_international" 
                                   value="1"
                                   {{ old('is_international', $doctor->is_international ?? false) ? 'checked' : '' }}
                                   class="mt-1 rounded text-purple-600 focus:ring-purple-500"
                                   x-data
                                   @change="$el.checked && !confirm('International doctors can only provide second opinions and cannot conduct full consultations or prescribe locally. Continue?') ? $el.checked = false : null">
                            <div class="flex-1">
                                <label for="is_international" class="block text-sm font-bold text-gray-900 cursor-pointer">
                                    International Doctor
                                </label>
                                <p class="text-xs text-gray-600 mt-1">Check this if you are practicing outside Nigeria/Rwanda. International doctors are restricted to providing second opinions only.</p>
                            </div>
                        </div>
                        
                        <!-- Country of Practice (for international doctors) -->
                        <div x-data="{ isInternational: {{ old('is_international', $doctor->is_international ?? false) ? 'true' : 'false' }} }" 
                             x-init="$watch('isInternational', value => { if (!value) { $refs.countryInput.value = ''; } })">
                            <div x-show="isInternational" x-transition class="pl-7">
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Country of Practice</label>
                                <input type="text" 
                                       name="country_of_practice" 
                                       value="{{ old('country_of_practice', $doctor->country_of_practice) }}"
                                       placeholder="e.g., United States, United Kingdom"
                                       x-ref="countryInput"
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Specify the country where you are licensed to practice</p>
                            </div>
                            <script>
                                document.getElementById('is_international').addEventListener('change', function(e) {
                                    Alpine.store('isInternational', e.target.checked);
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Licenses & Verification Tab -->
            <div x-show="activeTab === 'licenses'" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="flex items-start gap-2 mb-6">
                    <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Licenses & Verification</h3>
                        <p class="text-sm text-gray-600 mt-1">Upload your professional credentials and verification documents</p>
                    </div>
                </div>

                <!-- MDCN Certificate -->
                <div class="border border-gray-200 rounded-xl p-6 mb-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">MDCN Certificate</h4>
                            <p class="text-xs text-gray-500 mt-1">Medical and Dental Council of Nigeria</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            VERIFIED
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">mdcn_license_2024.pdf</p>
                            <p class="text-xs text-gray-500">1.2 MB</p>
                        </div>
                        <button type="button" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Insurance Documents -->
                <div class="border border-gray-200 rounded-xl p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Insurance Documents</h4>
                            <p class="text-xs text-gray-500 mt-1">Professional liability insurance</p>
                        </div>
                        @if($doctor->insurance_document)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                UPLOADED
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                ACTION REQUIRED
                            </span>
                        @endif
                    </div>
                    
                    @if($doctor->insurance_document)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-4">
                            <svg class="w-8 h-8 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">Insurance Document</p>
                                <a href="{{ Storage::url($doctor->insurance_document) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">View Document</a>
                            </div>
                        </div>
                        
                        <div x-data="{ showUpload: false }">
                            <button @click="showUpload = !showUpload" type="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span x-text="showUpload ? 'Cancel Change' : 'Change Document'"></span>
                            </button>
                            
                            <div x-show="showUpload" class="mt-4" x-transition>
                                <label class="flex flex-col items-center justify-center gap-3 p-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <div class="text-center">
                                        <p class="text-sm font-medium text-gray-700">Select New File</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG up to 5MB</p>
                                    </div>
                                    <input type="file" name="insurance_document" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="showUpload = false; $el.closest('form').querySelector('.file-selected-msg').textContent = 'New file selected: ' + $el.files[0].name">
                                </label>
                                <p class="text-xs text-emerald-600 mt-2 font-medium file-selected-msg"></p>
                            </div>
                        </div>
                    @else
                        <label class="flex flex-col items-center justify-center gap-3 p-8 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-700">Upload Insurance Document</p>
                                <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG up to 5MB</p>
                            </div>
                            <input type="file" name="insurance_document" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        </label>
                    @endif
                </div>
            </div>

            <!-- Bank Accounts Tab -->
            <div x-show="activeTab === 'bank'" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Settlement Accounts</h3>
                            <p class="text-sm text-gray-600 mt-1">Manage your payout bank accounts</p>
                        </div>
                    </div>
                    <a href="{{ route('doctor.bank-accounts') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 w-full md:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New
                    </a>
                </div>

                <!-- Bank Accounts Grid -->
                <div class="grid gap-4 md:grid-cols-2">
                    @forelse($bankAccounts ?? [] as $account)
                        <div class="flex items-center gap-4 p-5 border {{ $account->is_default ? 'border-indigo-500 border-2' : 'border-gray-200' }} rounded-xl hover:border-indigo-300 transition-colors">
                            <div class="w-12 h-12 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $account->bank_name }}</p>
                                    @if($account->is_default)
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full">PRIMARY</span>
                                    @endif
                                    @if($account->is_verified)
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full">✓</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-full">PENDING</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $account->account_number }} • {{ $account->account_name }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(!$account->is_default)
                                    <form method="POST" action="{{ route('doctor.bank-accounts.set-default', $account->id) }}">
                                        @csrf
                                        <button type="submit" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" title="Set as default">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('doctor.bank-accounts.delete', $account->id) }}" id="deleteForm{{ $account->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteBankAccount({{ $account->id }})" class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-gray-50 rounded-xl p-12 text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Bank Accounts Yet</h3>
                            <p class="text-xs text-gray-500 mb-6">Add your bank account details to receive payments for consultations.</p>
                            <a href="{{ route('doctor.bank-accounts') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Your First Bank Account
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>



            <!-- Action Buttons -->
            <div class="flex flex-col-reverse md:flex-row items-center justify-end gap-3 mt-8 bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <button type="button" class="w-full md:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                    Cancel Changes
                </button>
                <button type="submit" class="w-full md:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors shadow-lg shadow-indigo-100">
                    Save Profile
                </button>
            </div>
        </form>


    </div>
</div>
@endsection

@push('scripts')
<script>
// Character counter for biography
document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
    textarea.addEventListener('input', function() {
        const counter = this.parentElement.querySelector('span[x-text*="length"]');
        if (counter) {
            counter.textContent = this.value.length;
        }
    });
});

// Bank account deletion confirmation
function confirmDeleteBankAccount(accountId) {
    if (confirm('Are you sure you want to delete this bank account? This action cannot be undone.')) {
        document.getElementById('deleteForm' + accountId).submit();
    }
}
</script>
@endpush
