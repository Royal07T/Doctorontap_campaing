@extends('layouts.caregiver-auth')

@section('title', 'Verify Your Email')

@section('content')
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="mb-4">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-16 sm:h-20 w-auto mx-auto">
            </div>
            <h1 class="text-2xl font-bold text-white">Verify Your Email</h1>
            <p class="text-purple-200 mt-1 text-sm">One more step to access the caregiver portal</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white rounded-xl shadow-xl p-6">
            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif

            <!-- Info -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm">
                    We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.
                </p>
                @if(Auth::guard('care_giver')->user())
                <p class="text-gray-500 text-xs mt-2">
                    Sent to: <strong>{{ Auth::guard('care_giver')->user()->email }}</strong>
                </p>
                @endif
            </div>

            <!-- Resend Form -->
            <form method="POST" action="{{ route('care_giver.verification.resend') }}">
                @csrf
                <button type="submit"
                        class="w-full px-6 py-3 purple-gradient text-white font-semibold text-sm rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Resend Verification Link
                </button>
            </form>

            <!-- Help Text -->
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500">
                    Didn't receive the email? Check your spam folder or request a new link above.
                </p>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-4 text-center">
            <a href="{{ route('care_giver.login') }}" class="text-xs text-purple-200 hover:text-white font-semibold inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Login
            </a>
        </div>
    </div>
@endsection
