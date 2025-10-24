<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan Access - DoctorOnTap</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <img src="{{ asset('img/logo-text.png') }}" alt="DoctorOnTap" class="h-8">
                </div>
                <div class="text-sm text-gray-600">
                    Treatment Plan Access
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Treatment Plan Access</h1>
                        <p class="text-purple-100 mt-2">Reference: <span class="font-mono">{{ $consultation->reference }}</span></p>
                    </div>
                    <div class="text-right text-white">
                        <p class="text-sm text-purple-100">Patient</p>
                        <p class="font-semibold">{{ $consultation->first_name }} {{ $consultation->last_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if(isset($error))
                    <!-- Error State -->
                    <div class="text-center py-12">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Restricted</h3>
                        <p class="text-gray-600 mb-6">{{ $error }}</p>
                        
                        @if(isset($showPaymentButton) && $showPaymentButton)
                            <a href="{{ route('payment.request', $consultation->reference) }}" 
                               class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Make Payment
                            </a>
                        @endif
                    </div>
                @elseif(isset($showAccessButton))
                    <!-- Success State - Payment Made -->
                    <div class="text-center py-12">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Treatment Plan Ready</h3>
                        <p class="text-gray-600 mb-6">Your treatment plan is ready for viewing. Click the button below to access it.</p>
                        
                        <form method="POST" action="{{ route('treatment-plan.access', $consultation->reference) }}">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Treatment Plan
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Default State -->
                    <div class="text-center py-12">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Treatment Plan Status</h3>
                        <p class="text-gray-600 mb-6">Please wait while we process your request...</p>
                    </div>
                @endif

                <!-- Consultation Details -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Consultation Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Doctor:</span>
                            <span class="font-medium text-gray-900">{{ $consultation->doctor ? $consultation->doctor->name . ($consultation->doctor->gender ? ' (' . ucfirst($consultation->doctor->gender) . ')' : '') : 'Not Assigned' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <span class="font-medium text-gray-900">{{ ucfirst($consultation->status) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Date:</span>
                            <span class="font-medium text-gray-900">{{ $consultation->created_at->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Payment Status:</span>
                            <span class="font-medium {{ $consultation->isPaid() ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($consultation->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; 2025 DoctorOnTap. All rights reserved.</p>
                <p class="mt-1">If you have any questions, please contact our support team.</p>
            </div>
        </div>
    </footer>
</body>
</html>
