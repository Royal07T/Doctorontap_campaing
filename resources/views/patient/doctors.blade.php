@extends('layouts.patient')

@section('title', 'Available Doctors')

@section('content')
<!-- Header -->
<div class="mb-6">
    <a href="{{ route('patient.dashboard') }}" class="text-purple-600 hover:text-purple-800 font-medium text-xs inline-flex items-center mb-3">
        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Dashboard
    </a>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Available Doctors</h1>
            <p class="text-xs text-gray-500 mt-1">Browse and book appointments with our verified doctors</p>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
    <form method="GET" action="{{ route('patient.doctors') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1.5">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                   placeholder="Search by name or specialization..."
                   class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
        </div>
        <div>
            <label for="specialization" class="block text-xs font-medium text-gray-700 mb-1.5">Specialization</label>
            <select name="specialization" id="specialization" 
                    class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200">
                <option value="">All Specializations</option>
                @foreach($specializations as $spec)
                    <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>
                        {{ $spec }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 purple-gradient hover:opacity-90 text-white text-sm font-medium rounded-lg transition">
                Filter
            </button>
        </div>
    </form>
</div>

@if($doctors->count() > 0)
    <!-- Doctors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach($doctors as $doctor)
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all overflow-hidden">
                <!-- Doctor Photo/Avatar -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-5 text-center">
                    @if($doctor->photo_url)
                        <img src="{{ $doctor->photo_url }}" alt="Dr. {{ $doctor->name }}" class="w-20 h-20 rounded-full mx-auto object-cover border-3 border-white shadow-md">
                    @else
                        <div class="w-20 h-20 rounded-full bg-purple-600 flex items-center justify-center mx-auto border-3 border-white shadow-md">
                            <span class="text-2xl font-bold text-white">{{ substr($doctor->name, 0, 1) }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-5">
                    <!-- Doctor Name and Specialization -->
                    <div class="text-center mb-3">
                        <h3 class="text-sm font-bold text-gray-900 mb-1">
                            @php
                                $name = trim($doctor->name);
                                $nameLower = strtolower($name);
                                $hasDrPrefix = preg_match('/^dr\.?\s*/i', $nameLower);
                            @endphp
                            {{ $hasDrPrefix ? $name : 'Dr. ' . $name }}
                        </h3>
                        <p class="text-xs font-semibold text-purple-600">{{ $doctor->specialization ?? 'General Practitioner' }}</p>
                    </div>

                    <!-- Ratings and Consultation Count -->
                    <div class="mb-3 flex items-center justify-center gap-4 text-xs">
                        @php
                            $avgRating = $doctor->average_rating ?? 0;
                            $reviewsCount = $doctor->published_reviews_count ?? 0;
                            $consultationsCount = $doctor->consultations_count ?? 0;
                        @endphp
                        
                        @if($reviewsCount > 0)
                            <div class="flex items-center gap-1">
                                <div class="flex items-center text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($avgRating))
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @elseif($i - 0.5 <= $avgRating)
                                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20" style="clip-path: inset(0 50% 0 0);">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-gray-700 font-semibold">{{ number_format($avgRating, 1) }}</span>
                                <span class="text-gray-500">({{ $reviewsCount }})</span>
                            </div>
                        @else
                            <div class="text-gray-500">No reviews yet</div>
                        @endif
                        
                        @if($consultationsCount > 0)
                            <div class="flex items-center gap-1 text-gray-600">
                                <svg class="w-3 h-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>{{ $consultationsCount }} consultations</span>
                            </div>
                        @endif
                    </div>

                    <!-- Availability Status -->
                    <div class="mb-3 text-center">
                        @if($doctor->is_available)
                            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Available Now
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Currently Unavailable
                            </span>
                        @endif
                    </div>

                    <!-- Bio Preview -->
                    @if($doctor->bio)
                        <div class="mb-3">
                            <p class="text-xs text-gray-600 line-clamp-2">{{ \Illuminate\Support\Str::limit($doctor->bio, 100) }}</p>
                        </div>
                    @endif

                    <!-- Dropdown Toggle Button -->
                    <button @click="open = !open" class="w-full mb-3 flex items-center justify-center text-xs text-purple-600 hover:text-purple-800 font-medium">
                        <span x-text="open ? 'Show Less' : 'View Details'"></span>
                        <svg class="w-4 h-4 ml-1 transition-transform duration-200" 
                             :class="{ 'rotate-180': open }" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Content -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         x-cloak
                         class="border-t border-gray-100 pt-3 mt-3"
                         style="display: none;">
                        <!-- Professional Info -->
                        <div class="space-y-2 mb-3 text-xs">
                        @if($doctor->experience)
                            <div class="flex items-center text-gray-600">
                                    <svg class="w-3 h-3 mr-1.5 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $doctor->experience }}</span>
                            </div>
                        @endif

                        @if($doctor->languages)
                            <div class="flex items-center text-gray-600">
                                    <svg class="w-3 h-3 mr-1.5 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                <span>{{ $doctor->languages }}</span>
                            </div>
                        @endif

                        @if($doctor->location)
                            <div class="flex items-center text-gray-600">
                                    <svg class="w-3 h-3 mr-1.5 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ $doctor->location }}</span>
                            </div>
                        @endif
                    </div>

                        <!-- Full Bio -->
                        @if($doctor->bio)
                            <div class="mb-3">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">About</p>
                                <p class="text-xs text-gray-700 leading-relaxed">{{ $doctor->bio }}</p>
                            </div>
                        @endif

                    <!-- Verified Badge -->
                    @if($doctor->is_approved)
                            <div class="mb-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Verified Doctor
                            </span>
                        </div>
                    @endif
                    </div>

                    <!-- Action Button -->
                    <button onclick="window.openBookingModal({{ $doctor->id }}, '{{ addslashes($doctor->name) }}', '{{ addslashes($doctor->specialization ?? 'General Practitioner') }}')" 
                            class="block w-full text-center px-4 py-2 purple-gradient hover:opacity-90 text-white text-xs font-medium rounded-lg transition">
                        Book Appointment
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $doctors->links() }}
    </div>
@else
    <!-- No Doctors Found -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-sm font-semibold text-gray-900 mb-2">No Doctors Found</h3>
        <p class="text-xs text-gray-500 mb-4">We don't have any available doctors matching your criteria at the moment.</p>
        <div class="flex gap-2 justify-center">
            <a href="{{ route('patient.dashboard') }}" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 text-xs font-medium rounded-lg transition">
                Back to Dashboard
            </a>
            <a href="{{ route('consultation.index') }}" class="inline-block purple-gradient hover:opacity-90 text-white px-4 py-2 text-xs font-medium rounded-lg transition">
                Book General Consultation
            </a>
        </div>
    </div>
@endif

<!-- Info Box -->
<div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-4 w-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-xs font-medium text-blue-800">How to Book an Appointment</h3>
            <div class="mt-1.5 text-xs text-blue-700">
                <p>Click the "Book Appointment" button on any doctor's card to select a date and time for your consultation. Available slots are shown based on the doctor's schedule.</p>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" 
     x-data="bookingModal()" 
     x-show="showModal" 
     x-cloak 
     @click.away="closeModal()">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Book Appointment</h3>
                <p class="text-xs text-gray-500 mt-1" x-text="doctorName"></p>
                <p class="text-xs text-purple-600 font-medium" x-text="doctorSpecialization"></p>
            </div>
            <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form @submit.prevent="submitBooking()" enctype="multipart/form-data">
                <!-- DATE & TIME SELECTION -->
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200 p-5 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Step 1: Schedule Appointment</h3>
                            <p class="text-xs text-gray-600">Select date and time</p>
                        </div>
                    </div>

                    <!-- Date Selection -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Select Date *</label>
                        <input type="date" 
                               x-model="selectedDate" 
                               @change="loadTimeSlots()"
                               :min="minDate"
                               required
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        <p class="text-xs text-red-500 mt-1" x-show="dateError" x-text="dateError"></p>
                    </div>

                    <!-- Time Slot Selection -->
                    <div class="mb-4" x-show="selectedDate && availableSlots.length > 0">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Select Time *</label>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="slot in availableSlots" :key="slot.value">
                                <button type="button"
                                        @click="selectedTime = slot.value; checkSlotAvailability()"
                                        :class="{
                                            'bg-blue-600 text-white border-blue-600': selectedTime === slot.value && !slot.booked,
                                            'bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed': slot.booked,
                                            'bg-white text-gray-700 border-gray-300 hover:border-blue-500': selectedTime !== slot.value && !slot.booked
                                        }"
                                        :disabled="slot.booked"
                                        class="px-3 py-2 text-xs font-medium rounded-lg border transition">
                                    <span x-text="slot.label"></span>
                                    <span x-show="slot.booked" class="block text-[10px] mt-0.5">Booked</span>
                                </button>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 mt-2" x-show="selectedDate && availableSlots.length === 0">No available time slots for this date</p>
                    </div>
                </div>

                <!-- MEDICAL INFORMATION SECTION -->
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200 p-5 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Step 2: Medical Information</h3>
                            <p class="text-xs text-gray-600">Help us understand your health concern</p>
                        </div>
                    </div>
                    
                    <!-- Problem Description -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">What's the problem right now? *</label>
                        <input type="text" 
                               x-model="problem"
                               required
                               minlength="10"
                               maxlength="500"
                               placeholder="Brief description of your main concern (at least 10 characters)"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-colors">
                        <p x-show="errors.problem" class="text-red-500 text-xs mt-1" x-text="errors.problem"></p>
                    </div>

                    <!-- Medical Documents Upload -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Upload Medical Documents (Optional)</label>
                        <p class="text-xs text-gray-600 mb-2">Upload test results, lab reports, X-rays, or prescriptions (PDF, JPG, PNG, DOC - Max 5MB)</p>
                        <input type="file" 
                               @change="handleFileUpload($event)"
                               multiple
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white file:mr-4 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                        <div x-show="uploadedFiles.length > 0" class="mt-2">
                            <p class="text-xs font-semibold text-gray-700 mb-1.5">Selected files:</p>
                            <ul class="space-y-1">
                                <template x-for="(file, index) in uploadedFiles" :key="index">
                                    <li class="flex items-center justify-between text-xs bg-white px-3 py-2 rounded-lg border border-gray-200">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span x-text="file.name" class="truncate max-w-xs"></span>
                                            <span class="text-gray-500" x-text="'(' + (file.size / 1024).toFixed(0) + ' KB)'"></span>
                                        </span>
                                        <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700 ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <!-- Severity -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">How bad is it? *</label>
                        <select x-model="severity"
                                required
                                class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white transition-colors">
                            <option value="">Select Severity</option>
                            <option value="mild">🟢 Mild - Not urgent</option>
                            <option value="moderate">🟡 Moderate - Needs attention</option>
                            <option value="severe">🔴 Severe - Urgent care needed</option>
                        </select>
                        <p x-show="errors.severity" class="text-red-500 text-xs mt-1" x-text="errors.severity"></p>
                    </div>

                    <!-- Emergency Symptoms -->
                    <div x-data="symptomsSelector()" class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Are you experiencing any of these symptoms now? (Optional)</label>
                        
                        <!-- Selected Symptoms Display -->
                        <div x-show="selectedSymptoms.length > 0" class="flex flex-wrap gap-2 mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <template x-for="symptom in selectedSymptoms" :key="symptom.value">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-full">
                                    <span x-text="symptom.label"></span>
                                    <button type="button" @click="removeSymptom(symptom.value)" class="hover:bg-blue-700 rounded-full p-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                            <button type="button" @click="clearAll()" class="text-xs text-red-600 hover:text-red-800 font-medium underline ml-2">Clear all</button>
                        </div>

                        <!-- Dropdown Selector (Always visible) -->
                        <div class="relative">
                                <button type="button"
                                        @click="isOpen = !isOpen"
                                        class="w-full px-4 py-3 text-sm text-left bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all flex items-center justify-between hover:border-blue-400">
                                    <span class="text-gray-700" x-text="selectedSymptoms.length > 0 ? `${selectedSymptoms.length} symptom(s) selected` : 'Click to select symptoms'"></span>
                                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div x-show="isOpen"
                                     @click.away="isOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-2"
                                     class="absolute z-50 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-xl max-h-96 overflow-hidden"
                                     style="display: none;">
                                    <div class="p-3 border-b border-gray-200 bg-gray-50 sticky top-0">
                                        <div class="relative">
                                            <input type="text"
                                                   x-model="searchQuery"
                                                   @input="filterSymptoms()"
                                                   placeholder="Search symptoms..."
                                                   class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="overflow-y-auto max-h-80">
                                        <template x-if="filteredSymptoms.critical.length > 0">
                                            <div class="border-b border-gray-200">
                                                <div class="px-3 py-2 bg-red-50 sticky top-0">
                                                    <span class="text-xs font-bold text-red-700 uppercase tracking-wide">🚨 Critical/Emergency</span>
                                                </div>
                                                <template x-for="symptom in filteredSymptoms.critical" :key="symptom.value">
                                                    <label class="flex items-start gap-3 px-4 py-2.5 hover:bg-red-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                                        <input type="checkbox" 
                                                               :value="symptom.value"
                                                               :checked="isSelected(symptom.value)"
                                                               @change="toggleSymptom(symptom)"
                                                               class="mt-0.5 rounded text-red-600 focus:ring-red-500 flex-shrink-0">
                                                        <span class="text-sm text-gray-800" x-text="symptom.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="filteredSymptoms.respiratory.length > 0">
                                            <div class="border-b border-gray-200">
                                                <div class="px-3 py-2 bg-cyan-50 sticky top-0">
                                                    <span class="text-xs font-bold text-cyan-700 uppercase tracking-wide">🫁 Respiratory</span>
                                                </div>
                                                <template x-for="symptom in filteredSymptoms.respiratory" :key="symptom.value">
                                                    <label class="flex items-start gap-3 px-4 py-2.5 hover:bg-cyan-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                                        <input type="checkbox" 
                                                               :value="symptom.value"
                                                               :checked="isSelected(symptom.value)"
                                                               @change="toggleSymptom(symptom)"
                                                               class="mt-0.5 rounded text-cyan-600 focus:ring-cyan-500 flex-shrink-0">
                                                        <span class="text-sm text-gray-800" x-text="symptom.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="filteredSymptoms.neurological.length > 0">
                                            <div class="border-b border-gray-200">
                                                <div class="px-3 py-2 bg-purple-50 sticky top-0">
                                                    <span class="text-xs font-bold text-purple-700 uppercase tracking-wide">🧠 Neurological</span>
                                                </div>
                                                <template x-for="symptom in filteredSymptoms.neurological" :key="symptom.value">
                                                    <label class="flex items-start gap-3 px-4 py-2.5 hover:bg-purple-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                                        <input type="checkbox" 
                                                               :value="symptom.value"
                                                               :checked="isSelected(symptom.value)"
                                                               @change="toggleSymptom(symptom)"
                                                               class="mt-0.5 rounded text-purple-600 focus:ring-purple-500 flex-shrink-0">
                                                        <span class="text-sm text-gray-800" x-text="symptom.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="filteredSymptoms.pain.length > 0">
                                            <div class="border-b border-gray-200">
                                                <div class="px-3 py-2 bg-orange-50 sticky top-0">
                                                    <span class="text-xs font-bold text-orange-700 uppercase tracking-wide">🩹 Pain & Discomfort</span>
                                                </div>
                                                <template x-for="symptom in filteredSymptoms.pain" :key="symptom.value">
                                                    <label class="flex items-start gap-3 px-4 py-2.5 hover:bg-orange-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                                        <input type="checkbox" 
                                                               :value="symptom.value"
                                                               :checked="isSelected(symptom.value)"
                                                               @change="toggleSymptom(symptom)"
                                                               class="mt-0.5 rounded text-orange-600 focus:ring-orange-500 flex-shrink-0">
                                                        <span class="text-sm text-gray-800" x-text="symptom.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="filteredSymptoms.general.length > 0">
                                            <div>
                                                <div class="px-3 py-2 bg-blue-50 sticky top-0">
                                                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wide">📋 General Symptoms</span>
                                                </div>
                                                <template x-for="symptom in filteredSymptoms.general" :key="symptom.value">
                                                    <label class="flex items-start gap-3 px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                                        <input type="checkbox" 
                                                               :value="symptom.value"
                                                               :checked="isSelected(symptom.value)"
                                                               @change="toggleSymptom(symptom)"
                                                               class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 flex-shrink-0">
                                                        <span class="text-sm text-gray-800" x-text="symptom.label"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <div x-show="!hasResults()" class="px-4 py-8 text-center text-gray-500">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm">No symptoms found</p>
                                        </div>
                                    </div>

                                    <div class="p-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                                        <span class="text-xs text-gray-600" x-text="`${selectedSymptoms.length} selected`"></span>
                                        <button type="button" @click="isOpen = false" class="px-4 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">Done</button>
                                    </div>
                                </div>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <template x-for="symptom in selectedSymptoms" :key="symptom.value">
                            <input type="hidden" name="emergency_symptoms[]" :value="symptom.value">
                        </template>
                    </div>
                </div>

                <!-- CONSULTATION MODE -->
                <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl border border-purple-200 p-5 mb-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Step 3: Consultation Mode</h3>
                            <p class="text-xs text-gray-600">Choose how you'd like to consult</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="consultMode = 'voice'" 
                                :class="consultMode === 'voice' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500'"
                                class="px-4 py-2 text-xs font-medium rounded-lg border transition">
                            Voice Call
                        </button>
                        <button type="button" @click="consultMode = 'video'" 
                                :class="consultMode === 'video' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500'"
                                class="px-4 py-2 text-xs font-medium rounded-lg border transition">
                            Video Call
                        </button>
                        <button type="button" @click="consultMode = 'chat'" 
                                :class="consultMode === 'chat' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500'"
                                class="px-4 py-2 text-xs font-medium rounded-lg border transition">
                            Chat
                        </button>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs text-red-600" x-text="errorMessage"></p>
                </div>

                <!-- Success Message -->
                <div x-show="successMessage" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <p class="text-xs text-emerald-600" x-text="successMessage"></p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="button" @click="closeModal()" 
                            class="flex-1 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="isSubmitting || !selectedDate || !selectedTime || !problem"
                            :class="(isSubmitting || !selectedDate || !selectedTime || !problem) ? 'opacity-50 cursor-not-allowed' : ''"
                            class="flex-1 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        <span x-show="!isSubmitting">Book Appointment</span>
                        <span x-show="isSubmitting">Booking...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Make function globally available - must be defined before Alpine initializes
window.openBookingModal = function(doctorId, doctorName, doctorSpecialization) {
    const event = new CustomEvent('open-booking-modal', { 
        detail: { doctorId, doctorName, doctorSpecialization } 
    });
    window.dispatchEvent(event);
}

// Make Alpine component function globally available
function bookingModal() {
    return {
        showModal: false,
        doctorId: null,
        doctorName: '',
        doctorSpecialization: '',
        selectedDate: '',
        selectedTime: '',
        consultMode: 'voice',
        problem: '',
        severity: 'mild',
        availableSlots: [],
        bookedSlots: [],
        isSubmitting: false,
        errorMessage: '',
        successMessage: '',
        dateError: '',
        minDate: new Date().toISOString().split('T')[0],
        uploadedFiles: [],
        emergencySymptoms: [],
        // Validation errors
        errors: {},

        init() {
            window.addEventListener('open-booking-modal', (e) => {
                this.doctorId = e.detail.doctorId;
                this.doctorName = e.detail.doctorName;
                this.doctorSpecialization = e.detail.doctorSpecialization;
                this.showModal = true;
                this.resetForm();
                this.loadDoctorAvailability();
            });
        },

        resetForm() {
            this.selectedDate = '';
            this.selectedTime = '';
            this.consultMode = 'voice';
            this.problem = '';
            this.severity = 'mild';
            this.availableSlots = [];
            this.bookedSlots = [];
            this.errorMessage = '';
            this.successMessage = '';
            this.dateError = '';
            this.uploadedFiles = [];
            this.emergencySymptoms = [];
        },

        handleFileUpload(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                if (file.size > 5 * 1024 * 1024) {
                    this.errorMessage = `File ${file.name} is too large. Maximum size is 5MB.`;
                    return;
                }
                this.uploadedFiles.push(file);
            });
        },

        removeFile(index) {
            this.uploadedFiles.splice(index, 1);
        },

        closeModal() {
            this.showModal = false;
            setTimeout(() => this.resetForm(), 300);
        },

        async loadDoctorAvailability() {
            try {
                const response = await fetch(`/patient/doctors/${this.doctorId}/availability`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.bookedSlots = data.booked_slots || [];
                }
            } catch (error) {
                console.error('Error loading availability:', error);
            }
        },

        loadTimeSlots() {
            if (!this.selectedDate) return;
            
            this.selectedTime = '';
            this.dateError = '';
            
            const selectedDateObj = new Date(this.selectedDate);
            const dayOfWeek = selectedDateObj.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
            
            // Load doctor availability for this day
            fetch(`/patient/doctors/${this.doctorId}/availability`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const schedule = data.availability_schedule || {};
                    const daySchedule = schedule[dayOfWeek];
                    
                    if (!daySchedule || !daySchedule.enabled) {
                        this.dateError = 'Doctor is not available on this day';
                        this.availableSlots = [];
                        return;
                    }
                    
                    // Generate time slots (30-minute intervals)
                    const slots = [];
                    const start = new Date(`${this.selectedDate}T${daySchedule.start}`);
                    const end = new Date(`${this.selectedDate}T${daySchedule.end}`);
                    
                    let current = new Date(start);
                    while (current < end) {
                        const timeStr = current.toTimeString().slice(0, 5);
                        const slotDateTime = `${this.selectedDate} ${timeStr}`;
                        
                        // Check if this slot is booked
                        const isBooked = this.bookedSlots.some(booked => {
                            const bookedDate = booked.date;
                            const bookedTime = booked.time;
                            return bookedDate === this.selectedDate && bookedTime === timeStr;
                        });
                        
                        slots.push({
                            value: timeStr,
                            label: current.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
                            booked: isBooked
                        });
                        
                        current.setMinutes(current.getMinutes() + 30);
                    }
                    
                    this.availableSlots = slots;
                }
            })
            .catch(error => {
                console.error('Error loading time slots:', error);
                this.dateError = 'Error loading available time slots';
            });
        },

        async checkSlotAvailability() {
            if (!this.selectedDate || !this.selectedTime) return;
            
            const scheduledAt = `${this.selectedDate} ${this.selectedTime}`;
            
            try {
                const response = await fetch('/patient/doctors/check-slot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        doctor_id: this.doctorId,
                        scheduled_at: scheduledAt
                    })
                });
                
                const data = await response.json();
                if (!data.success || !data.available) {
                    this.errorMessage = data.message || 'This time slot is not available';
                    this.selectedTime = '';
                } else {
                    this.errorMessage = '';
                }
            } catch (error) {
                console.error('Error checking slot:', error);
            }
        },

        async submitBooking() {
            if (!this.selectedDate || !this.selectedTime || !this.problem) {
                this.errorMessage = 'Please fill in all required fields';
                return;
            }
            
            this.isSubmitting = true;
            this.errorMessage = '';
            this.successMessage = '';
            
            const scheduledAt = `${this.selectedDate} ${this.selectedTime}`;
            
            // Collect emergency symptoms from hidden inputs
            const symptomInputs = document.querySelectorAll('input[name="emergency_symptoms[]"]');
            const emergencySymptoms = Array.from(symptomInputs).map(input => input.value);
            
            try {
                const formData = new FormData();
                formData.append('doctor_id', this.doctorId);
                formData.append('scheduled_at', scheduledAt);
                formData.append('consult_mode', this.consultMode);
                formData.append('problem', this.problem);
                formData.append('severity', this.severity);
                
                // Add emergency symptoms
                emergencySymptoms.forEach(symptom => {
                    formData.append('emergency_symptoms[]', symptom);
                });
                
                // Add medical documents
                this.uploadedFiles.forEach((file, index) => {
                    formData.append(`medical_documents[${index}]`, file);
                });
                
                const response = await fetch('/patient/doctors/book', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                // Handle validation errors
                if (data.errors) {
                    this.errors = data.errors;
                    this.isSubmitting = false;
                    return;
                }
                
                if (data.success) {
                    this.successMessage = data.message || 'Appointment booked successfully!';
                    setTimeout(() => {
                        window.location.href = '/patient/consultations';
                    }, 1500);
                } else {
                    this.errorMessage = data.message || 'Failed to book appointment. Please try again.';
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                this.errorMessage = 'An error occurred. Please try again.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}

// Symptoms Selector Component
function symptomsSelector() {
    return {
        isOpen: false,
        searchQuery: '',
        selectedSymptoms: [],
        
        allSymptoms: {
            critical: [
                { value: 'chest_pain', label: 'Chest pain or pressure', category: 'critical' },
                { value: 'breathing', label: 'Severe difficulty breathing', category: 'critical' },
                { value: 'consciousness', label: 'Loss of consciousness / fainting', category: 'critical' },
                { value: 'bleeding', label: 'Uncontrolled heavy bleeding', category: 'critical' },
                { value: 'seizure', label: 'Seizure now or ongoing', category: 'critical' },
                { value: 'pregnancy_bleeding', label: 'Heavy vaginal bleeding in pregnancy', category: 'critical' }
            ],
            respiratory: [
                { value: 'cough', label: 'Persistent cough', category: 'respiratory' },
                { value: 'shortness_breath', label: 'Shortness of breath', category: 'respiratory' },
                { value: 'wheezing', label: 'Wheezing', category: 'respiratory' }
            ],
            neurological: [
                { value: 'weakness', label: 'Sudden weakness, numbness', category: 'neurological' },
                { value: 'speech', label: 'Slurred speech', category: 'neurological' },
                { value: 'headache', label: 'Severe headache', category: 'neurological' },
                { value: 'dizziness', label: 'Dizziness or vertigo', category: 'neurological' }
            ],
            pain: [
                { value: 'abdominal', label: 'Severe abdominal pain', category: 'pain' },
                { value: 'back_pain', label: 'Severe back pain', category: 'pain' },
                { value: 'joint_pain', label: 'Joint pain', category: 'pain' }
            ],
            general: [
                { value: 'fever', label: 'Very high fever', category: 'general' },
                { value: 'fatigue', label: 'Extreme fatigue', category: 'general' },
                { value: 'nausea', label: 'Nausea or vomiting', category: 'general' },
                { value: 'dehydration', label: 'Signs of dehydration', category: 'general' }
            ]
        },
        
        filteredSymptoms: {
            critical: [],
            respiratory: [],
            neurological: [],
            pain: [],
            general: []
        },
        
        init() {
            this.filterSymptoms();
        },
        
        filterSymptoms() {
            const query = this.searchQuery.toLowerCase();
            
            Object.keys(this.allSymptoms).forEach(category => {
                this.filteredSymptoms[category] = this.allSymptoms[category].filter(symptom => 
                    symptom.label.toLowerCase().includes(query)
                );
            });
        },
        
        getAllSymptomsFlat() {
            const allFlat = [];
            Object.keys(this.allSymptoms).forEach(category => {
                allFlat.push(...this.allSymptoms[category]);
            });
            return allFlat;
        },
        
        toggleSymptom(symptom) {
            const index = this.selectedSymptoms.findIndex(s => s.value === symptom.value);
            if (index > -1) {
                this.selectedSymptoms.splice(index, 1);
            } else {
                this.selectedSymptoms.push(symptom);
            }
        },
        
        removeSymptom(value) {
            this.selectedSymptoms = this.selectedSymptoms.filter(s => s.value !== value);
        },
        
        clearAll() {
            this.selectedSymptoms = [];
        },
        
        isSelected(value) {
            return this.selectedSymptoms.some(s => s.value === value);
        },
        
        hasResults() {
            return Object.values(this.filteredSymptoms).some(cat => cat.length > 0);
        }
    }
}
</script>
@endsection

