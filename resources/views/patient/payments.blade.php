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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Pending Payments</h2>
            <p class="text-xs text-gray-500 mt-1">Complete payment for these consultations</p>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($pendingConsultations as $consultation)
                <div class="p-5 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $consultation->reference }}</h3>
                            <p class="text-xs text-gray-600">
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
                        <div class="flex items-center space-x-3 ml-4">
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
                                    <p class="text-base font-bold text-gray-900">₦{{ number_format($fee, 2) }}</p>
                                    <p class="text-xs text-gray-500">Consultation Fee</p>
                                @else
                                    <p class="text-xs text-gray-500">No fee set</p>
                                @endif
                            </div>
                            <div>
                                @if($fee > 0)
                                    <form action="{{ route('patient.consultation.pay', $consultation->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 purple-gradient hover:opacity-90 text-white text-xs font-medium rounded-lg transition flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Pay Now</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="px-3 py-2 text-xs text-gray-500 bg-gray-100 rounded-lg">Payment Not Required</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Payment History Cards -->
<div class="space-y-4">
    @if($consultations->count() > 0)
        @foreach($consultations as $consultation)
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                <!-- Card Header -->
                <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex-1 flex items-center gap-3">
                            <div class="flex-shrink-0">
                                @if($consultation->payment_status === 'paid')
                                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                @else
                                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $consultation->reference }}</h3>
                                    @if($consultation->payment_status === 'paid')
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">✓ Paid</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">{{ ucfirst($consultation->payment_status) }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">
                                    @if($consultation->doctor)
                                        @php
                                            $doctorName = trim($consultation->doctor->name);
                                            $doctorNameLower = strtolower($doctorName);
                                            $hasDrPrefix = preg_match('/^dr\.?\s*/i', $doctorNameLower);
                                        @endphp
                                        {{ $hasDrPrefix ? $doctorName : 'Dr. ' . $doctorName }} • {{ $consultation->payment ? $consultation->payment->created_at->format('M d, Y') : $consultation->created_at->format('M d, Y') }}
                                    @else
                                        N/A • {{ $consultation->created_at->format('M d, Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex-shrink-0 text-right mr-4">
                                @if($consultation->payment)
                                    <p class="text-sm font-bold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</p>
                                @else
                                    <p class="text-xs text-gray-500">N/A</p>
                                @endif
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
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Reference</p>
                                <p class="text-xs text-gray-900 font-mono">{{ $consultation->reference }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                <p class="text-xs text-gray-900">{{ $consultation->payment ? $consultation->payment->created_at->format('M d, Y H:i A') : $consultation->created_at->format('M d, Y H:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Doctor</p>
                                <p class="text-xs text-gray-900">
                                    @if($consultation->doctor)
                                        @php
                                            $doctorName = trim($consultation->doctor->name);
                                            $doctorNameLower = strtolower($doctorName);
                                            $hasDrPrefix = preg_match('/^dr\.?\s*/i', $doctorNameLower);
                                        @endphp
                                        {{ $hasDrPrefix ? $doctorName : 'Dr. ' . $doctorName }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            @if($consultation->payment)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Amount</p>
                                <p class="text-xs text-gray-900 font-semibold">₦{{ number_format($consultation->payment->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Transaction Reference</p>
                                <p class="text-xs text-gray-900 font-mono">{{ $consultation->payment->transaction_reference ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Method</p>
                                <p class="text-xs text-gray-900">{{ ucfirst($consultation->payment->payment_method ?? 'N/A') }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-gray-200">
                            @if($consultation->payment_status === 'paid' && $consultation->payment)
                                <a href="{{ route('patient.consultation.receipt', $consultation->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
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
                                        <button type="submit" class="px-4 py-2 purple-gradient hover:opacity-90 text-white text-xs font-medium rounded-lg transition">
                                            Pay Now
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="mt-6">
            {{ $consultations->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Payments Yet</h3>
            <p class="text-xs text-gray-500 mb-4">Your payment history will appear here after completed transactions.</p>
        </div>
    @endif
</div>
@endsection

