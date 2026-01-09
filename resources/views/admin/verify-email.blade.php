<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
        body {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="mb-4">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-16 sm:h-20 w-auto mx-auto">
            </div>
            <h1 class="text-2xl font-bold text-white">Verify Your Email</h1>
            <p class="text-purple-100 mt-1 text-sm">Admin Portal</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white rounded-xl shadow-xl p-6">
            <!-- Icon -->
            <div class="text-center mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif

            <!-- Warning Message -->
            @if(session('warning'))
            <div class="mb-4 p-3 bg-amber-50 border-l-4 border-amber-500 text-amber-700 rounded text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('warning') }}
                </div>
            </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
            <div class="mb-4 p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <!-- Content -->
            <div class="text-center mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Please Verify Your Email Address</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Before proceeding, please check your email for a verification link.
                    If you didn't receive the email, click the button below to request another.
                </p>
                <p class="text-xs text-gray-500">
                    Email: <strong>{{ Auth::guard('admin')->user()->email }}</strong>
                </p>
            </div>

            <!-- Resend Button -->
            <form method="POST" action="{{ admin_route('admin.verification.resend') }}">
                @csrf
                <button type="submit" 
                        class="w-full px-6 py-3 purple-gradient text-white font-semibold text-sm rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Resend Verification Email
                </button>
            </form>

            <!-- Logout Link -->
            <div class="mt-4 text-center">
                <form method="POST" action="{{ admin_route('admin.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-purple-600 hover:text-purple-800 font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Help -->
        <div class="mt-4 text-center">
            <div class="inline-flex items-center gap-2 bg-purple-900 bg-opacity-30 px-4 py-2 rounded-lg">
                <svg class="w-4 h-4 text-purple-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-purple-100 text-xs">
                    Check your spam folder if you don't see the email
                </p>
            </div>
        </div>
    </div>
</body>
</html>

