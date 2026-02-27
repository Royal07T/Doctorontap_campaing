@extends('layouts.family')

@section('page-title', 'Portal Settings')
@section('patient', $patient->name)
@section('patient-initial', strtoupper(substr($patient->first_name ?? $patient->name, 0, 1)))
@section('patient-id', 'ID: ' . str_pad($patient->id, 6, '0', STR_PAD_LEFT))
@section('support-label', 'Help Center')
@section('support-text', 'Manage your portal preferences')
@section('support-cta', 'Get Help')
@section('header-cta', 'Save Changes')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Portal Settings</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your account and notification preferences</p>
    </div>

    {{-- Success / Error Messages --}}
    @if(session('profile_success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded flex items-center">
        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('profile_success') }}
    </div>
    @endif
    @if(session('password_success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded flex items-center">
        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('password_success') }}
    </div>
    @endif

    {{-- Profile --}}
    <form method="POST" action="{{ route('family.settings.profile') }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Profile Information</h2>
                <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-xs font-bold text-white hover:bg-purple-700 transition">
                    Save Profile
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xl">
                        {{ strtoupper(substr($member->first_name ?? $member->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $member->relationship ?? 'Family Member' }} · Member since {{ $member->created_at?->format('M Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-500 mb-1">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $member->name) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-300 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $member->email) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('email') border-red-300 @enderror">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-500 mb-1">Phone</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $member->phone ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone') border-red-300 @enderror">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="relationship" class="block text-xs font-semibold text-gray-500 mb-1">Relationship to Patient</label>
                        <input type="text" id="relationship" name="relationship" value="{{ old('relationship', $member->relationship ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('relationship') border-red-300 @enderror">
                        @error('relationship') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Notification Preferences --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Notification Preferences</h2>
        </div>
        <div class="p-5 space-y-4" x-data="{ criticalAlerts: true, vitalLogs: true, dailyReports: true, emailDigest: false }">
            @foreach([
                ['var' => 'criticalAlerts', 'label' => 'Critical Alerts', 'desc' => 'Receive instant notifications for critical vital signs'],
                ['var' => 'vitalLogs', 'label' => 'Vital Sign Logs', 'desc' => 'Get notified when vitals are recorded'],
                ['var' => 'dailyReports', 'label' => 'Daily Reports', 'desc' => 'Daily summary of care activities'],
                ['var' => 'emailDigest', 'label' => 'Email Digest', 'desc' => 'Weekly email summary of all activities'],
            ] as $pref)
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $pref['label'] }}</p>
                    <p class="text-xs text-gray-500">{{ $pref['desc'] }}</p>
                </div>
                <button @click="{{ $pref['var'] }} = !{{ $pref['var'] }}" type="button"
                    :class="{{ $pref['var'] }} ? 'bg-purple-600' : 'bg-gray-200'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <span :class="{{ $pref['var'] }} ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out mt-0.5 ml-0.5"></span>
                </button>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Security --}}
    <div id="security" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Security</h2>
        </div>
        <div class="p-5 space-y-5">
            {{-- Change Password Form --}}
            <form method="POST" action="{{ route('family.settings.password') }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900 mb-3">Change Password</p>
                    </div>
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-gray-500 mb-1">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter your current password"
                               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('current_password') border-red-300 @enderror">
                        @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-gray-500 mb-1">New Password</label>
                            <input type="password" id="password" name="password" placeholder="At least 8 characters"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('password') border-red-300 @enderror">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-gray-500 mb-1">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter new password"
                                   class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-xs font-bold text-white hover:bg-purple-700 transition">
                            Update Password
                        </button>
                    </div>
                </div>
            </form>

            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Two-Factor Authentication</p>
                        <p class="text-xs text-gray-500">Add an extra layer of security</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Not Enabled</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="bg-white rounded-xl shadow-sm border border-red-200">
        <div class="px-5 py-4 border-b border-red-100">
            <h2 class="text-sm font-bold text-red-700">Danger Zone</h2>
        </div>
        <div class="p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">Remove Portal Access</p>
                    <p class="text-xs text-gray-500">This will deactivate your family portal account</p>
                </div>
                <button class="px-4 py-2 rounded-lg border border-red-200 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                    Deactivate
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
