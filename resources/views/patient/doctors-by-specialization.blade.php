@extends('layouts.patient')

@section('title', 'Doctors - ' . $specialization)

@section('content')
<!-- Header -->
<div class="mb-8">
    <a href="{{ route('patient.dashboard') }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm inline-flex items-center mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Dashboard
    </a>
    <h1 class="text-2xl font-bold text-gray-800">
        @if(isset($symptomName))
            Doctors for {{ $symptomName }}
        @else
            {{ $specialization }}
        @endif
    </h1>
    <p class="text-gray-600 mt-2">
        @if(isset($symptomName))
            {{ $doctors->count() }} {{ Str::plural('doctor', $doctors->count()) }} available in {{ $specialization }}
        @else
            {{ $doctors->count() }} {{ Str::plural('doctor', $doctors->count()) }} available
        @endif
    </p>
</div>

@if($doctors->count() > 0)
    <!-- Doctors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($doctors as $doctor)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
                <!-- Doctor Photo/Avatar -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 text-center">
                    @if($doctor->photo_url)
                        <img src="{{ $doctor->photo_url }}" alt="Dr. {{ $doctor->name }}" class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                    @else
                        <div class="w-24 h-24 rounded-full bg-purple-600 flex items-center justify-center mx-auto border-4 border-white shadow-lg">
                            <span class="text-3xl font-bold text-white">{{ substr($doctor->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    <!-- Doctor Name and Specialization -->
                    <div class="text-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">
                            @php
                                $name = trim($doctor->name);
                                $nameLower = strtolower($name);
                                $hasDrPrefix = preg_match('/^dr\.?\s*/i', $nameLower);
                            @endphp
                            {{ $hasDrPrefix ? $name : 'Dr. ' . $name }}
                        </h3>
                        <p class="text-sm font-semibold text-purple-600">{{ $doctor->specialization ?? 'General Practitioner' }}</p>
                    </div>

                    <!-- Availability Status -->
                    <div class="mb-4 text-center">
                        @if($doctor->is_available)
                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Available Now
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Currently Unavailable
                            </span>
                        @endif
                    </div>

                    <!-- Bio -->
                    @if($doctor->bio)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 line-clamp-3">{{ Str::limit($doctor->bio, 150) }}</p>
                        </div>
                    @endif

                    <!-- Professional Info (without contact details) -->
                    <div class="space-y-2 mb-4 text-sm">
                        @if($doctor->experience)
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $doctor->experience }}</span>
                            </div>
                        @endif

                        @if($doctor->languages)
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                <span>{{ $doctor->languages }}</span>
                            </div>
                        @endif

                        @if($doctor->location)
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ $doctor->location }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Verified Badge -->
                    @if($doctor->is_approved)
                        <div class="mb-4 text-center">
                            <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Verified Doctor
                            </span>
                        </div>
                    @endif

                    <!-- Action Button -->
                    <a href="{{ route('patient.consultation.new') }}?doctor_id={{ $doctor->id }}" 
                       class="block w-full text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        Book Appointment
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    @else
        <!-- No Doctors Found -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Doctors Found</h3>
            <p class="text-gray-500 mb-4">We don't have any doctors specializing in {{ $specialization }} at the moment.</p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('patient.dashboard') }}" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                    Back to Dashboard
                </a>
                <a href="{{ route('consultation.index') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Book General Consultation
                </a>
            </div>
        </div>
    @endif

    <!-- Info Box -->
    <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">How to Book a Consultation</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Click the "Book Consultation" button on any doctor's card to start a consultation. You can describe your symptoms and our doctors will provide expert medical advice.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

