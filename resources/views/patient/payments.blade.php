@extends('layouts.patient')

@section('title', 'Payment History')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Payment History</h1>
        <p class="text-gray-600 mt-2">View all your payments and transactions</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-500 text-sm font-medium">Total Paid</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">₦{{ number_format($stats['total_paid'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm font-medium">Paid Consultations</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['paid_consultations'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-sm font-medium">Pending Payments</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['pending_payments'] }}</p>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
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
                                    <span class="text-gray-700">Dr. {{ $consultation->doctor->name ?? 'N/A' }}</span>
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
                                    @if($consultation->payment)
                                        <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">
                                            View Receipt
                                        </a>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                {{ $consultations->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Payments Yet</h3>
                <p class="text-gray-500 mb-4">Your payment history will appear here after completed transactions.</p>
            </div>
        @endif
    </div>
</div>
@endsection

