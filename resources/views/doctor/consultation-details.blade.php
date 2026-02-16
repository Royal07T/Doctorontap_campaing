@extends('layouts.doctor')

@section('title', 'Consultation Details')
@section('header-title', 'Consultation Details')

@push('styles')
    <style>
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        [x-cloak] { display: none !important; }
    </style>
@endpush

@section('x-data-custom')
consultationPage()
@endsection


@push('scripts')
    <script>
        function consultationPage() {
            return {
                sidebarOpen: false,
                pageLoading: false,
                showMessageModal: false,
                messageType: 'success',
                messageTitle: '',
                messageText: '',
                showConfirmModal: false,
                confirmTitle: '',
                confirmText: '',
                confirmCallback: null,
                isUpdating: false,
                // Review modal variables
                showReviewModal: false,
                selectedRating: 0,
                reviewComment: '',
                revieweeType: 'patient',
                isSubmittingReview: false,
                // Referral modal variables
                showReferModal: false,
                referredToDoctorId: '',
                referralReason: '',
                referralNotes: '',
                isReferring: false,
                availableDoctors: @json($availableDoctors ?? []),
                // Auto-save status
                autoSaveStatus: 'saved',
                lastSaved: null,
                autoSaveInterval: null,
                
                // Group doctors by specialization
                get groupedDoctors() {
                    const grouped = {};
                    this.availableDoctors.forEach(doctor => {
                        const spec = doctor.specialization || 'General Practice';
                        if (!grouped[spec]) {
                            grouped[spec] = [];
                        }
                        grouped[spec].push(doctor);
                    });
                    return grouped;
                },
                
                showMessage(type, title, text) {
                    this.messageType = type;
                    this.messageTitle = title;
                    this.messageText = text;
                    this.showMessageModal = true;
                },
                
                showConfirm(title, text, callback) {
                    this.confirmTitle = title;
                    this.confirmText = text;
                    this.confirmCallback = callback;
                    this.showConfirmModal = true;
                },
                
                executeConfirm() {
                    this.showConfirmModal = false;
                    if (this.confirmCallback && typeof this.confirmCallback === 'function') {
                        this.confirmCallback();
                    }
                    this.confirmCallback = null;
                },
                
                async doUpdateStatus(newStatus) {
                    this.isUpdating = true;
                    try {
                        const url = '{{ route("doctor.consultations.update-status", $consultation->id) }}';
                        console.log('Updating status to:', newStatus, 'URL:', url);
                        
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        
                        console.log('Response status:', response.status);
                        
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Error response:', errorText);
                            try {
                                const errorData = JSON.parse(errorText);
                                this.showMessage('error', 'Error', errorData.message || 'Failed to update status');
                            } catch (e) {
                                this.showMessage('error', 'Error', `Server error (${response.status}): ${errorText.substring(0, 100)}`);
                            }
                            return;
                        }
                        
                        const data = await response.json();
                        console.log('Response data:', data);
                        
                        if (data.success) {
                            this.showMessage('success', 'Success!', data.message || 'Status updated successfully! Admin has been notified.');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to update status');
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        this.showMessage('error', 'Error', 'Error updating status: ' + error.message);
                    } finally {
                        this.isUpdating = false;
                    }
                },
                
                updateStatus(newStatus) {
                    console.log('updateStatus called with:', newStatus);
                    this.showConfirm('Confirm Status Change', 'Are you sure you want to change the consultation status to ' + newStatus + '?', () => {
                        console.log('Confirm callback executed');
                        this.doUpdateStatus(newStatus);
                    });
                },
                
                openReviewModal() {
                    this.showReviewModal = true;
                    this.selectedRating = 0;
                    this.reviewComment = '';
                    this.revieweeType = 'patient';
                },
                
                async submitReview() {
                    if (this.selectedRating === 0) {
                        this.showMessage('error', 'Rating Required', 'Please select a rating before submitting.');
                        return;
                    }
                    
                    this.isSubmittingReview = true;
                    try {
                        const response = await fetch('{{ route("doctor.reviews.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                consultation_id: {{ $consultation->id }},
                                reviewee_type: this.revieweeType,
                                rating: this.selectedRating,
                                comment: this.reviewComment,
                                would_recommend: true
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showMessage('success', 'Review Submitted', 'Thank you for your feedback! Your review has been submitted successfully.');
                            this.showReviewModal = false;
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to submit review');
                        }
                    } catch (error) {
                        console.error('Error submitting review:', error);
                        this.showMessage('error', 'Error', 'An error occurred while submitting your review.');
                    } finally {
                        this.isSubmittingReview = false;
                    }
                },
                
                async submitReferral() {
                    if (!this.referredToDoctorId || this.referralReason.length < 10) {
                        this.showMessage('error', 'Validation Error', 'Please select a doctor and provide a reason (minimum 10 characters).');
                        return;
                    }
                    
                    // Get selected doctor details for confirmation
                    const selectedDoctor = this.availableDoctors.find(d => d.id == this.referredToDoctorId);
                    const doctorName = selectedDoctor ? (selectedDoctor.name || (selectedDoctor.first_name + ' ' + selectedDoctor.last_name)) : 'Selected Doctor';
                    const doctorSpecialization = selectedDoctor?.specialization || '';
                    
                    // Show confirmation
                    this.showConfirm(
                        'Confirm Referral',
                        `Are you sure you want to refer this patient to Dr. ${doctorName}${doctorSpecialization ? ' (' + doctorSpecialization + ')' : ''}? This action will create a new consultation and notify both the patient and the referred doctor.`,
                        () => {
                            this.doSubmitReferral();
                        }
                    );
                },
                
                async doSubmitReferral() {
                    this.isReferring = true;
                    try {
                        const response = await fetch('{{ route("doctor.consultations.refer", $consultation->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                referred_to_doctor_id: this.referredToDoctorId,
                                reason: this.referralReason,
                                notes: this.referralNotes
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showMessage('success', 'Referral Successful', data.message || 'Patient has been successfully referred. Notifications have been sent to the patient and the referred doctor.');
                            this.showReferModal = false;
                            // Reset form
                            this.referredToDoctorId = '';
                            this.referralReason = '';
                            this.referralNotes = '';
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            this.showMessage('error', 'Error', data.message || 'Failed to refer patient');
                        }
                    } catch (error) {
                        console.error('Error submitting referral:', error);
                        this.showMessage('error', 'Error', 'An error occurred while submitting the referral.');
                    } finally {
                        this.isReferring = false;
                    }
                },

                // Auto-save treatment plan
                initAutoSave() {
                    const form = document.getElementById('treatmentPlanForm');
                    if (!form || {{ $consultation->treatment_plan_created ? 'true' : 'false' }}) return;
                    
                    let saveTimeout;
                    const self = this;
                    
                    form.addEventListener('input', function() {
                        self.autoSaveStatus = 'saving';
                        clearTimeout(saveTimeout);
                        
                        saveTimeout = setTimeout(async () => {
                            try {
                                const formData = new FormData(form);
                                const data = {};
                                for (let [key, value] of formData.entries()) {
                                    if (key.includes('[')) {
                        // Handle nested arrays
                        const keys = key.match(/(\w+)(?:\[(\w+)\])?/g);
                        let obj = data;
                        for (let i = 0; i < keys.length - 1; i++) {
                            const k = keys[i].replace(/\[|\]/g, '');
                            if (!obj[k]) obj[k] = {};
                            obj = obj[k];
                        }
                        const lastKey = keys[keys.length - 1].replace(/\[|\]/g, '');
                        obj[lastKey] = value;
                    } else {
                        data[key] = value;
                    }
                                }
                                
                                const response = await fetch('{{ route("doctor.consultations.auto-save-treatment-plan", $consultation->id) }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify(data)
                                });
                                
                                const result = await response.json();
                                if (result.success) {
                                    self.autoSaveStatus = 'saved';
                                    self.lastSaved = result.timestamp || new Date().toLocaleTimeString();
                                }
                            } catch (error) {
                                console.error('Auto-save failed:', error);
                                self.autoSaveStatus = 'error';
                            }
                        }, 2000); // Auto-save after 2 seconds of inactivity
                    });
                }
            }
        }
        
        // Initialize auto-save when page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (window.Alpine && Alpine.store) {
                setTimeout(() => {
                    const page = Alpine.$data(document.querySelector('[x-data*="consultationPage"]'));
                    if (page && page.initAutoSave) {
                        page.initAutoSave();
                    }
                }, 500);
            }
        });
    </script>
@endpush

@section('content')
    <!-- Consultation Lifecycle Banner (In-App Consultations Only) -->
    <x-consultation.partials.lifecycle-banner :consultation="$consultation" userType="doctor" />
    
    <div class="max-w-7xl mx-auto p-4 md:p-6">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Consultations
            </a>
        </div>

        <!-- 3-Zone Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ 
            expandedSections: {
                complaint: true,
                history: false,
                family: false,
                drug: false,
                social: false,
                documents: false
            },
            autoSaveStatus: 'saved',
            lastSaved: null
        }">
            
            <!-- LEFT SIDEBAR: Patient Summary & Quick Actions -->
            <div class="lg:col-span-3 space-y-4">
                <!-- Patient Summary Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-6">
                    <!-- Patient Avatar & Name -->
                    <div class="text-center mb-5 pb-5 border-b border-gray-200">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center mx-auto mb-3 border-4 border-white shadow-md">
                            <span class="text-2xl font-bold text-indigo-600">{{ substr($consultation->full_name, 0, 1) }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $consultation->first_name }} {{ $consultation->last_name }}</h3>
                        <p class="text-xs text-gray-500 font-mono mb-2">{{ $consultation->reference }}</p>
                        <div class="flex items-center justify-center gap-2 text-xs text-gray-600">
                            <span>{{ $consultation->age ?? ($consultation->patient && $consultation->patient->date_of_birth ? $consultation->patient->date_of_birth->age : 'N/A') }} years</span>
                            <span>•</span>
                            <span class="capitalize">{{ $consultation->gender }}</span>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Consultation Status</label>
                        @if($consultation->status === 'completed')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider border border-emerald-200">
                                ✓ Completed
                            </span>
                        @elseif($consultation->status === 'pending')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 uppercase tracking-wider border border-amber-200">
                                ⏳ Pending
                            </span>
                        @elseif($consultation->status === 'scheduled')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider border border-indigo-200">
                                📅 Scheduled
                            </span>
                        @elseif($consultation->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 uppercase tracking-wider border border-rose-200">
                                ✕ Cancelled
                            </span>
                        @endif
                    </div>

                    <!-- Payment Status -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment Status</label>
                        @if($consultation->payment_status === 'paid')
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider border border-emerald-200">
                                ✓ Paid
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700 uppercase tracking-wider border border-rose-200">
                                Unpaid
                            </span>
                        @endif
                    </div>

                    <!-- Consultation Mode -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Consultation Mode</label>
                        @php
                            $mode = $consultation->consultation_mode ?? $consultation->consult_mode ?? 'whatsapp';
                        @endphp
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg border border-gray-200">
                            @if($mode === 'voice')
                                <span class="text-lg">🎤</span>
                                <p class="text-sm text-gray-900 font-semibold">Voice Call</p>
                            @elseif($mode === 'video')
                                <span class="text-lg">🎥</span>
                                <p class="text-sm text-gray-900 font-semibold">Video Call</p>
                            @elseif($mode === 'chat')
                                <span class="text-lg">💬</span>
                                <p class="text-sm text-gray-900 font-semibold">Chat</p>
                            @else
                                <span class="text-lg">📱</span>
                                <p class="text-sm text-gray-900 font-semibold">WhatsApp</p>
                            @endif
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1 italic">Selected by patient</p>
                    </div>

                    <!-- Scheduled Time -->
                    @if($consultation->scheduled_at)
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Scheduled Time</label>
                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm font-semibold text-gray-900">{{ $consultation->scheduled_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-600">{{ $consultation->scheduled_at->format('h:i A') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Contact Info -->
                    <div class="mb-5 pb-5 border-b border-gray-200 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Email</label>
                            <p class="text-xs text-gray-900 break-all">{{ $consultation->email ?: ($consultation->booking ? $consultation->booking->payer_email : 'N/A') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Mobile</label>
                            <p class="text-xs text-gray-900">{{ $consultation->mobile }}</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="space-y-2">
                        <button @click="updateStatus('scheduled')" 
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Update Status
                        </button>
                        @php
                            $hasReferral = false;
                            try {
                                $hasReferral = $consultation->hasReferral();
                            } catch (\Exception $e) {
                                $hasReferral = false;
                            }
                            $canShowReferButton = !$hasReferral && $consultation->status !== 'cancelled';
                        @endphp
                        @if($canShowReferButton)
                        <button @click="showReferModal = true" 
                                class="w-full px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Refer Patient
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CENTER: Medical Information -->
            <div class="lg:col-span-5 space-y-4">
                <!-- Problem Description Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="expandedSections.complaint = !expandedSections.complaint" 
                            class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-gray-900">Presenting Complaint</h3>
                                <p class="text-xs text-gray-500">Problem description & severity</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedSections.complaint }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="expandedSections.complaint" x-transition class="px-5 pb-5 border-t border-gray-100">
                        <div class="pt-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Problem Description</label>
                                <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200">{{ $consultation->problem }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Severity</label>
                                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold
                                    {{ $consultation->severity === 'mild' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $consultation->severity === 'moderate' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                                    {{ $consultation->severity === 'severe' ? 'bg-red-100 text-red-700 border border-red-200' : '' }}">
                                    {{ ucfirst($consultation->severity ?? 'Not specified') }}
                                </span>
                            </div>
                            @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Emergency Symptoms</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($consultation->emergency_symptoms as $symptom)
                                    <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-xs border border-red-200 font-medium">
                                        {{ $symptom }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Patient Medical Information (Active Consultations Only) -->
                @if($consultation->status !== 'completed' && $patientMedicalInfo)
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-blue-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Patient Medical Information
                        </h3>
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Active</span>
                    </div>
                    <p class="text-xs text-blue-700 mb-4 italic">Accessible only during active consultations</p>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        @if($patientMedicalInfo->blood_group)
                        <div class="bg-white p-2 rounded border border-blue-200">
                            <p class="text-blue-800 font-semibold mb-1">Blood Group</p>
                            <p class="text-blue-900">{{ $patientMedicalInfo->blood_group }}</p>
                        </div>
                        @endif
                        @if($patientMedicalInfo->genotype)
                        <div class="bg-white p-2 rounded border border-blue-200">
                            <p class="text-blue-800 font-semibold mb-1">Genotype</p>
                            <p class="text-blue-900">{{ $patientMedicalInfo->genotype }}</p>
                        </div>
                        @endif
                        @if($patientMedicalInfo->height)
                        <div class="bg-white p-2 rounded border border-blue-200">
                            <p class="text-blue-800 font-semibold mb-1">Height</p>
                            <p class="text-blue-900">{{ $patientMedicalInfo->height }} cm</p>
                        </div>
                        @endif
                        @if($patientMedicalInfo->weight)
                        <div class="bg-white p-2 rounded border border-blue-200">
                            <p class="text-blue-800 font-semibold mb-1">Weight</p>
                            <p class="text-blue-900">{{ $patientMedicalInfo->weight }} kg</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- History of Complaint (Collapsible) -->
                @if($consultation->history_of_complaint)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="expandedSections.history = !expandedSections.history" 
                            class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-gray-900">History of Complaint</h3>
                                <p class="text-xs text-gray-500">Detailed history</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedSections.history }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="expandedSections.history" x-transition class="px-5 pb-5 border-t border-gray-100">
                        <div class="pt-4">
                            <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200 whitespace-pre-line">{{ $consultation->history_of_complaint }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Family History (Collapsible) -->
                @if($consultation->family_history)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="expandedSections.family = !expandedSections.family" 
                            class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-gray-900">Family History</h3>
                                <p class="text-xs text-gray-500">Family medical history</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedSections.family }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="expandedSections.family" x-transition class="px-5 pb-5 border-t border-gray-100">
                        <div class="pt-4">
                            <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200 whitespace-pre-line">{{ $consultation->family_history }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Drug History (Collapsible) -->
                @if($consultation->drug_history)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="expandedSections.drug = !expandedSections.drug" 
                            class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.494l-3.564 2.691a2 2 0 01-1.696.07l-3.19-1.35a2 2 0 00-2.182 1.865l-.345 3.378a.5.5 0 01-.404.404l-3.378.345a2 2 0 01-1.865-2.182l1.35-3.19a2 2 0 00-.07-1.696l-2.691-3.564a6 6 0 00-.494-3.86l.477-2.387a2 2 0 01.547-1.022l2.8-2.8a2 2 0 012.829 0l1.894 1.894a2 2 0 001.414.586H10a2 2 0 012 2v.586a2 2 0 001.414.586l1.894 1.894a2 2 0 010 2.829l-2.8 2.8z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-gray-900">Drug History</h3>
                                <p class="text-xs text-gray-500">Medications & allergies</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedSections.drug }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="expandedSections.drug" x-transition class="px-5 pb-5 border-t border-gray-100">
                        <div class="pt-4">
                            <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200 whitespace-pre-line">{{ $consultation->drug_history }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Social History (Collapsible) -->
                @if($consultation->social_history)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="expandedSections.social = !expandedSections.social" 
                            class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-gray-900">Social History</h3>
                                <p class="text-xs text-gray-500">Lifestyle & habits</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedSections.social }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="expandedSections.social" x-transition class="px-5 pb-5 border-t border-gray-100">
                        <div class="pt-4">
                            <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200 whitespace-pre-line">{{ $consultation->social_history }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Medical Documents (Collapsible) -->
                @if($consultation->medical_documents && count($consultation->medical_documents) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button @click="expandedSections.documents = !expandedSections.documents" 
                            class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-gray-900">Uploaded Documents</h3>
                                <p class="text-xs text-gray-500">{{ count($consultation->medical_documents) }} file(s)</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedSections.documents }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="expandedSections.documents" x-transition class="px-5 pb-5 border-t border-gray-100">
                        <div class="pt-4 space-y-2">
                            @foreach($consultation->medical_documents as $doc)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $doc['name'] ?? 'Document' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ isset($doc['size']) ? number_format($doc['size'] / 1024, 2) . ' KB' : '' }}</p>
                                </div>
                                @if(isset($doc['url']))
                                <a href="{{ $doc['url'] }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- RIGHT: Treatment Plan Editor -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-6" id="treatment-plan">
                    <!-- Header -->
                    <div class="mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Treatment Plan
                            </h2>
                            <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold uppercase tracking-wider border border-indigo-200">
                                Medical Record
                            </span>
                        </div>
                        
                        <!-- Draft Mode Indicator -->
                        @if(!$consultation->treatment_plan_created)
                        <div class="flex items-center gap-2 mt-3 p-2 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-semibold text-amber-700">Draft Mode</span>
                            <span class="text-[10px] text-amber-600 ml-auto" x-text="autoSaveStatus === 'saving' ? 'Saving...' : (autoSaveStatus === 'saved' && lastSaved ? 'Saved ' + lastSaved : 'Auto-save enabled')"></span>
                        </div>
                        @endif
                    </div>

                    @if($consultation->hasTreatmentPlan())
                        <!-- Treatment Plan Created View -->
                        <div class="space-y-4">
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                                <p class="text-xs text-emerald-800 flex items-center">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <strong>Treatment Plan Created</strong> on {{ $consultation->treatment_plan_created_at->format('M d, Y h:i A') }}
                                </p>
                            </div>

                            @if($consultation->diagnosis)
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Diagnosis</label>
                                <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200">{{ $consultation->diagnosis }}</p>
                            </div>
                            @endif

                            @if($consultation->treatment_plan)
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Treatment Plan</label>
                                <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-200 whitespace-pre-line">{{ $consultation->treatment_plan }}</p>
                            </div>
                            @endif

                            @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Prescribed Medications</label>
                                <div class="space-y-2">
                                    @foreach($consultation->prescribed_medications as $medication)
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <p class="text-xs font-semibold text-gray-900 mb-1">{{ $medication['name'] }}</p>
                                        <p class="text-[10px] text-gray-600">Dosage: {{ $medication['dosage'] }} | Frequency: {{ $medication['frequency'] }} | Duration: {{ $medication['duration'] }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-4">
                                <p class="text-xs text-amber-800 flex items-start leading-relaxed">
                                    <svg class="w-3 h-3 mr-1.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span><strong>Treatment Plan Locked:</strong> This treatment plan has been finalized and cannot be edited. It is now a permanent medical record.</span>
                                </p>
                            </div>
                        </div>
                    @else
                        <!-- Treatment Plan Form -->
                        @if($consultation->status === 'completed')
                            @include('doctor.partials.treatment-plan-form', ['consultation' => $consultation])
                            
                            <!-- Sticky Save Button with Warning -->
                            <div class="sticky bottom-0 mt-6 pt-4 border-t-2 border-gray-200 bg-white">
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                                    <p class="text-xs text-amber-800 flex items-start leading-relaxed">
                                        <svg class="w-3 h-3 mr-1.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        <span><strong>Warning:</strong> This action will permanently lock this medical record. Once saved, the treatment plan cannot be edited.</span>
                                    </p>
                                </div>
                                <button type="submit" form="treatmentPlanForm" 
                                        class="w-full px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Final Treatment Plan
                                </button>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">Treatment plan can only be created when consultation status is "Completed".</p>
                                <button @click="updateStatus('completed')" 
                                        class="mt-4 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    Mark as Completed
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Review Section Anchor -->
    <div id="review" x-init="
        if (window.location.hash === '#review') {
            setTimeout(() => {
                openReviewModal();
                window.location.hash = '';
            }, 300);
        }
    "></div>

    <!-- Message Modal -->
    <div x-show="showMessageModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showMessageModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showMessageModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full"
                     :class="{
                         'bg-emerald-100': messageType === 'success',
                         'bg-red-100': messageType === 'error',
                         'bg-blue-100': messageType === 'info'
                     }">
                    <svg x-show="messageType === 'success'" class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="messageType === 'error'" class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <svg x-show="messageType === 'info'" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center">
                    <h3 class="text-sm font-bold text-gray-900 mb-1.5" x-text="messageTitle"></h3>
                    <p class="text-xs text-gray-600 mb-4 leading-relaxed" x-text="messageText"></p>
                </div>

                <!-- Button -->
                <button @click="showMessageModal = false"
                        class="w-full px-4 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showConfirmModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showConfirmModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showConfirmModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-5 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center mb-4">
                    <h3 class="text-sm font-bold text-gray-900 mb-1.5" x-text="confirmTitle"></h3>
                    <p class="text-xs text-gray-600 leading-relaxed" x-text="confirmText"></p>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-2">
                    <button @click="showConfirmModal = false"
                            class="flex-1 px-4 py-2 text-xs font-semibold bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button @click="executeConfirm()"
                            class="flex-1 px-4 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div x-show="showReviewModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showReviewModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showReviewModal = false"></div>
            
            <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90"
                 @click.stop>
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Rate Patient</h2>
                        <p class="text-gray-600 text-sm mt-1">Share your experience with {{ $consultation->full_name }}</p>
                    </div>
                    <button @click="showReviewModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Consultation Info -->
                <div class="bg-blue-50 p-4 rounded-lg mb-6">
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>Reference:</strong> {{ $consultation->reference }}</p>
                        <p><strong>Patient:</strong> {{ $consultation->full_name }}</p>
                        <p><strong>Date:</strong> {{ $consultation->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <!-- Review Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">What are you rating?</label>
                    <div class="flex gap-3">
                        <label class="flex items-center px-4 py-2 border-2 rounded-lg cursor-pointer transition"
                               :class="revieweeType === 'patient' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'">
                            <input type="radio" x-model="revieweeType" value="patient" class="sr-only">
                            <span class="text-sm font-medium" :class="revieweeType === 'patient' ? 'text-blue-700' : 'text-gray-700'">Patient</span>
                        </label>
                        <label class="flex items-center px-4 py-2 border-2 rounded-lg cursor-pointer transition"
                               :class="revieweeType === 'platform' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'">
                            <input type="radio" x-model="revieweeType" value="platform" class="sr-only">
                            <span class="text-sm font-medium" :class="revieweeType === 'platform' ? 'text-blue-700' : 'text-gray-700'">Platform</span>
                        </label>
                    </div>
                </div>

                <!-- Star Rating -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">Overall Rating *</label>
                    <div class="flex justify-center space-x-2 mb-2">
                        <template x-for="i in 5" :key="i">
                            <svg 
                                class="w-12 h-12 cursor-pointer transition-all hover:scale-110" 
                                :class="i <= selectedRating ? 'text-yellow-400' : 'text-gray-300'"
                                @click="selectedRating = i"
                                fill="currentColor" 
                                viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </template>
                    </div>
                    <p class="text-center text-gray-500 text-sm" x-text="selectedRating > 0 ? ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][selectedRating] : 'Click on the stars to rate'"></p>
                </div>

                <!-- Comment -->
                <div class="mb-6">
                    <label for="reviewComment" class="block text-sm font-semibold text-gray-900 mb-2">
                        Share your feedback (optional)
                    </label>
                    <textarea 
                        x-model="reviewComment"
                        id="reviewComment"
                        rows="4"
                        placeholder="Share your thoughts about the consultation experience..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 resize-none text-sm"
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button 
                        type="button"
                        @click="submitReview()"
                        :disabled="isSubmittingReview || selectedRating === 0"
                        class="flex-1 purple-gradient text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <span x-show="!isSubmittingReview">Submit Review</span>
                        <span x-show="isSubmittingReview">Submitting...</span>
                    </button>
                    <button 
                        type="button"
                        @click="showReviewModal = false"
                        class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-sm">
                        Cancel
                    </button>
                </div>
                </div>
            </div>
        </div>

    <!-- Refer Patient Modal -->
    <div x-show="showReferModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showReferModal = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="modal-backdrop fixed inset-0" @click="showReferModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Refer Patient to Another Doctor</h3>
                    <button @click="showReferModal = false" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Consultation Info -->
                <div class="bg-purple-50 p-4 rounded-lg m-6">
                    <div class="text-sm text-gray-700 space-y-1">
                        <p><strong>Reference:</strong> {{ $consultation->reference }}</p>
                        <p><strong>Patient:</strong> {{ $consultation->full_name }}</p>
                        <p><strong>Age:</strong> {{ $consultation->age }} years | <strong>Gender:</strong> {{ ucfirst($consultation->gender) }}</p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitReferral()" class="px-6 pb-6">
                    <!-- Select Doctor -->
                    <div class="mb-6">
                        <label for="referredToDoctorId" class="block text-sm font-semibold text-gray-900 mb-2">
                            Select Doctor to Refer To <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select 
                                x-model="referredToDoctorId"
                                id="referredToDoctorId"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 text-sm appearance-none bg-white">
                                <option value="">-- Select a doctor --</option>
                                <template x-for="(doctors, specialization) in groupedDoctors" :key="specialization">
                                    <optgroup :label="specialization || 'General Practice'">
                                        <template x-for="doctor in doctors" :key="doctor.id">
                                            <option :value="doctor.id" 
                                                    x-text="doctor.name + (doctor.specialization ? ' (' + doctor.specialization + ')' : '')">
                                            </option>
                                        </template>
                                    </optgroup>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Only available and approved doctors are shown</p>
                        
                        <!-- Selected Doctor Info -->
                        <div x-show="referredToDoctorId" 
                             x-cloak
                             class="mt-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                            <template x-for="doctor in availableDoctors" :key="doctor.id">
                                <div x-show="doctor.id == referredToDoctorId" class="text-sm">
                                    <p class="font-semibold text-gray-900" x-text="doctor.name"></p>
                                    <p class="text-gray-600 mt-1" x-show="doctor.specialization">
                                        <span class="font-medium">Specialization:</span> 
                                        <span x-text="doctor.specialization"></span>
                                    </p>
                                    <p class="text-gray-600" x-show="doctor.email">
                                        <span class="font-medium">Email:</span> 
                                        <span x-text="doctor.email"></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-6">
                        <label for="referralReason" class="block text-sm font-semibold text-gray-900 mb-2">
                            Reason for Referral <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="referralReason"
                            id="referralReason"
                            required
                            minlength="10"
                            maxlength="1000"
                            rows="4"
                            placeholder="Please provide a clear reason for referring this patient (e.g., requires specialist care, outside my area of expertise, etc.)"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 resize-none text-sm"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
                    </div>

                    <!-- Additional Notes -->
                    <div class="mb-6">
                        <label for="referralNotes" class="block text-sm font-semibold text-gray-900 mb-2">
                            Additional Notes (Optional)
                        </label>
                        <textarea 
                            x-model="referralNotes"
                            id="referralNotes"
                            maxlength="2000"
                            rows="3"
                            placeholder="Any additional information that might be helpful for the receiving doctor..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 resize-none text-sm"
                        ></textarea>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> A new consultation will be created for the referred doctor with all patient information and medical history. The patient and the receiving doctor will be notified.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3">
                        <button 
                            type="submit"
                            :disabled="isReferring || !referredToDoctorId || referralReason.length < 10"
                            class="flex-1 purple-gradient text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                            <span x-show="!isReferring">Submit Referral</span>
                            <span x-show="isReferring">Submitting...</span>
                        </button>
                        <button 
                            type="button"
                            @click="showReferModal = false"
                            class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <x-system-preloader x-show="isUpdating" message="Updating Status..." subtext="Please wait while we finalize the change." />
    <x-system-preloader x-show="isSubmittingReview" message="Submitting Review..." subtext="Please wait..." />
    <x-system-preloader x-show="isReferring" message="Submitting Referral..." subtext="Please wait while we process the referral..." />
@endpush
