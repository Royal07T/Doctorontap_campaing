@extends('layouts.patient')

@section('title', 'Book Appointment - ' . $doctor->name)

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
        <h1 class="text-2xl font-bold text-gray-900">Book Appointment</h1>
        <div class="flex items-center gap-2 mt-2">
            <span class="text-sm text-gray-500">with</span>
            <span class="text-sm font-bold text-purple-600 bg-purple-50 px-3 py-1 rounded-md">{{ $doctor->name }}</span>
            <span class="text-sm text-gray-500">-</span>
            <span class="text-sm text-gray-600">{{ $doctor->specialization ?? 'General Practitioner' }}</span>
        </div>
    </div>

    <!-- Doctor Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start gap-4">
            <div class="w-20 h-20 rounded-2xl bg-gray-50 p-1 border border-gray-100 flex-shrink-0">
                @if($doctor->photo_url)
                    <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->name }}" class="w-full h-full object-cover rounded-lg">
                @else
                    <div class="w-full h-full bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 font-bold text-3xl">
                        {{ substr($doctor->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $doctor->name }}</h3>
                <p class="text-base text-purple-600 font-semibold mb-4">{{ $doctor->specialization ?? 'General Practitioner' }}</p>
                <div class="space-y-3">
                    @if($doctor->location)
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $doctor->location }}
                    </div>
                    @endif
                    @php
                        if ($doctor->use_default_fee) {
                            $defaultFee = \App\Models\Setting::get('default_consultation_fee', 5000);
                            $feeDisplay = '₦' . number_format($defaultFee, 0);
                        } elseif ($doctor->min_consultation_fee && $doctor->max_consultation_fee) {
                            $feeDisplay = '₦' . number_format($doctor->min_consultation_fee, 0) . ' - ₦' . number_format($doctor->max_consultation_fee, 0);
                        } elseif ($doctor->consultation_fee) {
                            $feeDisplay = '₦' . number_format($doctor->consultation_fee, 0);
                        } else {
                            $feeDisplay = 'Contact for pricing';
                        }
                    @endphp
                    <div class="flex items-center text-base font-semibold text-purple-600">
                        <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $feeDisplay }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="bookingForm({{ $doctor->id }}, '{{ addslashes($doctor->name) }}', '{{ addslashes($doctor->specialization ?? 'General Practitioner') }}')">
        <form method="POST" action="{{ route('patient.doctors.book.store') }}" @submit.prevent="submitBooking()" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="doctor_id" :value="doctorId">

            <!-- Step 1: Schedule -->
            <div class="mb-8 pb-8 border-b border-gray-200">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">1</div>
                    <h4 class="text-lg font-bold text-gray-900">Select Date & Time</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" 
                               x-model="selectedDate" 
                               @change="loadTimeSlots()"
                               :min="minDate"
                               required
                               name="scheduled_date"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                        @error('scheduled_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Time Slots -->
                <div class="mt-6" x-show="selectedDate && availableSlots.length > 0">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Available Slots</label>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                        <template x-for="slot in availableSlots" :key="slot.value">
                            <button type="button"
                                    @click="selectedTime = slot.value"
                                    :disabled="slot.booked"
                                    :class="{
                                        'bg-purple-600 text-white shadow-md ring-2 ring-purple-200 border-transparent': selectedTime === slot.value,
                                        'bg-gray-50 text-gray-300 cursor-not-allowed': slot.booked,
                                        'bg-white text-gray-700 border-gray-200 hover:border-purple-500 hover:text-purple-600': selectedTime !== slot.value && !slot.booked
                                    }"
                                    class="py-2.5 px-4 text-sm font-medium border rounded-lg transition-all text-center">
                                <span x-text="slot.label"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="scheduled_time" x-model="selectedTime" required>
                </div>
                <p x-show="selectedDate && availableSlots.length === 0" class="text-sm text-gray-500 mt-2 italic">No slots available for this date.</p>
                @error('scheduled_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Step 2: Details -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">2</div>
                    <h4 class="text-lg font-bold text-gray-900">Medical Purpose</h4>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Symptom / Reason <span class="text-red-500">*</span></label>
                        <textarea x-model="problem" 
                                  name="problem"
                                  required
                                  rows="4"
                                  placeholder="Describe your symptoms or reason for visit..." 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"></textarea>
                        @error('problem')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Symptoms Checkboxes -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Symptoms (Select all that apply)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 max-h-96 overflow-y-auto">
                            <!-- Critical Symptoms -->
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-red-700 uppercase mb-2">Critical</p>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="chest_pain" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Chest pain or pressure</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="breathing" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Severe difficulty breathing</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="consciousness" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Loss of consciousness / fainting</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="bleeding" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Uncontrolled heavy bleeding</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="seizure" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Seizure now or ongoing</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="pregnancy_bleeding" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Heavy vaginal bleeding in pregnancy</span>
                                </label>
                            </div>
                            
                            <!-- Respiratory Symptoms -->
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-blue-700 uppercase mb-2">Respiratory</p>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="cough" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Persistent cough</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="shortness_breath" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Shortness of breath</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="wheezing" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Wheezing</span>
                                </label>
                            </div>
                            
                            <!-- Neurological Symptoms -->
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-purple-700 uppercase mb-2">Neurological</p>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="weakness" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Sudden weakness, numbness</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="speech" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Slurred speech</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="headache" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Severe headache</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="dizziness" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Dizziness or vertigo</span>
                                </label>
                            </div>
                            
                            <!-- Pain Symptoms -->
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-orange-700 uppercase mb-2">Pain</p>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="abdominal" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Severe abdominal pain</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="back_pain" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Severe back pain</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="joint_pain" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Joint pain</span>
                                </label>
                            </div>
                            
                            <!-- General Symptoms -->
                            <div class="space-y-2">
                                <p class="text-xs font-bold text-gray-700 uppercase mb-2">General</p>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="fever" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Very high fever</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="fatigue" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Extreme fatigue</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="nausea" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Nausea or vomiting</span>
                                </label>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="dehydration" class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm text-gray-700">Signs of dehydration</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Results / Blood Work File Upload -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Lab Results / Blood Work (Optional)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-purple-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="lab_results" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload files</span>
                                        <input id="lab_results" name="lab_results[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="sr-only">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC, DOCX up to 10MB each</p>
                            </div>
                        </div>
                        <div id="file-list" class="mt-2 space-y-2"></div>
                        @error('lab_results')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('lab_results.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Severity -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Severity <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-4">
                            <button type="button" @click="severity = 'mild'" 
                                    :class="severity === 'mild' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    class="py-3 border rounded-lg text-sm font-semibold transition-all">
                                Mild
                            </button>
                            <button type="button" @click="severity = 'moderate'" 
                                    :class="severity === 'moderate' ? 'bg-yellow-50 border-yellow-500 text-yellow-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    class="py-3 border rounded-lg text-sm font-semibold transition-all">
                                Moderate
                            </button>
                            <button type="button" @click="severity = 'severe'" 
                                    :class="severity === 'severe' ? 'bg-red-50 border-red-500 text-red-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    class="py-3 border rounded-lg text-sm font-semibold transition-all">
                                Severe
                            </button>
                        </div>
                        <input type="hidden" name="severity" x-model="severity" required>
                        @error('severity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Consultation Mode <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-3 gap-4">
                            <button type="button" @click="consultMode = 'video'" 
                                    :class="consultMode === 'video' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    class="flex flex-col items-center justify-center py-4 border rounded-lg transition-all">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <span class="text-sm font-semibold">Video</span>
                            </button>
                            <button type="button" @click="consultMode = 'voice'"
                                    :class="consultMode === 'voice' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    class="flex flex-col items-center justify-center py-4 border rounded-lg transition-all">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                                <span class="text-sm font-semibold">Voice</span>
                            </button>
                            <button type="button" @click="consultMode = 'chat'"
                                    :class="consultMode === 'chat' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400'"
                                    class="flex flex-col items-center justify-center py-4 border rounded-lg transition-all">
                                <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                <span class="text-sm font-semibold">Chat</span>
                            </button>
                        </div>
                        <input type="hidden" name="consult_mode" x-model="consultMode" required>
                        @error('consult_mode')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('patient.doctors') }}" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="isSubmitting || !selectedDate || !selectedTime || !problem || !severity"
                        class="flex-1 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">Confirm Booking</span>
                    <span x-show="isSubmitting" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bookingForm', (doctorId, doctorName, doctorSpecialization) => ({
        doctorId: doctorId,
        doctorName: doctorName,
        doctorSpecialization: doctorSpecialization,
        selectedDate: '',
        selectedTime: '',
        consultMode: 'video',
        problem: '',
        severity: 'mild',
        availableSlots: [],
        isSubmitting: false,
        minDate: new Date().toISOString().split('T')[0],
        
        async loadTimeSlots() {
            if (!this.selectedDate || !this.doctorId) return;
            
            try {
                const url = `{{ route('patient.doctors.availability', ['id' => ':id']) }}`.replace(':id', this.doctorId) + `?date=${this.selectedDate}`;
                
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    console.error('Availability fetch failed');
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    const date = new Date(this.selectedDate);
                    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    const dayName = days[date.getDay()];
                    
                    const schedule = data.availability_schedule && data.availability_schedule[dayName];
                    const bookedSlots = data.booked_slots || [];
                    
                    this.availableSlots = [];

                    if (schedule && schedule.enabled) {
                        const start = schedule.start;
                        const end = schedule.end;
                        
                        const [startHour, startMin] = start.split(':').map(Number);
                        const [endHour, endMin] = end.split(':').map(Number);
                        
                        const isOvernight = endHour < startHour || (endHour === startHour && endMin < startMin);
                        const startTotalMinutes = startHour * 60 + startMin;
                        const endTotalMinutes = endHour * 60 + endMin;
                        
                        let currentHour = startHour;
                        let currentMin = startMin;
                        let iterations = 0;
                        const maxIterations = isOvernight ? 48 : 48;
                        
                        while (iterations < maxIterations) {
                            let currentTotalMinutes = currentHour * 60 + currentMin;
                            
                            if (isOvernight) {
                                if (currentHour < startHour || (currentHour === startHour && currentMin < startMin)) {
                                    currentTotalMinutes += 24 * 60;
                                }
                                if (currentTotalMinutes > (endTotalMinutes + 24 * 60)) {
                                    break;
                                }
                            } else {
                                if (currentTotalMinutes > endTotalMinutes) {
                                    break;
                                }
                            }
                            
                            const timeString = `${currentHour.toString().padStart(2, '0')}:${currentMin.toString().padStart(2, '0')}`;
                            const isBooked = bookedSlots.some(slot => slot.date === this.selectedDate && slot.time === timeString);
                            
                            let labelHour = currentHour;
                            const ampm = labelHour >= 12 ? 'PM' : 'AM';
                            labelHour = labelHour % 12;
                            labelHour = labelHour ? labelHour : 12;
                            const label = `${labelHour.toString().padStart(2, '0')}:${currentMin.toString().padStart(2, '0')} ${ampm}`;
                            
                            this.availableSlots.push({
                                value: timeString,
                                label: label,
                                booked: isBooked
                            });
                            
                            currentMin += 30;
                            if (currentMin >= 60) {
                                currentHour++;
                                currentMin -= 60;
                            }
                            if (currentHour >= 24) {
                                currentHour = 0;
                            }
                            iterations++;
                        }
                    }
                }
            } catch (e) {
                console.error("Error loading slots", e);
            }
        },
        
        async submitBooking() {
            this.isSubmitting = true;
            
            // Combine date and time for scheduled_at
            const scheduledAt = `${this.selectedDate} ${this.selectedTime}`;
            
            // Create form data
            const formData = new FormData();
            formData.append('doctor_id', this.doctorId);
            formData.append('scheduled_at', scheduledAt);
            formData.append('problem', this.problem);
            formData.append('consult_mode', this.consultMode);
            formData.append('severity', this.severity);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            // Add symptoms checkboxes
            const symptomsCheckboxes = document.querySelectorAll('input[name="symptoms[]"]:checked');
            symptomsCheckboxes.forEach((checkbox) => {
                formData.append('symptoms[]', checkbox.value);
            });
            
            // Add lab results files
            const labResultsInput = document.getElementById('lab_results');
            if (labResultsInput && labResultsInput.files) {
                for (let i = 0; i < labResultsInput.files.length; i++) {
                    formData.append('lab_results[]', labResultsInput.files[i]);
                }
            }
            
            try {
                const response = await fetch('{{ route("patient.doctors.book.store") }}', { 
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                let result;
                
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    // If HTML response (likely validation error or redirect), handle it
                    const text = await response.text();
                    console.error('Non-JSON response received:', text.substring(0, 200));
                    
                    // Try to parse as JSON anyway (in case content-type is wrong)
                    try {
                        result = JSON.parse(text);
                    } catch (parseError) {
                        // If it's HTML, it's likely a validation error page
                        if (response.status === 422) {
                            alert('Please check the form for errors and try again.');
                        } else if (response.status === 302 || response.redirected) {
                            // Redirect response - follow it
                            window.location.href = '{{ route("patient.consultations") }}';
                            return;
                        } else {
                            alert('An unexpected error occurred. Please try again.');
                        }
                        this.isSubmitting = false;
                        return;
                    }
                }
                
                if (response.ok && result.success) {
                    // Redirect to consultations page on success
                    window.location.href = '{{ route("patient.consultations") }}';
                } else {
                    // Show error message
                    const errorMsg = result.message || result.error || 'Failed to book appointment. Please try again.';
                    alert(errorMsg);
                    this.isSubmitting = false;
                }
            } catch (e) {
                console.error("Booking error", e);
                alert('An error occurred while booking. Please try again.');
                this.isSubmitting = false;
            }
        }
    }));
});

// File upload preview
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('lab_results');
    const fileList = document.getElementById('file-list');
    
    if (fileInput && fileList) {
        fileInput.addEventListener('change', function(e) {
            fileList.innerHTML = '';
            const files = Array.from(e.target.files);
            
            files.forEach((file, index) => {
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
                `;
                fileList.appendChild(fileItem);
            });
        });
    }
});
</script>
@endsection

