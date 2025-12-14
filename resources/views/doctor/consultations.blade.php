<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Consultations - Doctor Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        [x-cloak] {
            display: none !important;
        }
        /* Custom Scrollbar for Treatment Plan Modal */
        .treatment-plan-modal ::-webkit-scrollbar {
            width: 8px;
        }
        .treatment-plan-modal ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .treatment-plan-modal ::-webkit-scrollbar-thumb {
            background: #14b8a6;
            border-radius: 10px;
        }
        .treatment-plan-modal ::-webkit-scrollbar-thumb:hover {
            background: #0d9488;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ 
    sidebarOpen: false,
    showTreatmentPlanModal: false,
    currentConsultationId: null,
    isEditMode: false,
    loading: false,
    autoSaveInterval: null,
    lastSaveTime: null,
    init() {
        // Auto-open consultation modal if consultation_id is in session or URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const openConsultationId = urlParams.get('open') || @json(session('consultation_id'));
        
        if (openConsultationId) {
            this.$nextTick(() => {
                const consultationId = parseInt(openConsultationId);
                // Try to find the consultation in the page to get its hasTreatmentPlan status
                // For now, we'll just open the modal - the loadConsultationData will fetch the actual data
                setTimeout(() => {
                    // Check if consultation exists by trying to fetch it
                    fetch(`/doctor/consultations/${consultationId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.consultation) {
                                const hasPlan = data.consultation.has_treatment_plan || false;
                                this.openTreatmentPlanModal(consultationId, hasPlan);
                                // Clean up URL parameter
                                const url = new URL(window.location);
                                url.searchParams.delete('open');
                                window.history.replaceState({}, '', url);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading consultation:', error);
                        });
                }, 500);
            });
        }
    },
    treatmentPlanForm: {
        presenting_complaint: '',
        history_of_complaint: '',
        past_medical_history: '',
        family_history: '',
        drug_history: '',
        social_history: '',
        diagnosis: '',
        investigation: '',
        treatment_plan: '',
        prescribed_medications: [],
        follow_up_instructions: '',
        lifestyle_recommendations: '',
        referrals: [],
        next_appointment_date: '',
        additional_notes: ''
    },
    openTreatmentPlanModal(consultationId, hasPlan, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        try {
            console.log('Opening treatment plan modal for consultation:', consultationId);
            console.log('Alpine.js available:', typeof Alpine !== 'undefined');
            this.currentConsultationId = consultationId;
            this.isEditMode = hasPlan === true || hasPlan === 'true';
            this.showTreatmentPlanModal = true;
            console.log('showTreatmentPlanModal set to:', this.showTreatmentPlanModal);
            
            // Force display update
            setTimeout(() => {
                const modal = document.querySelector('.treatment-plan-modal');
                if (modal) {
                    modal.style.display = 'block';
                    modal.style.zIndex = '100';
                    modal.style.position = 'fixed';
                    modal.style.top = '0';
                    modal.style.left = '0';
                    modal.style.right = '0';
                    modal.style.bottom = '0';
                    console.log('Modal display forced to block with z-index 100');
                    console.log('Modal computed styles:', {
                        display: window.getComputedStyle(modal).display,
                        zIndex: window.getComputedStyle(modal).zIndex,
                        position: window.getComputedStyle(modal).position,
                        visibility: window.getComputedStyle(modal).visibility,
                        opacity: window.getComputedStyle(modal).opacity
                    });
                } else {
                    console.error('Modal element not found!');
                }
            }, 50);
            
            this.loadConsultationData(consultationId);
            this.startAutoSave();
        } catch (error) {
            console.error('Error opening treatment plan modal:', error);
            CustomAlert.error('Failed to open treatment plan form. Please try again.');
        }
    },
    closeTreatmentPlanModal() {
        this.showTreatmentPlanModal = false;
        this.currentConsultationId = null;
        this.stopAutoSave();
        // Force hide
        const modal = document.querySelector('.treatment-plan-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    },
    async loadConsultationData(consultationId) {
        try {
            const response = await fetch(`/doctor/consultations/${consultationId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            
            // Check if response is HTML (error page) instead of JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Received HTML instead of JSON:', text.substring(0, 200));
                throw new Error('Server returned HTML instead of JSON. Please check the request.');
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.success && data.consultation) {
                const c = data.consultation;
                this.treatmentPlanForm = {
                    presenting_complaint: c.presenting_complaint || '',
                    history_of_complaint: c.history_of_complaint || '',
                    past_medical_history: c.past_medical_history || '',
                    family_history: c.family_history || '',
                    drug_history: c.drug_history || '',
                    social_history: c.social_history || '',
                    diagnosis: c.diagnosis || '',
                    investigation: c.investigation || '',
                    treatment_plan: c.treatment_plan || '',
                    prescribed_medications: c.prescribed_medications || [],
                    follow_up_instructions: c.follow_up_instructions || '',
                    lifestyle_recommendations: c.lifestyle_recommendations || '',
                    referrals: c.referrals || [],
                    next_appointment_date: c.next_appointment_date || '',
                    additional_notes: c.additional_notes || ''
                };
            } else {
                console.error('Failed to load consultation data:', data);
                CustomAlert.error(data.message || 'Failed to load consultation data');
            }
        } catch (error) {
            console.error('Error loading consultation:', error);
            CustomAlert.error('Failed to load consultation data. Please try again.');
        }
    },
    startAutoSave() {
        this.stopAutoSave();
        this.autoSaveInterval = setInterval(() => {
            this.autoSaveDraft();
        }, 30000); // Auto-save every 30 seconds
    },
    stopAutoSave() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
            this.autoSaveInterval = null;
        }
    },
    async autoSaveDraft() {
        if (!this.currentConsultationId) return;
        try {
            const response = await fetch(`/doctor/consultations/${this.currentConsultationId}/auto-save-treatment-plan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.treatmentPlanForm)
            });
            const data = await response.json();
            if (data.success) {
                this.lastSaveTime = data.timestamp;
            }
        } catch (error) {
            console.error('Auto-save error:', error);
        }
    },
    async loadPatientHistory() {
        if (!this.currentConsultationId) return;
        try {
            const response = await fetch(`/doctor/consultations/${this.currentConsultationId}/patient-history`);
            const data = await response.json();
            if (data.success && data.has_history && data.history) {
                const h = data.history;
                this.treatmentPlanForm.past_medical_history = h.past_medical_history || this.treatmentPlanForm.past_medical_history;
                this.treatmentPlanForm.family_history = h.family_history || this.treatmentPlanForm.family_history;
                this.treatmentPlanForm.drug_history = h.drug_history || this.treatmentPlanForm.drug_history;
                this.treatmentPlanForm.social_history = h.social_history || this.treatmentPlanForm.social_history;
                CustomAlert.success('Patient history loaded from ' + h.last_consultation_date);
            } else {
                CustomAlert.info('No previous medical history found for this patient.');
            }
        } catch (error) {
            console.error('Error loading patient history:', error);
            CustomAlert.error('Failed to load patient history.');
        }
    },
    async submitTreatmentPlan() {
        if (!this.currentConsultationId) return;
        this.loading = true;
        try {
            const response = await fetch(`/doctor/consultations/${this.currentConsultationId}/treatment-plan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.treatmentPlanForm)
            });
            const data = await response.json();
            if (data.success) {
                CustomAlert.success(data.message || 'Treatment plan saved successfully!');
                this.closeTreatmentPlanModal();
                window.location.reload();
            } else {
                CustomAlert.error('Error: ' + (data.message || 'Failed to save treatment plan'));
            }
        } catch (error) {
            console.error('Error saving treatment plan:', error);
            CustomAlert.error('Failed to save treatment plan. Please try again.');
        } finally {
            this.loading = false;
        }
    },
    addMedication() {
        this.treatmentPlanForm.prescribed_medications.push({
            name: '',
            dosage: '',
            frequency: '',
            duration: ''
        });
    },
    removeMedication(index) {
        this.treatmentPlanForm.prescribed_medications.splice(index, 1);
    },
    addReferral() {
        this.treatmentPlanForm.referrals.push({
            specialist: '',
            reason: '',
            urgency: 'routine'
        });
    },
    removeReferral(index) {
        this.treatmentPlanForm.referrals.splice(index, 1);
    }
}">
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

                <a href="{{ route('doctor.profile') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>

                <a href="{{ route('doctor.availability') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Availability</span>
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
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-white">My Consultations</h1>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                        <p class="text-xs text-gray-600 uppercase font-medium">Total</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                        <p class="text-xs text-gray-600 uppercase font-medium">Paid</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['paid'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                        <p class="text-xs text-gray-600 uppercase font-medium">Unpaid</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['unpaid'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                        <p class="text-xs text-gray-600 uppercase font-medium">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                        <p class="text-xs text-gray-600 uppercase font-medium">Completed</p>
                        <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['completed'] }}</p>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <form method="GET" action="{{ route('doctor.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Name, email, phone, reference"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                        </div>
                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                            <select id="payment_status"
                                    name="payment_status"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                                <option value="">All Payments</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>✅ Paid</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>❌ Unpaid</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Consultation Status</label>
                            <select id="status"
                                    name="status"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                Filter
                            </button>
                            <a href="{{ route('doctor.consultations') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Consultations Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($consultations as $consultation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $consultation->reference }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $consultation->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $consultation->age }} yrs, {{ ucfirst($consultation->gender) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $consultation->mobile }}</div>
                                        <div class="text-xs text-gray-500">{{ $consultation->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2" x-data="{ 
                                            status: '{{ $consultation->status }}',
                                            updating: false,
                                            getStatusClass() {
                                                if (this.status === 'completed') return 'bg-green-100 text-green-800 border-green-300';
                                                if (this.status === 'pending') return 'bg-yellow-100 text-yellow-800 border-yellow-300';
                                                if (this.status === 'scheduled') return 'bg-blue-100 text-blue-800 border-blue-300';
                                                return 'bg-red-100 text-red-800 border-red-300';
                                            }
                                        }">
                                            <select 
                                                x-model="status"
                                                @change="
                                                    updating = true;
                                                    const originalStatus = '{{ $consultation->status }}';
                                                    fetch('{{ route('doctor.consultations.update-status', $consultation->id) }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                            'Accept': 'application/json'
                                                        },
                                                        body: JSON.stringify({ status: status })
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        updating = false;
                                                        if (data.success) {
                                                            // Show success message
                                                            const alert = document.createElement('div');
                                                            alert.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                                                            alert.textContent = data.message || 'Status updated successfully!';
                                                            document.body.appendChild(alert);
                                                            setTimeout(() => alert.remove(), 3000);
                                                            
                                                            // Reload page to reflect changes across platform
                                                            setTimeout(() => window.location.reload(), 1000);
                                                        } else {
                                                            CustomAlert.error('Error: ' + (data.message || 'Failed to update status'));
                                                            status = originalStatus;
                                                        }
                                                    })
                                                    .catch(error => {
                                                        updating = false;
                                                        console.error('Error:', error);
                                                        CustomAlert.error('Failed to update status. Please try again.');
                                                        status = originalStatus;
                                                    });
                                                "
                                                :disabled="updating"
                                                :class="getStatusClass() + ' px-3 py-1.5 text-xs font-semibold rounded-full border-2 focus:outline-none focus:ring-2 focus:ring-purple-500 hover:shadow-md transition-all cursor-pointer'"
                                                style="min-width: 120px;">
                                                <option value="pending" {{ $consultation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="scheduled" {{ $consultation->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                <option value="completed" {{ $consultation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $consultation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            <span x-show="updating" class="text-xs text-gray-500">
                                                <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($consultation->payment_status === 'paid')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                ✅ Paid
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                ❌ Unpaid
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $consultation->created_at->format('M d, Y') }}
                                        <div class="text-xs text-gray-500">{{ $consultation->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-col space-y-2">
                                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" class="text-purple-600 hover:text-purple-900">
                                                View Details
                                            </a>
                                            @if($consultation->status === 'completed')
                                                <button 
                                                    type="button"
                                                    @click.stop.prevent="openTreatmentPlanModal({{ $consultation->id }}, {{ $consultation->hasTreatmentPlan() ? 'true' : 'false' }}, $event)"
                                                    class="text-sm px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-medium">
                                                    {{ $consultation->hasTreatmentPlan() ? '📝 Edit Plan' : '➕ Fill Treatment Plan' }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Consultations Found</h3>
                                            <p class="text-gray-600">{{ request()->has('search') || request()->has('status') || request()->has('payment_status') ? 'Try adjusting your filters' : 'Your consultations will appear here' }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($consultations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $consultations->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Treatment Plan Modal -->
    <div x-show="showTreatmentPlanModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="treatment-plan-modal fixed inset-0 z-[100] overflow-y-auto"
         @click.self="closeTreatmentPlanModal()">
        <div class="flex items-end sm:items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" style="z-index: 101;">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" style="z-index: 101;"></div>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 sm:align-middle sm:max-w-5xl w-full" style="z-index: 102; position: relative; max-height: 85vh; display: flex; flex-direction: column;">
                <!-- Header - Fixed -->
                <div class="bg-teal-600 px-4 py-4 sm:px-6 sm:py-5 text-white flex-shrink-0 border-b border-teal-700 shadow-md" style="background-color: #0d9488 !important;">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between">
                        <div class="flex items-start space-x-3 sm:space-x-4 flex-1 min-w-0 mb-4 sm:mb-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0d9488 !important;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xl sm:text-2xl font-bold leading-tight mb-1 text-white" style="color: #ffffff !important; opacity: 1 !important;">
                                    <span x-show="isEditMode">Edit Treatment Plan</span>
                                    <span x-show="!isEditMode">Create Treatment Plan</span>
                                </h3>
                                <p class="text-xs sm:text-sm leading-relaxed font-medium text-white" style="color: #ffffff !important; opacity: 0.9 !important;">
                                    Complete the form below to document patient treatment and create a comprehensive treatment plan
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-end space-x-2 sm:ml-4 flex-shrink-0 w-full sm:w-auto">
                            <button @click="loadPatientHistory()" 
                                    class="flex-1 sm:flex-none justify-center px-3 py-2 sm:px-4 bg-white text-teal-700 hover:bg-teal-50 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center space-x-2 shadow-sm hover:shadow-md"
                                    style="background-color: #ffffff !important; color: #0f766e !important;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Load History</span>
                            </button>
                            <button @click="closeTreatmentPlanModal()" 
                                    class="w-9 h-9 sm:w-10 sm:h-10 bg-teal-700 text-white hover:bg-teal-800 rounded-lg flex items-center justify-center transition-all shadow-sm hover:shadow-md"
                                    style="background-color: #0f766e !important; color: #ffffff !important;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div x-show="lastSaveTime" class="mt-3 flex items-center space-x-2 text-white text-sm bg-teal-800 rounded-lg px-3 py-1.5 inline-flex" style="background-color: #115e59 !important; color: #ffffff !important; opacity: 1 !important;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" style="color: #ffffff !important;">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium" style="color: #ffffff !important;">Draft saved at <span x-text="lastSaveTime" class="font-bold" style="color: #ffffff !important;"></span></span>
                    </div>
                </div>

                <!-- Scrollable Content Area -->
                <!-- Scrollable Content Area -->
                <div class="bg-gray-50 px-4 py-4 sm:px-6 flex-1 overflow-y-auto text-gray-900" style="max-height: calc(85vh - 140px);">
                    <form @submit.prevent="submitTreatmentPlan()">
                        <!-- Section 1: Patient History -->
                        <div class="mb-5">
                            <div class="flex items-center space-x-2 mb-3 pb-2 border-b border-blue-200">
                                <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-gray-800">Patient History</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Presenting Complaint -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        Presenting Complaint <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="treatmentPlanForm.presenting_complaint" 
                                              required
                                              rows="2"
                                              placeholder="Patient's main complaint..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- History of Complaint -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        History of Complaint <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="treatmentPlanForm.history_of_complaint" 
                                              required
                                              rows="2"
                                              placeholder="Detailed history..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- Past Medical History -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Past Medical History</label>
                                    <textarea x-model="treatmentPlanForm.past_medical_history" 
                                              rows="2"
                                              placeholder="Previous conditions..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- Family History -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Family History</label>
                                    <textarea x-model="treatmentPlanForm.family_history" 
                                              rows="2"
                                              placeholder="Family medical history..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- Drug History -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Drug History</label>
                                    <textarea x-model="treatmentPlanForm.drug_history" 
                                              rows="2"
                                              placeholder="Medications & allergies..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- Social History -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Social History</label>
                                    <textarea x-model="treatmentPlanForm.social_history" 
                                              rows="2"
                                              placeholder="Lifestyle & habits..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Clinical Assessment -->
                        <div class="mb-5">
                            <div class="flex items-center space-x-2 mb-3 pb-2 border-b border-green-200">
                                <div class="w-6 h-6 bg-green-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-gray-800">Clinical Assessment</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Diagnosis -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        Diagnosis <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="treatmentPlanForm.diagnosis" 
                                              required
                                              rows="2"
                                              placeholder="Primary & secondary diagnoses..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- Investigation -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Investigation</label>
                                    <textarea x-model="treatmentPlanForm.investigation" 
                                              rows="2"
                                              placeholder="Recommended tests..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Treatment Plan -->
                        <div class="mb-5">
                            <div class="flex items-center space-x-2 mb-3 pb-2 border-b border-teal-200">
                                <div class="w-6 h-6 bg-teal-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-gray-800">Treatment Plan</h4>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Treatment Plan Details <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="treatmentPlanForm.treatment_plan" 
                                          required
                                          rows="3"
                                          placeholder="Detailed treatment plan..."
                                          class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                            </div>
                        </div>

                        <!-- Section 4: Medications & Prescriptions -->
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-yellow-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-yellow-100 rounded flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-800">Prescribed Medications</h4>
                                </div>
                                <button type="button" @click="addMedication()" 
                                        class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-xs font-medium transition-all flex items-center space-x-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Add</span>
                                </button>
                            </div>
                            
                            <template x-for="(med, index) in treatmentPlanForm.prescribed_medications" :key="index">
                                <div class="mb-3 p-3 bg-white border border-yellow-200 rounded-lg hover:border-yellow-300 transition-all">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold text-gray-600">Med #<span x-text="index + 1"></span></span>
                                        <button type="button" @click="removeMedication(index)" 
                                                class="text-red-500 hover:text-red-700 text-xs font-medium">
                                            Remove
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" x-model="med.name" placeholder="Name" 
                                               class="w-full px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-500">
                                        <input type="text" x-model="med.dosage" placeholder="Dosage" 
                                               class="w-full px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-500">
                                        <input type="text" x-model="med.frequency" placeholder="Frequency" 
                                               class="w-full px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-500">
                                        <input type="text" x-model="med.duration" placeholder="Duration" 
                                               class="w-full px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-yellow-400 focus:ring-1 focus:ring-yellow-500">
                                    </div>
                                </div>
                            </template>
                            <div x-show="treatmentPlanForm.prescribed_medications.length === 0" class="text-center py-4 text-gray-400 text-xs">
                                <p>No medications added. Click "Add" to add one.</p>
                            </div>
                        </div>

                        <!-- Section 5: Follow-up & Recommendations -->
                        <div class="mb-5">
                            <div class="flex items-center space-x-2 mb-3 pb-2 border-b border-indigo-200">
                                <div class="w-6 h-6 bg-indigo-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-gray-800">Follow-up & Recommendations</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Follow-up Instructions -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Follow-up Instructions</label>
                                    <textarea x-model="treatmentPlanForm.follow_up_instructions" 
                                              rows="2"
                                              placeholder="Follow-up care instructions..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>

                                <!-- Lifestyle Recommendations -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Lifestyle Recommendations</label>
                                    <textarea x-model="treatmentPlanForm.lifestyle_recommendations" 
                                              rows="2"
                                              placeholder="Diet, exercise, lifestyle..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 6: Referrals -->
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-red-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-red-100 rounded flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-800">Referrals</h4>
                                </div>
                                <button type="button" @click="addReferral()" 
                                        class="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 text-xs font-medium transition-all flex items-center space-x-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Add</span>
                                </button>
                            </div>
                            
                            <template x-for="(ref, index) in treatmentPlanForm.referrals" :key="index">
                                <div class="mb-3 p-3 bg-white border border-red-200 rounded-lg hover:border-red-300 transition-all">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold text-gray-600">Ref #<span x-text="index + 1"></span></span>
                                        <button type="button" @click="removeReferral(index)" 
                                                class="text-red-500 hover:text-red-700 text-xs font-medium">Remove</button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" x-model="ref.specialist" placeholder="Specialist" 
                                               class="w-full px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-500">
                                        <select x-model="ref.urgency" 
                                                class="w-full px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-500">
                                            <option value="routine">🟢 Routine</option>
                                            <option value="urgent">🟡 Urgent</option>
                                            <option value="emergency">🔴 Emergency</option>
                                        </select>
                                        <input type="text" x-model="ref.reason" placeholder="Reason" 
                                               class="w-full col-span-2 px-2 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-500">
                                    </div>
                                </div>
                            </template>
                            <div x-show="treatmentPlanForm.referrals.length === 0" class="text-center py-4 text-gray-400 text-xs">
                                <p>No referrals added. Click "Add" to add one.</p>
                            </div>
                        </div>

                        <!-- Section 7: Additional Information -->
                        <div class="mb-5">
                            <div class="flex items-center space-x-2 mb-3 pb-2 border-b border-gray-200">
                                <div class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                    </svg>
                                </div>
                                <h4 class="text-base font-bold text-gray-800">Additional Information</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Next Appointment Date -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Next Appointment Date</label>
                                    <input type="date" x-model="treatmentPlanForm.next_appointment_date" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white">
                                </div>

                                <!-- Additional Notes -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Additional Notes</label>
                                    <textarea x-model="treatmentPlanForm.additional_notes" 
                                              rows="2"
                                              placeholder="Additional notes..."
                                              class="w-full px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all bg-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="sticky bottom-0 bg-gray-50 -mx-4 -mb-4 sm:-mx-6 px-4 py-4 sm:px-6 border-t border-gray-200 flex flex-col-reverse sm:flex-row items-center sm:justify-end gap-3 sm:gap-0 sm:space-x-3 mt-6">
                            <button type="button" @click="closeTreatmentPlanModal()" 
                                    class="w-full sm:w-auto px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-all text-sm">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="loading"
                                    class="w-full sm:w-auto px-6 py-2 bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-lg hover:from-teal-700 hover:to-teal-800 font-medium disabled:opacity-50 transition-all shadow-md flex items-center justify-center space-x-2 text-sm"
                                    style="background-color: #0f766e !important; color: #ffffff !important;">
                                <span x-show="!loading" x-text="isEditMode ? 'Update Plan' : 'Create Plan'" style="color: #ffffff !important;">Create Plan</span>
                                <span x-show="loading" class="flex items-center space-x-2" style="color: #ffffff !important;">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Saving...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    @include('components.custom-alert-modal')
</body>
</html>
