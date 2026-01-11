<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Services Monitoring - Super Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false, activeTab: '{{ in_array($serviceType, ['sms', 'email', 'payment']) ? $serviceType : 'sms' }}' }">
    <div class="flex h-screen overflow-hidden">
        @include('super-admin.shared.sidebar', ['active' => 'services'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'Services Monitoring'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Real-time Data Indicator -->
                <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-800">Real-time data from database</span>
                    </div>
                    <span class="text-xs text-blue-600">Last updated: {{ now()->format('H:i:s') }}</span>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- SMS Statistics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">SMS</h3>
                            <span class="text-2xl">💬</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total</span>
                                <span class="font-semibold text-gray-900">{{ number_format($stats['sms']['total']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600">Successful</span>
                                <span class="font-semibold text-green-700">{{ number_format($stats['sms']['success']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600">Failed</span>
                                <span class="font-semibold text-red-700">{{ number_format($stats['sms']['failed']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600">Pending</span>
                                <span class="font-semibold text-yellow-700">{{ number_format($stats['sms']['pending']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Email Statistics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Email</h3>
                            <span class="text-2xl">✉️</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total</span>
                                <span class="font-semibold text-gray-900">{{ number_format($stats['email']['total']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600">Successful</span>
                                <span class="font-semibold text-green-700">{{ number_format($stats['email']['success']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600">Failed</span>
                                <span class="font-semibold text-red-700">{{ number_format($stats['email']['failed']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600">Pending</span>
                                <span class="font-semibold text-yellow-700">{{ number_format($stats['email']['pending']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Statistics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Payments</h3>
                            <span class="text-2xl">💳</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total</span>
                                <span class="font-semibold text-gray-900">{{ number_format($stats['payment']['total']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600">Successful</span>
                                <span class="font-semibold text-green-700">{{ number_format($stats['payment']['success']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-red-600">Failed</span>
                                <span class="font-semibold text-red-700">{{ number_format($stats['payment']['failed']) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-yellow-600">Pending</span>
                                <span class="font-semibold text-yellow-700">{{ number_format($stats['payment']['pending']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <form method="GET" action="{{ route('super-admin.services.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="all" {{ $serviceType === 'all' ? 'selected' : '' }}>All Services</option>
                                <option value="sms" {{ $serviceType === 'sms' ? 'selected' : '' }}>SMS</option>
                                <option value="email" {{ $serviceType === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="payment" {{ $serviceType === 'payment' ? 'selected' : '' }}>Payments</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="success" {{ $status === 'success' ? 'selected' : '' }}>Successful</option>
                                <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                            <select name="date_range" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $dateRange === 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ $dateRange === 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="all" {{ $dateRange === 'all' ? 'selected' : '' }}>All Time</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button type="button" @click="activeTab = 'sms'" 
                                    :class="activeTab === 'sms' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-4 text-sm font-medium border-b-2 transition">
                                SMS Notifications
                            </button>
                            <button type="button" @click="activeTab = 'email'" 
                                    :class="activeTab === 'email' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-4 text-sm font-medium border-b-2 transition">
                                Email Notifications
                            </button>
                            <button type="button" @click="activeTab = 'payment'" 
                                    :class="activeTab === 'payment' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-6 py-4 text-sm font-medium border-b-2 transition">
                                Payments
                            </button>
                        </nav>
                    </div>

                    <!-- SMS Tab Content -->
                    <div x-show="activeTab === 'sms'" class="p-6">
                        <form method="GET" action="{{ route('super-admin.services.index') }}" class="mb-4">
                            <input type="hidden" name="type" value="{{ $serviceType }}">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="hidden" name="date_range" value="{{ $dateRange }}">
                            <input type="text" name="search" placeholder="Search SMS..." 
                                   value="{{ request('search') }}"
                                   class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                   onchange="this.form.submit()">
                        </form>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($smsData['logs'] as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $log->created_at->format('M d, Y H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $log->recipient }}</div>
                                                <div class="text-xs text-gray-500">{{ $log->recipient_name }}</div>
                                                @if($log->consultation_reference)
                                                    <div class="text-xs text-purple-600 mt-1">
                                                        <a href="{{ route('admin.consultation.show', $log->consultation_id) }}" class="hover:underline">
                                                            Ref: {{ $log->consultation_reference }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst(str_replace('_', ' ', $log->category)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $log->status === 'sent' || $log->status === 'delivered' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($log->provider ?? 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-red-600">
                                                {{ $log->error_message ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                No SMS notifications found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($smsData['logs'], 'links'))
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $smsData['logs']->appends(request()->except('sms_page'))->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Email Tab Content -->
                    <div x-show="activeTab === 'email'" class="p-6" style="display: none;">
                        <form method="GET" action="{{ route('super-admin.services.index') }}" class="mb-4">
                            <input type="hidden" name="type" value="{{ $serviceType }}">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="hidden" name="date_range" value="{{ $dateRange }}">
                            <input type="text" name="search" placeholder="Search emails..." 
                                   value="{{ request('search') }}"
                                   class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                   onchange="this.form.submit()">
                        </form>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($emailData['logs'] as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $log->created_at->format('M d, Y H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $log->recipient }}</div>
                                                <div class="text-xs text-gray-500">{{ $log->recipient_name }}</div>
                                                @if($log->consultation_reference)
                                                    <div class="text-xs text-purple-600 mt-1">
                                                        <a href="{{ route('admin.consultation.show', $log->consultation_id) }}" class="hover:underline">
                                                            Ref: {{ $log->consultation_reference }}
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                {{ $log->subject ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $log->status === 'sent' || $log->status === 'delivered' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($log->provider ?? 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-red-600">
                                                {{ $log->error_message ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                No email notifications found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($emailData['logs'], 'links'))
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $emailData['logs']->appends(request()->except('email_page'))->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Payment Tab Content -->
                    <div x-show="activeTab === 'payment'" class="p-6" style="display: none;">
                        <form method="GET" action="{{ route('super-admin.services.index') }}" class="mb-4">
                            <input type="hidden" name="type" value="{{ $serviceType }}">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="hidden" name="date_range" value="{{ $dateRange }}">
                            <input type="text" name="search" placeholder="Search payments..." 
                                   value="{{ request('search') }}"
                                   class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                   onchange="this.form.submit()">
                        </form>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($paymentData['payments'] as $payment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $payment->created_at->format('M d, Y H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment->reference }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $payment->customer_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $payment->customer_email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                ₦{{ number_format($payment->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $payment->status === 'success' ? 'bg-green-100 text-green-800' : ($payment->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($payment->payment_method ?? 'N/A') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                No payments found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($paymentData['payments'], 'links'))
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $paymentData['payments']->appends(request()->except('payment_page'))->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

