<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consultation Details - Doctor Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        [x-cloak] { display: none !important; }
    </style>
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
</head>
<body class="bg-gray-100 min-h-screen" x-data="consultationPage()">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="purple-gradient p-5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-8 w-auto">
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::guard('doctor')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">Dr. {{ Auth::guard('doctor')->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::guard('doctor')->user()->specialization ?? 'Doctor' }}</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('doctor.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('doctor.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>My Consultations</span>
                </a>

                <a href="{{ route('doctor.bank-accounts') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Bank Accounts</span>
                </a>

                <a href="{{ route('doctor.payment-history') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Payment History</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>View Website</span>
                </a>

                <form method="POST" action="{{ route('doctor.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-white">Consultation Details</h1>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Consultations
                    </a>
                </div>

                <!-- Patient Information -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">Patient Information</h2>
                        <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded-lg font-mono text-sm font-bold">
                            {{ $consultation->reference }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Full Name</label>
                            <p class="text-lg text-gray-900">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Email</label>
                            <p class="text-lg text-gray-900">{{ $consultation->email ?: ($consultation->booking ? $consultation->booking->payer_email : 'N/A') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Mobile</label>
                            <p class="text-lg text-gray-900">{{ $consultation->mobile }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Age</label>
                            <p class="text-lg text-gray-900">{{ $consultation->age }} years</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Gender</label>
                            <p class="text-lg text-gray-900 capitalize">{{ $consultation->gender }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Consultation Mode</label>
                            <p class="text-lg text-gray-900 capitalize">{{ str_replace('_', ' ', $consultation->consult_mode) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Details -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Medical Details</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Problem Description</label>
                            <p class="text-base text-gray-900 leading-relaxed">{{ $consultation->problem }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Severity</label>
                            <span class="inline-flex px-4 py-2 rounded-full text-sm font-semibold
                                {{ $consultation->severity === 'mild' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $consultation->severity === 'moderate' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $consultation->severity === 'severe' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($consultation->severity) }}
                            </span>
                        </div>

                        @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-2">Emergency Symptoms</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($consultation->emergency_symptoms as $symptom)
                                <span class="px-3 py-1 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
                                    {{ $symptom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Treatment Plan Section -->
                <div id="treatment-plan" class="bg-white rounded-xl shadow-md p-6 mb-6">
                @if($consultation->hasTreatmentPlan())
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Treatment Plan</h2>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-green-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <strong>Treatment Plan Created</strong> on {{ $consultation->treatment_plan_created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    @if($consultation->diagnosis)
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Diagnosis</label>
                        <p class="text-base text-gray-900">{{ $consultation->diagnosis }}</p>
                    </div>
                    @endif

                    @if($consultation->treatment_plan)
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Treatment Plan</label>
                        <p class="text-base text-gray-900 whitespace-pre-line">{{ $consultation->treatment_plan }}</p>
                    </div>
                    @endif

                    @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Prescribed Medications</label>
                        <div class="space-y-2">
                            @foreach($consultation->prescribed_medications as $medication)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p class="font-semibold text-gray-900">{{ $medication['name'] }}</p>
                                <p class="text-sm text-gray-600">Dosage: {{ $medication['dosage'] }} | Frequency: {{ $medication['frequency'] }} | Duration: {{ $medication['duration'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Back to Consultations
                        </a>
                        <button onclick="document.getElementById('treatment-plan').scrollIntoView({ behavior: 'smooth' });" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Treatment Plan
                        </button>
                    </div>
                    
                    <!-- Show form for editing -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        @include('doctor.partials.treatment-plan-form', ['consultation' => $consultation])
                    </div>
                @else
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Treatment Plan</h2>
                    @if($consultation->status === 'completed')
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-800 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <strong>Create Treatment Plan</strong> - Please fill out the treatment plan form below.
                            </p>
                        </div>
                        
                        @include('doctor.partials.treatment-plan-form', ['consultation' => $consultation])
                    @else
                        <p class="text-gray-600 mb-4">Treatment plan can only be created when the consultation status is set to "Completed".</p>
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Back to Consultations
                        </a>
                    @endif
                @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Message Modal -->
    <div x-show="showMessageModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showMessageModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="modal-backdrop fixed inset-0" @click="showMessageModal = false"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full"
                     :class="{
                         'bg-green-100': messageType === 'success',
                         'bg-red-100': messageType === 'error',
                         'bg-blue-100': messageType === 'info'
                     }">
                    <svg x-show="messageType === 'success'" class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="messageType === 'error'" class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <svg x-show="messageType === 'info'" class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="messageTitle"></h3>
                    <p class="text-gray-600 mb-6" x-text="messageText"></p>
                </div>

                <!-- Button -->
                <button @click="showMessageModal = false"
                        class="w-full px-6 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg transition-all">
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
            
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                
                <!-- Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="confirmTitle"></h3>
                    <p class="text-gray-600" x-text="confirmText"></p>
                </div>

                <!-- Buttons -->
                <div class="flex space-x-3">
                    <button @click="showConfirmModal = false"
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button @click="executeConfirm()"
                            class="flex-1 px-6 py-3 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg transition-all">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-system-preloader x-show="isUpdating" message="Updating Status..." subtext="Please wait while we finalize the change." />
</body>
</html>
