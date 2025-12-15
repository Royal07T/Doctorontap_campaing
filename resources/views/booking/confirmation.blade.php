<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Confirmation - DoctorOnTap</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Booking Confirmed!</h1>
                <p class="text-gray-600">Your multi-patient booking has been created successfully</p>
            </div>

            <!-- Booking Details Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Booking Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Booking Reference:</span>
                        <p class="text-lg font-semibold text-gray-900">{{ $booking->reference }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500">Payer:</span>
                        <p class="text-gray-900">{{ $booking->payer_name }}</p>
                        <p class="text-sm text-gray-600">{{ $booking->payer_email }}</p>
                        <p class="text-sm text-gray-600">{{ $booking->payer_mobile }}</p>
                    </div>

                    @if($booking->doctor)
                    <div>
                        <span class="text-sm font-medium text-gray-500">Assigned Doctor:</span>
                        <p class="text-gray-900">Dr. {{ $booking->doctor->first_name }} {{ $booking->doctor->last_name }}</p>
                    </div>
                    @endif

                    <div>
                        <span class="text-sm font-medium text-gray-500">Consultation Mode:</span>
                        <p class="text-gray-900 capitalize">{{ $booking->consult_mode }}</p>
                    </div>
                </div>
            </div>

            <!-- Patients List -->
            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Patients in This Booking</h2>
                
                <div class="space-y-4">
                    @foreach($booking->bookingPatients as $bp)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">
                                    {{ $bp->patient->name }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $bp->patient->age }} years old, {{ ucfirst($bp->patient->gender) }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Relationship: {{ ucfirst(str_replace('_', ' ', $bp->relationship_to_payer)) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Consultation: {{ $bp->consultation->reference }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-gray-900">
                                    ₦{{ number_format($bp->adjusted_fee, 2) }}
                                </p>
                                @if($bp->hasFeeAdjustment())
                                <p class="text-xs text-blue-600">Adjusted</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Invoice Summary -->
            @if($booking->invoice)
            <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Invoice Summary</h2>
                
                <div class="space-y-2 mb-4">
                    @foreach($booking->invoice->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $item->description }}</span>
                        <span class="text-gray-900">₦{{ number_format($item->total_price, 2) }}</span>
                    </div>
                    @endforeach
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                        <span class="text-2xl font-bold text-purple-600">
                            ₦{{ number_format($booking->invoice->total_amount, 2) }}
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <span class="text-sm font-medium text-gray-500">Invoice Reference:</span>
                    <p class="text-gray-900 font-mono">{{ $booking->invoice->reference }}</p>
                </div>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">What's Next?</h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>You will receive a confirmation email with booking details</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>The assigned doctor will review your booking</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Payment link will be sent when consultations are ready</span>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('consultation.index') }}" 
                    class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg text-center transition-colors">
                    Back to Home
                </a>
                @if($booking->invoice && $booking->payment_status !== 'paid')
                <button onclick="window.print()" 
                    class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                    Print Invoice
                </button>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
