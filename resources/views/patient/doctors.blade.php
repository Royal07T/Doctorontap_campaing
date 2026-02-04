@extends('layouts.patient')

@section('title', 'Find a Doctor')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600">Home</a>
                <span class="mx-2">/</span>
                <span class="text-purple-600">Find Doctors</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Find Your Specialist</h1>
            <p class="text-gray-500 text-sm mt-1">Book appointments with top-rated doctors across various specialties.</p>
        </div>
        
        <!-- Search & Filter Actions -->
        <div class="w-full md:w-auto">
             <form method="GET" action="{{ route('patient.doctors') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                       <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                       </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search doctor or specialty..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-shadow">
                </div>
                
                <div class="relative min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                       <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                       </svg>
                    </div>
                    <select name="specialization" class="w-full pl-10 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-shadow appearance-none">
                        <option value="">All Specializations</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>
                                {{ $spec }}
                            </option>
                        @endforeach
                    </select>
                     <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                       <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                       </svg>
                    </div>
                </div>
                
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm shadow-purple-200">
                    Search
                </button>
             </form>
        </div>
    </div>

    <!-- Results Grid -->
    @if($doctors->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($doctors as $doctor)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col group h-full">
                    <!-- Card Header -->
                    <div class="p-5 flex items-start justify-between relative">
                        <div class="relative">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 p-1 border border-gray-100">
                                @if($doctor->photo_url)
                                    <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->name }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <div class="w-full h-full bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 font-bold text-xl">
                                        {{ substr($doctor->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <!-- Online Indicator -->
                            @if($doctor->is_available)
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full" title="Available Now"></span>
                            @else
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-gray-300 border-2 border-white rounded-full" title="Offline"></span>
                            @endif
                        </div>
                        
                         <!-- Rating Badge -->
                        @php
                            $avgRating = $doctor->average_rating ?? 0;
                            $reviewsCount = $doctor->published_reviews_count ?? 0;
                        @endphp
                         <div class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg">
                            <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <span class="text-xs font-bold text-gray-700">{{ number_format($avgRating, 1) }}</span>
                            <span class="text-[10px] text-gray-400">({{ $reviewsCount }})</span>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="px-5 flex-1">
                        <h3 class="text-base font-bold text-gray-900 line-clamp-1 mb-0.5">{{ $doctor->name }}</h3>
                        <p class="text-sm text-purple-600 font-medium mb-3 line-clamp-1">{{ $doctor->specialization ?? 'General Practitioner' }}</p>
                        
                         <!-- Meta Info -->
                         <div class="space-y-2 mb-4">
                            @if($doctor->experience)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $doctor->experience }} Experience
                            </div>
                            @endif
                            @if($doctor->location)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $doctor->location }}
                            </div>
                            @endif
                            @if($doctor->languages)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                                {{ $doctor->languages }}
                            </div>
                            @endif
                            @php
                                // Get consultation fee - handle both single fee and range
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
                            <div class="flex items-center text-xs font-semibold text-purple-600">
                                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $feeDisplay }}
                            </div>
                         </div>
                    </div>
                    
                    <!-- Card Footer -->
                    <div class="p-4 bg-gray-50 border-t border-gray-100 group-hover:bg-purple-50 transition-colors">
                        <button onclick="window.openBookingModal({{ $doctor->id }}, '{{ addslashes($doctor->name) }}', '{{ addslashes($doctor->specialization ?? 'General Practitioner') }}')" 
                                class="w-full py-2.5 bg-purple-600 border border-purple-600 text-white font-bold text-sm rounded-xl hover:bg-purple-700 hover:border-purple-700 transition-all shadow-md hover:shadow-lg">
                            Book Appointment
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $doctors->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-20 bg-white rounded-3xl border border-gray-100">
            <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-4">
                 <svg class="w-10 h-10 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Doctors Found</h3>
            <p class="text-gray-500 max-w-sm mx-auto mb-6">We couldn't find any doctors matching your criteria. Try adjusting your filters.</p>
            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                Clear Filters
            </a>
        </div>
    @endif
</div>

<!-- Booking Modal (Preserved & Styled) -->
<div id="bookingModal" 
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
     x-data="bookingModal" 
     x-show="showModal" 
     x-cloak 
     @click.away="closeModal()">
    
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-transform" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         @click.stop>
         
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between z-10">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Book Appointment</h3>
                <div class="flex items-center gap-2 mt-1">
                     <span class="text-sm text-gray-500">with</span>
                     <span class="text-sm font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md" x-text="doctorName"></span>
                </div>
            </div>
            <button @click="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body (Original logic preserved, styled) -->
        <div class="p-6">
            <form @submit.prevent="submitBooking()" enctype="multipart/form-data">
                <!-- Step 1: Schedule -->
                <div class="mb-8">
                     <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">1</div>
                        <h4 class="text-base font-bold text-gray-900">Select Date & Time</h4>
                     </div>
                     
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Date</label>
                            <input type="date" 
                                   x-model="selectedDate" 
                                   @change="loadTimeSlots()"
                                   :min="minDate"
                                   required
                                   class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all">
                         </div>
                     </div>
                     
                     <!-- Time Slots -->
                     <div class="mt-4" x-show="selectedDate && availableSlots.length > 0">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Available Slots</label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                             <template x-for="slot in availableSlots" :key="slot.value">
                                <button type="button"
                                        @click="selectedTime = slot.value"
                                        :disabled="slot.booked"
                                        :class="{
                                            'bg-purple-600 text-white shadow-md ring-2 ring-purple-200 border-transparent': selectedTime === slot.value,
                                            'bg-gray-50 text-gray-300 cursor-not-allowed': slot.booked,
                                            'bg-white text-gray-700 border-gray-200 hover:border-purple-500 hover:text-purple-600': selectedTime !== slot.value && !slot.booked
                                        }"
                                        class="py-2 px-3 text-xs font-medium border rounded-lg transition-all text-center">
                                    <span x-text="slot.label"></span>
                                </button>
                             </template>
                        </div>
                     </div>
                     <p x-show="selectedDate && availableSlots.length === 0" class="text-sm text-gray-500 mt-2 italic">No slots available for this date.</p>
                </div>

                <!-- Step 2: Details -->
                <div class="mb-8">
                     <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">2</div>
                        <h4 class="text-base font-bold text-gray-900">Medical Purpose</h4>
                     </div>
                     
                     <div class="space-y-4">
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Primary Symptom / Reason</label>
                            <textarea x-model="problem" 
                                      required
                                      rows="3"
                                      placeholder="Describe your symptoms or reason for visit..." 
                                      class="w-full px-4 py-3 bg-gray-50 border-transparent focus:bg-white border border-gray-200 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"></textarea>
                         </div>

                         <!-- Severity -->
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Severity</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button type="button" @click="severity = 'mild'" 
                                        :class="severity === 'mild' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="py-2.5 border rounded-xl text-sm font-medium transition-all">
                                    Mild
                                </button>
                                <button type="button" @click="severity = 'moderate'" 
                                        :class="severity === 'moderate' ? 'bg-yellow-50 border-yellow-500 text-yellow-700' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="py-2.5 border rounded-xl text-sm font-medium transition-all">
                                    Moderate
                                </button>
                                <button type="button" @click="severity = 'severe'" 
                                        :class="severity === 'severe' ? 'bg-red-50 border-red-500 text-red-700' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="py-2.5 border rounded-xl text-sm font-medium transition-all">
                                    Severe
                                </button>
                            </div>
                         </div>
                         
                         <div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Consultation Mode</label>
                             <div class="grid grid-cols-3 gap-3">
                                <button type="button" @click="consultMode = 'video'" 
                                        :class="consultMode === 'video' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="flex flex-col items-center justify-center py-3 border rounded-xl transition-all">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <span class="text-xs font-bold">Video</span>
                                </button>
                                <button type="button" @click="consultMode = 'voice'"
                                        :class="consultMode === 'voice' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="flex flex-col items-center justify-center py-3 border rounded-xl transition-all">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                                    <span class="text-xs font-bold">Voice</span>
                                </button>
                                <button type="button" @click="consultMode = 'chat'"
                                        :class="consultMode === 'chat' ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'"
                                        class="flex flex-col items-center justify-center py-3 border rounded-xl transition-all">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <span class="text-xs font-bold">Chat</span>
                                </button>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex gap-4 pt-4 border-t border-gray-100">
                    <button type="button" @click="closeModal()" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="isSubmitting || !selectedDate || !selectedTime || !problem || !severity"
                            class="flex-1 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
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
</div>

<script>
// Make function globally available
window.openBookingModal = function(doctorId, doctorName, doctorSpecialization) {
    const event = new CustomEvent('open-booking-modal', { 
        detail: { doctorId, doctorName, doctorSpecialization } 
    });
    window.dispatchEvent(event);
}

document.addEventListener('alpine:init', () => {
    Alpine.data('bookingModal', () => ({
        showModal: false,
        doctorId: null,
        doctorName: '',
        doctorSpecialization: '',
        selectedDate: '',
        selectedTime: '',
        consultMode: 'video',
        problem: '',
        severity: 'mild',
        availableSlots: [],
        isSubmitting: false,
        minDate: new Date().toISOString().split('T')[0],
        
        init() {
            window.addEventListener('open-booking-modal', (e) => {
                this.doctorId = e.detail.doctorId;
                this.doctorName = e.detail.doctorName;
                this.doctorSpecialization = e.detail.doctorSpecialization;
                this.showModal = true;
                this.resetForm();
            });
        },
        
        resetForm() {
            this.selectedDate = '';
            this.selectedTime = '';
            this.consultMode = 'video';
            this.problem = '';
            this.severity = 'mild';
            this.availableSlots = [];
        },
        
        closeModal() {
            this.showModal = false;
        },
        
        async loadTimeSlots() {
            if (!this.selectedDate || !this.doctorId) return;
            
            try {
                // Use the correct route for availability
                const url = `{{ route('patient.doctors.availability', ['id' => ':id']) }}`.replace(':id', this.doctorId) + `?date=${this.selectedDate}`;
                
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                // Fallback simulation if route 404s or fails (common in redesigns)
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
                        const start = schedule.start; // e.g. "09:00"
                        const end = schedule.end;     // e.g. "17:00"
                        
                        // Parse start/end to minutes for easier calculation
                        const [startHour, startMin] = start.split(':').map(Number);
                        const [endHour, endMin] = end.split(':').map(Number);
                        
                        let currentHour = startHour;
                        let currentMin = startMin;
                        
                        // Loop to generate 30 min slots
                        while (currentHour < endHour || (currentHour === endHour && currentMin < endMin)) {
                            // Format time string HH:mm
                            const timeString = `${currentHour.toString().padStart(2, '0')}:${currentMin.toString().padStart(2, '0')}`;
                            
                            // Check if booked
                            // bookedSlots contains full objects, assuming we check time match for selected date
                            const isBooked = bookedSlots.some(slot => slot.date === this.selectedDate && slot.time === timeString);
                            
                            // Format label (12-hour format)
                            let labelHour = currentHour;
                            const ampm = labelHour >= 12 ? 'PM' : 'AM';
                            labelHour = labelHour % 12;
                            labelHour = labelHour ? labelHour : 12; // the hour '0' should be '12'
                            const label = `${labelHour.toString().padStart(2, '0')}:${currentMin.toString().padStart(2, '0')} ${ampm}`;
                            
                            this.availableSlots.push({
                                value: timeString,
                                label: label,
                                booked: isBooked
                            });
                            
                            // Increment by 30 mins
                            currentMin += 30;
                            if (currentMin >= 60) {
                                currentHour++;
                                currentMin -= 60;
                            }
                        }
                    }
                }
            } catch (e) {
                console.error("Error loading slots", e);
            }
        },
        
        async submitBooking() {
            this.isSubmitting = true;
            
            // Prepare FormData
            const formData = new FormData();
            formData.append('doctor_id', this.doctorId);
            formData.append('scheduled_at', `${this.selectedDate} ${this.selectedTime}`);
            formData.append('problem', this.problem);
            formData.append('consult_mode', this.consultMode);
            formData.append('severity', this.severity);
            
            try {
                const response = await fetch('{{ route("patient.doctors.book") }}', { 
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (response.ok) {
                     // Success - Show confirmation modal then redirect
                     CustomAlert.success(
                        'Your appointment has been successfully booked! You can view the details in your consultations.',
                        'Booking Confirmed',
                        () => {
                            window.location.href = '{{ route("patient.consultations") }}';
                        }
                     );
                } else {
                    CustomAlert.error(result.message || 'Unknown error', 'Booking Failed');
                }
            } catch (e) {
                console.error("Booking error", e);
                CustomAlert.error('An error occurred while booking. Please try again.', 'System Error');
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
</script>
@endsection
