@extends('layouts.caregiver-auth')

@section('title', 'Care Giver Login')

@section('content')
    <!-- Login Container -->
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="mb-4">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-16 sm:h-20 w-auto mx-auto">
            </div>
            <h1 class="text-2xl font-bold text-white">Care Giver Portal</h1>
            <p class="text-purple-200 mt-1 text-sm">Sign in to access your dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-xl p-6">
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

            <!-- Email Verification Notice -->
            @if(session('verification_required'))
            <div class="mb-4 p-3 bg-amber-50 border-l-4 border-amber-500 text-amber-700 rounded text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="font-semibold">Email verification required</p>
                        <p class="text-xs mt-1">Please check your email ({{ session('verification_email') }}) and click the verification link to activate your account.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('care_giver.login.post') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                        Email Address
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           required
                           autofocus
                           placeholder="caregiver@doctorontap.com"
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 @error('email') border-red-500 @enderror">
                    @error('email')
                    <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">
                        Password
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password"
                               name="password"
                               required
                               placeholder="Enter your password"
                               class="w-full px-4 py-2.5 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 @error('password') border-red-500 @enderror">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <!-- Eye Icon (Show) -->
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Slash Icon (Hide) -->
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-5 flex items-center">
                    <input type="checkbox"
                           id="remember"
                           name="remember"
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">
                        Keep me signed in
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full px-6 py-3 purple-gradient text-white font-semibold text-sm rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Sign In to Dashboard
                </button>
            </form>

            <!-- Footer Links -->
            <div class="mt-5 pt-4 border-t border-gray-200 text-center">
                <a href="{{ url('/') }}" class="text-xs text-purple-600 hover:text-purple-800 font-semibold inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Website
                </a>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-4 text-center">
            <div class="inline-flex items-center gap-2 bg-purple-900 bg-opacity-30 px-4 py-2 rounded-lg">
                <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-purple-100 text-xs">
                    Secure portal • All activities are logged
                </p>
            </div>
        </div>
    </div>

    <!-- System Preloader (hidden by default, shown only during form submission) -->
    <x-system-preloader x-show="pageLoading" message="Signing in..." />

    @push('scripts')
    <script>
        // Hide preloader on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Alpine !== 'undefined') {
                const body = Alpine.$data(document.body);
                if (body) {
                    body.pageLoading = false;
                }
            }
        });

        // Show preloader on form submission
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.tagName === 'FORM' && form.action.includes('/care-giver/login')) {
                if (typeof Alpine !== 'undefined') {
                    const body = Alpine.$data(document.body);
                    if (body) {
                        body.pageLoading = true;
                    }
                }
            }
        });
    </script>

    @endpush
@endsection

