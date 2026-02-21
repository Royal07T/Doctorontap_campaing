@extends('layouts.patient')

@section('title', 'Doctor Details - ' . $doctor->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('patient.doctors') }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm inline-flex items-center mb-3">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Doctors
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Doctor Details</h1>
    </div>

    <!-- Doctor Profile Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 lg:p-8">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Doctor Photo -->
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-2xl bg-gray-50 p-1 border border-gray-100">
                        @if($doctor->photo_url)
                            <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->name }}" class="w-full h-full object-cover rounded-xl">
                        @else
                            <div class="w-full h-full bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 font-bold text-4xl">
                                {{ substr($doctor->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <!-- Online Indicator -->
                    @if($doctor->is_available)
                        <div class="mt-3 flex items-center justify-center gap-2">
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="text-xs font-semibold text-green-600">Available Now</span>
                        </div>
                    @else
                        <div class="mt-3 flex items-center justify-center gap-2">
                            <span class="w-3 h-3 bg-gray-300 rounded-full"></span>
                            <span class="text-xs font-semibold text-gray-500">Offline</span>
                        </div>
                    @endif
                </div>

                <!-- Doctor Info -->
                <div class="flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $doctor->name }}</h2>
                            <p class="text-lg text-purple-600 font-semibold mb-3">{{ $doctor->specialization ?? 'General Practitioner' }}</p>
                        </div>
                        <!-- Rating -->
                        @if($avgRating > 0)
                        <div class="flex items-center gap-1 bg-yellow-50 px-3 py-2 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-base font-bold text-gray-700">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-xs text-gray-400">({{ $reviewsCount }} reviews)</span>
                        </div>
                        @endif
                    </div>

                    <!-- Key Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @if($doctor->experience)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Experience</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $doctor->experience }}</p>
                            </div>
                        </div>
                        @endif

                        @if($doctor->location)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Location</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $doctor->location }}</p>
                            </div>
                        </div>
                        @endif

                        @if($doctor->languages)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Languages</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $doctor->languages }}</p>
                            </div>
                        </div>
                        @endif

                        @if($doctor->place_of_work)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Place of Work</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $doctor->place_of_work }}</p>
                            </div>
                        </div>
                        @endif

                        @php
                            // Get consultation fee - always use doctor-set prices
                            // For fee ranges, show only the minimum fee
                            if ($doctor->min_consultation_fee && $doctor->max_consultation_fee) {
                                $feeDisplay = '₦' . number_format($doctor->min_consultation_fee, 0);
                            } elseif ($doctor->consultation_fee) {
                                $feeDisplay = '₦' . number_format($doctor->consultation_fee, 0);
                            } else {
                                $feeDisplay = 'Contact for pricing';
                            }
                        @endphp
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-purple-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Consultation Fee</p>
                                <p class="text-sm font-bold text-purple-600">{{ $feeDisplay }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bio -->
                    @if($doctor->bio)
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-2">About</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $doctor->bio }}</p>
                    </div>
                    @endif

                    <!-- Badges -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        @if($doctor->is_consultant ?? false)
                            <span class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Second Opinion Available
                            </span>
                        @endif
                        
                        @if($doctor->is_international ?? false)
                            <span class="inline-flex items-center px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                International
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('patient.doctors.book', $doctor->id) }}" 
                           class="flex-1 py-3 bg-purple-600 border border-purple-600 text-white font-bold text-sm rounded-xl hover:bg-purple-700 hover:border-purple-700 transition-all shadow-md hover:shadow-lg text-center">
                            Book Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    @if($doctor->days_of_availability)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Availability</h3>
        <p class="text-sm text-gray-600 leading-relaxed">{{ $doctor->days_of_availability }}</p>
    </div>
    @endif
</div>
@endsection

