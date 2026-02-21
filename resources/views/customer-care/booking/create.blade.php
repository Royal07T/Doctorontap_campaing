@extends('layouts.customer-care')

@section('title', 'Book Service Request - Customer Care')

@php
    $headerTitle = 'New Service Request';
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Book Service Request</h1>
        <p class="text-sm text-gray-600 mt-1">Book a consultation on behalf of patient</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg shadow-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-emerald-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-emerald-900">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-900">{{ session('error') }}</p>
                @if(str_contains(session('error'), 'too large') || str_contains(session('error'), 'POST data') || str_contains(session('error'), 'exceeds') || str_contains(session('error'), 'File upload'))
                <div class="mt-3 p-3 bg-red-100 rounded-lg border border-red-200">
                    <p class="text-xs font-semibold text-red-800 mb-1">File Size Limits:</p>
                    <ul class="text-xs text-red-700 space-y-1 list-disc list-inside">
                        <li>Maximum 2MB per file</li>
                        <li>Maximum 6MB total for all files</li>
                        <li>If your files are larger, please compress them or split them into smaller files</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg shadow-sm">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-red-900 mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Info Message -->
    @if(session('info'))
    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg shadow-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-blue-900">{{ session('info') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if($prospect && $prospect->status !== 'Converted')
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="font-semibold text-red-900">Prospect Must Be Converted</p>
                <p class="text-sm text-red-800 mt-1">This prospect must be converted to a patient before booking a service.</p>
                <a href="{{ route('customer-care.prospects.convert', $prospect) }}" 
                   class="mt-2 inline-block px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">
                    Convert to Patient
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="{ 
        ...patientSearch(),
        ...bookingForm(),
        init() {
            // Initialize booking form watchers
            this.$watch('selectedDate', () => this.getScheduledAt());
            this.$watch('selectedTime', () => this.getScheduledAt());
        }
    }">
        <form method="POST" action="{{ route('customer-care.booking.store') }}" id="bookingForm" @submit="getScheduledAt()" enctype="multipart/form-data">
            @csrf

            <!-- Patient Search Section -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search Patient *</label>
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           @input.debounce.300ms="searchPatients()"
                           @focus="showResults = true"
                           @click.away="showResults = false"
                           placeholder="Search by name, email, or phone number..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           :class="selectedPatient ? 'bg-gray-50' : ''">
                    <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    
                    <!-- Search Results Dropdown -->
                    <div x-show="showResults && searchResults.length > 0" 
                         x-transition
                         class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <template x-for="patient in searchResults" :key="patient.id">
                            <button type="button"
                                    @click="selectPatient(patient)"
                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors">
                                <p class="font-semibold text-gray-900" x-text="patient.name"></p>
                                <p class="text-sm text-gray-600" x-text="patient.phone + ' • ' + patient.email"></p>
                            </button>
                        </template>
                    </div>
                    
                    <!-- No Results Message -->
                    <div x-show="showResults && searchQuery.length >= 2 && searchResults.length === 0 && !isSearching" 
                         class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-center text-gray-500">
                        No patients found. Try a different search term.
                    </div>
                    
                    <!-- Loading Indicator -->
                    <div x-show="isSearching" 
                         class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-center text-gray-500">
                        Searching...
                    </div>
                </div>
                
                <!-- Selected Patient Info -->
                <div x-show="selectedPatient" 
                     x-transition
                     class="mt-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Booking For</h3>
                            <p class="text-lg font-bold text-gray-900" x-text="selectedPatient.name"></p>
                            <p class="text-sm text-gray-600" x-text="selectedPatient.phone + ' • ' + selectedPatient.email"></p>
                        </div>
                        <button type="button" 
                                @click="clearSelection()"
                                class="text-gray-500 hover:text-gray-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Hidden inputs for patient/prospect -->
                @if($patient)
                <input type="hidden" name="patient_id" :value="selectedPatient ? selectedPatient.id : {{ $patient->id }}">
                @elseif($prospect && $prospect->status === 'Converted')
                <input type="hidden" name="prospect_id" value="{{ $prospect->id }}">
                @else
                <input type="hidden" name="patient_id" :value="selectedPatient ? selectedPatient.id : ''">
                @endif
                
                <!-- Legacy Support: If patient/prospect passed via URL -->
                @if($patient)
                <div class="mt-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200" x-show="!selectedPatient">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Booking For</h3>
                            <p class="text-lg font-bold text-gray-900">{{ $patient->first_name }} {{ $patient->last_name }}</p>
                            <p class="text-sm text-gray-600">{{ \App\Helpers\PrivacyHelper::maskPhone($patient->phone) }} • {{ \App\Helpers\PrivacyHelper::maskEmail($patient->email ?? '—') }}</p>
                        </div>
                    </div>
                </div>
                @elseif($prospect && $prospect->status === 'Converted')
                <div class="mt-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200" x-show="!selectedPatient">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Booking For</h3>
                            <p class="text-lg font-bold text-gray-900">{{ $prospect->full_name }}</p>
                            <p class="text-sm text-gray-600">{{ \App\Helpers\PrivacyHelper::maskPhone($prospect->mobile_number) }} • {{ \App\Helpers\PrivacyHelper::maskEmail($prospect->email ?? '—') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Consultation Mode -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Consultation Mode *</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" 
                           :class="serviceType === 'video' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                        <input type="radio" name="service_type" value="video" x-model="serviceType" required class="mr-3">
                        <div>
                            <p class="font-semibold text-gray-900">Video Consultation</p>
                            <p class="text-xs text-gray-600">Live video call with doctor</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                           :class="serviceType === 'audio' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                        <input type="radio" name="service_type" value="audio" x-model="serviceType" required class="mr-3">
                        <div>
                            <p class="font-semibold text-gray-900">Audio Consultation</p>
                            <p class="text-xs text-gray-600">Voice call with doctor</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                           :class="serviceType === 'chat' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                        <input type="radio" name="service_type" value="chat" x-model="serviceType" required class="mr-3">
                        <div>
                            <p class="font-semibold text-gray-900">Chat Consultation</p>
                            <p class="text-xs text-gray-600">Text-based chat with doctor</p>
                        </div>
                    </label>
                </div>
                @error('service_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Doctor Selection -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Doctor *</label>
                <select name="doctor_id" x-model="doctorId" @change="loadAvailability()" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Choose a doctor...</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->name }} - {{ $doctor->specialization }}</option>
                    @endforeach
                </select>
                @error('doctor_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Selection -->
            <div class="mb-6" x-show="doctorId">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Date *</label>
                <input type="date" name="scheduled_date" x-model="selectedDate" @change="loadTimeSlots()" 
                       :min="minDate" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p x-show="dateError" class="mt-1 text-xs text-red-600" x-text="dateError"></p>
            </div>

            <!-- Time Selection -->
            <div class="mb-6" x-show="selectedDate && !dateError">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Time *</label>
                <div class="grid grid-cols-4 gap-2" x-show="availableSlots.length > 0">
                    <template x-for="slot in availableSlots" :key="slot">
                        <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                               :class="selectedTime === slot ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                            <input type="radio" name="scheduled_time" :value="slot" x-model="selectedTime" required class="sr-only">
                            <span class="text-sm font-semibold" x-text="slot"></span>
                        </label>
                    </template>
                </div>
                <p x-show="availableSlots.length === 0 && selectedDate" class="text-sm text-gray-600">No available time slots for this date</p>
                <input type="hidden" name="scheduled_at" :value="scheduledAt">
            </div>

            <!-- Medical Information -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Medical Problem/Complaint *</label>
                <textarea name="problem" rows="4" required
                          placeholder="Describe the patient's medical problem or complaint..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                @error('problem')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Symptoms Checkboxes -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Symptoms (Select all that apply)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 max-h-96 overflow-y-auto">
                    <!-- Critical Symptoms -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-red-700 uppercase mb-2">Critical</p>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="chest_pain" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Chest pain or pressure</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="breathing" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Severe difficulty breathing</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="consciousness" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Loss of consciousness / fainting</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="bleeding" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Uncontrolled heavy bleeding</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="seizure" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Seizure now or ongoing</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="pregnancy_bleeding" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Heavy vaginal bleeding in pregnancy</span>
                        </label>
                    </div>
                    
                    <!-- Respiratory Symptoms -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-blue-700 uppercase mb-2">Respiratory</p>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="cough" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Persistent cough</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="shortness_breath" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Shortness of breath</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="wheezing" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Wheezing</span>
                        </label>
                    </div>
                    
                    <!-- Neurological Symptoms -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-purple-700 uppercase mb-2">Neurological</p>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="weakness" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Sudden weakness, numbness</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="speech" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Slurred speech</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="headache" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Severe headache</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="dizziness" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Dizziness or vertigo</span>
                        </label>
                    </div>
                    
                    <!-- Pain Symptoms -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-orange-700 uppercase mb-2">Pain</p>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="abdominal" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Severe abdominal pain</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="back_pain" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Severe back pain</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="joint_pain" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Joint pain</span>
                        </label>
                    </div>
                    
                    <!-- General Symptoms -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-gray-700 uppercase mb-2">General</p>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="fever" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Very high fever</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="fatigue" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Extreme fatigue</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="nausea" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Nausea or vomiting</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                            <input type="checkbox" name="symptoms[]" value="dehydration" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Signs of dehydration</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Lab Results / Blood Work File Upload -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Lab Results / Blood Work (Optional)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="lab_results" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload files</span>
                                <input id="lab_results" name="lab_results[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX up to 2MB each (max 6MB total)</p>
                    </div>
                </div>
                <div id="file-list" class="mt-2 space-y-2"></div>
                <div id="file-error" class="mt-2 text-sm text-red-600 hidden"></div>
                @error('lab_results')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @error('lab_results.*')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Severity *</label>
                    <select name="severity" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select severity</option>
                        <option value="low">Low</option>
                        <option value="moderate">Moderate</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    @error('severity')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Age</label>
                    <input type="number" 
                           name="age" 
                           min="1" 
                           max="150" 
                           :value="getAgeValue()"
                           :readonly="selectedPatient || hasLegacyPatient"
                           :class="(selectedPatient || hasLegacyPatient) ? 'bg-gray-50' : ''"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p x-show="selectedPatient || hasLegacyPatient" class="mt-1 text-xs text-gray-500">Auto-filled from patient profile</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                    <div x-show="selectedPatient || hasLegacyPatient">
                        <select name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                disabled>
                            <option :value="getGenderValue()" selected x-text="getGenderValue()"></option>
                        </select>
                        <input type="hidden" name="gender" :value="getGenderValue()">
                        <p class="mt-1 text-xs text-gray-500">Auto-filled from patient profile</p>
                    </div>
                    <div x-show="!selectedPatient && !hasLegacyPatient">
                        <select name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ $patient ? route('customer-care.customers.show', $patient) : ($prospect ? route('customer-care.prospects.show', $prospect) : route('customer-care.dashboard')) }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md"
                        :disabled="!hasPatient()"
                        :class="!hasPatient() ? 'opacity-50 cursor-not-allowed' : ''">
                    Book Consultation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function patientSearch() {
    return {
        searchQuery: '',
        searchResults: [],
        selectedPatient: null,
        showResults: false,
        isSearching: false,
        hasLegacyPatient: {{ $patient ? 'true' : 'false' }},
        hasLegacyProspect: {{ ($prospect && $prospect->status === 'Converted') ? 'true' : 'false' }},
        
        async searchPatients() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }
            
            this.isSearching = true;
            this.showResults = true;
            
            try {
                const response = await fetch(`{{ route('customer-care.patients.search') }}?q=${encodeURIComponent(this.searchQuery)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                this.searchResults = data.patients || [];
            } catch (error) {
                console.error('Error searching patients:', error);
                this.searchResults = [];
            } finally {
                this.isSearching = false;
            }
        },
        
        selectPatient(patient) {
            this.selectedPatient = patient;
            this.searchQuery = patient.name;
            this.showResults = false;
            this.searchResults = [];
        },
        
        clearSelection() {
            this.selectedPatient = null;
            this.searchQuery = '';
            this.showResults = false;
            this.searchResults = [];
        },
        
        hasPatient() {
            return this.selectedPatient || this.hasLegacyPatient || this.hasLegacyProspect;
        },
        
        getAgeValue() {
            if (this.selectedPatient && this.selectedPatient.age) {
                return this.selectedPatient.age;
            }
            @if($patient && $patient->age)
                return {{ $patient->age }};
            @endif
            return '{{ old('age') }}';
        },
        
        getGenderValue() {
            if (this.selectedPatient && this.selectedPatient.gender) {
                return this.selectedPatient.gender;
            }
            @if($patient && $patient->gender)
                @php
                    $normalizedGender = ucfirst(strtolower(trim($patient->gender)));
                    if (!in_array($normalizedGender, ['Male', 'Female', 'Other'])) {
                        if (in_array(strtolower($normalizedGender), ['m', 'male'])) {
                            $normalizedGender = 'Male';
                        } elseif (in_array(strtolower($normalizedGender), ['f', 'female'])) {
                            $normalizedGender = 'Female';
                        } else {
                            $normalizedGender = 'Other';
                        }
                    }
                @endphp
                return '{{ $normalizedGender }}';
            @endif
            return 'Other';
        }
    }
}

function bookingForm() {
    return {
        serviceType: '',
        doctorId: '',
        selectedDate: '',
        selectedTime: '',
        availableSlots: [],
        bookedSlots: [],
        dateError: '',
        minDate: new Date().toISOString().split('T')[0],
        scheduledAt: '',

        loadAvailability() {
            if (!this.doctorId) return;
            this.selectedDate = '';
            this.selectedTime = '';
            this.availableSlots = [];
            this.bookedSlots = [];
        },

        async loadTimeSlots() {
            if (!this.selectedDate || !this.doctorId) return;
            this.selectedTime = '';
            this.dateError = '';
            
            const selectedDateObj = new Date(this.selectedDate);
            const dayOfWeek = selectedDateObj.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
            
            try {
                const response = await fetch(`/customer-care/booking/doctors/${this.doctorId}/availability`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
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
                    const startTime = daySchedule.start;
                    const endTime = daySchedule.end;
                    
                    const [startHour, startMin] = startTime.split(':').map(Number);
                    const [endHour, endMin] = endTime.split(':').map(Number);
                    const isOvernight = endHour < startHour || (endHour === startHour && endMin < startMin);
                    
                    let currentHour = startHour;
                    let currentMin = startMin;
                    let slotDate = new Date(`${this.selectedDate}T${startTime}`);
                    
                    while (true) {
                        const timeStr = `${String(currentHour).padStart(2, '0')}:${String(currentMin).padStart(2, '0')}`;
                        const slotDateTime = `${this.selectedDate} ${timeStr}`;
                        
                        // Check if slot is booked
                        const isBooked = this.bookedSlots.some(bs => bs.date === this.selectedDate && bs.time === timeStr);
                        if (!isBooked && slotDate >= new Date()) {
                            slots.push(timeStr);
                        }
                        
                        // Move to next 30-minute slot
                        currentMin += 30;
                        if (currentMin >= 60) {
                            currentMin = 0;
                            currentHour++;
                        }
                        if (currentHour >= 24) {
                            currentHour = 0;
                        }
                        
                        slotDate = new Date(`${this.selectedDate}T${timeStr}`);
                        
                        // Break conditions
                        if (!isOvernight && (currentHour > endHour || (currentHour === endHour && currentMin >= endMin))) {
                            break;
                        }
                        if (isOvernight && currentHour === endHour && currentMin >= endMin) {
                            break;
                        }
                        if (slots.length > 48) break; // Safety limit
                    }
                    
                    this.availableSlots = slots;
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                this.dateError = 'Failed to load available time slots';
            }
        },

        getScheduledAt() {
            if (this.selectedDate && this.selectedTime) {
                // Ensure time is in HH:mm format (time slots are already in HH:mm format)
                const timeStr = this.selectedTime.length === 5 ? this.selectedTime : (this.selectedTime + ':00');
                this.scheduledAt = `${this.selectedDate} ${timeStr}`;
            } else {
                this.scheduledAt = '';
            }
        }
    }
}

// File upload preview and validation
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('lab_results');
    const fileList = document.getElementById('file-list');
    const fileError = document.getElementById('file-error');
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB per file (matches PHP upload_max_filesize)
    const MAX_TOTAL_SIZE = 6 * 1024 * 1024; // 6MB total (safe limit below PHP post_max_size of 8MB)
    
    if (fileInput && fileList && fileError) {
        fileInput.addEventListener('change', function(e) {
            fileList.innerHTML = '';
            fileError.classList.add('hidden');
            fileError.textContent = '';
            
            const files = Array.from(e.target.files);
            let totalSize = 0;
            const validFiles = [];
            const errors = [];
            
            // Validate each file
            files.forEach((file, index) => {
                const fileSize = file.size;
                
                // Check individual file size
                if (fileSize > MAX_FILE_SIZE) {
                    errors.push(`${file.name} is too large (${(fileSize / 1024 / 1024).toFixed(2)} MB). Maximum size is 2MB per file.`);
                    return;
                }
                
                // Only add to total if file is valid
                totalSize += fileSize;
                validFiles.push(file);
            });
            
            // Check total size (only for valid files)
            if (validFiles.length > 0 && totalSize > MAX_TOTAL_SIZE) {
                errors.push(`Total file size (${(totalSize / 1024 / 1024).toFixed(2)} MB) exceeds the maximum limit of 6MB. Please reduce the number or size of files.`);
            }
            
            // Show errors if any
            if (errors.length > 0) {
                fileError.innerHTML = errors.map(err => `<p>${err}</p>`).join('');
                fileError.classList.remove('hidden');
                fileInput.value = ''; // Clear the input
                return;
            }
            
            // Display valid files
            validFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200';
                fileItem.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm text-gray-700">${file.name}</span>
                        <span class="text-xs text-gray-500">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                    </div>
                    <button type="button" class="text-red-500 hover:text-red-700" onclick="removeFile(${index})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
        });
        
        // Drag and drop support
        const dropZone = fileInput.closest('.border-dashed');
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
            });
            
            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
                
                const files = Array.from(e.dataTransfer.files);
                const dataTransfer = new DataTransfer();
                files.forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
    }
    
    // Function to remove a file
    window.removeFile = function(index) {
        const fileInput = document.getElementById('lab_results');
        const fileList = document.getElementById('file-list');
        const dt = new DataTransfer();
        const files = Array.from(fileInput.files);
        
        files.splice(index, 1);
        files.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        
        // Re-trigger change event to update display
        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
    };
});
</script>
@endpush
@endsection

