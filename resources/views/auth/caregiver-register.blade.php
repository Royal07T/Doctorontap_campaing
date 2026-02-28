<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Care Giver Registration – {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('doctor.partials.register-styles')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body x-data="{ isSubmitting: false }">
<div class="reg-shell">

    <aside class="reg-image-panel">
        <img src="{{ asset('img/Stethoscope wallpaper for doctors.jpeg') }}" alt="Care Giver registration" loading="eager">
        <div class="reg-image-overlay"></div>
        <div class="reg-image-content">
            <div class="reg-image-panel-logo">
                <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap">
            </div>
            <div>
                <h1 class="reg-image-hero-title">
                    Join Nigeria's leading<br>
                    <span>care giver network</span>
                </h1>
                <p class="reg-image-hero-subtitle">
                    Support patients and families, manage assignments, and deliver quality care — all from one place.
                </p>
                <div class="reg-trust-badges">
                    <div class="reg-trust-badge">
                        <div class="reg-trust-badge-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="reg-trust-badge-text">
                            <strong>Verified Care Givers</strong>
                            <span>All applicants are reviewed and verified</span>
                        </div>
                    </div>
                    <div class="reg-trust-badge">
                        <div class="reg-trust-badge-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="reg-trust-badge-text">
                            <strong>Quick Approval</strong>
                            <span>Applications reviewed in 1–2 business days</span>
                        </div>
                    </div>
                </div>
            </div>
            <p style="font-size:0.75rem;color:rgba(255,255,255,0.4);">&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </aside>

    <div class="reg-form-panel">
        <header class="reg-topbar">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="reg-topbar-logo">
            <span class="reg-topbar-tagline"></span>
            <a href="{{ route('care_giver.login') }}" class="reg-topbar-btn">
                Already registered?
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Sign In
            </a>
        </header>

        <div class="reg-mobile-hero">
            <div class="reg-mobile-hero-badge">
                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Nigeria's #1 Telehealth Platform
            </div>
            <h1>Join as Care Giver</h1>
            <p>Support patients, manage assignments &amp; deliver quality care — all from one place.</p>
            <div class="reg-mobile-trust">
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Verified
                </div>
                <div class="reg-mobile-trust-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    1–2 Day Approval
                </div>
            </div>
        </div>

        <div class="reg-progress-wrap">
            <div class="reg-progress-track">
                <div id="progress-bar" class="reg-progress-fill" data-step="0" style="width:25%;" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="4"></div>
            </div>
            <div class="reg-steps-row">
                @foreach(['Personal', 'Professional', 'Address & Docs', 'Security'] as $i => $stepLabel)
                    <div class="reg-step-item" data-progress-step="{{ $i }}">
                        <div class="reg-step-circle {{ $i === 0 ? 'current' : '' }}" data-step-circle="{{ $i }}">{{ $i + 1 }}</div>
                        <span class="reg-step-label {{ $i === 0 ? 'current' : '' }}" data-step-label="{{ $i }}">{{ $stepLabel }}</span>
                    </div>
                @endforeach
            </div>
            <p class="text-center mt-2 text-white/90 text-sm font-medium"><span id="progress-text">Step 1 of 4</span></p>
        </div>

        <div class="reg-form-area">
            <div class="reg-form-inner">
                <div class="registration-step active" style="display: block;">
                    <form method="POST" action="{{ route('caregiver.register.submit') }}" enctype="multipart/form-data" @submit="isSubmitting = true">
                        @csrf

                        @if ($errors->any())
                            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
                                <p class="font-semibold">Please correct the following errors:</p>
                                <ul class="mt-2 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" data-section="0">
                            <div class="reg-section-header sm:col-span-2">
                                <div class="reg-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <div class="reg-section-title">Personal Information</div>
                                    <div class="reg-section-subtitle">Your basic details</div>
                                </div>
                            </div>
                            <div>
                                <label for="first_name" class="form-label">First Name <span class="req">*</span></label>
                                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required minlength="2" maxlength="255" placeholder="e.g., Jane" class="form-control @error('first_name') border-red-500 @enderror">
                                @error('first_name')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="last_name" class="form-label">Last Name <span class="req">*</span></label>
                                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required minlength="2" maxlength="255" placeholder="e.g., Doe" class="form-control @error('last_name') border-red-500 @enderror">
                                @error('last_name')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="form-label">Email Address <span class="req">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="e.g., jane@example.com" class="form-control @error('email') border-red-500 @enderror">
                                @error('email')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="phone" class="form-label">Phone Number <span class="req">*</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required maxlength="20" placeholder="e.g., 08012345678" class="form-control @error('phone') border-red-500 @enderror">
                                @error('phone')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="req">*</span></label>
                                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="form-control @error('date_of_birth') border-red-500 @enderror">
                                @error('date_of_birth')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="gender" class="form-label">Gender <span class="req">*</span></label>
                                <select id="gender" name="gender" required class="form-control @error('gender') border-red-500 @enderror">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-10" data-section="1">
                            <div class="reg-section-header sm:col-span-2">
                                <div class="reg-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="reg-section-title">Professional Details</div>
                                    <div class="reg-section-subtitle">Your qualifications and experience</div>
                                </div>
                            </div>
                            <div>
                                <label for="role" class="form-label">Role Applied For <span class="req">*</span></label>
                                <select id="role" name="role" required class="form-control @error('role') border-red-500 @enderror">
                                    <option value="">Select role</option>
                                    <option value="Registered Nurse" {{ old('role') == 'Registered Nurse' ? 'selected' : '' }}>Registered Nurse</option>
                                    <option value="Auxiliary Nurse" {{ old('role') == 'Auxiliary Nurse' ? 'selected' : '' }}>Auxiliary Nurse</option>
                                    <option value="Caregiver" {{ old('role') == 'Caregiver' ? 'selected' : '' }}>Caregiver</option>
                                    <option value="Medical Assistant" {{ old('role') == 'Medical Assistant' ? 'selected' : '' }}>Medical Assistant</option>
                                </select>
                                @error('role')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="experience_years" class="form-label">Years of Experience <span class="req">*</span></label>
                                <input type="number" id="experience_years" name="experience_years" value="{{ old('experience_years') }}" required min="0" placeholder="e.g., 5" class="form-control @error('experience_years') border-red-500 @enderror">
                                @error('experience_years')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" id="license_number" name="license_number" value="{{ old('license_number') }}" placeholder="Optional" class="form-control @error('license_number') border-red-500 @enderror">
                                @error('license_number')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="bio" class="form-label">Short Bio / Summary</label>
                                <textarea id="bio" name="bio" rows="3" placeholder="Briefly describe your experience and skills..." class="form-control min-h-[6rem] @error('bio') border-red-500 @enderror">{{ old('bio') }}</textarea>
                                @error('bio')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-10" data-section="2">
                            <div class="reg-section-header sm:col-span-2">
                                <div class="reg-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <div class="reg-section-title">Address & Documents</div>
                                    <div class="reg-section-subtitle">Where you're based and optional documents</div>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="address" class="form-label">Street Address <span class="req">*</span></label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}" required placeholder="Street, area, landmark" class="form-control @error('address') border-red-500 @enderror">
                                @error('address')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="state_id" class="form-label">State <span class="req">*</span></label>
                                <select id="state_id" name="state_id" required class="form-control @error('state_id') border-red-500 @enderror">
                                    <option value="">Select your state</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}" {{ old('state_id') == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                    @endforeach
                                </select>
                                @error('state_id')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="city_id" class="form-label">City <span class="req">*</span></label>
                                <select id="city_id" name="city_id" required disabled class="form-control @error('city_id') border-red-500 @enderror">
                                    <option value="">Select state first</option>
                                </select>
                                @error('city_id')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="profile_photo" class="form-label">Profile Photo</label>
                                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="form-control text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <p class="form-hint">Optional. Max 2MB. JPG, PNG.</p>
                            </div>
                            <div>
                                <label for="cv_file" class="form-label">CV (PDF/DOC)</label>
                                <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" class="form-control text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                <p class="form-hint">Optional. Max 5MB.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-10" data-section="3">
                            <div class="reg-section-header sm:col-span-2">
                                <div class="reg-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <div class="reg-section-title">Account Security</div>
                                    <div class="reg-section-subtitle">Create a secure password</div>
                                </div>
                            </div>
                            <div>
                                <label for="password" class="form-label">Password <span class="req">*</span></label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required minlength="8" placeholder="Minimum 8 characters" autocomplete="new-password" class="form-control pr-10 @error('password') border-red-500 @enderror">
                                    <button type="button" onclick="window.toggleCaregiverPasswordVisibility('password', 'password-eye')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600" aria-label="Toggle password">
                                        <svg id="password-eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg id="password-eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @error('password')<p class="form-error-msg">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="req">*</span></label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Re-enter password" autocomplete="new-password" class="form-control pr-10">
                                    <button type="button" onclick="window.toggleCaregiverPasswordVisibility('password_confirmation', 'password-confirmation-eye')" class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600" aria-label="Toggle password">
                                        <svg id="password-confirmation-eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg id="password-confirmation-eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="btn-row end mt-10">
                            <button type="submit" :disabled="isSubmitting" class="btn-primary">
                                <span x-show="!isSubmitting" class="flex items-center gap-2">Submit Application <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span>
                                <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                    Submitting...
                                </span>
                            </button>
                        </div>

                        <div class="reg-secure-badge">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Your data is secured and encrypted</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<x-system-preloader x-show="isSubmitting" message="Submitting your application..." />

<script>
window.toggleCaregiverPasswordVisibility = function(inputId, buttonId) {
    var el = document.getElementById(inputId);
    var open = document.getElementById(buttonId + '-open');
    var closed = document.getElementById(buttonId + '-closed');
    if (!el) return;
    if (el.type === 'password') {
        el.type = 'text';
        if (open) open.classList.add('hidden');
        if (closed) closed.classList.remove('hidden');
    } else {
        el.type = 'password';
        if (open) open.classList.remove('hidden');
        if (closed) closed.classList.add('hidden');
    }
};
document.addEventListener('DOMContentLoaded', function() {
    var stateSelect = document.getElementById('state_id');
    var citySelect = document.getElementById('city_id');
    var oldCityId = @json(old('city_id'));
    if (stateSelect && citySelect) {
        stateSelect.addEventListener('change', function() {
            var stateId = this.value;
            citySelect.innerHTML = '<option value="">Loading cities...</option>';
            citySelect.disabled = true;
            if (!stateId) {
                citySelect.innerHTML = '<option value="">Select state first</option>';
                citySelect.disabled = false;
                return;
            }
            fetch('{{ url("caregiver/cities") }}/' + stateId)
                .then(function(r) { return r.json(); })
                .then(function(cities) {
                    citySelect.innerHTML = '<option value="">Select your city</option>';
                    cities.forEach(function(c) {
                        var opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        if (oldCityId && c.id == oldCityId) opt.selected = true;
                        citySelect.appendChild(opt);
                    });
                    citySelect.disabled = false;
                })
                .catch(function() {
                    citySelect.innerHTML = '<option value="">Error loading cities</option>';
                    citySelect.disabled = false;
                });
        });
        if (stateSelect.value) stateSelect.dispatchEvent(new Event('change'));
    }
    var sections = document.querySelectorAll('[data-section]');
    var progressSteps = document.querySelectorAll('[data-progress-step]');
    var progressBar = document.getElementById('progress-bar');
    var progressText = document.getElementById('progress-text');
    function updateProgress(step) {
        var pct = ((step + 1) / 4) * 100;
        progressBar.style.width = pct + '%';
        progressBar.setAttribute('data-step', step);
        progressBar.setAttribute('aria-valuenow', step + 1);
        if (progressText) progressText.textContent = 'Step ' + (step + 1) + ' of 4';
        progressSteps.forEach(function(stepEl, index) {
            var circle = stepEl.querySelector('.reg-step-circle');
            var label = stepEl.querySelector('.reg-step-label');
            if (!circle) return;
            circle.classList.remove('current', 'done');
            if (label) label.classList.remove('current', 'done');
            if (index < step) {
                circle.classList.add('done');
                circle.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                if (label) label.classList.add('done');
            } else if (index === step) {
                circle.classList.add('current');
                circle.textContent = index + 1;
                if (label) label.classList.add('current');
            } else {
                circle.textContent = index + 1;
            }
        });
    }
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var sectionIndex = parseInt(entry.target.dataset.section);
                updateProgress(sectionIndex);
            }
        });
    }, { root: null, rootMargin: '-50% 0px -50% 0px', threshold: 0 });
    sections.forEach(function(s) { observer.observe(s); });
    updateProgress(0);
    document.querySelectorAll('input, select, textarea').forEach(function(input) {
        input.addEventListener('focus', function() {
            var section = this.closest('[data-section]');
            if (section) updateProgress(parseInt(section.dataset.section));
        });
    });
});
</script>
</body>
</html>
