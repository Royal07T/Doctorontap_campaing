<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Financial Hub - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>.purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }</style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false }">
<div class="flex h-screen overflow-hidden">
    @include('admin.shared.sidebar', ['active' => 'financial-hub'])
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('admin.shared.header', ['title' => 'Financial Hub'])
        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">

        {{-- Sub-navigation --}}
        <div class="flex items-center space-x-2 mb-6">
            <a href="{{ route('admin.financial-hub.index') }}" class="px-4 py-2 text-sm font-medium text-white purple-gradient rounded-lg">Overview</a>
            <a href="{{ route('admin.financial-hub.invoices') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Invoices</a>
            <a href="{{ route('admin.financial-hub.payments') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Payments</a>
            <a href="{{ route('admin.financial-hub.payouts') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Doctor Payouts</a>
        </div>

        {{-- Stats grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach([
                ['label' => 'Total Revenue',     'value' => '₦'.number_format($stats['totalRevenue'], 2),    'color' => 'green',  'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'This Month',        'value' => '₦'.number_format($stats['monthlyRevenue'], 2),  'color' => 'blue',   'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['label' => 'Pending Payments',  'value' => '₦'.number_format($stats['pendingPayments'], 2), 'color' => 'amber',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Unpaid Invoices',   'value' => '₦'.number_format($stats['unpaidInvoices'], 2),  'color' => 'red',    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ] as $stat)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 border-l-4 border-{{ $stat['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $stat['value'] }}</p>
                    </div>
                    <div class="bg-{{ $stat['color'] }}-50 p-2.5 rounded-xl">
                        <svg class="w-5 h-5 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            {{-- Revenue Trend Chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Revenue Trend (Last 6 Months)</h3>
                <canvas id="revenueTrendChart" height="120"></canvas>
            </div>

            {{-- Revenue by Payment Method --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Revenue by Method</h3>
                @forelse($revenueByMethod as $method)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800 capitalize">{{ $method->payment_method ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $method->count }} transactions</p>
                        </div>
                        <p class="text-sm font-bold text-gray-900">₦{{ number_format($method->total, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No payment data</p>
                @endforelse
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            {{-- Care Plan Revenue --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Care Plan Subscriptions</h3>
                @php
                    $planColors = [
                        'standard'  => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'border' => 'border-blue-200'],
                        'executive' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200'],
                        'sovereign' => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'border' => 'border-amber-200'],
                    ];
                @endphp
                <div class="space-y-3">
                    @forelse($carePlanRevenue as $type => $plan)
                        @php $c = $planColors[$type] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200']; @endphp
                        <div class="{{ $c['bg'] }} border {{ $c['border'] }} rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold {{ $c['text'] }} capitalize">{{ str_replace('_', ' ', $type) }}</p>
                                    <p class="text-xs {{ $c['text'] }} opacity-75 mt-0.5">{{ $plan->count }} total plans</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold {{ $c['text'] }}">{{ $plan->active_count }}</p>
                                    <p class="text-xs {{ $c['text'] }} opacity-75">Active</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No care plans</p>
                    @endforelse
                </div>
            </div>

            {{-- Doctor Payouts Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Doctor Payouts</h3>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-green-600 font-medium uppercase">Paid Out</p>
                        <p class="text-lg font-bold text-green-700 mt-1">₦{{ number_format($stats['totalPayouts'], 2) }}</p>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-amber-600 font-medium uppercase">Pending</p>
                        <p class="text-lg font-bold text-amber-700 mt-1">₦{{ number_format($stats['pendingPayouts'], 2) }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.financial-hub.payouts') }}" class="block text-center text-sm text-purple-600 hover:text-purple-800 font-medium">View All Payouts →</a>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Recent Transactions</h3>
                <a href="{{ route('admin.financial-hub.payments') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">View All →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Method</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-sm font-mono text-gray-700">{{ \Illuminate\Support\Str::limit($payment->reference, 20) }}</td>
                            <td class="px-5 py-3">
                                <p class="text-sm text-gray-800">{{ $payment->customer_name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->customer_email ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold text-gray-900">₦{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600 capitalize">{{ $payment->payment_method ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $sc = match($payment->status) {
                                        'success' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'failed'  => 'bg-red-100 text-red-700',
                                        default   => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $sc }}">{{ ucfirst($payment->status) }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $payment->created_at->format('M j, H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">No transactions yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueTrendChart');
    if (ctx) {
        const data = @json($monthlyTrend);
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    label: 'Revenue (₦)',
                    data: Object.values(data),
                    backgroundColor: 'rgba(147, 51, 234, 0.15)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => '₦' + v.toLocaleString() } }
                }
            }
        });
    }
});
</script>
</body>
</html>
