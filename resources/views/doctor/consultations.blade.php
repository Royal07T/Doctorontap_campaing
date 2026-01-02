@extends('layouts.doctor')

@section('title', 'My Consultations')
@section('header-title', 'My Consultations')

@push('x-data-extra')
, isGlobalUpdating: false
@endpush

@section('content')
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-4 border-l-4 border-blue-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total</p>
                        <p class="text-lg font-bold text-gray-900 mb-1">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500">Consultations</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-4 border-l-4 border-emerald-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Paid</p>
                        <p class="text-lg font-bold text-emerald-600 mb-1">{{ $stats['paid'] }}</p>
                        <p class="text-xs text-gray-500">Consultations</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-4 border-l-4 border-red-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Unpaid</p>
                        <p class="text-lg font-bold text-red-600 mb-1">{{ $stats['unpaid'] }}</p>
                        <p class="text-xs text-gray-500">Consultations</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-4 border-l-4 border-amber-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Pending</p>
                        <p class="text-lg font-bold text-amber-600 mb-1">{{ $stats['pending'] }}</p>
                        <p class="text-xs text-gray-500">Consultations</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-4 border-l-4 border-purple-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Completed</p>
                        <p class="text-lg font-bold text-purple-600 mb-1">{{ $stats['completed'] }}</p>
                        <p class="text-xs text-gray-500">Consultations</p>
                    </div>
                </div>
                
                <!-- Total Earnings Card -->
                <div class="mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-4 border-l-4 border-green-500 max-w-xs">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Earnings</p>
                        <p class="text-lg font-bold text-green-600 mb-1">₦{{ number_format($stats['total_earnings'] ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500">All Time</p>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search & Filter
                        </h2>
                    </div>
                    <form method="GET" action="{{ route('doctor.consultations') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label for="search" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Name, email, phone, reference"
                                       class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                            </div>
                        </div>
                        <div>
                            <label for="payment_status" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Payment Status</label>
                            <select id="payment_status"
                                    name="payment_status"
                                    class="w-full py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                                <option value="">All Payments</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>✅ Paid</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>❌ Unpaid</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Consultation Status</label>
                            <select id="status"
                                    name="status"
                                    class="w-full py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
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
                                        consultationId: {{ $consultation->id }},
                                        async updateStatus(newStatus) {
                                            if (!newStatus || newStatus === '') {
                                                console.error('No status provided');
                                                return;
                                            }
                                            
                                if (!this.consultationId) {
                                                console.error('Consultation ID is missing');
                                                if (typeof showAlertModal === 'function') {
                                                    showAlertModal('Error: Consultation ID is missing. Please refresh the page.', 'error');
                                                }
                                                return;
                                            }
                                            
                                            this.isUpdating = true;
                                            this.isGlobalUpdating = true;
                                            try {
                                    const url = `/doctor/consultations/${this.consultationId}/update-status`;
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
                                                    }
                                                    setTimeout(() => window.location.reload(), 1500);
                                                } else {
                                                    if (typeof showAlertModal === 'function') {
                                                        showAlertModal(data.message || 'Failed to update status', 'error');
                                                    }
                                                }
                                            } catch (error) {
                                                console.error('Error updating status:', error);
                                                if (typeof showAlertModal === 'function') {
                                                    showAlertModal('Error updating status: ' + error.message, 'error');
                                                }
                                            } finally {
                                                this.isUpdating = false;
                                                this.isGlobalUpdating = false;
                                            }
                                        }
                        }" 
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
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
                                            @else
                                                <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-gray-900">{{ $consultation->reference }}</h3>
                                            @if($consultation->is_multi_patient_booking && $consultation->booking)
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700" title="Multi-Patient Booking: {{ $consultation->booking->reference }}">
                                                        <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    Multi
                                                    </span>
                                                @endif
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($consultation->status === 'completed') bg-emerald-100 text-emerald-700
                                                    @elseif($consultation->status === 'pending') bg-amber-100 text-amber-700
                                                    @elseif($consultation->status === 'scheduled') bg-blue-100 text-blue-700
                                                    @elseif($consultation->status === 'cancelled') bg-red-100 text-red-700
                                                    @else bg-gray-100 text-gray-700 @endif">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                                @if($consultation->payment_status === 'paid')
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">✓ Paid</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Unpaid</span>
                                                @endif
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
                                            <p class="text-xs text-gray-600 mt-0.5">{{ $consultation->age }} yrs, {{ ucfirst($consultation->gender) }}</p>
                                            @if($consultation->is_multi_patient_booking && $consultation->booking)
                                                <p class="text-xs text-blue-600 mt-1">Payer: {{ $consultation->booking->payer_name }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Contact</p>
                                            <p class="text-xs text-gray-900">{{ $consultation->mobile }}</p>
                                            <p class="text-xs text-gray-600">{{ $consultation->email ?: ($consultation->booking ? $consultation->booking->payer_email : 'N/A') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                            <p class="text-xs text-gray-900">{{ $consultation->created_at->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-600">{{ $consultation->created_at->format('h:i A') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Status</p>
                                            @if($consultation->payment_status === 'paid')
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                                    ✓ Paid
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                                    Unpaid
                                                </span>
                                            @endif
                                        </div>
                                            </div>

                                    <!-- Status Update -->
                                    <div class="pt-3 border-t border-gray-200">
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Update Status</label>
                                        <select @change="updateStatus($event.target.value)" 
                                                :disabled="isUpdating"
                                                class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition
                                                {{ $consultation->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : '' }}
                                                {{ $consultation->status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-200' : '' }}
                                                {{ $consultation->status === 'scheduled' ? 'bg-blue-50 text-blue-800 border-blue-200' : '' }}
                                                {{ $consultation->status === 'cancelled' ? 'bg-red-50 text-red-800 border-red-200' : '' }}">
                                            <option value="pending" {{ $consultation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="scheduled" {{ $consultation->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="completed" {{ $consultation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $consultation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="pt-3 border-t border-gray-200 flex flex-wrap items-center gap-2">
                                        <a href="{{ route('doctor.consultations.view', $consultation->id) }}" 
                                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                                View Details
                                            </a>
                                            @if($consultation->status === 'completed')
                                                @if(!$consultation->hasDoctorReview())
                                                    <a href="{{ route('doctor.consultations.view', $consultation->id) }}#review" 
                                                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                        Rate Patient
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-lg">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                        Review Submitted
                                                    </span>
                                                @endif
                                                @if($consultation->hasTreatmentPlan())
                                                <a href="{{ route('doctor.consultations.view', $consultation->id) }}#treatment-plan" 
                                                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Edit Treatment Plan
                                                    </a>
                                                @else
                                                <a href="{{ route('doctor.consultations.view', $consultation->id) }}#treatment-plan" 
                                                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Create Treatment Plan
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                </div>
                            </div>
                        </div>
                                @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Consultations Found</h3>
                            <p class="text-xs text-gray-500 mb-4">{{ request()->has('search') || request()->has('status') || request()->has('payment_status') ? 'Try adjusting your filters' : 'Your consultations will appear here' }}</p>
                                        </div>
                                @endforelse

                    <!-- Pagination -->
                    @if($consultations->hasPages())
                    <div class="mt-6">
                        {{ $consultations->links() }}
                    </div>
                    @endif
                </div>
@endsection

@push('scripts')
    <x-system-preloader x-show="isGlobalUpdating" message="Updating Consultation Status..." subtext="Notifying patient and admin of the change." />
@endpush
