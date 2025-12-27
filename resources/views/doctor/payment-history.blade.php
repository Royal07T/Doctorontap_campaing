@extends('layouts.doctor')

@section('title', 'Payment History')
@section('header-title', 'Payment History')

@push('x-data-extra')
, selectedPayment: null, showDetailsModal: false
@endpush

@section('content')
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Paid</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">₦{{ number_format($stats['total_paid'], 2) }}</p>
                                <p class="text-xs text-gray-500">Earnings</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-amber-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Pending Earnings</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">₦{{ number_format($stats['pending_amount'], 2) }}</p>
                                <p class="text-xs text-gray-500">Awaiting</p>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Paid Consultations</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['paid_consultations'] }}</p>
                                <p class="text-xs text-gray-500">Completed</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Unpaid Consultations</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['unpaid_consultations'] }}</p>
                                <p class="text-xs text-gray-500">Pending</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History Cards -->
                <div class="space-y-4">
                    @forelse($payments as $payment)
                        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                            <!-- Card Header -->
                            <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex-1 flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            @if($payment->status === 'completed')
                                                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                            @elseif($payment->status === 'pending')
                                                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                            @elseif($payment->status === 'failed')
                                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            @else
                                                <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-gray-900 font-mono">{{ $payment->reference }}</h3>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($payment->status === 'completed') bg-emerald-100 text-emerald-700
                                                    @elseif($payment->status === 'pending') bg-amber-100 text-amber-700
                                                    @elseif($payment->status === 'failed') bg-red-100 text-red-700
                                                    @else bg-blue-100 text-blue-700 @endif">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600">₦{{ number_format($payment->doctor_amount, 2) }} • {{ $payment->created_at->format('M d, Y') }}</p>
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
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                            <p class="text-xs text-gray-900">{{ $payment->created_at->format('M d, Y') }}</p>
                                        @if($payment->paid_at)
                                                <p class="text-xs text-gray-600 mt-0.5">Paid: {{ $payment->paid_at->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Consultations</p>
                                            <p class="text-xs text-gray-900 font-semibold">{{ $payment->total_consultations_count }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Your Amount</p>
                                            <p class="text-xs text-gray-900 font-bold">₦{{ number_format($payment->doctor_amount, 2) }}</p>
                                            <p class="text-xs text-gray-600 mt-0.5">({{ $payment->doctor_percentage }}% of ₦{{ number_format($payment->total_consultations_amount, 2) }})</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Bank Account</p>
                                            @if($payment->bankAccount)
                                                <p class="text-xs text-gray-900">{{ $payment->bankAccount->bank_name }}</p>
                                                <p class="text-xs text-gray-600 font-mono">{{ $payment->bankAccount->masked_account_number }}</p>
                                            @else
                                                <p class="text-xs text-gray-400">N/A</p>
                                        @endif
                                        </div>
                                        @if($payment->korapay_fee)
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Transaction Fee</p>
                                            <p class="text-xs text-amber-600 font-semibold">₦{{ number_format($payment->korapay_fee, 2) }}</p>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payout Status</p>
                                        @if($payment->korapay_reference)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                    @if($payment->korapay_status === 'success') bg-emerald-100 text-emerald-700
                                                    @elseif($payment->korapay_status === 'failed') bg-red-100 text-red-700
                                                    @else bg-blue-100 text-blue-700 @endif">
                                                    {{ ucfirst($payment->korapay_status ?? 'processing') }}
                                                </span>
                                                @if($payment->payout_completed_at)
                                                    <p class="text-xs text-emerald-600 mt-1">Completed: {{ $payment->payout_completed_at->format('M d, Y') }}</p>
                                                @elseif($payment->payout_initiated_at)
                                                    <p class="text-xs text-blue-600 mt-1">Initiated: {{ $payment->payout_initiated_at->format('M d, Y') }}</p>
                                                @endif
                                        @else
                                                <p class="text-xs text-gray-400">Not initiated</p>
                                        @endif
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="pt-3 border-t border-gray-200">
                                        <button @click="selectedPayment = {{ $payment->toJson() }}; showDetailsModal = true" 
                                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View Full Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                                @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Payment History Yet</h3>
                            <p class="text-xs text-gray-500">Your payments will appear here once admin processes them.</p>
                                        </div>
                                @endforelse

                    <!-- Pagination -->
                    @if($payments->hasPages())
                    <div class="mt-6">
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>

                <!-- Payment Details Modal -->
                <div x-show="showDetailsModal" 
                     x-cloak
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                     style="display: none;">
                    <div @click.away="showDetailsModal = false" class="bg-white rounded-xl shadow-2xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900">Payment Details</h2>
                            <button @click="showDetailsModal = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <template x-if="selectedPayment">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Reference</p>
                                        <p class="text-xs text-gray-900 font-mono font-semibold" x-text="selectedPayment.reference"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Status</p>
                                        <p class="text-xs text-gray-900 font-semibold capitalize" x-text="selectedPayment.status"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">KoraPay Status</p>
                                        <p class="text-xs text-gray-900 font-semibold capitalize" x-text="selectedPayment.korapay_status || 'Not initiated'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Total Consultations</p>
                                        <p class="text-xs text-gray-900 font-semibold" x-text="selectedPayment.total_consultations_count"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Method</p>
                                        <p class="text-xs text-gray-900 font-semibold capitalize" x-text="selectedPayment.payment_method || 'N/A'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Total Amount</p>
                                        <p class="text-xs text-gray-900 font-semibold" x-text="'₦' + parseFloat(selectedPayment.total_consultations_amount).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Your Share (<span x-text="selectedPayment.doctor_percentage"></span>%)</p>
                                        <p class="text-sm font-bold text-emerald-600" x-text="'₦' + parseFloat(selectedPayment.doctor_amount).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Platform Fee</p>
                                        <p class="text-xs text-gray-900 font-semibold" x-text="'₦' + parseFloat(selectedPayment.platform_fee).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Transaction Reference</p>
                                        <p class="text-xs text-gray-900 font-mono" x-text="selectedPayment.transaction_reference || 'N/A'"></p>
                                    </div>
                                    <div x-show="selectedPayment.korapay_reference">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">KoraPay Reference</p>
                                        <p class="text-xs text-gray-900 font-mono" x-text="selectedPayment.korapay_reference"></p>
                                    </div>
                                    <div x-show="selectedPayment.korapay_fee">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">KoraPay Fee</p>
                                        <p class="text-xs text-amber-600 font-semibold" x-text="'₦' + parseFloat(selectedPayment.korapay_fee || 0).toLocaleString()"></p>
                                    </div>
                                </div>

                                <div x-show="selectedPayment.korapay_reference" class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <h3 class="text-xs font-semibold text-blue-900 mb-3 uppercase tracking-wide">Payout Information</h3>
                                    <div class="space-y-2 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">KoraPay Status:</span>
                                            <span class="font-semibold capitalize text-gray-900" x-text="selectedPayment.korapay_status || 'processing'"></span>
                                        </div>
                                        <div x-show="selectedPayment.payout_initiated_at" class="flex justify-between">
                                            <span class="text-gray-600">Initiated:</span>
                                            <span class="font-semibold text-gray-900" x-text="new Date(selectedPayment.payout_initiated_at).toLocaleDateString()"></span>
                                        </div>
                                        <div x-show="selectedPayment.payout_completed_at" class="flex justify-between">
                                            <span class="text-gray-600">Completed:</span>
                                            <span class="font-semibold text-emerald-600" x-text="new Date(selectedPayment.payout_completed_at).toLocaleDateString()"></span>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="selectedPayment.payment_notes" class="mt-4">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Notes</p>
                                    <p class="text-xs bg-gray-50 p-3 rounded-lg text-gray-700 leading-relaxed" x-text="selectedPayment.payment_notes"></p>
                                </div>
                            </div>
                        </template>

                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <button @click="showDetailsModal = false" class="w-full px-5 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush

