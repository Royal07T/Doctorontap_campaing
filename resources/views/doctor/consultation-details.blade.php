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
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        @if($consultation->treatment_plan_created)
                            <div class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-gray-400 text-white rounded-lg cursor-not-allowed" title="Treatment plan cannot be edited once saved">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    
                    <!-- Show form for editing only if not created/saved -->
                    @if(!$consultation->treatment_plan_created)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            @include('doctor.partials.treatment-plan-form', ['consultation' => $consultation])
                        </div>
                    @else
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <p class="text-xs text-amber-800 flex items-center leading-relaxed">
                                    <svg class="w-3.5 h-3.5 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
@endsection

@push('scripts')
    <x-system-preloader x-show="isUpdating" message="Updating Status..." subtext="Please wait while we finalize the change." />
@endpush
