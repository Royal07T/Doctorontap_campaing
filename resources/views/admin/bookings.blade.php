<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Multi-Patient Bookings - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'consultations'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-xl font-bold text-white">Multi-Patient Bookings</h1>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Back Button -->
                <div class="mb-6">
                    <a href="{{ route('admin.consultations') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Consultations
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                    <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, payer name, email..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 bg-white">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Payment</label>
                            <select name="payment_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 bg-white">
                                <option value="">All Payments</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Payer</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Patients</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Doctor</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Total Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($bookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $booking->reference }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $booking->payer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $booking->payer_email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $booking->bookingPatients->count() }} patient(s)</div>
                                        <div class="text-xs text-gray-500">
                                            @foreach($booking->bookingPatients->take(2) as $bp)
                                                {{ $bp->patient->name }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                            @if($booking->bookingPatients->count() > 2)
                                                +{{ $booking->bookingPatients->count() - 2 }} more
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $booking->doctor ? $booking->doctor->name : 'Any Doctor' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-semibold text-gray-900">₦{{ number_format($booking->total_adjusted_amount, 2) }}</div>
                                        @if($booking->total_adjusted_amount != $booking->total_amount)
                                            <div class="text-xs text-gray-500">Base: ₦{{ number_format($booking->total_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                        <div class="mt-1">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $booking->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $booking->payment_status === 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $booking->payment_status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}">
                                                {{ ucfirst($booking->payment_status) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $booking->created_at->format('M d, Y') }}
                                        <div class="text-xs text-gray-500">{{ $booking->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-400">
                                        —
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        No multi-patient bookings found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </main>
        </div>
    </div>
    @include('admin.shared.preloader')
</body>
</html>
