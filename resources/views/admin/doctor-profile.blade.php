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
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, showPaymentModal: false, selectedConsultations: [], pageLoading: false }">
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
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        <a href="{{ route('admin.doctors') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Back to Doctors</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Consultations</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['total_consultations'] }}</p>
                        <p class="text-xs text-gray-500">All time</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Paid Consultations</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['paid_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Paid</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-amber-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Unpaid Consultations</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['unpaid_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Unpaid</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Paid</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">₦{{ number_format($stats['total_paid_to_doctor'], 2) }}</p>
                        <p class="text-xs text-gray-500">Earnings</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Doctor Information -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Doctor Information
                                </h2>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Name</p>
                                    <p class="text-xs font-semibold text-gray-900">{{ $doctor->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</p>
                                    <p class="text-xs font-semibold text-gray-900">{{ $doctor->email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                                    <p class="text-xs font-semibold text-gray-900">{{ $doctor->phone }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Specialization</p>
                                    <p class="text-xs font-semibold text-gray-900">{{ $doctor->specialization }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consultation Fee</p>
                                    <p class="text-xs font-semibold text-gray-900">₦{{ number_format($doctor->effective_consultation_fee, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</p>
                                    <span class="inline-block px-2 py-0.5 text-xs rounded-full font-semibold {{ $doctor->is_approved ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $doctor->is_approved ? 'Approved' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Accounts -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Bank Accounts
                                    </h2>
                                @if($doctor->bankAccounts->where('is_verified', false)->count() > 0)
                                        <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-semibold">
                                        {{ $doctor->bankAccounts->where('is_verified', false)->count() }} Pending
                                    </span>
                                @endif
                                </div>
                            </div>
                            @forelse($doctor->bankAccounts as $account)
                                <div class="mb-3 p-3 border rounded-xl {{ $account->is_default ? 'border-purple-500 bg-purple-50' : ($account->is_verified ? 'border-gray-200 bg-gray-50' : 'border-amber-300 bg-amber-50') }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @if($account->is_default)
                                                <span class="inline-block bg-purple-600 text-white text-xs px-1.5 py-0.5 rounded-full font-semibold">DEFAULT</span>
                                            @endif
                                            @if($account->is_verified)
                                                <span class="inline-flex items-center gap-0.5 bg-emerald-100 text-emerald-700 text-xs px-1.5 py-0.5 rounded-full font-semibold">
                                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Verified
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-0.5 bg-amber-100 text-amber-700 text-xs px-1.5 py-0.5 rounded-full font-semibold">
                                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Pending
                                                </span>
                                            @endif
                                        </div>
                                        @if(!$account->is_verified)
                                            <button onclick="verifyBankAccount({{ $account->id }})" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Verify
                                            </button>
                                        @endif
                                    </div>
                                    <h3 class="text-xs font-semibold text-gray-900 mb-2">{{ $account->bank_name }}</h3>
                                    <div class="space-y-1">
                                        <p class="text-xs text-gray-700">
                                            <span class="font-medium">Account Name:</span> 
                                            <span class="text-gray-900">{{ $account->account_name }}</span>
                                        </p>
                                        <p class="text-xs text-gray-700">
                                            <span class="font-medium">Account Number:</span> 
                                            <span class="text-gray-900 font-mono">{{ $account->account_number }}</span>
                                        </p>
                                        <p class="text-xs text-gray-700">
                                            <span class="font-medium">Account Type:</span> 
                                            <span class="text-gray-900 capitalize">{{ $account->account_type ?? 'N/A' }}</span>
                                        </p>
                                        @if($account->bank_code)
                                        <p class="text-xs text-gray-700">
                                            <span class="font-medium">Bank Code:</span> 
                                            <span class="text-gray-900">{{ $account->bank_code }}</span>
                                        </p>
                                        @endif
                                        @if($account->verified_at)
                                        <p class="text-xs text-gray-500 mt-1.5">
                                            Verified on: {{ $account->verified_at->format('M d, Y H:i') }}
                                            @if($account->verifiedBy)
                                                by {{ $account->verifiedBy->name }}
                                            @endif
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <p class="text-xs text-gray-500">No bank accounts added yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Unpaid Consultations -->
                        @if($unpaidConsultations->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Unpaid Consultations
                                    </h2>
                                <div class="text-right">
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Pending Amount</p>
                                        <p class="text-sm font-bold text-purple-600">₦{{ number_format($stats['pending_payment'], 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                        @foreach($unpaidConsultations as $consultation)
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs font-semibold text-gray-900 font-mono">{{ $consultation->reference }}</p>
                                                <p class="text-xs text-gray-600 mt-0.5">{{ $consultation->full_name }} • {{ $consultation->created_at->format('M d, Y') }}</p>
                                            </div>
                                            <p class="text-xs font-semibold text-gray-900">₦{{ number_format($doctor->effective_consultation_fee, 2) }}</p>
                                        </div>
                                    </div>
                                        @endforeach
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <a href="{{ route('admin.doctor-payments.create') }}?doctor_id={{ $doctor->id }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create Payment
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Payment History -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Payment History
                                </h2>
                            </div>
                            <div class="space-y-3">
                            @forelse($paymentHistory as $payment)
                                    <div class="p-3 border border-gray-200 rounded-lg bg-gray-50">
                                        <div class="flex justify-between items-start mb-2">
                                        <div>
                                                <p class="text-xs font-semibold text-gray-900 font-mono">{{ $payment->reference }}</p>
                                                <p class="text-xs text-gray-600 mt-0.5">{{ $payment->total_consultations_count }} consultations • {{ $payment->created_at->format('M d, Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                                <p class="text-xs font-bold text-gray-900">₦{{ number_format($payment->doctor_amount, 2) }}</p>
                                                <span class="inline-block px-2 py-0.5 text-xs rounded-full mt-1 font-semibold
                                                    {{ $payment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($payment->paid_at)
                                            <p class="text-xs text-gray-500 mt-1.5">Paid on {{ $payment->paid_at->format('M d, Y') }} by {{ $payment->paidBy->name ?? 'Admin' }}</p>
                                    @endif
                                </div>
                            @empty
                                    <p class="text-xs text-gray-500 text-center py-4">No payment history yet</p>
                            @endforelse
                            </div>
                        </div>

                        <!-- Recent Consultations -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Recent Consultations
                                </h2>
                            </div>
                            <div class="space-y-2">
                                        @foreach($recentConsultations as $consultation)
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <a href="{{ route('admin.consultation.show', $consultation->id) }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800 font-mono">
                                                    {{ $consultation->reference }}
                                                </a>
                                                <p class="text-xs text-gray-600 mt-0.5">{{ $consultation->full_name }} • {{ $consultation->created_at->format('M d, Y') }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                                    {{ $consultation->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                                <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                                                    {{ $consultation->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ ucfirst($consultation->payment_status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                        @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function verifyBankAccount(accountId) {
            // Use custom confirm modal
            if (typeof showConfirmModal === 'function') {
                showConfirmModal('Are you sure you want to verify this bank account?', () => {
                    performVerification(accountId);
                });
            }
        }

        function performVerification(accountId) {
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
                    // Use custom alert modal
                    if (typeof showAlertModal === 'function') {
                        showAlertModal(data.message, 'success', 'Success');
                        // Reload after a short delay to show the success message
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    // Use custom alert modal for errors
                    if (typeof showAlertModal === 'function') {
                        showAlertModal('Error: ' + data.message, 'error', 'Error');
                    }
                }
            })
            .catch(error => {
                // Use custom alert modal for errors
                if (typeof showAlertModal === 'function') {
                    showAlertModal('An error occurred. Please try again.', 'error', 'Error');
                }
                console.error(error);
            });
        }
    </script>

    @include('components.alert-modal')
    
    @include('admin.shared.preloader')
</body>
</html>

