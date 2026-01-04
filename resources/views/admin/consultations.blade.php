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
<body class="bg-gray-100 min-h-screen" x-data="bulkActionsData()">
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
            <form method="GET" action="{{ route('admin.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
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
                    <a href="{{ route('admin.consultations') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions Button -->
        <div class="mb-6 flex justify-end">
            <button @click="showBulkActionsModal = true; clearSelection()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Bulk Actions
            </button>
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
                                <a href="{{ route('admin.consultation.show', $consultation->id) }}" 
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

    <!-- Bulk Actions Modal -->
    <div x-show="showBulkActionsModal" 
         @click.away="showBulkActionsModal = false"
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         style="display: none;">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-xl flex flex-col" @click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">Bulk Actions</h3>
                <button @click="showBulkActionsModal = false; clearSelection()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Action Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Select Action</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button @click="bulkAction = 'send_reminder'" 
                                :class="bulkAction === 'send_reminder' ? 'bg-blue-100 border-blue-500' : 'bg-gray-50 border-gray-300'"
                                class="p-4 border-2 rounded-lg transition text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Send Reminder</p>
                                    <p class="text-xs text-gray-600">Email, SMS, WhatsApp & in-app</p>
                                </div>
                            </div>
                        </button>
                        <button @click="bulkAction = 'send_payment_reminder'" 
                                :class="bulkAction === 'send_payment_reminder' ? 'bg-yellow-100 border-yellow-500' : 'bg-gray-50 border-gray-300'"
                                class="p-4 border-2 rounded-lg transition text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Send Payment Reminder</p>
                                    <p class="text-xs text-gray-600">Email payment requests</p>
                                </div>
                            </div>
                        </button>
                        <button @click="bulkAction = 'reassign'" 
                                :class="bulkAction === 'reassign' ? 'bg-purple-100 border-purple-500' : 'bg-gray-50 border-gray-300'"
                                class="p-4 border-2 rounded-lg transition text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Re-assign Doctor</p>
                                    <p class="text-xs text-gray-600">Assign to new doctor</p>
                                </div>
                            </div>
                        </button>
                        <button @click="bulkAction = 'delete'" 
                                :class="bulkAction === 'delete' ? 'bg-red-100 border-red-500' : 'bg-gray-50 border-gray-300'"
                                class="p-4 border-2 rounded-lg transition text-left">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Delete</p>
                                    <p class="text-xs text-gray-600">Permanently delete</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Consultations List -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-semibold text-gray-700">Select Consultations</label>
                        <button @click="toggleSelectAll()" class="text-xs text-purple-600 hover:text-purple-800 font-semibold">
                            <span x-text="selectedConsultations.length === consultations.length ? 'Deselect All' : 'Select All'"></span>
                        </button>
                    </div>
                    <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-12">
                                        <input type="checkbox" 
                                               @change="toggleSelectAll()"
                                               :checked="selectedConsultations.length === consultations.length && consultations.length > 0"
                                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Patient</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Payment</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="consultation in consultations" :key="consultation.id">
                                    <tr :class="isSelected(consultation.id) ? 'bg-purple-50' : ''" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input type="checkbox" 
                                                   @change="toggleSelection(consultation.id)"
                                                   :checked="isSelected(consultation.id)"
                                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm font-mono text-gray-900" x-text="consultation.reference"></span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900" x-text="consultation.full_name"></span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-700': consultation.status === 'completed',
                                                      'bg-amber-100 text-amber-700': consultation.status === 'pending',
                                                      'bg-blue-100 text-blue-700': consultation.status === 'scheduled',
                                                      'bg-red-100 text-red-700': consultation.status === 'cancelled'
                                                  }"
                                                  x-text="consultation.status.charAt(0).toUpperCase() + consultation.status.slice(1)"></span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-700': consultation.payment_status === 'paid',
                                                      'bg-red-100 text-red-700': consultation.payment_status === 'unpaid',
                                                      'bg-amber-100 text-amber-700': consultation.payment_status === 'pending'
                                                  }"
                                                  x-text="consultation.payment_status.charAt(0).toUpperCase() + consultation.payment_status.slice(1)"></span>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="consultations.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                        No consultations available
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-gray-500" x-show="selectedConsultations.length > 0">
                        <span x-text="selectedConsultations.length"></span> consultation(s) selected
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between p-6 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-600">
                    <span x-text="selectedConsultations.length"></span> selected • 
                    <span x-text="bulkAction ? bulkAction.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'No action selected'"></span>
                </div>
                <div class="flex gap-3">
                    <button @click="showBulkActionsModal = false; clearSelection()" 
                            class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button @click="executeBulkAction()" 
                            :disabled="selectedConsultations.length === 0 || !bulkAction || isProcessing"
                            class="px-4 py-2 text-sm font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing">Apply Action</span>
                        <span x-show="isProcessing" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Reassign Doctor Modal -->
    <div id="bulkReassignModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-purple-100">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Bulk Reassign Doctor</h3>
            <p class="text-gray-600 text-center mb-4">
                Reassign <span id="bulkReassignCount" class="font-semibold"></span> consultation(s) to a new doctor
            </p>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select New Doctor</label>
                <select id="bulkNewDoctorSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                    <option value="">-- Select a doctor --</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->full_name }} - {{ $doctor->specialization ?? 'General' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button onclick="closeBulkReassignModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmBulkReassignment()" id="bulkReassignBtn" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                    Reassign
                </button>
            </div>
        </div>
    </div>

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

        // Bulk Reassign Doctor Modal Functions
        let bulkReassignConsultationIds = [];
        
        function showBulkReassignModal(count, consultationIds) {
            bulkReassignConsultationIds = consultationIds;
            document.getElementById('bulkReassignCount').textContent = count;
            document.getElementById('bulkNewDoctorSelect').value = '';
            document.getElementById('bulkReassignModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeBulkReassignModal() {
            document.getElementById('bulkReassignModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            bulkReassignConsultationIds = [];
        }

        async function confirmBulkReassignment() {
            const newDoctorId = document.getElementById('bulkNewDoctorSelect').value;
            
            if (!newDoctorId) {
                showAlertModal('Please select a doctor', 'error');
                return;
            }

            const reassignBtn = document.getElementById('bulkReassignBtn');
            reassignBtn.disabled = true;
            reassignBtn.textContent = 'Reassigning...';

            try {
                const response = await fetch('/admin/consultations/bulk-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        action: 'reassign',
                        consultation_ids: bulkReassignConsultationIds,
                        doctor_id: newDoctorId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeBulkReassignModal();
                    showAlertModal(data.message, 'success');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showAlertModal(data.message || 'Failed to reassign consultations', 'error');
                }
            } catch (error) {
                showAlertModal('Error reassigning consultations', 'error');
            } finally {
                reassignBtn.disabled = false;
                reassignBtn.textContent = 'Reassign';
            }
        }

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

    <script>
        // Bulk Actions Data and Functions
        const consultationsData = @json($consultationsData);
        
        function bulkActionsData() {
            return {
                sidebarOpen: false,
                pageLoading: false,
                showBulkActionsModal: false,
                selectedConsultations: [],
                bulkAction: '',
                isProcessing: false,
                consultations: consultationsData,
                toggleSelection(id) {
                    const index = this.selectedConsultations.indexOf(id);
                    if (index > -1) {
                        this.selectedConsultations.splice(index, 1);
                    } else {
                        this.selectedConsultations.push(id);
                    }
                },
                isSelected(id) {
                    return this.selectedConsultations.includes(id);
                },
                toggleSelectAll() {
                    if (this.selectedConsultations.length === this.consultations.length) {
                        this.selectedConsultations = [];
                    } else {
                        this.selectedConsultations = this.consultations.map(c => c.id);
                    }
                },
                clearSelection() {
                    this.selectedConsultations = [];
                    this.bulkAction = '';
                },
                async executeBulkAction() {
                    if (this.selectedConsultations.length === 0) {
                        showAlertModal('Please select at least one consultation', 'error');
                        return;
                    }
                    if (!this.bulkAction) {
                        showAlertModal('Please select an action to perform', 'error');
                        return;
                    }
                    
                    const action = this.bulkAction;
                    const count = this.selectedConsultations.length;
                    
                    if (action === 'reassign') {
                        this.showBulkActionsModal = false;
                        showBulkReassignModal(count, this.selectedConsultations);
                        return;
                    }
                    
                    let confirmMessage = '';
                    switch(action) {
                        case 'send_reminder':
                            confirmMessage = `Send reminder notifications to ${count} consultation(s)?`;
                            break;
                        case 'send_payment_reminder':
                            confirmMessage = `Send payment reminder emails to ${count} consultation(s)?`;
                            break;
                        case 'delete':
                            confirmMessage = `Are you sure you want to delete ${count} consultation(s)? This action cannot be undone.`;
                            break;
                        default:
                            showAlertModal('Invalid action selected', 'error');
                            return;
                    }
                    
                    showConfirmModal(confirmMessage, async () => {
                        await this.processBulkAction(action);
                    });
                },
                async processBulkAction(action, doctorId = null) {
                    this.isProcessing = true;
                    try {
                        const payload = {
                            action: action,
                            consultation_ids: this.selectedConsultations
                        };
                        if (doctorId) {
                            payload.doctor_id = doctorId;
                        }
                        
                        const response = await fetch('/admin/consultations/bulk-action', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showBulkActionsModal = false;
                            this.clearSelection();
                            showAlertModal(data.message || 'Action completed successfully', 'success');
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showAlertModal(data.message || 'Action failed', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlertModal('An error occurred while processing the request', 'error');
                    } finally {
                        this.isProcessing = false;
                    }
                }
            };
        }
    </script>

    @include('components.alert-modal')
    @include('admin.shared.preloader')
</body>
</html>

