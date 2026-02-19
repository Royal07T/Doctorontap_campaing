@extends('layouts.doctor')

@section('title', 'Settings - Doctor')

@php
    $headerTitle = 'Settings';
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-600 mt-1">Manage your account preferences and security</p>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-emerald-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm font-semibold text-emerald-900">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm font-semibold text-red-900">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-red-900">Please fix the following errors:</p>
                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Profile Section -->
    <div id="profile" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Profile Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                <p class="text-gray-900 text-base">Dr. {{ $doctor->name }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                <p class="text-gray-900 text-base">{{ $doctor->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
                <p class="text-gray-900 text-base">{{ $doctor->phone ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Specialization</label>
                <p class="text-gray-900 text-base">{{ $doctor->specialization ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Account Status</label>
                <div class="flex items-center gap-2">
                    @if($stats['is_verified'])
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            Pending Verification
                        </span>
                    @endif
                    @if($stats['is_available'])
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Online
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            Offline
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Member Since</label>
                <p class="text-gray-900 text-base">{{ $stats['member_since'] ? $stats['member_since']->format('F d, Y') : '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Last Login</label>
                <p class="text-gray-900 text-base">{{ $stats['last_login'] ? $stats['last_login']->format('F d, Y g:i A') : 'Never' }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Location</label>
                <p class="text-gray-900 text-base">{{ $doctor->city ?? '—' }}{{ $doctor->state ? ', ' . $doctor->state : '' }}</p>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="pt-6 border-t border-gray-200">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Account Statistics
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">Consultations</p>
                    <p class="text-2xl font-bold text-indigo-900">{{ number_format($stats['consultations_completed']) }}</p>
                    <p class="text-xs text-indigo-600 mt-1">{{ $stats['consultations_pending'] }} pending</p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-4 border border-emerald-100">
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-1">Total Earnings</p>
                    <p class="text-2xl font-bold text-emerald-900">₦{{ number_format($stats['total_earnings'], 2) }}</p>
                    <p class="text-xs text-emerald-600 mt-1">Completed</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Bank Accounts</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($stats['bank_accounts']) }}</p>
                    <p class="text-xs text-blue-600 mt-1">{{ $stats['verified_bank_accounts'] }} verified</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-1">Support Tickets</p>
                    <p class="text-2xl font-bold text-purple-900">{{ number_format($stats['support_tickets']) }}</p>
                    <p class="text-xs text-purple-600 mt-1">{{ $stats['resolved_tickets'] }} resolved</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Section -->
    <div id="security" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Security</h2>
        
        <!-- Change Password -->
        <div class="mb-6 pb-6 border-b border-gray-200" x-data="{ showCurrentPassword: false, showNewPassword: false, showConfirmPassword: false }">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Change Password</h3>
            <p class="text-xs text-gray-600 mb-4">Update your account password to keep your account secure</p>
            <form method="POST" action="{{ route('doctor.settings.change-password') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Current Password *</label>
                    <div class="relative">
                        <input :type="showCurrentPassword ? 'text' : 'password'" name="current_password" required
                               class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="showCurrentPassword = !showCurrentPassword" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">New Password *</label>
                    <div class="relative">
                        <input :type="showNewPassword ? 'text' : 'password'" name="password" required minlength="8"
                               class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="showNewPassword = !showNewPassword" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm New Password *</label>
                    <div class="relative">
                        <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                               class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="showConfirmPassword = !showConfirmPassword" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-end pt-4">
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Password Reset Link -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Forgot Password?</h3>
            <p class="text-xs text-gray-600 mb-3">If you've forgotten your password, you can request a password reset link</p>
            <a href="{{ route('doctor.password.request') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Request Password Reset
            </a>
        </div>
    </div>

    <!-- Account Deactivation Section -->
    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6" x-data="{ showDeactivationModal: false, deactivationReason: '', confirmDeactivation: false }">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Account Deactivation</h2>
        <p class="text-sm text-gray-600 mb-4">Deactivating your account will prevent you from receiving new consultations and accessing your dashboard. You will be logged out immediately.</p>
        
        @if($doctor->is_available)
        <button @click="showDeactivationModal = true" 
                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
            Deactivate Account
        </button>
        @else
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-sm font-semibold text-amber-900">Your account is currently deactivated.</p>
            <p class="text-xs text-amber-700 mt-1">Please contact support to reactivate your account.</p>
        </div>
        @endif

        <!-- Deactivation Modal -->
        <div x-show="showDeactivationModal" 
             x-cloak
             @click.away="showDeactivationModal = false"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                     @click="showDeactivationModal = false"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="{{ route('doctor.settings.deactivate-account') }}" @submit.prevent="if(confirmDeactivation && deactivationReason.length >= 10) { $el.submit(); } else { alert('Please provide a reason (minimum 10 characters) and confirm deactivation.'); }">
                        @csrf
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900">Deactivate Account</h3>
                                <button type="button" @click="showDeactivationModal = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-semibold text-red-900 mb-2">⚠️ Warning: This action cannot be undone easily</p>
                                <ul class="text-xs text-red-800 space-y-1 list-disc list-inside">
                                    <li>You will be logged out immediately</li>
                                    <li>You will not receive new consultations</li>
                                    <li>You will not be able to access your dashboard</li>
                                    <li>You will need to contact support to reactivate</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Reason for Deactivation <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    x-model="deactivationReason"
                                    name="reason"
                                    required
                                    minlength="10"
                                    maxlength="1000"
                                    rows="4"
                                    placeholder="Please provide a reason for deactivating your account (minimum 10 characters)..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none text-sm"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    <span x-text="deactivationReason.length"></span> / 10 characters minimum
                                </p>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="confirmDeactivation"
                                           name="confirmation" 
                                           value="1"
                                           required
                                           class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    <span class="ml-3 text-sm text-gray-700">
                                        I understand that deactivating my account will log me out and prevent access to my dashboard. I confirm that I want to proceed with account deactivation.
                                    </span>
                                </label>
                                @error('confirmation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" 
                                    @click="showDeactivationModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="!confirmDeactivation || deactivationReason.length < 10"
                                    class="px-6 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Confirm Deactivation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

