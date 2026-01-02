<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan Access - DoctorOnTap</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar - DoctorOnTap Design -->
    <nav class="sticky top-0 z-50 purple-gradient shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo - Prominent on Mobile -->
                <a href="{{ url('/') }}" class="flex items-center group flex-shrink-0">
                    <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap Logo" class="h-10 sm:h-11 md:h-12 w-auto transition-transform group-hover:scale-105">
                </a>
                
                <!-- Contact Actions - Mobile First -->
                <div class="flex items-center gap-2 sm:gap-3 md:gap-6 text-white">
                    <!-- Email - Icon on Mobile, Full Info on Desktop -->
                    <a href="mailto:{{ config('mail.admin_email') }}" class="flex items-center gap-2 hover:text-purple-200 transition-colors group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 md:w-11 md:h-11 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="hidden lg:block text-right">
                            <p class="text-xs font-medium opacity-90 leading-tight">Email</p>
                            <p class="text-sm font-semibold leading-tight">{{ config('mail.admin_email') }}</p>
                        </div>
                    </a>
                    
                    <!-- WhatsApp - Icon on Mobile, Full Info on Desktop -->
                    <a href="https://wa.me/2348177777122" target="_blank" class="flex items-center gap-2 hover:text-purple-200 transition-colors group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 md:w-11 md:h-11 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center transition-all">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </div>
                        <div class="hidden lg:block text-right">
                            <p class="text-xs font-medium opacity-90 leading-tight">WhatsApp</p>
                            <p class="text-sm font-semibold leading-tight">08177777122</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </nav>

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
                <p>© {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
                <p class="mt-1">If you have any questions, please contact our support team.</p>
            </div>
        </div>
    </footer>
</body>
</html>
