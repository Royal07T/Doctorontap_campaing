@extends('layouts.caregiver-auth')

@section('title', 'PIN Verification')

@section('content')
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-6">
            <div class="mb-4">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-16 sm:h-20 w-auto mx-auto">
            </div>
            <h1 class="text-2xl font-bold text-white">Security Verification</h1>
            <p class="text-purple-200 mt-1 text-sm">Enter your PIN to access patient data</p>
        </div>

        <!-- PIN Card -->
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

            <!-- Error Messages -->
            @if($errors->any())
            <div class="mb-4 p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $errors->first() }}
                </div>
            </div>
            @endif

            <!-- Lock Icon -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <p class="text-gray-600 text-sm">
                    Sensitive patient data requires secondary verification.
                </p>
            </div>

            <!-- PIN Form -->
            <form method="POST" action="{{ route('care_giver.pin.verify.post') }}" id="pin-form" autocomplete="off">
                @csrf

                <!-- PIN Input Fields -->
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide text-center">
                        Enter Your PIN
                    </label>
                    <div class="flex justify-center gap-3" id="pin-inputs">
                        <input type="password" maxlength="1" id="pin-0" data-index="0"
                               class="pin-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="password" maxlength="1" id="pin-1" data-index="1"
                               class="pin-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="password" maxlength="1" id="pin-2" data-index="2"
                               class="pin-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="password" maxlength="1" id="pin-3" data-index="3"
                               class="pin-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="password" maxlength="1" id="pin-4" data-index="4"
                               class="pin-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="password" maxlength="1" id="pin-5" data-index="5"
                               class="pin-input w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition-colors"
                               inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                    </div>
                    <!-- Hidden actual input -->
                    <input type="hidden" name="pin" id="pin-value">
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-btn" disabled
                        class="w-full px-6 py-3 purple-gradient text-white font-semibold text-sm rounded-lg hover:shadow-lg hover:scale-[1.02] transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Verify PIN</span>
                </button>
            </form>

            <!-- Help Text -->
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500">
                    PIN expires after 15 minutes of inactivity. Contact your administrator if you've forgotten your PIN.
                </p>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-4 text-center">
            <div class="inline-flex items-center gap-2 bg-purple-900 bg-opacity-30 px-4 py-2 rounded-lg">
                <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-purple-100 text-xs">
                    HIPAA-compliant • All access is audited
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.pin-input');
            const hiddenInput = document.getElementById('pin-value');
            const submitBtn = document.getElementById('submit-btn');
            const form = document.getElementById('pin-form');

            // Update hidden input and button state
            function updatePinValue() {
                let pin = '';
                inputs.forEach(input => {
                    pin += input.value;
                    if (input.value) {
                        input.classList.add('border-purple-500', 'bg-purple-50');
                    } else {
                        input.classList.remove('border-purple-500', 'bg-purple-50');
                    }
                });
                hiddenInput.value = pin;
                submitBtn.disabled = pin.length < 6;
            }

            // Handle input
            inputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^0-9]/g, '');
                    e.target.value = value;

                    updatePinValue();

                    // Auto-advance to next field
                    if (value && index < 5) {
                        inputs[index + 1].focus();
                    }
                });

                // Handle backspace
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace') {
                        if (!e.target.value && index > 0) {
                            e.preventDefault();
                            inputs[index - 1].value = '';
                            inputs[index - 1].focus();
                            updatePinValue();
                        }
                    }
                });

                // Handle paste
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData)
                        .getData('text')
                        .replace(/[^0-9]/g, '')
                        .slice(0, 6);

                    for (let i = 0; i < pasted.length && i < 6; i++) {
                        inputs[i].value = pasted[i];
                    }

                    updatePinValue();

                    // Focus last filled or next empty
                    const focusIndex = Math.min(pasted.length, 5);
                    inputs[focusIndex].focus();
                });
            });

            // Form submit handler
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.querySelector('span').textContent = 'Verifying...';
            });

            // Focus first input on load
            inputs[0].focus();
        });
    </script>
    @endpush
@endsection
