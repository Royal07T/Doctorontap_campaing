<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Confirmed - DoctorOnTap</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface font-sans antialiased text-text-main">
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute top-[-10%] right-[-5%] w-[600px] h-[600px] rounded-full bg-primary/5 blur-3xl filter opacity-70"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[600px] h-[600px] rounded-full bg-accent/5 blur-3xl filter opacity-70"></div>
        </div>

        <div class="max-w-lg w-full">
            <div class="relative bg-white rounded-3xl shadow-2xl p-8 sm:p-10 border border-gray-100 overflow-hidden">
                <!-- Decorative Top Bar -->
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-primary to-accent"></div>

                <!-- Success Icon -->
                <div class="flex items-center justify-center w-24 h-24 mx-auto mb-8 rounded-full bg-green-50 border-4 border-white shadow-sm ring-8 ring-green-50/50">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="text-center">
                    <h1 class="text-3xl font-extrabold font-heading text-gray-900 mb-4">
                        Booking Confirmed! 🎉
                    </h1>
                    <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                        Thank you! Your consultation has been booked successfully. We will contact you shortly via WhatsApp to schedule your consultation. <span class="block mt-2 font-bold text-primary">Remember: You only pay AFTER your consultation is complete.</span>
                    </p>
                    
                    <!-- Reference Number Card -->
                    <div class="bg-accent/5 border border-accent/20 rounded-2xl p-6 mb-8 relative group">
                        <p class="text-sm text-accent font-bold uppercase tracking-wider mb-2">Your Reference Number:</p>
                        <p class="text-2xl font-black text-gray-900 font-mono tracking-tight select-all">
                            {{ $booking->reference }}
                        </p>
                        <p class="text-xs text-accent/70 mt-3 font-medium">Please save this reference for your records</p>
                    </div>
                    
                    <!-- Email Info Card -->
                    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 mb-8 text-left flex items-start gap-4">
                        <div class="text-2xl">📧</div>
                        <div>
                            <p class="text-sm text-primary font-bold mb-1">Check Your Email</p>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                We've sent a confirmation email with all your booking details and a payment link (if applicable).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="space-y-4">
                    <a href="{{ route('consultation.index') }}" 
                       class="inline-block w-full px-8 py-5 bg-gradient-to-r from-primary to-primary-dark text-white font-bold text-lg rounded-2xl shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:scale-[1.02] active:scale-[0.98] transition-all text-center">
                        Got it, Thanks! ✨
                    </a>
                    <a href="{{ route('consultation.index') }}" 
                       class="inline-block w-full px-8 py-4 bg-white border-2 border-primary/20 text-primary font-bold rounded-2xl hover:bg-primary/5 transition-all text-center flex items-center justify-center gap-2 group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Homepage
                    </a>
                </div>
                
                <p class="text-center text-xs text-text-muted mt-6">
                    &copy; {{ date('Y') }} DoctorOnTap. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
