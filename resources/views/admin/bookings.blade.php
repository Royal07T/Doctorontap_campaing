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
                    <a href="{{ admin_route('admin.consultations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Consultations
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Search & Filter
                        </h2>
                    </div>
                    <form method="GET" action="{{ admin_route('admin.bookings.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, payer name, email..."
                                   class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Status</label>
                            <select name="status" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Payment</label>
                            <select name="payment_status" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                                <option value="">All Payments</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ admin_route('admin.bookings.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Bookings Cards -->
                <div class="space-y-4">
                    @forelse($bookings as $booking)
                        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                            <!-- Card Header -->
                            <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex-1 flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            @if($booking->status === 'completed')
                                                <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                            @elseif($booking->status === 'pending')
                                                <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                            @elseif($booking->status === 'cancelled')
                                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-gray-900 font-mono">{{ $booking->reference }}</h3>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($booking->status === 'completed') bg-emerald-100 text-emerald-700
                                                    @elseif($booking->status === 'pending') bg-amber-100 text-amber-700
                                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-700 @endif">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($booking->payment_status === 'paid') bg-emerald-100 text-emerald-700
                                                    @elseif($booking->payment_status === 'unpaid') bg-red-100 text-red-700
                                                    @elseif($booking->payment_status === 'pending') bg-amber-100 text-amber-700 @endif">
                                                    {{ ucfirst($booking->payment_status) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ $booking->payer_name }} • ₦{{ number_format($booking->total_adjusted_amount, 2) }} • {{ $booking->bookingPatients->count() }} patient(s)</p>
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
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payer</p>
                                            <p class="text-xs text-gray-900 font-semibold">{{ $booking->payer_name }}</p>
                                            <p class="text-xs text-gray-600">{{ $booking->payer_email }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Doctor</p>
                                            <p class="text-xs text-gray-900">{{ $booking->doctor ? $booking->doctor->name : 'Any Doctor' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Patients</p>
                                            <p class="text-xs text-gray-900 font-semibold mb-1">{{ $booking->bookingPatients->count() }} patient(s)</p>
                                            <p class="text-xs text-gray-600">
                                                @foreach($booking->bookingPatients->take(3) as $bp)
                                                    {{ $bp->patient->name }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                                @if($booking->bookingPatients->count() > 3)
                                                    +{{ $booking->bookingPatients->count() - 3 }} more
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Total Amount</p>
                                            <p class="text-xs font-bold text-emerald-600">₦{{ number_format($booking->total_adjusted_amount, 2) }}</p>
                                            @if($booking->total_adjusted_amount != $booking->total_amount)
                                                <p class="text-xs text-gray-500 mt-0.5">Base: ₦{{ number_format($booking->total_amount, 2) }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                            <p class="text-xs text-gray-900">{{ $booking->created_at->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $booking->created_at->format('h:i A') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Multi-Patient Bookings Found</h3>
                        </div>
                    @endforelse

                    <!-- Pagination -->
                    @if($bookings->hasPages())
                    <div class="mt-6">
                        {{ $bookings->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
    @include('admin.shared.preloader')
</body>
</html>
