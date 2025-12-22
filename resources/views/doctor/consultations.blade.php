@extends('layouts.doctor')

@section('title', 'My Consultations')
@section('header-title', 'My Consultations')

@push('x-data-extra')
, isGlobalUpdating: false
@endpush

@section('content')
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
@endsection

@push('scripts')
    <x-system-preloader x-show="isGlobalUpdating" message="Updating Consultation Status..." subtext="Notifying patient and admin of the change." />
@endpush
