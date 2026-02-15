@extends('layouts.patient')

@section('title', 'Doctors - ' . $specialization)

@section('content')
<!-- Header -->
<div class="mb-6">
    <a href="{{ route('patient.dashboard') }}" class="text-purple-600 hover:text-purple-800 font-medium text-xs inline-flex items-center mb-3">
        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Dashboard
    </a>
    <h1 class="text-lg font-bold text-gray-900">
        @if(isset($symptomName))
            Doctors for {{ $symptomName }}
        @else
            {{ $specialization }}
        @endif
    </h1>
    <p class="text-xs text-gray-500 mt-1">
        @if(isset($symptomName))
            {{ $doctors->count() }} {{ Str::plural('doctor', $doctors->count()) }} available in {{ $specialization }}
        @else
            {{ $doctors->count() }} {{ Str::plural('doctor', $doctors->count()) }} available
        @endif
    </p>
</div>

@if($doctors->count() > 0)
    <!-- Doctors Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

                    <!-- Availability Status -->
                    <div class="mb-3 text-center flex flex-col gap-2">
                        @if($doctor->is_available)
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Available Now
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                Currently Unavailable
                            </span>
                        @endif
                        
                        <!-- Second Opinion Badge -->
                        @if($doctor->can_provide_second_opinion ?? true)
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Second Opinion Available
                            </span>
                        @endif
                        
                        <!-- International Doctor Badge -->
                        @if($doctor->is_international ?? false)
                            <span class="inline-flex items-center justify-center px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                International Doctor
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
@else
    <!-- No Doctors Found -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-sm font-semibold text-gray-900 mb-2">No Doctors Found</h3>
        <p class="text-xs text-gray-500 mb-4">We don't have any doctors specializing in {{ $specialization }} at the moment.</p>
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
</div>

<!-- Booking Modal -->
<div id="bookingModal" 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" 
     x-data="bookingModal()" 
     x-show="showModal" 
     x-cloak 
     @click.away="closeModal()">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
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

        <div class="p-6">
            <form @submit.prevent="submitBooking()" enctype="multipart/form-data">
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

                    <div class="mb-4" x-show="selectedDate && availableSlots.length > 0">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide">Select Time *</label>
                            <button type="button" 
                                    @click="loadTimeSlots()" 
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </button>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="slot in availableSlots" :key="slot.value">
                                <div class="relative group">
                                <button type="button"
                                        @click="selectedTime = slot.value; checkSlotAvailability()"
                                        :class="{
                                                'bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-200': selectedTime === slot.value && !slot.booked && !slot.conflict,
                                                'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed': slot.booked,
                                                'bg-yellow-50 text-yellow-700 border-yellow-300 ring-2 ring-yellow-100': slot.conflict,
                                                'bg-white text-gray-700 border-gray-300 hover:border-blue-500 hover:bg-blue-50': selectedTime !== slot.value && !slot.booked && !slot.conflict
                                        }"
                                            :disabled="slot.booked || slot.conflict"
                                            class="w-full px-2 py-2 text-xs font-medium rounded-lg border transition-all relative flex flex-col items-center justify-center min-h-[3rem]">
                                    <span x-text="slot.label"></span>
                                        <span x-show="slot.booked" class="text-[9px] mt-0.5 text-red-500 font-semibold uppercase tracking-wider">Booked</span>
                                        <span x-show="slot.conflict" class="text-[9px] mt-0.5 text-yellow-600 font-semibold uppercase tracking-wider">Taken</span>
                                        <span x-show="selectedTime === slot.value && !slot.booked && !slot.conflict" 
                                              class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white rounded-full shadow-sm flex items-center justify-center">
                                            <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </span>
                                </button>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 mt-2" x-show="selectedDate && availableSlots.length === 0">No available time slots for this date</p>
                    </div>
                </div>

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

                    <div x-data="symptomsSelector()" class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Are you experiencing any of these symptoms now? (Optional)</label>
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
                        <template x-for="symptom in selectedSymptoms" :key="symptom.value">
                            <input type="hidden" name="emergency_symptoms[]" :value="symptom.value">
                        </template>
                    </div>
                </div>

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
                                :class="consultMode === 'voice' ? 'bg-purple-600 text-white border-purple-600 shadow-md ring-2 ring-purple-200' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500 hover:bg-purple-50'"
                                class="flex flex-col items-center justify-center px-4 py-3 text-xs font-medium rounded-lg border transition-all duration-200">
                            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                            Voice Call
                        </button>
                        <button type="button" @click="consultMode = 'video'" 
                                :class="consultMode === 'video' ? 'bg-purple-600 text-white border-purple-600 shadow-md ring-2 ring-purple-200' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500 hover:bg-purple-50'"
                                class="flex flex-col items-center justify-center px-4 py-3 text-xs font-medium rounded-lg border transition-all duration-200">
                            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Video Call
                        </button>
                        <button type="button" @click="consultMode = 'chat'" 
                                :class="consultMode === 'chat' ? 'bg-purple-600 text-white border-purple-600 shadow-md ring-2 ring-purple-200' : 'bg-white text-gray-700 border-gray-300 hover:border-purple-500 hover:bg-purple-50'"
                                class="flex flex-col items-center justify-center px-4 py-3 text-xs font-medium rounded-lg border transition-all duration-200">
                            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Chat
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-3 text-center flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        All modes use secure in-app consultation
                    </p>
                </div>

                <div x-show="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs text-red-600" x-text="errorMessage"></p>
                </div>

                <div x-show="successMessage" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <p class="text-xs text-emerald-600" x-text="successMessage"></p>
                </div>

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
window.openBookingModal = function(doctorId, doctorName, doctorSpecialization) {
    const event = new CustomEvent('open-booking-modal', { 
        detail: { doctorId, doctorName, doctorSpecialization } 
    });
    window.dispatchEvent(event);
}

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
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache'
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

        async loadTimeSlots() {
            if (!this.selectedDate) return;
            this.selectedTime = '';
            this.dateError = '';
            const selectedDateObj = new Date(this.selectedDate);
            const dayOfWeek = selectedDateObj.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
            try {
                const response = await fetch(`/patient/doctors/${this.doctorId}/availability`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache'
                }
                });
                const data = await response.json();
                if (data.success) {
                    this.bookedSlots = data.booked_slots || [];
                    const schedule = data.availability_schedule || {};
                    const daySchedule = schedule[dayOfWeek];
                    if (!daySchedule || !daySchedule.enabled) {
                        this.dateError = 'Doctor is not available on this day';
                        this.availableSlots = [];
                        return;
                    }
                    const slots = [];
                    const startTime = daySchedule.start; // e.g., "18:00"
                    const endTime = daySchedule.end; // e.g., "06:00"
                    
                    // Parse times
                    const [startHour, startMin] = startTime.split(':').map(Number);
                    const [endHour, endMin] = endTime.split(':').map(Number);
                    
                    // Check if this is an overnight schedule (end < start)
                    const isOvernight = endHour < startHour || (endHour === startHour && endMin < startMin);
                    
                    let currentHour = startHour;
                    let currentMin = startMin;
                    let slotDate = new Date(`${this.selectedDate}T${startTime}`);
                    const maxIterations = isOvernight ? 48 : 24; // Prevent infinite loops
                    let iterations = 0;
                    
                    while (iterations < maxIterations) {
                        const timeStr = `${String(currentHour).padStart(2, '0')}:${String(currentMin).padStart(2, '0')}`;
                        const slotDateTimeObj = new Date(`${this.selectedDate}T${timeStr}`);
                        
                        // Check if we've reached the end time
                        if (!isOvernight) {
                            // Normal schedule: stop when we reach or pass end time
                            if (currentHour > endHour || (currentHour === endHour && currentMin >= endMin)) {
                                break;
                            }
                        } else {
                            // Overnight schedule: stop when we pass end time (on same day)
                            // For overnight, we generate slots from start to 23:59, then 00:00 to end
                            if (currentHour > 23) {
                                // Reset to next day 00:00
                                currentHour = 0;
                                currentMin = 0;
                                slotDate = new Date(slotDate);
                                slotDate.setDate(slotDate.getDate() + 1);
                            }
                            // Stop when we've passed end time on the next day
                            if (slotDate.getDate() > new Date(this.selectedDate).getDate() && 
                                (currentHour > endHour || (currentHour === endHour && currentMin >= endMin))) {
                                break;
                            }
                        }
                        
                        const isBooked = this.bookedSlots.some(booked => {
                            const bookedDateTime = new Date(`${booked.date}T${booked.time}`);
                            const timeDiff = Math.abs(slotDateTimeObj - bookedDateTime) / 1000 / 60;
                            return timeDiff < 30;
                        });
                        
                        slots.push({
                            value: timeStr,
                            label: slotDateTimeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
                            booked: isBooked,
                            conflict: false
                        });
                        
                        // Move to next 30-minute slot
                        currentMin += 30;
                        if (currentMin >= 60) {
                            currentMin = 0;
                            currentHour++;
                        }
                        if (currentHour >= 24) {
                            currentHour = 0;
                        }
                        
                        slotDate.setHours(currentHour, currentMin, 0, 0);
                        iterations++;
                    }
                    
                    this.availableSlots = slots;
                } else {
                    this.dateError = 'Error loading doctor availability';
                    this.availableSlots = [];
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                this.dateError = 'Error loading available time slots. Please refresh the page.';
                this.availableSlots = [];
            }
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
                    const conflictingSlotIndex = this.availableSlots.findIndex(s => s.value === this.selectedTime);
                    if (conflictingSlotIndex !== -1) {
                        this.availableSlots[conflictingSlotIndex].conflict = true;
                    }
                    this.selectedTime = '';
                } else {
                    this.errorMessage = '';
                }
            } catch (error) {
                console.error('Error checking slot:', error);
                this.errorMessage = 'Error checking slot availability. Please try again.';
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
            const symptomInputs = document.querySelectorAll('input[name="emergency_symptoms[]"]');
            const emergencySymptoms = Array.from(symptomInputs).map(input => input.value);
            try {
                const formData = new FormData();
                formData.append('doctor_id', this.doctorId);
                formData.append('scheduled_at', scheduledAt);
                formData.append('consult_mode', this.consultMode);
                formData.append('problem', this.problem);
                formData.append('severity', this.severity);
                emergencySymptoms.forEach(symptom => {
                    formData.append('emergency_symptoms[]', symptom);
                });
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
                if (data.errors) {
                    this.errors = data.errors;
                    this.isSubmitting = false;
                    return;
                }
                if (response.status === 409 || data.error === 'time_slot_conflict') {
                    this.errorMessage = data.message || 'This time slot was just booked by another patient. Please select a different time.';
                    this.selectedTime = '';
                    this.loadTimeSlots();
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
                    this.isSubmitting = false;
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

