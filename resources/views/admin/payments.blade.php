<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payments - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="purple-gradient shadow-lg">
        <div class="container mx-auto px-5 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap Logo" class="h-10 w-auto">
                    </a>
                    <span class="text-white font-bold text-xl">Payments</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white text-sm">👤 {{ Auth::guard('admin')->user()->name }}</span>
                    <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-purple-200 transition-colors">Dashboard</a>
                    <a href="{{ route('admin.consultations') }}" class="text-white hover:text-purple-200 transition-colors">Consultations</a>
                    <a href="{{ route('admin.doctors') }}" class="text-white hover:text-purple-200 transition-colors">Doctors</a>
                    <a href="{{ url('/') }}" class="text-white hover:text-purple-200 transition-colors">View Website</a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white hover:text-red-300 transition-colors font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-5 py-6">
        <!-- Filter -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <form method="GET" action="{{ route('admin.payments') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Payment Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-5 py-2 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-md transition-all">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Reference</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Customer</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Doctor</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Fee</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Method</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-mono text-gray-900">
                                {{ $payment->reference }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $payment->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $payment->customer_email }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $payment->doctor ? $payment->doctor->name : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                <span class="text-emerald-700">NGN {{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                {{ $payment->fee ? 'NGN '.number_format($payment->fee, 2) : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $payment->status === 'success' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $payment->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $payment->status === 'failed' ? 'bg-rose-100 text-rose-800' : '' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                {{ $payment->payment_method ? ucwords(str_replace('_', ' ', $payment->payment_method)) : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                                <div>{{ $payment->created_at->format('M d, Y') }}</div>
                                <div class="text-gray-500">{{ $payment->created_at->format('h:i A') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <p class="text-lg font-semibold">No payments found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($payments->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>

