<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - DoctorOnTap</title>
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

    <!-- Failed Content -->
    <div class="container mx-auto px-5 py-16">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 text-center">
                <!-- Failed Icon -->
                <div class="mb-6">
                    <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>

                <!-- Failed Message -->
                <h1 class="text-3xl md:text-4xl font-bold text-red-600 mb-4">
                    Payment Failed ❌
                </h1>
                
                <p class="text-xl text-gray-700 mb-6">
                    {{ $message ?? 'Unfortunately, your payment could not be processed.' }}
                </p>

                @if(isset($reference))
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <p class="text-sm text-gray-600">Transaction Reference:</p>
                    <p class="font-mono font-semibold text-gray-900">{{ $reference }}</p>
                </div>
                @endif

                <!-- Possible Reasons -->
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6 mb-6 text-left">
                    <h3 class="font-bold text-yellow-700 mb-3">⚠️ Possible Reasons:</h3>
                    <ul class="text-gray-700 space-y-2">
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Insufficient funds in your account</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Incorrect card details or OTP</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Network or connectivity issues</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            <span>Payment was cancelled</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ url('/') }}" class="inline-block px-8 py-4 text-lg font-bold text-white rounded-xl purple-gradient hover:shadow-2xl hover:scale-105 transition-all">
                        Try Again
                    </a>
                    <a href="mailto:{{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }}" class="inline-block px-8 py-4 text-lg font-bold text-purple-700 bg-white border-2 border-purple-700 rounded-xl hover:bg-purple-50 hover:scale-105 transition-all">
                        Contact Support
                    </a>
                </div>

                <!-- Help Text -->
                <p class="text-sm text-gray-500 mt-6">
                    Need help? Contact us at {{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }} or call 08177777122
                </p>
            </div>
        </div>
    </div>
</body>
</html>

