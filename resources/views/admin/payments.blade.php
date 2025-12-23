<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payments - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.shared.sidebar', ['active' => 'payments'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">Payments</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
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
            <form method="GET" action="{{ route('admin.payments') }}" class="space-y-3">
                <!-- Row 1 -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, name, email..."
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                            <option value="">All</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <!-- Doctor -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Doctor</label>
                        <select name="doctor_id" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Method</label>
                        <select name="payment_method" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                            <option value="">All Methods</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_money" {{ request('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        </select>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <!-- Date From -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">From Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">To Date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                    </div>

                    <!-- Min Amount -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Min Amount</label>
                        <input type="number" name="amount_min" value="{{ request('amount_min') }}" placeholder="0"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                    </div>

                    <!-- Max Amount -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Max Amount</label>
                        <input type="number" name="amount_max" value="{{ request('amount_max') }}" placeholder="100000"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                    </div>

                    <!-- Submit -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('admin.payments') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Payments Cards -->
        <div class="space-y-4">
            @forelse($payments as $payment)
                <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                    <!-- Card Header -->
                    <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <div class="p-5 flex items-center justify-between">
                            <div class="flex-1 flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    @if($payment->status === 'success')
                                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                                    @elseif($payment->status === 'pending')
                                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                                    @elseif($payment->status === 'failed')
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-sm font-semibold text-gray-900 font-mono">{{ $payment->reference }}</h3>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                            @if($payment->status === 'success') bg-emerald-100 text-emerald-700
                                            @elseif($payment->status === 'pending') bg-amber-100 text-amber-700
                                            @elseif($payment->status === 'failed') bg-red-100 text-red-700 @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $payment->customer_name }} • NGN {{ number_format($payment->amount, 2) }}</p>
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
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Customer</p>
                                    <p class="text-xs text-gray-900 font-semibold">{{ $payment->customer_name }}</p>
                                    <p class="text-xs text-gray-600">{{ $payment->customer_email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Doctor</p>
                                    <p class="text-xs text-gray-900">{{ $payment->doctor ? $payment->doctor->full_name : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Amount</p>
                                    <p class="text-xs font-bold text-emerald-600">NGN {{ number_format($payment->amount, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Fee</p>
                                    <p class="text-xs text-gray-900">{{ $payment->fee ? 'NGN '.number_format($payment->fee, 2) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Method</p>
                                    <p class="text-xs text-gray-900">{{ $payment->payment_method ? ucwords(str_replace('_', ' ', $payment->payment_method)) : 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date</p>
                                    <p class="text-xs text-gray-900">{{ $payment->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->created_at->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">No Payments Found</h3>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($payments->hasPages())
            <div class="mt-6">
                {{ $payments->links() }}
            </div>
            @endif
        </div>
            </main>
        </div>
    </div>
    @include('admin.shared.preloader')
</body>
</html>

