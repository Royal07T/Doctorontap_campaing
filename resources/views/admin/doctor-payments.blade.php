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
        @include('admin.shared.sidebar', ['active' => 'payments'])

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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payments as $payment)
                                <tr>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payment->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($payment->status === 'pending')
                                            <button @click="completePayment({{ $payment->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                                Complete
                                            </button>
                                        @endif
                                        <button @click="viewPayment({{ $payment->id }})" class="text-purple-600 hover:text-purple-900">
                                            View
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No payments found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $payments->links() }}
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

                                <div x-show="consultations.length > 0">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Consultations *</label>
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
                                <button type="submit" class="flex-1 purple-gradient text-white px-6 py-3 rounded-lg hover:opacity-90 transition-all font-medium" :disabled="selectedConsultations.length === 0">
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
            </main>
        </div>
    </div>

    <script>
        function paymentManager() {
            return {
                sidebarOpen: false,
                showCreateModal: false,
                showCompleteModal: false,
                selectedDoctor: '',
                consultations: [],
                selectedConsultations: [],
                doctorPercentage: {{ \App\Models\Setting::get('doctor_payment_percentage', 70) }},
                currentPaymentId: null,
                completeForm: {
                    payment_method: '',
                    transaction_reference: '',
                    payment_notes: ''
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
                    if (!this.selectedDoctor) return;

                    try {
                        const response = await fetch(`/admin/doctors/${this.selectedDoctor}/unpaid-consultations`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.consultations = data.consultations;
                            this.selectedConsultations = [];
                        }
                    } catch (error) {
                        CustomAlert.error('Failed to load consultations');
                        console.error(error);
                    }
                },

                async submitPayment() {
                    // Validate form
                    if (!this.selectedDoctor) {
                        CustomAlert.warning('Please select a doctor');
                        return;
                    }
                    
                    if (this.selectedConsultations.length === 0) {
                        CustomAlert.warning('Please select at least one consultation');
                        return;
                    }

                    try {
                        const response = await fetch('/admin/doctor-payments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                doctor_id: this.selectedDoctor,
                                consultation_ids: this.selectedConsultations,
                                doctor_percentage: this.doctorPercentage
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            CustomAlert.success(data.message || 'Payment created successfully');
                            this.showCreateModal = false;
                            // Reset form
                            this.selectedDoctor = '';
                            this.consultations = [];
                            this.selectedConsultations = [];
                            location.reload();
                        } else {
                            CustomAlert.error('Error: ' + (data.message || 'Failed to create payment'));
                            console.error('Payment creation error:', data);
                        }
                    } catch (error) {
                        CustomAlert.error('An error occurred: ' + error.message);
                        console.error('Payment creation exception:', error);
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
                            CustomAlert.success(data.message);
                            location.reload();
                        } else {
                            CustomAlert.error('Error: ' + data.message);
                        }
                    } catch (error) {
                        CustomAlert.error('An error occurred');
                        console.error(error);
                    }
                },

                viewPayment(paymentId) {
                    // Implement view payment details
                    CustomAlert.info('View payment details - ID: ' + paymentId);
                }
            }
        }
    </script>
    @include('components.custom-alert-modal')
    
    <!-- System Preloader -->
    <x-system-preloader message="Loading..." subtext="Please wait while we process your request." />
</body>
</html>

