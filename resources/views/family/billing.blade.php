@extends('layouts.family')

@section('page-title', 'Billing & Receipts')
@section('patient', $patient->name)
@section('patient-initial', strtoupper(substr($patient->first_name ?? $patient->name, 0, 1)))
@section('patient-id', 'ID: ' . str_pad($patient->id, 6, '0', STR_PAD_LEFT))
@section('support-label', 'Billing Support')
@section('support-text', 'Questions about charges?')
@section('support-cta', 'Contact Billing')
@section('header-cta', 'Pay Balance')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Billing & Receipts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage invoices and view payment history</p>
        </div>
        @if($pendingTotal > 0)
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Outstanding:</span>
            <span class="text-lg font-bold text-red-600">${{ number_format($pendingTotal, 2) }}</span>
        </div>
        @endif
    </div>

    {{-- Two-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Left: Invoice table ─── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Recent Invoices --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900">Recent Invoices</h2>
                    <button class="text-xs font-semibold text-purple-600 hover:text-purple-800">Download All</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">Invoice ID</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">Period</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wide">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($invoices as $inv)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-4">
                                    <span class="font-mono text-sm font-medium text-gray-900">{{ $inv['id'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-gray-600">{{ $inv['period'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-gray-900">${{ number_format($inv['amount'], 2) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($inv['status'] === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                        PENDING
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        PAID
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($inv['status'] === 'pending')
                                    <button class="px-4 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 transition">
                                        Pay Now
                                    </button>
                                    @else
                                    <button class="px-4 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200 transition">
                                        Receipt
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Service History Timeline --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900">Service History</h2>
                    <a href="{{ route('family.history') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800">View Full History &rarr;</a>
                </div>
                <div class="p-5">
                    @php
                        $serviceItems = [
                            ['type' => 'consultation', 'title' => 'Doctor Consultation', 'provider' => 'Dr. A. Okonkwo', 'date' => now()->subDays(2)->format('M d, Y'), 'cost' => 150.00, 'icon' => '🩺', 'bg' => 'bg-blue-50'],
                            ['type' => 'physio', 'title' => 'Physiotherapy Session', 'provider' => 'PhysioPlus Center', 'date' => now()->subDays(5)->format('M d, Y'), 'cost' => 120.00, 'icon' => '💪', 'bg' => 'bg-emerald-50'],
                            ['type' => 'caregiver', 'title' => 'Caregiver Daily Shift', 'provider' => '8hrs @ ₦5,000/hr', 'date' => now()->subDay()->format('M d, Y'), 'cost' => 40000, 'icon' => '👩‍⚕️', 'bg' => 'bg-purple-50'],
                        ];
                    @endphp

                    <div class="relative">
                        {{-- Timeline line --}}
                        <div class="absolute left-5 top-0 bottom-0 w-px bg-gray-200"></div>

                        <div class="space-y-6">
                            @foreach($serviceItems as $svc)
                            <div class="relative flex gap-4 pl-2">
                                {{-- Dot --}}
                                <div class="w-10 h-10 rounded-full {{ $svc['bg'] }} flex items-center justify-center z-10 flex-shrink-0">
                                    <span class="text-lg">{{ $svc['icon'] }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900">{{ $svc['title'] }}</p>
                                        <span class="text-sm font-bold text-gray-900">
                                            @if($svc['cost'] >= 1000) ₦{{ number_format($svc['cost']) }} @else ${{ number_format($svc['cost'], 2) }} @endif
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $svc['provider'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $svc['date'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Right sidebar ─── --}}
        <div class="space-y-4">

            {{-- Account Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Account Summary</h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs text-gray-500">Monthly Spend</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">$2,840.00</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Next Auto-Pay</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ now()->endOfMonth()->format('M d') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Sessions Logged</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">24</p>
                        </div>
                    </div>
                    <button class="w-full py-2.5 rounded-lg border border-purple-200 text-sm font-semibold text-purple-700 hover:bg-purple-50 transition">
                        Request Statement
                    </button>
                </div>
            </div>

            {{-- Payment Method --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Payment Method</h2>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-4 text-white" viewBox="0 0 24 16" fill="currentColor"><rect width="24" height="16" rx="2"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">•••• •••• •••• 4291</p>
                            <p class="text-xs text-gray-400">Expires 09/26</p>
                        </div>
                        <button class="text-xs text-purple-600 font-semibold hover:text-purple-800">Edit</button>
                    </div>
                </div>
            </div>

            {{-- Add Services --}}
            <div class="rounded-xl bg-gradient-to-br from-purple-600 to-indigo-700 p-5 text-white">
                <h3 class="text-sm font-bold">Add Services</h3>
                <p class="text-xs text-white/70 mt-1">Book additional care sessions</p>
                <div class="mt-4 space-y-2">
                    <button class="w-full py-2 rounded-lg bg-white/20 text-sm font-semibold hover:bg-white/30 transition">
                        💪 Book Physiotherapy
                    </button>
                    <button class="w-full py-2 rounded-lg bg-white/20 text-sm font-semibold hover:bg-white/30 transition">
                        🏥 Nursing Care
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
