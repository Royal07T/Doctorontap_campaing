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

@push('x-data-extra')
, showReviewModal: false, selectedRating: 0, reviewComment: '', revieweeType: 'patient', isSubmittingReview: false
@endpush

@push('scripts')
    <script>
        function consultationPage() {
            return {
                sidebarOpen: false,
                showMessageModal: false,
                messageType: 'success',
                messageTitle: '',
                messageText: '',
                showConfirmModal: false,
                confirmTitle: '',
                confirmText: '',
                confirmCallback: null,
                isUpdating: false,
                
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
                }
            }
        }
    </script>
@endpush

@section('content')
    <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-600 hover:text-purple-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Consultations
                    </a>
                </div>

                <!-- Patient Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Patient Information
                        </h2>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-lg font-mono text-xs font-semibold">
                            {{ $consultation->reference }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Full Name</label>
                            <p class="text-sm text-gray-900 font-semibold">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
                            <p class="text-sm text-gray-900">{{ $consultation->email ?: ($consultation->booking ? $consultation->booking->payer_email : 'N/A') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Mobile</label>
                            <p class="text-sm text-gray-900">{{ $consultation->mobile }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Age</label>
                            <p class="text-sm text-gray-900">{{ $consultation->age }} years</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Gender</label>
                            <p class="text-sm text-gray-900 capitalize">{{ $consultation->gender }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Consultation Mode</label>
                            <p class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $consultation->consult_mode) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Medical Details
                        </h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Problem Description</label>
                            <p class="text-xs text-gray-700 leading-relaxed">{{ $consultation->problem }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Severity</label>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $consultation->severity === 'mild' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $consultation->severity === 'moderate' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $consultation->severity === 'severe' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($consultation->severity) }}
                            </span>
                        </div>

                        @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Emergency Symptoms</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($consultation->emergency_symptoms as $symptom)
                                <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-xs border border-red-200">
                                    {{ $symptom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Treatment Plan Section -->
                <div id="treatment-plan" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                @if($consultation->hasTreatmentPlan())
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Treatment Plan
                        </h2>
                    </div>
                    
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 mb-4">
                        <p class="text-xs text-emerald-800 flex items-center">
                            <svg class="w-2 h-2 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong>Treatment Plan Created</strong> on {{ $consultation->treatment_plan_created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    @if($consultation->diagnosis)
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Diagnosis</label>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ $consultation->diagnosis }}</p>
                    </div>
                    @endif

                    @if($consultation->treatment_plan)
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Treatment Plan</label>
                        <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">{{ $consultation->treatment_plan }}</p>
                    </div>
                    @endif

                    @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Prescribed Medications</label>
                        <div class="space-y-2">
                            @foreach($consultation->prescribed_medications as $medication)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-900 mb-1">{{ $medication['name'] }}</p>
                                <p class="text-xs text-gray-600">Dosage: {{ $medication['dosage'] }} | Frequency: {{ $medication['frequency'] }} | Duration: {{ $medication['duration'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Consultations
                        </a>
                        <div class="flex items-center gap-2">
                            @if($consultation->status === 'completed' && !$consultation->hasDoctorReview())
                                <button @click="openReviewModal()" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Rate Patient
                                </button>
                            @elseif($consultation->status === 'completed' && $consultation->hasDoctorReview())
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Review Submitted
                                </span>
                            @endif
                            @if($consultation->treatment_plan_created)
                                <div class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-gray-400 text-white rounded-lg cursor-not-allowed" title="Treatment plan cannot be edited once saved">
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Treatment Plan Locked
                                </div>
                            @else
                                <button onclick="document.getElementById('treatment-plan').scrollIntoView({ behavior: 'smooth' });" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Treatment Plan
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Show form for editing only if not created/saved -->
                    @if(!$consultation->treatment_plan_created)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            @include('doctor.partials.treatment-plan-form', ['consultation' => $consultation])
                        </div>
                    @else
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <p class="text-xs text-amber-800 flex items-center leading-relaxed">
                                    <svg class="w-2 h-2 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <span><strong>Treatment Plan Locked:</strong> This treatment plan has already been saved and cannot be edited. Once a treatment plan is saved, it becomes a permanent medical record and will be sent to the patient once payment is made.</span>
                                </p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Treatment Plan
                        </h2>
                    </div>
                    @if($consultation->status === 'completed')
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-blue-800 flex items-center">
                                <svg class="w-2 h-2 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <strong>Create Treatment Plan</strong> - Please fill out the treatment plan form below.
                            </p>
                        </div>
                        
                        @include('doctor.partials.treatment-plan-form', ['consultation' => $consultation])
                    @else
                        <p class="text-xs text-gray-600 mb-4">Treatment plan can only be created when the consultation status is set to "Completed".</p>
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Consultations
                        </a>
                    @endif
                @endif
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
@endsection

@push('scripts')
    <x-system-preloader x-show="isUpdating" message="Updating Status..." subtext="Please wait while we finalize the change." />
    <x-system-preloader x-show="isSubmittingReview" message="Submitting Review..." subtext="Please wait..." />
@endpush
