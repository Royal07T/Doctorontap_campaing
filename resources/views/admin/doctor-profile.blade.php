<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctor Profile - {{ $doctor->name }} - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, isSubmitting: false, isLoading: false, showPaymentModal: false, selectedConsultations: [] }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.shared.sidebar', ['active' => 'doctors'])

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
                        <div>
                            <h1 class="text-xl font-bold text-white">Doctor Profile</h1>
                            <p class="text-purple-200 text-sm">Dr. {{ $doctor->full_name }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.doctors') }}" class="text-white hover:text-purple-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Doctors</span>
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Total Consultations</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_consultations'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Paid Consultations</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['paid_consultations'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Unpaid Consultations</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['unpaid_consultations'] }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
                        <p class="text-gray-600 text-xs font-medium uppercase">Total Paid</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ number_format($stats['total_paid_to_doctor'], 2) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Doctor Information -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-800 mb-4">Doctor Information</h2>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <p class="text-gray-600">Name</p>
                                    <p class="font-semibold">{{ $doctor->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Email</p>
                                    <p class="font-semibold">{{ $doctor->email }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Phone</p>
                                    <p class="font-semibold">{{ $doctor->phone }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Specialization</p>
                                    <p class="font-semibold">{{ $doctor->specialization }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Consultation Fee</p>
                                    <p class="font-semibold">₦{{ number_format($doctor->effective_consultation_fee, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Status</p>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full {{ $doctor->is_approved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $doctor->is_approved ? 'Approved' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Accounts -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-800 mb-4">Bank Accounts</h2>
                            @forelse($doctor->bankAccounts as $account)
                                <div class="mb-4 p-4 border rounded-lg {{ $account->is_default ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}">
                                    @if($account->is_default)
                                        <span class="inline-block bg-purple-600 text-white text-xs px-2 py-1 rounded-full mb-2">DEFAULT</span>
                                    @endif
                                    <h3 class="font-semibold text-gray-800">{{ $account->bank_name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $account->account_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $account->masked_account_number }}</p>
                                    @if($account->is_verified)
                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mt-2">✓ Verified</span>
                                    @else
                                        <div class="mt-2">
                                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Pending</span>
                                            <button onclick="verifyBankAccount({{ $account->id }})" class="ml-2 text-xs text-purple-600 hover:text-purple-800 font-semibold">
                                                Verify Now
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">No bank accounts added yet</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Unpaid Consultations -->
                        @if($unpaidConsultations->count() > 0)
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-bold text-gray-800">Unpaid Consultations</h2>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Pending Amount</p>
                                    <p class="text-xl font-bold text-purple-600">₦{{ number_format($stats['pending_payment'], 2) }}</p>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($unpaidConsultations as $consultation)
                                        <tr>
                                            <td class="px-4 py-3 text-sm">{{ $consultation->reference }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $consultation->full_name }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $consultation->created_at->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold">₦{{ number_format($doctor->effective_consultation_fee, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('admin.doctor-payments.create') }}?doctor_id={{ $doctor->id }}" class="purple-gradient text-white px-6 py-3 rounded-lg hover:opacity-90 transition-all inline-block">
                                    Create Payment
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Payment History -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-800 mb-4">Payment History</h2>
                            @forelse($paymentHistory as $payment)
                                <div class="mb-4 p-4 border border-gray-200 rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $payment->reference }}</p>
                                            <p class="text-sm text-gray-600">{{ $payment->total_consultations_count }} consultations</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $payment->created_at->format('M d, Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-lg text-gray-800">₦{{ number_format($payment->doctor_amount, 2) }}</p>
                                            <span class="inline-block px-2 py-1 text-xs rounded-full mt-1 
                                                {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($payment->paid_at)
                                        <p class="text-xs text-gray-500 mt-2">Paid on {{ $payment->paid_at->format('M d, Y') }} by {{ $payment->paidBy->name ?? 'Admin' }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">No payment history yet</p>
                            @endforelse
                        </div>

                        <!-- Recent Consultations -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-bold text-gray-800 mb-4">Recent Consultations</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($recentConsultations as $consultation)
                                        <tr>
                                            <td class="px-4 py-3 text-sm">
                                                <a href="{{ route('admin.consultation.show', $consultation->id) }}" class="text-purple-600 hover:text-purple-800">
                                                    {{ $consultation->reference }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ $consultation->full_name }}</td>
                                            <td class="px-4 py-3 text-sm">{{ $consultation->created_at->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    {{ $consultation->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    {{ $consultation->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($consultation->payment_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function verifyBankAccount(accountId) {
            CustomAlert.confirm('Are you sure you want to verify this bank account?', () => {
                fetch(`/admin/doctors/bank-accounts/${accountId}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        CustomAlert.success(data.message);
                        location.reload();
                    } else {
                        CustomAlert.error('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    CustomAlert.error('An error occurred. Please try again.');
                    console.error(error);
                });
            });
        }
    </script>
    @include('components.custom-alert-modal')
    
    <!-- System Preloader -->
    <x-system-preloader message="Loading..." subtext="Please wait while we process your request." />
</body>
</html>

