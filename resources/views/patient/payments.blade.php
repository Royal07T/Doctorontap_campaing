@extends('layouts.patient')

@section('title', 'Payment History')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600">Home</a>
                <span class="mx-2">/</span>
                <span class="text-purple-600">Payments</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Financial Overview</h1>
            <p class="text-gray-500 text-sm mt-1">Track your consultation fees and transaction history.</p>
        </div>
        <div>
            <div class="inline-flex items-center p-1 bg-gray-100 rounded-xl">
                 <span class="px-4 py-2 bg-white text-gray-900 text-xs font-bold rounded-lg shadow-sm">
                    All Transactions
                 </span>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Total Spent</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">₦{{ number_format($stats['total_paid'] ?? 0, 2) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Paid Sessions</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['paid_consultations'] ?? 0 }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Pending</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['pending_payments'] ?? 0 }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Pending Payments Section (if any) -->
    @if(isset($pendingConsultations) && $pendingConsultations->count() > 0)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl overflow-hidden">
             <div class="px-6 py-4 border-b border-amber-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h2 class="text-sm font-bold text-amber-900 uppercase tracking-wide">Pending Payments</h2>
            </div>
            <div class="divide-y divide-amber-100">
                @foreach($pendingConsultations as $consultation)
                    <div class="p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-base font-bold text-gray-900">{{ $consultation->reference }}</h3>
                                <span class="text-xs text-gray-500">• {{ $consultation->created_at->format('M d, Y') }}</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Consultation with <span class="font-medium text-purple-700">Dr. {{ $consultation->doctor->name ?? 'Assigned' }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
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
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">₦{{ number_format($fee, 2) }}</p>
                                    <p class="text-xs text-gray-500">Amount Due</p>
                                </div>
                                <form action="{{ route('patient.consultation.pay', $consultation->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-gray-200">
                                        Pay Now
                                    </button>
                                </form>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold">Free</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Transaction History -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Transaction History</h2>
        </div>
        
        @if($consultations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wide">Reference</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wide">Service</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wide">Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wide">Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wide text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($consultations as $consultation)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $consultation->reference }}</span>
                            @if($consultation->payment && $consultation->payment->transaction_reference)
                                <div class="text-[10px] text-gray-400 mt-0.5">TX: {{ substr($consultation->payment->transaction_reference, 0, 10) }}...</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                             <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Consultation</p>
                                    <p class="text-xs text-gray-500">Dr. {{ $consultation->doctor->name ?? 'Assigned' }}</p>
                                </div>
                             </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $consultation->created_at->format('M d, Y') }}<br>
                            <span class="text-xs text-gray-400">{{ $consultation->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($consultation->payment)
                                <span class="text-sm font-bold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($consultation->payment_status === 'paid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    Paid
                                </span>
                            @elseif($consultation->payment_status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                    Pending
                                </span>
                            @elseif($consultation->payment_status === 'failed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    Failed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                    {{ ucfirst($consultation->payment_status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                             @if($consultation->payment_status === 'paid' && $consultation->payment)
                                <a href="{{ route('patient.consultation.receipt', $consultation->id) }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 hover:underline">
                                    Receipt
                                </a>
                            @elseif($consultation->payment_status === 'pending')
                                <form action="{{ route('patient.consultation.pay', $consultation->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-amber-600 hover:text-amber-800 hover:underline">
                                        Pay Now
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $consultations->links() }}
        </div>
        
        @else
            <div class="text-center py-20">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">No Transaction History</h3>
                <p class="text-xs text-gray-500">You haven't made any payments yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
