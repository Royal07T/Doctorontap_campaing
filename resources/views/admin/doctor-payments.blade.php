<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctor Payments - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="paymentManager()">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.shared.sidebar', ['active' => 'doctor-payments'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-white">Doctor Payments</h1>
                    </div>
                    <button @click="showCreateModal = true" class="bg-white text-purple-600 px-6 py-2 rounded-lg hover:bg-purple-50 transition-all font-medium flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Create Payment</span>
                    </button>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Total Payments</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_payments'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Pending</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['pending_payments'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Completed</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['completed_payments'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Total Paid</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ number_format($stats['total_paid_amount'], 2) }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Doctor</label>
                            <select name="doctor_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Doctors</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="purple-gradient text-white px-6 py-2 rounded-lg hover:opacity-90 transition-all">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Payments Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" @change="toggleSelectAll" class="rounded text-purple-600 focus:ring-purple-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KoraPay</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payments as $payment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" value="{{ $payment->id }}" x-model="selectedPayments" class="rounded text-purple-600 focus:ring-purple-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $payment->reference }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a href="{{ route('admin.doctors.profile', $payment->doctor_id) }}" class="text-purple-600 hover:text-purple-800">
                                            {{ $payment->doctor->full_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payment->total_consultations_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ₦{{ number_format($payment->doctor_amount, 2) }}
                                        @if($payment->korapay_fee)
                                            <span class="block text-xs text-gray-500 font-normal">
                                                Fee: ₦{{ number_format($payment->korapay_fee, 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($payment->status === 'completed') bg-green-100 text-green-800
                                            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($payment->korapay_reference)
                                            <div class="text-xs">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($payment->korapay_status === 'success') bg-green-100 text-green-800
                                                    @elseif($payment->korapay_status === 'failed') bg-red-100 text-red-800
                                                    @else bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ ucfirst($payment->korapay_status ?? 'processing') }}
                                                </span>
                                                <div class="text-gray-500 mt-1 font-mono text-xs">
                                                    {{ substr($payment->korapay_reference, 0, 12) }}...
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Not initiated</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payment->created_at->format('M d, Y') }}
                                        @if($payment->payout_completed_at)
                                            <div class="text-xs text-green-600">
                                                Paid: {{ $payment->payout_completed_at->format('M d') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            @if($payment->status === 'pending' && !$payment->korapay_reference)
                                                <button @click="initiatePayout({{ $payment->id }})" class="text-blue-600 hover:text-blue-900 text-xs" title="Initiate KoraPay Payout">
                                                    💳 Payout
                                                </button>
                                            @elseif($payment->status === 'failed' || $payment->korapay_status === 'failed')
                                                <button @click="initiatePayout({{ $payment->id }})" class="text-green-600 hover:text-green-900 text-xs" title="Retry Failed Payout">
                                                    🔄 Retry
                                                </button>
                                            @elseif($payment->status === 'processing' || ($payment->korapay_status && $payment->korapay_status !== 'success' && $payment->korapay_status !== 'failed'))
                                                <button @click="verifyPayout({{ $payment->id }})" class="text-indigo-600 hover:text-indigo-900 text-xs" title="Verify Status">
                                                    🔍 Verify
                                                </button>
                                            @endif
                                            @if($payment->status === 'pending' && !$payment->korapay_reference)
                                                <button @click="completePayment({{ $payment->id }})" class="text-green-600 hover:text-green-900 text-xs" title="Manual Completion">
                                                    ✓ Complete
                                                </button>
                                            @endif
                                            <button @click="viewPayment({{ $payment->id }})" class="text-purple-600 hover:text-purple-900 text-xs">
                                                👁️ View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No payments found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Bulk Actions & Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                        <div x-show="selectedPayments.length > 0" class="flex items-center space-x-3">
                            <span class="text-sm text-gray-600" x-text="`${selectedPayments.length} selected`"></span>
                            <button @click="processBulkPayout" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all text-sm font-medium">
                                💳 Initiate Bulk Payout
                            </button>
                        </div>
                        <div>
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>

                <!-- Create Payment Modal -->
                <div x-show="showCreateModal" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div @click.away="showCreateModal = false" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="bg-white rounded-lg p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Create Doctor Payment</h2>
                            <button @click="showCreateModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitPayment">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Doctor *</label>
                                    <select x-model="selectedDoctor" @change="loadConsultations" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                        <option value="">Choose a doctor</option>
                                        @foreach($doctors as $doc)
                                            <option value="{{ $doc->id }}">{{ $doc->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div x-show="selectedDoctor && consultations.length === 0 && !loadingConsultations" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800">No unpaid consultations found for this doctor.</p>
                                </div>
                                
                                <div x-show="selectedDoctor && loadingConsultations" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <p class="text-sm text-blue-800">Loading consultations...</p>
                                </div>

                                <div x-show="consultations.length > 0">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-medium text-gray-700">Select Consultations *</label>
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" 
                                                   @change="toggleSelectAllConsultations" 
                                                   :checked="selectedConsultations.length === consultations.length && consultations.length > 0"
                                                   class="rounded text-purple-600 focus:ring-purple-500">
                                            <span class="text-sm text-purple-600 font-medium">Select All</span>
                                        </label>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-lg p-4">
                                        <template x-for="consultation in consultations" :key="consultation.id">
                                            <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                <input type="checkbox" :value="consultation.id" x-model="selectedConsultations" class="rounded text-purple-600 focus:ring-purple-500">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium" x-text="consultation.reference"></p>
                                                    <p class="text-xs text-gray-500" x-text="`${consultation.patient_name} - ${consultation.date}`"></p>
                                                </div>
                                                <p class="text-sm font-semibold" x-text="`₦${consultation.amount.toLocaleString()}`"></p>
                                            </label>
                                        </template>
                                    </div>
                                    <p x-show="selectedConsultations.length === 0" class="text-xs text-red-600 mt-2">Please select at least one consultation</p>
                                    <p x-show="selectedConsultations.length > 0" class="text-xs text-green-600 mt-2" x-text="`${selectedConsultations.length} consultation(s) selected`"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Doctor Percentage *</label>
                                    <input type="number" x-model="doctorPercentage" min="0" max="100" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <p class="text-xs text-gray-500 mt-1">Platform will receive the remaining percentage. Default: {{ \App\Models\Setting::get('doctor_payment_percentage', 70) }}%</p>
                                </div>

                                <div x-show="selectedConsultations.length > 0" class="bg-purple-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-gray-800 mb-2">Payment Summary</h3>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between">
                                            <span>Selected Consultations:</span>
                                            <span class="font-semibold" x-text="selectedConsultations.length"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Total Amount:</span>
                                            <span class="font-semibold" x-text="`₦${totalAmount.toLocaleString()}`"></span>
                                        </div>
                                        <div class="flex justify-between text-purple-600">
                                            <span>Doctor Share (<span x-text="doctorPercentage"></span>%):</span>
                                            <span class="font-bold" x-text="`₦${doctorShare.toLocaleString()}`"></span>
                                        </div>
                                        <div class="flex justify-between text-gray-600">
                                            <span>Platform Fee (<span x-text="100 - doctorPercentage"></span>%):</span>
                                            <span class="font-semibold" x-text="`₦${platformFee.toLocaleString()}`"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex space-x-4">
                                <button type="button" @click="showCreateModal = false" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-medium">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        @click.prevent="submitPayment()"
                                        class="flex-1 purple-gradient text-white px-6 py-3 rounded-lg hover:opacity-90 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed" 
                                        :disabled="selectedConsultations.length === 0 || !selectedDoctor || consultations.length === 0">
                                    Create Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Complete Payment Modal -->
                <div x-show="showCompleteModal" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div @click.away="showCompleteModal = false" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="bg-white rounded-lg p-8 max-w-lg w-full">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Complete Payment</h2>
                            <button @click="showCompleteModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitComplete">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                                    <select x-model="completeForm.payment_method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                        <option value="">Select method</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="cash">Cash</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference</label>
                                    <input type="text" x-model="completeForm.transaction_reference" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Notes</label>
                                    <textarea x-model="completeForm.payment_notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex space-x-4">
                                <button type="button" @click="showCompleteModal = false" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-medium">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-all font-medium">
                                    Mark as Completed
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payment Details Modal -->
                <div x-show="showDetailsModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="showDetailsModal = false">
                    <div @click.away="showDetailsModal = false" class="bg-white rounded-lg p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Payment Details</h2>
                            <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div x-show="loadingPaymentDetails" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                            <p class="mt-2 text-gray-600">Loading payment details...</p>
                        </div>

                        <!-- Payment Details -->
                        <template x-if="selectedPayment && !loadingPaymentDetails">
                            <div class="space-y-6">
                                <!-- Payment Overview -->
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Reference</p>
                                        <p class="font-semibold text-lg" x-text="selectedPayment.reference"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status</p>
                                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold"
                                              :class="{
                                                  'bg-green-100 text-green-800': selectedPayment.status === 'completed',
                                                  'bg-yellow-100 text-yellow-800': selectedPayment.status === 'pending',
                                                  'bg-blue-100 text-blue-800': selectedPayment.status === 'processing',
                                                  'bg-red-100 text-red-800': selectedPayment.status === 'failed'
                                              }"
                                              x-text="selectedPayment.status.charAt(0).toUpperCase() + selectedPayment.status.slice(1)"></span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">KoraPay Status</p>
                                        <p class="font-semibold capitalize" x-text="selectedPayment.korapay_status || 'Not initiated'"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Doctor</p>
                                        <p class="font-semibold" x-text="selectedPayment.doctor?.full_name || 'N/A'"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Total Consultations</p>
                                        <p class="font-semibold" x-text="selectedPayment.total_consultations_count"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Created Date</p>
                                        <p class="font-semibold" x-text="new Date(selectedPayment.created_at).toLocaleDateString()"></p>
                                    </div>
                                </div>

                                <!-- Financial Summary -->
                                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-6 border border-purple-200">
                                    <h3 class="text-lg font-bold text-gray-800 mb-4">Financial Summary</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Total Amount</p>
                                            <p class="font-bold text-lg text-gray-900" x-text="'₦' + parseFloat(selectedPayment.total_consultations_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Doctor Share (<span x-text="selectedPayment.doctor_percentage"></span>%)</p>
                                            <p class="font-bold text-lg text-green-600" x-text="'₦' + parseFloat(selectedPayment.doctor_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Platform Fee</p>
                                            <p class="font-bold text-lg text-gray-700" x-text="'₦' + parseFloat(selectedPayment.platform_fee || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                                        </div>
                                        <div x-show="selectedPayment.korapay_fee">
                                            <p class="text-sm text-gray-600">KoraPay Fee</p>
                                            <p class="font-bold text-lg text-orange-600" x-text="'₦' + parseFloat(selectedPayment.korapay_fee || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Account Information -->
                                <div x-show="selectedPayment.bank_account" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <h3 class="font-semibold text-blue-900 mb-2">Bank Account Details</h3>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-gray-600">Bank:</span>
                                            <span class="font-semibold ml-2" x-text="selectedPayment.bank_account?.bank_name || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Account Name:</span>
                                            <span class="font-semibold ml-2" x-text="selectedPayment.bank_account?.account_name || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Account Number:</span>
                                            <span class="font-semibold ml-2 font-mono" x-text="selectedPayment.bank_account?.account_number || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Account Type:</span>
                                            <span class="font-semibold ml-2 capitalize" x-text="selectedPayment.bank_account?.account_type || 'N/A'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- KoraPay Payout Information -->
                                <div x-show="selectedPayment.korapay_reference" class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                                    <h3 class="font-semibold text-indigo-900 mb-2">KoraPay Payout Information</h3>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-gray-600">KoraPay Reference:</span>
                                            <span class="font-semibold ml-2 font-mono text-xs" x-text="selectedPayment.korapay_reference"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Status:</span>
                                            <span class="font-semibold ml-2 capitalize" x-text="selectedPayment.korapay_status || 'processing'"></span>
                                        </div>
                                        <div x-show="selectedPayment.payout_initiated_at">
                                            <span class="text-gray-600">Initiated:</span>
                                            <span class="font-semibold ml-2" x-text="new Date(selectedPayment.payout_initiated_at).toLocaleString()"></span>
                                        </div>
                                        <div x-show="selectedPayment.payout_completed_at">
                                            <span class="text-gray-600">Completed:</span>
                                            <span class="font-semibold ml-2 text-green-600" x-text="new Date(selectedPayment.payout_completed_at).toLocaleString()"></span>
                                        </div>
                                    </div>
                                    <!-- Error Details (shown when failed) -->
                                    <div x-show="selectedPayment.korapay_status === 'failed' && selectedPayment.korapay_response" class="mt-3 pt-3 border-t border-red-200">
                                        <h4 class="font-semibold text-red-800 mb-2">Error Information:</h4>
                                        <div class="bg-red-50 rounded-lg p-3 space-y-2">
                                            <template x-if="getErrorMessage(selectedPayment.korapay_response)">
                                                <div>
                                                    <p class="text-sm font-semibold text-red-900 mb-1">Error Message:</p>
                                                    <p class="text-red-700" x-text="getErrorMessage(selectedPayment.korapay_response)"></p>
                                                </div>
                                            </template>
                                            <template x-if="!getErrorMessage(selectedPayment.korapay_response)">
                                                <div>
                                                    <p class="text-sm font-semibold text-red-900 mb-1">Error:</p>
                                                    <p class="text-red-700">Payout failed. Please check the payment details and try again.</p>
                                                </div>
                                            </template>
                                            <template x-if="getErrorAmount(selectedPayment.korapay_response)">
                                                <div class="text-sm">
                                                    <span class="text-red-800 font-semibold">Amount Attempted:</span>
                                                    <span class="text-red-700 ml-2" x-text="'₦' + parseFloat(getErrorAmount(selectedPayment.korapay_response) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                                </div>
                                            </template>
                                            <template x-if="getErrorTraceId(selectedPayment.korapay_response)">
                                                <div class="text-sm">
                                                    <span class="text-red-800 font-semibold">Trace ID:</span>
                                                    <span class="text-red-700 ml-2 font-mono text-xs" x-text="getErrorTraceId(selectedPayment.korapay_response)"></span>
                                                </div>
                                            </template>
                                            <div class="mt-3 pt-2 border-t border-red-200">
                                                <p class="text-xs text-red-600 italic">
                                                    💡 Tip: Common reasons for failure include insufficient funds in KoraPay wallet, invalid bank account details, or network issues. Please verify the bank account and try again.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Method & Transaction -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Payment Method</p>
                                        <p class="font-semibold capitalize" x-text="selectedPayment.payment_method || 'N/A'"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Transaction Reference</p>
                                        <p class="font-semibold font-mono text-xs" x-text="selectedPayment.transaction_reference || 'N/A'"></p>
                                    </div>
                                    <div x-show="selectedPayment.paid_at">
                                        <p class="text-sm text-gray-600">Paid At</p>
                                        <p class="font-semibold" x-text="new Date(selectedPayment.paid_at).toLocaleString()"></p>
                                    </div>
                                    <div x-show="selectedPayment.paid_by_user">
                                        <p class="text-sm text-gray-600">Processed By</p>
                                        <p class="font-semibold" x-text="selectedPayment.paid_by_user?.name || 'N/A'"></p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div x-show="selectedPayment.payment_notes || selectedPayment.admin_notes">
                                    <h3 class="font-semibold text-gray-800 mb-2">Notes</h3>
                                    <div class="space-y-2">
                                        <div x-show="selectedPayment.payment_notes" class="bg-gray-50 p-3 rounded border border-gray-200">
                                            <p class="text-xs text-gray-600 mb-1">Payment Notes:</p>
                                            <p class="text-sm" x-text="selectedPayment.payment_notes"></p>
                                        </div>
                                        <div x-show="selectedPayment.admin_notes" class="bg-blue-50 p-3 rounded border border-blue-200">
                                            <p class="text-xs text-gray-600 mb-1">Admin Notes:</p>
                                            <p class="text-sm" x-text="selectedPayment.admin_notes"></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Consultations List -->
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-3">Consultations Included (<span x-text="paymentConsultations.length"></span>)</h3>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="max-h-60 overflow-y-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <template x-for="consultation in paymentConsultations" :key="consultation.id">
                                                        <tr>
                                                            <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="consultation.reference"></td>
                                                            <td class="px-4 py-3 text-sm text-gray-600" x-text="consultation.full_name"></td>
                                                            <td class="px-4 py-3 text-sm text-gray-600" x-text="new Date(consultation.created_at).toLocaleDateString()"></td>
                                                            <td class="px-4 py-3 text-sm font-semibold" x-text="'₦' + parseFloat(consultation.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                                                            <td class="px-4 py-3 text-sm">
                                                                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                                                      :class="{
                                                                          'bg-green-100 text-green-800': consultation.payment_status === 'paid',
                                                                          'bg-yellow-100 text-yellow-800': consultation.payment_status === 'pending',
                                                                          'bg-red-100 text-red-800': consultation.payment_status === 'unpaid'
                                                                      }"
                                                                      x-text="consultation.payment_status ? consultation.payment_status.charAt(0).toUpperCase() + consultation.payment_status.slice(1) : 'N/A'"></span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr x-show="paymentConsultations.length === 0">
                                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No consultations found</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="mt-6">
                            <button @click="showDetailsModal = false" class="w-full px-6 py-3 purple-gradient text-white rounded-lg hover:opacity-90 transition-all font-medium">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function paymentManager() {
            return {
                pageLoading: false,
                sidebarOpen: false,
                showCreateModal: false,
                showCompleteModal: false,
                showDetailsModal: false,
                selectedDoctor: '',
                consultations: [],
                selectedConsultations: [],
                selectedPayments: [],
                loadingConsultations: false,
                doctorPercentage: {{ \App\Models\Setting::get('doctor_payment_percentage', 70) }},
                currentPaymentId: null,
                selectedPayment: null,
                loadingPaymentDetails: false,
                paymentConsultations: [],
                completeForm: {
                    payment_method: '',
                    transaction_reference: '',
                    payment_notes: ''
                },
                
                // Helper functions to parse error response
                getErrorData(response) {
                    if (!response) return null;
                    if (typeof response === 'string') {
                        try {
                            return JSON.parse(response);
                        } catch (e) {
                            return null;
                        }
                    }
                    return response;
                },
                
                getErrorMessage(response) {
                    const data = this.getErrorData(response);
                    return data?.message || null;
                },
                
                getErrorAmount(response) {
                    const data = this.getErrorData(response);
                    return data?.amount || null;
                },
                
                getErrorTraceId(response) {
                    const data = this.getErrorData(response);
                    return data?.trace_id || null;
                },

                get totalAmount() {
                    return this.consultations
                        .filter(c => this.selectedConsultations.includes(c.id))
                        .reduce((sum, c) => sum + c.amount, 0);
                },

                get doctorShare() {
                    return (this.totalAmount * this.doctorPercentage) / 100;
                },

                get platformFee() {
                    return this.totalAmount - this.doctorShare;
                },

                async loadConsultations() {
                    if (!this.selectedDoctor) {
                        this.consultations = [];
                        this.selectedConsultations = [];
                        return;
                    }

                    this.loadingConsultations = true;
                    this.consultations = [];
                    this.selectedConsultations = [];

                    try {
                        const response = await fetch(`/admin/doctors/${this.selectedDoctor}/unpaid-consultations`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.consultations = data.consultations || [];
                            this.selectedConsultations = [];
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal('Failed to load consultations: ' + (data.message || 'Unknown error'), 'error');
                            }
                            this.consultations = [];
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Failed to load consultations. Please try again.', 'error');
                        }
                        console.error(error);
                        this.consultations = [];
                    } finally {
                        this.loadingConsultations = false;
                    }
                },

                async submitPayment() {
                    // Validation
                    if (!this.selectedDoctor) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please select a doctor', 'error');
                        }
                        return;
                    }

                    if (this.selectedConsultations.length === 0) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please select at least one consultation', 'error');
                        }
                        return;
                    }

                    if (!this.doctorPercentage || this.doctorPercentage <= 0 || this.doctorPercentage > 100) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please enter a valid doctor percentage (0-100)', 'error');
                        }
                        return;
                    }

                    try {
                        const response = await fetch('/admin/doctor-payments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                doctor_id: this.selectedDoctor,
                                consultation_ids: this.selectedConsultations,
                                doctor_percentage: this.doctorPercentage
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'success', 'Payment Created Successfully');
                            }
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message || 'Failed to create payment', 'error');
                            }
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred while creating payment. Please check the console for details.', 'error');
                        }
                        console.error('Payment creation error:', error);
                    }
                },

                completePayment(paymentId) {
                    this.currentPaymentId = paymentId;
                    this.showCompleteModal = true;
                },

                async submitComplete() {
                    try {
                        const response = await fetch(`/admin/doctor-payments/${this.currentPaymentId}/complete`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.completeForm)
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'success');
                            }
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal('Error: ' + data.message, 'error');
                            }
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred', 'error');
                        }
                        console.error(error);
                    }
                },

                async viewPayment(paymentId) {
                    this.loadingPaymentDetails = true;
                    this.selectedPayment = null;
                    this.paymentConsultations = [];
                    this.showDetailsModal = true;

                    try {
                        const response = await fetch(`/admin/doctor-payments/${paymentId}/details`);
                        const data = await response.json();

                        if (data.success) {
                            this.selectedPayment = data.payment;
                            this.paymentConsultations = data.consultations || [];
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message || 'Failed to load payment details', 'error');
                            }
                            this.showDetailsModal = false;
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred while loading payment details', 'error');
                        }
                        this.showDetailsModal = false;
                        console.error(error);
                    } finally {
                        this.loadingPaymentDetails = false;
                    }
                },

                toggleSelectAll() {
                    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                    const selectAll = event.target.checked;
                    checkboxes.forEach(cb => {
                        if (cb.value) {
                            cb.checked = selectAll;
                            if (selectAll && !this.selectedPayments.includes(parseInt(cb.value))) {
                                this.selectedPayments.push(parseInt(cb.value));
                            } else if (!selectAll) {
                                this.selectedPayments = this.selectedPayments.filter(id => id !== parseInt(cb.value));
                            }
                        }
                    });
                },

                toggleSelectAllConsultations() {
                    const selectAll = event.target.checked;
                    if (selectAll) {
                        // Select all consultation IDs
                        this.selectedConsultations = this.consultations.map(c => c.id);
                    } else {
                        // Deselect all
                        this.selectedConsultations = [];
                    }
                },

                initiatePayout(paymentId) {
                    const confirmMessage = 'Initiate KoraPay payout for this payment? This will verify the bank account and process the transfer.';
                    if (typeof showConfirmModal === 'function') {
                        showConfirmModal(confirmMessage, () => {
                            this.performInitiatePayout(paymentId);
                        });
                    }
                },

                async performInitiatePayout(paymentId) {

                    try {
                        const response = await fetch(`/admin/doctor-payments/${paymentId}/initiate-payout`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'success', 'Payout Initiated');
                            }
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'error');
                            }
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred while initiating payout', 'error');
                        }
                        console.error(error);
                    }
                },

                async verifyPayout(paymentId) {
                    try {
                        const response = await fetch(`/admin/doctor-payments/${paymentId}/verify-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'success', 'Payout Status');
                            }
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message || 'Failed to verify payout status', 'error');
                            }
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred while verifying payout', 'error');
                        }
                        console.error(error);
                    }
                },

                processBulkPayout() {
                    if (this.selectedPayments.length === 0) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('Please select at least one payment', 'error');
                        }
                        return;
                    }

                    const confirmMessage = `Initiate KoraPay payout for ${this.selectedPayments.length} payment(s)? This will process all selected payments.`;
                    if (typeof showConfirmModal === 'function') {
                        showConfirmModal(confirmMessage, () => {
                            this.performBulkPayout();
                        });
                    }
                },

                async performBulkPayout() {

                    try {
                        const response = await fetch('/admin/doctor-payments/bulk-payout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                payment_ids: this.selectedPayments
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'success', 'Bulk Payout Initiated');
                            }
                            this.selectedPayments = [];
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            if (typeof showAlertModal === 'function') {
                                showAlertModal(data.message, 'error');
                            }
                        }
                    } catch (error) {
                        if (typeof showAlertModal === 'function') {
                            showAlertModal('An error occurred while processing bulk payout', 'error');
                        }
                        console.error(error);
                    }
                }
            }
        }
    </script>

    <!-- Include Alert Modal Component -->
    @include('components.alert-modal')
    @include('admin.shared.preloader')
</body>
</html>

