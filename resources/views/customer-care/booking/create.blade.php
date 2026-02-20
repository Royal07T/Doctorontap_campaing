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
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-900">{{ session('error') }}</p>
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('customer-care.booking.store') }}" id="bookingForm" x-data="bookingForm()" @submit="getScheduledAt()">
            @csrf

            @if($patient)
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
            @elseif($prospect && $prospect->status === 'Converted')
            <input type="hidden" name="prospect_id" value="{{ $prospect->id }}">
            @endif

            @if(!$patient && !$prospect)
            <!-- Warning: No Patient/Prospect Selected -->
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold text-amber-900">Patient or Prospect Required</p>
                        <p class="text-sm text-amber-800 mt-1">Please select a patient or prospect before booking a consultation. You can search for patients or view prospects from the links below.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('customer-care.customers.index') }}" class="inline-flex items-center gap-2 text-xs px-3 py-1.5 bg-amber-600 text-white rounded-lg font-semibold hover:bg-amber-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Search Patients
                            </a>
                            <a href="{{ route('customer-care.prospects.index') }}" class="inline-flex items-center gap-2 text-xs px-3 py-1.5 bg-amber-100 text-amber-800 rounded-lg font-semibold hover:bg-amber-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                View Prospects
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Patient/Prospect Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Booking For</h3>
                @if($patient)
                <p class="text-lg font-bold text-gray-900">{{ $patient->first_name }} {{ $patient->last_name }}</p>
                <p class="text-sm text-gray-600">{{ $patient->phone }} • {{ $patient->email ?? '—' }}</p>
                @elseif($prospect)
                <p class="text-lg font-bold text-gray-900">{{ $prospect->full_name }}</p>
                <p class="text-sm text-gray-600">{{ $prospect->mobile_number }} • {{ $prospect->email ?? '—' }}</p>
                @endif
            </div>
            @endif

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
                    @if($patient && $patient->age)
                        <input type="number" name="age" min="1" max="150" 
                               value="{{ $patient->age }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                               readonly>
                        <p class="mt-1 text-xs text-gray-500">Auto-filled from patient profile</p>
                    @else
                        <input type="number" name="age" min="1" max="150" 
                               value="{{ old('age') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                    @if($patient && $patient->gender)
                        @php
                            // Normalize gender to match validation rules (Male, Female, Other)
                            $normalizedGender = ucfirst(strtolower(trim($patient->gender)));
                            if (!in_array($normalizedGender, ['Male', 'Female', 'Other'])) {
                                // Handle common variations
                                if (in_array(strtolower($normalizedGender), ['m', 'male'])) {
                                    $normalizedGender = 'Male';
                                } elseif (in_array(strtolower($normalizedGender), ['f', 'female'])) {
                                    $normalizedGender = 'Female';
                                } else {
                                    $normalizedGender = 'Other';
                                }
                            }
                        @endphp
                        <select name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                disabled>
                            <option value="{{ $normalizedGender }}" selected>{{ $normalizedGender }}</option>
                        </select>
                        <input type="hidden" name="gender" value="{{ $normalizedGender }}">
                        <p class="mt-1 text-xs text-gray-500">Auto-filled from patient profile</p>
                    @else
                        <select name="gender"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    @endif
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
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md {{ (!$patient && !$prospect) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ (!$patient && !$prospect) ? 'disabled' : '' }}>
                    Book Consultation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
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

        init() {
            // Watch for changes to update scheduledAt
            this.$watch('selectedDate', () => this.getScheduledAt());
            this.$watch('selectedTime', () => this.getScheduledAt());
        },

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
</script>
@endpush
@endsection

