@extends('layouts.doctor')

@section('title', 'Payment History')
@section('header-title', 'Payment History')

@push('x-data-extra')
, selectedPayment: null, showDetailsModal: false
@endpush

@section('content')
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase mb-1">Total Paid</p>
                                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['total_paid'], 2) }}</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase mb-1">Pending Earnings</p>
                                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['pending_amount'], 2) }}</p>
                            </div>
                            <div class="bg-yellow-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase mb-1">Paid Consultations</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['paid_consultations'] }}</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase mb-1">Unpaid Consultations</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['unpaid_consultations'] }}</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History Table -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">All Payments</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Account</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payout Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payments as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $payment->reference }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $payment->created_at->format('M d, Y') }}</div>
                                        @if($payment->paid_at)
                                            <div class="text-xs text-gray-500">Paid: {{ $payment->paid_at->format('M d, Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $payment->total_consultations_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">₦{{ number_format($payment->doctor_amount, 2) }}</div>
                                        <div class="text-xs text-gray-500">
                                            Total: ₦{{ number_format($payment->total_consultations_amount, 2) }}
                                            ({{ $payment->doctor_percentage }}%)
                                        </div>
                                        @if($payment->korapay_fee)
                                            <div class="text-xs text-orange-600 mt-1">
                                                Fee: ₦{{ number_format($payment->korapay_fee, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($payment->bankAccount)
                                            <div>{{ $payment->bankAccount->bank_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $payment->bankAccount->masked_account_number }}</div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($payment->status === 'completed') bg-green-100 text-green-800
                                            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($payment->korapay_reference)
                                            <div class="text-xs">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($payment->korapay_status === 'success') bg-green-100 text-green-800
                                                    @elseif($payment->korapay_status === 'failed') bg-red-100 text-red-800
                                                    @else bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ ucfirst($payment->korapay_status ?? 'processing') }}
                                                </span>
                                                <div class="text-gray-500 mt-1 font-mono text-xs">
                                                    Ref: {{ substr($payment->korapay_reference, 0, 15) }}...
                                                </div>
                                                @if($payment->payout_completed_at)
                                                    <div class="text-green-600 text-xs mt-1">
                                                        Completed: {{ $payment->payout_completed_at->format('M d, Y') }}
                                                    </div>
                                                @elseif($payment->payout_initiated_at)
                                                    <div class="text-blue-600 text-xs mt-1">
                                                        Initiated: {{ $payment->payout_initiated_at->format('M d, Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Not initiated</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="selectedPayment = {{ $payment->toJson() }}; showDetailsModal = true" class="text-purple-600 hover:text-purple-900">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Payment History Yet</h3>
                                            <p class="text-gray-600">Your payments will appear here once admin processes them.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $payments->links() }}
                    </div>
                </div>

                <!-- Payment Details Modal -->
                <div x-show="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
                    <div @click.away="showDetailsModal = false" class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Payment Details</h2>
                            <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <template x-if="selectedPayment">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Reference</p>
                                        <p class="font-semibold" x-text="selectedPayment.reference"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status</p>
                                        <p class="font-semibold capitalize" x-text="selectedPayment.status"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">KoraPay Status</p>
                                        <p class="font-semibold capitalize" x-text="selectedPayment.korapay_status || 'Not initiated'"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Consultations</p>
                                        <p class="font-semibold" x-text="selectedPayment.total_consultations_count"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Payment Method</p>
                                        <p class="font-semibold capitalize" x-text="selectedPayment.payment_method || 'N/A'"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Amount</p>
                                        <p class="font-semibold" x-text="'₦' + parseFloat(selectedPayment.total_consultations_amount).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Your Share (<span x-text="selectedPayment.doctor_percentage"></span>%)</p>
                                        <p class="font-bold text-lg text-green-600" x-text="'₦' + parseFloat(selectedPayment.doctor_amount).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Platform Fee</p>
                                        <p class="font-semibold" x-text="'₦' + parseFloat(selectedPayment.platform_fee).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Transaction Reference</p>
                                        <p class="font-semibold" x-text="selectedPayment.transaction_reference || 'N/A'"></p>
                                    </div>
                                    <div x-show="selectedPayment.korapay_reference">
                                        <p class="text-sm text-gray-600">KoraPay Reference</p>
                                        <p class="font-semibold font-mono text-xs" x-text="selectedPayment.korapay_reference"></p>
                                    </div>
                                    <div x-show="selectedPayment.korapay_fee">
                                        <p class="text-sm text-gray-600">KoraPay Fee</p>
                                        <p class="font-semibold text-orange-600" x-text="'₦' + parseFloat(selectedPayment.korapay_fee || 0).toLocaleString()"></p>
                                    </div>
                                </div>

                                <div x-show="selectedPayment.korapay_reference" class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <h3 class="font-semibold text-blue-900 mb-2">Payout Information</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">KoraPay Status:</span>
                                            <span class="font-semibold capitalize" x-text="selectedPayment.korapay_status || 'processing'"></span>
                                        </div>
                                        <div x-show="selectedPayment.payout_initiated_at" class="flex justify-between">
                                            <span class="text-gray-600">Initiated:</span>
                                            <span class="font-semibold" x-text="new Date(selectedPayment.payout_initiated_at).toLocaleDateString()"></span>
                                        </div>
                                        <div x-show="selectedPayment.payout_completed_at" class="flex justify-between">
                                            <span class="text-gray-600">Completed:</span>
                                            <span class="font-semibold text-green-600" x-text="new Date(selectedPayment.payout_completed_at).toLocaleDateString()"></span>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="selectedPayment.payment_notes">
                                    <p class="text-sm text-gray-600 mb-1">Payment Notes</p>
                                    <p class="text-sm bg-gray-50 p-3 rounded" x-text="selectedPayment.payment_notes"></p>
                                </div>
                            </div>
                        </template>

                        <div class="mt-6">
                            <button @click="showDetailsModal = false" class="w-full px-6 py-3 purple-gradient text-white rounded-lg hover:opacity-90 transition-all font-medium">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush

