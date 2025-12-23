<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 purple-gradient shadow-lg">
        <div class="container mx-auto px-5 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center group">
                    <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap Logo" class="h-7 sm:h-8 md:h-10 w-auto transition-transform group-hover:scale-105">
                </a>
                <a href="{{ url('/') }}" class="text-white hover:text-purple-200 font-semibold transition-colors">
                    Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Success Content -->
    <div class="container mx-auto px-5 py-16">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 text-center">
                <!-- Success Icon -->
                <div class="mb-6">
                    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Success Message -->
                <h1 class="text-3xl md:text-4xl font-bold text-green-600 mb-4">
                    Payment Successful! ✅
                </h1>
                
                <p class="text-xl text-gray-700 mb-6">
                    {{ $message ?? 'Your payment has been processed successfully.' }}
                </p>

                @if(isset($payment))
                <!-- Payment Details -->
                <div class="bg-gray-50 rounded-xl p-6 mb-6 text-left">
                    <h3 class="font-bold text-gray-700 mb-4 text-center">Payment Details</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Reference:</span>
                            <span class="font-semibold text-gray-900">{{ $payment->reference }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount Paid:</span>
                            <span class="font-semibold text-gray-900">NGN {{ number_format($payment->amount, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-semibold text-green-600">{{ ucfirst($payment->status) }}</span>
                        </div>
                        
                        @if($payment->payment_method)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</span>
                        </div>
                        @endif
                        
                        @if($payment->doctor)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Doctor:</span>
                            <span class="font-semibold text-gray-900">{{ $payment->doctor->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Next Steps -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-6">
                    <h3 class="font-bold text-blue-700 mb-3">📋 What Happens Next?</h3>
                    <ul class="text-left text-gray-700 space-y-2">
                        <li class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>You will receive a confirmation email shortly</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>Our team will contact you via WhatsApp within 1-2 hours</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>We'll schedule your consultation at a convenient time</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                @if(isset($source) && $source === 'dashboard' && Auth::guard('patient')->check())
                    <a href="{{ route('patient.dashboard') }}" class="inline-block px-8 py-4 text-lg font-bold text-white rounded-xl purple-gradient hover:shadow-2xl hover:scale-105 transition-all">
                        Back to Dashboard
                    </a>
                @else
                    <a href="{{ url('/') }}" class="inline-block px-8 py-4 text-lg font-bold text-white rounded-xl purple-gradient hover:shadow-2xl hover:scale-105 transition-all">
                        Back to Home
                    </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>

