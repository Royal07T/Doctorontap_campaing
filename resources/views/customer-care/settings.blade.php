@extends('layouts.customer-care')

@section('title', 'Settings - Customer Care')

@php
    $headerTitle = 'Settings';
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-600 mt-1">Manage your account preferences and security</p>
    </div>

    <!-- Profile Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Profile</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
                <p class="text-gray-900">{{ $user->name }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <p class="text-gray-900">{{ $user->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Phone</label>
                <p class="text-gray-900">{{ $user->phone ?? '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Preferences Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Preferences</h2>
        <p class="text-sm text-gray-600">Preference settings coming soon</p>
    </div>

    <!-- Security Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Security</h2>
        <p class="text-sm text-gray-600 mb-4">Security settings coming soon</p>
    </div>

    <!-- Logout Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Session</h2>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-900">Logout</p>
                <p class="text-xs text-gray-600 mt-1">Sign out of your account</p>
            </div>
            <form method="POST" action="{{ route('customer-care.logout') }}">
                @csrf
                <button type="submit" 
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

