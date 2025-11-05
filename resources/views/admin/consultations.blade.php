<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Consultations - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
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
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <form method="GET" action="{{ route('admin.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, reference..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Payment</label>
                    <select name="payment_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="flex items-end">
                    <button type="submit" class="w-full px-5 py-2 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-md transition-all">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Consultations Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Reference</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Patient</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Doctor</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Problem</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Payment</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($consultations as $consultation)
                        <tr class="hover:bg-gray-50" x-data="{ 
                            isUpdating: false, 
                            isSending: false,
                            isReassigning: false,
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
                        }">
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-900">
                                {{ $consultation->reference }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $consultation->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $consultation->email }}</div>
                                <div class="text-xs text-gray-500">{{ $consultation->mobile }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $consultation->doctor ? $consultation->doctor->full_name : 'Any Doctor' }}
                                </div>
                                <button @click="openReassignModal()" 
                                        class="mt-1 text-xs text-purple-600 hover:text-purple-800 font-semibold underline">
                                    Reassign Doctor
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $consultation->problem }}">
                                    {{ $consultation->problem }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <select @change="updateStatus($event.target.value)" :disabled="isUpdating"
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold border-0
                                        {{ $consultation->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $consultation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $consultation->status === 'cancelled' ? 'bg-rose-100 text-rose-800' : '' }}
                                        cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="pending" {{ $consultation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="scheduled" {{ $consultation->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="completed" {{ $consultation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $consultation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $consultation->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $consultation->payment_status === 'unpaid' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $consultation->payment_status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                    {{ ucfirst($consultation->payment_status) }}
                                </span>
                                @if($consultation->payment_request_sent)
                                <div class="text-xs text-gray-500 mt-1">Sent {{ $consultation->payment_request_sent_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                                <div>{{ $consultation->created_at->format('M d, Y') }}</div>
                                <div class="text-gray-500">{{ $consultation->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium space-y-1.5">
                                @if($consultation->status === 'completed' && $consultation->payment_status === 'unpaid' && !$consultation->payment_request_sent)
                                <button @click="sendPayment()" :disabled="isSending"
                                        class="w-full px-3 py-1.5 text-xs bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 font-semibold"
                                        x-text="isSending ? 'Sending...' : 'Send Payment'">
                                </button>
                                @elseif($consultation->payment_request_sent && $consultation->payment_status === 'unpaid')
                                <button @click="sendPayment()" :disabled="isSending"
                                        class="w-full px-3 py-1.5 text-xs bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50 font-semibold"
                                        x-text="isSending ? 'Sending...' : 'Resend Payment'">
                                </button>
                                @endif
                                <a href="{{ route('admin.consultation.show', $consultation->id) }}" 
                                   class="block w-full px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-center font-semibold">
                                    View Details
                                </a>
                                <button onclick="deleteConsultation({{ $consultation->id }})" 
                                        class="w-full px-3 py-1.5 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-semibold">No consultations found</p>
                                <p class="text-sm">Try adjusting your filters</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($consultations->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
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

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-yellow-100">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirm Action</h3>
            <p id="confirmMessage" class="text-gray-600 text-center mb-6"></p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmAction()" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100">
                <svg id="alertIcon" class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <p id="alertMessage" class="text-gray-600 text-center mb-6"></p>
            <button onclick="closeAlertModal()" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                OK
            </button>
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
        let confirmCallback = null;
        let currentConsultationId = null;

        function showConfirmModal(message, onConfirm) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            confirmCallback = onConfirm;
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            confirmCallback = null;
        }

        function confirmAction() {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        }

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

        function showAlertModal(message, type = 'error') {
            const modal = document.getElementById('alertModal');
            const icon = document.getElementById('alertIcon');
            const text = document.getElementById('alertMessage');
            
            text.textContent = message;
            
            if (type === 'success') {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                icon.parentElement.className = 'flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-green-100';
                icon.className = 'w-6 h-6 text-green-600';
            } else {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                icon.parentElement.className = 'flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100';
                icon.className = 'w-6 h-6 text-red-600';
            }
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAlertModal() {
            document.getElementById('alertModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
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
</body>
</html>

