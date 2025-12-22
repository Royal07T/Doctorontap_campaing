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
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, isGlobalUpdating: false }">
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
                                <tr class="hover:bg-gray-50" 
                                    data-consultation-id="{{ $consultation->id }}"
                                    x-data="{ 
                                        isUpdating: false,
                                        consultationId: {{ $consultation->id }},
                                        async updateStatus(newStatus) {
                                            if (!newStatus || newStatus === '') {
                                                console.error('No status provided');
                                                return;
                                            }
                                            
                                            const consultationId = this.consultationId || this.$el.getAttribute('data-consultation-id');
                                            
                                            if (!consultationId) {
                                                console.error('Consultation ID is missing');
                                                if (typeof showAlertModal === 'function') {
                                                    showAlertModal('Error: Consultation ID is missing. Please refresh the page.', 'error');
                                                } else {
                                                    if (typeof showAlertModal === 'function') {
                                                        showAlertModal('Error: Consultation ID is missing. Please refresh the page.', 'error');
                                                    }
                                                }
                                                return;
                                            }
                                            
                                            this.isUpdating = true;
                                            this.isGlobalUpdating = true;
                                            try {
                                                const url = `/doctor/consultations/${consultationId}/update-status`;
                                                console.log('Updating status:', { consultationId, newStatus, url });
                                                
                                                const response = await fetch(url, {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                        'Accept': 'application/json'
                                                    },
                                                    body: JSON.stringify({ status: newStatus })
                                                });
                                                
                                                if (!response.ok) {
                                                    const errorText = await response.text();
                                                    throw new Error(`Server error: ${response.status} - ${errorText}`);
                                                }
                                                
                                                const data = await response.json();
                                                if (data.success) {
                                                    if (typeof showAlertModal === 'function') {
                                                        showAlertModal('Status updated successfully! Admin has been notified.', 'success');
                                                    } else {
                                                        if (typeof showAlertModal === 'function') {
                                                            showAlertModal('Status updated successfully! Admin has been notified.', 'success');
                                                        }
                                                    }
                                                    setTimeout(() => window.location.reload(), 1500);
                                                } else {
                                                    if (typeof showAlertModal === 'function') {
                                                        showAlertModal(data.message || 'Failed to update status', 'error');
                                                    } else {
                                                        if (typeof showAlertModal === 'function') {
                                                            showAlertModal(data.message || 'Failed to update status', 'error');
                                                        }
                                                    }
                                                }
                                            } catch (error) {
                                                console.error('Error updating status:', error);
                                                if (typeof showAlertModal === 'function') {
                                                    showAlertModal('Error updating status: ' + error.message, 'error');
                                                } else {
                                                    if (typeof showAlertModal === 'function') {
                                                        showAlertModal('Error updating status: ' + error.message, 'error');
                                                    }
                                                }
                                            } finally {
                                                this.isUpdating = false;
                                                this.isGlobalUpdating = false;
                                            }
                                        }
                                    }">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $consultation->reference }}</div>
                                            @if($consultation->is_multi_patient_booking && $consultation->booking)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Multi-Patient Booking: {{ $consultation->booking->reference }}">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    Multi
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $consultation->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $consultation->age }} yrs, {{ ucfirst($consultation->gender) }}</div>
                                        @if($consultation->is_multi_patient_booking && $consultation->booking)
                                            <div class="text-xs text-blue-600 mt-1">
                                                Payer: {{ $consultation->booking->payer_name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $consultation->mobile }}</div>
                                        <div class="text-xs text-gray-500">{{ $consultation->email ?: ($consultation->booking ? $consultation->booking->payer_email : 'N/A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select @change="updateStatus($event.target.value)" :disabled="isUpdating"
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold border-0 cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500
                                                {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $consultation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $consultation->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $consultation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            <option value="pending" {{ $consultation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="scheduled" {{ $consultation->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="completed" {{ $consultation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $consultation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
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
                                        <div class="flex flex-col space-y-1">
                                            <a href="{{ route('doctor.consultations.view', $consultation->id) }}" class="text-purple-600 hover:text-purple-900">
                                                View Details
                                            </a>
                                            @if($consultation->status === 'completed')
                                                @if($consultation->hasTreatmentPlan())
                                                    <a href="{{ route('doctor.consultations.view', $consultation->id) }}#treatment-plan" class="text-green-600 hover:text-green-800 inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Edit Treatment Plan
                                                    </a>
                                                @else
                                                    <a href="{{ route('doctor.consultations.view', $consultation->id) }}#treatment-plan" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Create Treatment Plan
                                                    </a>
                                                @endif
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

    <x-system-preloader x-show="isGlobalUpdating" message="Updating Consultation Status..." subtext="Notifying patient and admin of the change." />
</body>
</html>
