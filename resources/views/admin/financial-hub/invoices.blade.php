<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - Financial Hub - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }</style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
<div class="flex h-screen overflow-hidden">
    @include('admin.shared.sidebar', ['active' => 'financial-hub'])
    <div class="flex-1 flex flex-col overflow-hidden">
        @include('admin.shared.header', ['title' => 'Invoices'])
        <main class="flex-1 overflow-y-auto bg-gray-100 p-6">

        {{-- Sub-nav --}}
        <div class="flex items-center space-x-2 mb-6">
            <a href="{{ route('admin.financial-hub.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Overview</a>
            <a href="{{ route('admin.financial-hub.invoices') }}" class="px-4 py-2 text-sm font-medium text-white purple-gradient rounded-lg">Invoices</a>
            <a href="{{ route('admin.financial-hub.payments') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Payments</a>
            <a href="{{ route('admin.financial-hub.payouts') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Doctor Payouts</a>
        </div>

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoices..."
                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 w-48 focus:ring-2 focus:ring-purple-500">
            <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="">All Status</option>
                @foreach(['draft','pending','paid','overdue','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">Filter</button>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Paid</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Issued</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-sm font-mono text-gray-700">{{ $inv->reference }}</td>
                            <td class="px-5 py-3">
                                <p class="text-sm text-gray-800">{{ $inv->customer_name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $inv->customer_email ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold text-gray-900">₦{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-600">₦{{ number_format($inv->amount_paid, 2) }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $sc = match($inv->status) {
                                        'paid'      => 'bg-green-100 text-green-700',
                                        'pending'   => 'bg-amber-100 text-amber-700',
                                        'overdue'   => 'bg-red-100 text-red-700',
                                        'draft'     => 'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-gray-100 text-gray-500',
                                        default     => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full {{ $sc }}">{{ ucfirst($inv->status) }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $inv->issued_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $inv->due_at?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
                <div class="px-5 py-3 border-t border-gray-200">{{ $invoices->withQueryString()->links() }}</div>
            @endif
        </div>

        </main>
    </div>
</div>
</body>
</html>
