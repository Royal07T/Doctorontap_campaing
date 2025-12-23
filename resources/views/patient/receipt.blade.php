<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $consultation->reference }} - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
            }
        }
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Print Button -->
        <div class="mb-6 no-print flex justify-end">
            <button onclick="window.print()" class="px-4 py-2 purple-gradient hover:opacity-90 text-white text-xs font-medium rounded-lg transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Receipt</span>
            </button>
            <a href="{{ route('patient.payments') }}" class="ml-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-medium rounded-lg transition">
                Back to Payments
            </a>
        </div>

        <!-- Receipt Container -->
        <div class="bg-white receipt-container rounded-lg shadow-2xl border border-gray-200 overflow-hidden">
            <!-- Header with Purple Gradient -->
            <div class="purple-gradient text-white py-8 px-6 md:py-10 md:px-12">
                <div class="flex flex-col items-center text-center">
                    <div class="mb-4">
                        <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-12 md:h-14 w-auto mx-auto">
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Payment Receipt</h1>
                    <p class="text-sm md:text-base text-white/90 font-medium">Transaction Confirmation</p>
                    <div class="mt-4 w-24 h-0.5 bg-white/30 rounded-full"></div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="p-8 md:p-12">

            <!-- Receipt Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Left Column -->
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wide">Payment Information</h2>
                    <div class="space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Receipt Number:</span>
                            <span class="text-xs font-semibold text-gray-900 font-mono">{{ $consultation->payment->reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Transaction Reference:</span>
                            <span class="text-xs font-semibold text-gray-900 font-mono">{{ $consultation->payment->payment_reference ?? $consultation->payment->reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Payment Date:</span>
                            <span class="text-xs font-semibold text-gray-900">{{ $consultation->payment->created_at->format('F d, Y h:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Payment Status:</span>
                            <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                ✓ {{ ucfirst($consultation->payment->status) }}
                            </span>
                        </div>
                        @if($consultation->payment->payment_method)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Payment Method:</span>
                            <span class="text-xs font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $consultation->payment->payment_method)) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wide">Consultation Details</h2>
                    <div class="space-y-2.5">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Consultation Reference:</span>
                            <span class="text-xs font-semibold text-gray-900 font-mono">{{ $consultation->reference }}</span>
                        </div>
                        @if($consultation->doctor)
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Doctor:</span>
                            <span class="text-xs font-semibold text-gray-900">
                                @php
                                    $doctorName = trim($consultation->doctor->name);
                                    $doctorNameLower = strtolower($doctorName);
                                    $hasDrPrefix = preg_match('/^dr\.?\s*/i', $doctorNameLower);
                                @endphp
                                {{ $hasDrPrefix ? $doctorName : 'Dr. ' . $doctorName }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Specialization:</span>
                            <span class="text-xs font-semibold text-gray-900">{{ $consultation->doctor->specialization ?? 'General Practitioner' }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Consultation Date:</span>
                            <span class="text-xs font-semibold text-gray-900">{{ $consultation->created_at->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-600">Consultation Status:</span>
                            <span class="text-xs font-semibold text-gray-900">{{ ucfirst($consultation->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="mb-6 pb-6 border-b-2 border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wide">Patient Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <span class="text-xs text-gray-600">Name:</span>
                        <span class="text-xs font-semibold text-gray-900 ml-2">{{ $consultation->full_name }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-600">Email:</span>
                        <span class="text-xs font-semibold text-gray-900 ml-2">{{ $consultation->email }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-600">Phone:</span>
                        <span class="text-xs font-semibold text-gray-900 ml-2">{{ $consultation->mobile }}</span>
                    </div>
                    @if($consultation->age)
                    <div>
                        <span class="text-xs text-gray-600">Age:</span>
                        <span class="text-xs font-semibold text-gray-900 ml-2">{{ $consultation->age }} years</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-3 uppercase tracking-wide">Payment Summary</h2>
                <div class="bg-gray-50 rounded-lg p-5">
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-700">Consultation Fee:</span>
                            <span class="text-base font-bold text-gray-900">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                        </div>
                        @if($consultation->payment->fee && $consultation->payment->fee > 0)
                        <div class="flex justify-between items-center text-xs text-gray-600">
                            <span>Transaction Fee:</span>
                            <span>₦{{ number_format($consultation->payment->fee, 2) }}</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-300 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-900">Total Paid:</span>
                                <span class="text-xl font-bold text-purple-600">₦{{ number_format($consultation->payment->amount, 2) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Currency: {{ strtoupper($consultation->payment->currency ?? 'NGN') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center pt-6 border-t-2 border-gray-200">
                <p class="text-xs text-gray-600 mb-1.5">Thank you for choosing DoctorOnTap!</p>
                <p class="text-xs text-gray-500">This is an official receipt for your payment. Please keep this for your records.</p>
                <p class="text-xs text-gray-500 mt-3">For any inquiries, please contact us at {{ config('mail.admin_email') }} or call 08177777122</p>
            </div>
            </div>
        </div>
    </div>
</body>
</html>
