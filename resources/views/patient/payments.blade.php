@extends('layouts.patient')

@section('title', 'Payment History')

@section('content')
<!-- Statistics -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-emerald-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total Paid</p>
                <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['total_paid'], 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">All Time</p>
            </div>
            <div class="bg-emerald-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Paid</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['paid_consultations'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Consultations</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-amber-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Pending</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_payments'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Payments</p>
            </div>
            <div class="bg-amber-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Pending Payments Section -->
@if(isset($pendingConsultations) && $pendingConsultations->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Pending Payments</h2>
            <p class="text-sm text-gray-600 mt-1">Complete payment for these consultations</p>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($pendingConsultations as $consultation)
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $consultation->reference }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($consultation->doctor)
                                            @php
                                                $doctorName = trim($consultation->doctor->name);
                                                $doctorNameLower = strtolower($doctorName);
                                                $hasDrPrefix = preg_match('/^dr\.?\s*/i', $doctorNameLower);
                                            @endphp
                                            {{ $hasDrPrefix ? $doctorName : 'Dr. ' . $doctorName }} - {{ $consultation->doctor->specialization ?? 'General Practitioner' }}
                                        @else
                                            No doctor assigned yet
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $consultation->created_at->format('M d, Y H:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                @php
                                    $fee = 0;
                                    if ($consultation->consultation_type === 'pay_now') {
                                        $fee = \App\Models\Setting::get('consultation_fee_pay_now', \App\Models\Setting::get('pay_now_consultation_fee', 4500));
                                    } elseif ($consultation->consultation_type === 'pay_later') {
                                        $fee = \App\Models\Setting::get('consultation_fee_pay_later', \App\Models\Setting::get('pay_later_consultation_fee', 5000));
                                    } else {
                                        $fee = $consultation->doctor->effective_consultation_fee ?? 0;
                                    }
                                @endphp
                                @if($fee > 0)
                                    <p class="text-2xl font-bold text-gray-900">₦{{ number_format($fee, 2) }}</p>
                                    <p class="text-xs text-gray-500">Consultation Fee</p>
                                @else
                                    <p class="text-sm text-gray-500">No fee set</p>
                                @endif
                            </div>
                            <div>
                                @if($fee > 0)
                                    <form action="{{ route('patient.consultation.pay', $consultation->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors flex items-center space-x-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Pay Now</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="px-4 py-2 text-sm text-gray-500 bg-gray-100 rounded-lg">Payment Not Required</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Payment History Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($consultations->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($consultations as $consultation)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-900">{{ $consultation->reference }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consultation->doctor)
                                        @php
                                            $doctorName = trim($consultation->doctor->name);
                                            $doctorNameLower = strtolower($doctorName);
                                            $hasDrPrefix = preg_match('/^dr\.?\s*/i', $doctorNameLower);
                                        @endphp
                                        <span class="text-gray-700">{{ $hasDrPrefix ? $doctorName : 'Dr. ' . $doctorName }}</span>
                                    @else
                                        <span class="text-gray-700">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consultation->payment)
                                        <span class="font-semibold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consultation->payment_status === 'paid')
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            ✓ Paid
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($consultation->payment_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $consultation->payment ? $consultation->payment->created_at->format('M d, Y') : $consultation->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($consultation->payment_status === 'paid' && $consultation->payment)
                                        <a href="#" class="text-purple-600 hover:text-purple-800 font-medium">
                                            View Receipt
                                        </a>
                                    @else
                                        @php
                                            $fee = 0;
                                            if ($consultation->consultation_type === 'pay_now') {
                                                $fee = \App\Models\Setting::get('consultation_fee_pay_now', \App\Models\Setting::get('pay_now_consultation_fee', 4500));
                                            } elseif ($consultation->consultation_type === 'pay_later') {
                                                $fee = \App\Models\Setting::get('consultation_fee_pay_later', \App\Models\Setting::get('pay_later_consultation_fee', 5000));
                                            } else {
                                                $fee = $consultation->doctor->effective_consultation_fee ?? 0;
                                            }
                                        @endphp
                                        @if($fee > 0)
                                            <form action="{{ route('patient.consultation.pay', $consultation->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-4 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                    Pay Now
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $consultations->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Payments Yet</h3>
            <p class="text-sm text-gray-500 mb-4">Your payment history will appear here after completed transactions.</p>
        </div>
    @endif
</div>
@endsection

