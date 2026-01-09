<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Consultations - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.shared.sidebar', ['active' => 'consultations'])

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
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">Consultations</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="mb-4 pb-4 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Search & Filter
                </h2>
            </div>
            <form method="GET" action="{{ admin_route('admin.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, reference..."
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Payment</label>
                    <select name="payment_status" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ admin_route('admin.consultations') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Consultations Cards -->
        <div class="space-y-4">
            @forelse($consultations as $consultation)
                <div x-data="{ 
                    open: false,
                    isUpdating: false, 
                    isSending: false,
                    async updateStatus(newStatus) {
                        this.isUpdating = true;
                        try {
                            const response = await fetch('/admin/consultation/{{ $consultation->id }}/status', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({ status: newStatus })
                            });
                            const data = await response.json();
                            if (data.success) {
                                showAlertModal('Status updated successfully', 'success');
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                showAlertModal(data.message || 'Failed to update status', 'error');
                            }
                        } catch (error) {
                            showAlertModal('Error updating status', 'error');
                        } finally {
                            this.isUpdating = false;
                        }
                    },
                    async sendPayment() {
                        showConfirmModal('Send payment request email to {{ $consultation->email }}?', async () => {
                            this.isSending = true;
                            try {
                                const response = await fetch('/admin/consultation/{{ $consultation->id }}/send-payment', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                    }
                                });
                                const data = await response.json();
                                if (data.success) {
                                    showAlertModal(data.message, 'success');
                                    setTimeout(() => window.location.reload(), 1500);
                                } else {
                                    showAlertModal(data.message || 'Failed to send payment request', 'error');
                                }
                            } catch (error) {
                                showAlertModal('Error sending payment request', 'error');
                            } finally {
                                this.isSending = false;
                            }
                        });
                    },
                    openReassignModal() {
                        showReassignModal({{ $consultation->id }}, '{{ $consultation->doctor ? $consultation->doctor->full_name : 'No Doctor' }}');
                    }
                }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                    <!-- Card Header -->
                    <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <div class="p-5 flex items-center justify-between">
                            <div class="flex-1 flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    @if($consultation->status === 'completed')
                                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                    @elseif($consultation->status === 'pending')
                                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                    @elseif($consultation->status === 'scheduled')
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                    @elseif($consultation->status === 'cancelled')
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-sm font-semibold text-gray-900 font-mono">{{ $consultation->reference }}</h3>
                                        @if($consultation->is_multi_patient_booking && $consultation->booking)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700" title="Multi-Patient Booking: {{ $consultation->booking->reference }}">
                                                <svg class="w-2.5 h-2.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                Multi
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                            @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700
                                            @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700
                                            @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-700
                                            @elseif($consultation->status === 'cancelled') bg-red-100 text-red-700 @endif">
                                            {{ ucfirst($consultation->status) }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                            @if($consultation->payment_status === 'paid') bg-emerald-100 text-emerald-700
                                            @elseif($consultation->payment_status === 'unpaid') bg-red-100 text-red-700
                                            @elseif($consultation->payment_status === 'pending') bg-amber-100 text-amber-700 @endif">
                                            {{ ucfirst($consultation->payment_status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $consultation->full_name }} • {{ $consultation->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
                                     :class="{ 'rotate-180': open }" 
                                     fill="none" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
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
                         class="border-t border-gray-100 bg-gray-50"
                         style="display: none;">
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Patient</p>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $consultation->full_name }}</p>
                                    <p class="text-xs text-gray-600 mt-0.5">{{ $consultation->email }}</p>
                                    <p class="text-xs text-gray-600">{{ $consultation->mobile }}</p>
                                    @if($consultation->is_multi_patient_booking && $consultation->booking)
                                        <p class="text-xs text-blue-600 mt-1">Payer: {{ $consultation->booking->payer_name }} ({{ $consultation->booking->payer_email }})</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Doctor</p>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $consultation->doctor ? $consultation->doctor->full_name : 'Any Doctor' }}</p>
                                    <button @click="openReassignModal()" 
                                            class="mt-1 text-xs text-purple-600 hover:text-purple-800 font-semibold">
                                        Reassign Doctor
                                    </button>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Problem</p>
                                    <p class="text-xs text-gray-700 leading-relaxed">{{ $consultation->problem }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                    <p class="text-xs text-gray-900">{{ $consultation->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Status</p>
                                    <select @change="updateStatus($event.target.value)" :disabled="isUpdating"
                                            class="px-2.5 py-1 rounded-full text-xs font-semibold border-0
                                            {{ $consultation->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $consultation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $consultation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                            cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        <option value="pending" {{ $consultation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="scheduled" {{ $consultation->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="completed" {{ $consultation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $consultation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                @if($consultation->payment_request_sent)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Request</p>
                                    <p class="text-xs text-gray-600">Sent {{ $consultation->payment_request_sent_at->diffForHumans() }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                                @if($consultation->status === 'completed' && $consultation->payment_status === 'unpaid' && !$consultation->payment_request_sent)
                                    <button @click="sendPayment()" :disabled="isSending"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition disabled:opacity-50"
                                            x-text="isSending ? 'Sending...' : 'Send Payment'">
                                    </button>
                                @elseif($consultation->payment_request_sent && $consultation->payment_status === 'unpaid')
                                    <button @click="sendPayment()" :disabled="isSending"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition disabled:opacity-50"
                                            x-text="isSending ? 'Sending...' : 'Resend Payment'">
                                    </button>
                                @endif
                                <a href="{{ admin_route('admin.consultation.show', $consultation->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </a>
                                <button onclick="deleteConsultation({{ $consultation->id }})" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">No Consultations Found</h3>
                    <p class="text-xs text-gray-500">Try adjusting your filters</p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($consultations->hasPages())
            <div class="mt-6">
                {{ $consultations->links() }}
            </div>
            @endif
        </div>
            </main>
        </div>
    </div>

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

    <!-- Note: Confirmation and Alert modals are provided by components/alert-modal.blade.php -->

    <!-- Reassign Doctor Modal -->
    <div id="reassignModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-purple-100">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Reassign Doctor</h3>
            <p class="text-gray-600 text-center mb-4">
                Current: <span id="currentDoctor" class="font-semibold"></span>
            </p>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select New Doctor</label>
                <select id="newDoctorSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                    <option value="">-- Select a doctor --</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->full_name }} - {{ $doctor->specialization ?? 'General' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button onclick="closeReassignModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmReassignment()" id="reassignBtn" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                    Reassign
                </button>
            </div>
        </div>
    </div>

    <script>
        // Modal System for Confirmations and Alerts
        // Note: confirmCallback and modal functions are defined in components/alert-modal.blade.php
        let currentConsultationId = null;

        // Delete Consultation
        async function deleteConsultation(id) {
            showConfirmModal(
                'Are you sure you want to delete this consultation? The record will be archived and can be restored if needed.',
                async () => {
                    try {
                        const response = await fetch(`/admin/consultations/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            showAlertModal(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            showAlertModal(data.message || 'An error occurred', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlertModal('A network error occurred', 'error');
                    }
                }
            );
        }

        // Note: showAlertModal and closeAlertModal are defined in components/alert-modal.blade.php

        // Reassign Doctor Modal Functions
        function showReassignModal(consultationId, currentDoctorName) {
            currentConsultationId = consultationId;
            document.getElementById('currentDoctor').textContent = currentDoctorName;
            document.getElementById('newDoctorSelect').value = '';
            document.getElementById('reassignModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeReassignModal() {
            document.getElementById('reassignModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentConsultationId = null;
        }

        async function confirmReassignment() {
            const newDoctorId = document.getElementById('newDoctorSelect').value;
            
            if (!newDoctorId) {
                showAlertModal('Please select a doctor', 'error');
                return;
            }

            const reassignBtn = document.getElementById('reassignBtn');
            reassignBtn.disabled = true;
            reassignBtn.textContent = 'Reassigning...';

            try {
                const response = await fetch(`/admin/consultation/${currentConsultationId}/reassign-doctor`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ doctor_id: newDoctorId })
                });

                const data = await response.json();

                if (data.success) {
                    closeReassignModal();
                    showAlertModal(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlertModal(data.message || 'Failed to reassign doctor', 'error');
                }
            } catch (error) {
                showAlertModal('Error reassigning doctor', 'error');
            } finally {
                reassignBtn.disabled = false;
                reassignBtn.textContent = 'Reassign';
            }
        }
    </script>

    @include('components.alert-modal')
    @include('admin.shared.preloader')
</body>
</html>

