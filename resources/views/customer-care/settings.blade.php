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

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-emerald-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-sm font-semibold text-emerald-900">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Profile Section -->
    <div id="profile" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
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
    <div id="preferences" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Dashboard Preferences</h2>
        <p class="text-sm text-gray-600 mb-6">Customize your dashboard experience to match your workflow</p>
        
        @php
            $preferences = $user->getDashboardPreferences();
        @endphp
        
        <form method="POST" action="{{ route('customer-care.settings.preferences') }}" class="space-y-6">
            @csrf
            
            <!-- Auto-Refresh Interval -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Auto-Refresh Interval</label>
                <p class="text-xs text-gray-600 mb-3">How often the dashboard should automatically refresh (in seconds)</p>
                <div class="flex items-center gap-4">
                    <input type="number" name="auto_refresh_interval" 
                           value="{{ $preferences['auto_refresh_interval'] ?? 30 }}" 
                           min="10" max="300" step="10"
                           class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <span class="text-sm text-gray-600">seconds (10-300)</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">Set to 0 to disable auto-refresh</p>
            </div>
            
            <!-- Items Per Page -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Items Per Page</label>
                <p class="text-xs text-gray-600 mb-3">Number of items to display per page in lists</p>
                <select name="items_per_page" 
                        class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="5" {{ ($preferences['items_per_page'] ?? 10) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ ($preferences['items_per_page'] ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ ($preferences['items_per_page'] ?? 10) == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ ($preferences['items_per_page'] ?? 10) == 20 ? 'selected' : '' }}>20</option>
                    <option value="25" {{ ($preferences['items_per_page'] ?? 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($preferences['items_per_page'] ?? 10) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            
            <!-- Dashboard Sections Visibility -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Dashboard Sections</label>
                <p class="text-xs text-gray-600 mb-4">Show or hide specific sections on your dashboard</p>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_statistics" value="1" 
                               {{ ($preferences['show_statistics'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Statistics Cards</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_queue_management" value="1" 
                               {{ ($preferences['show_queue_management'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Queue Management</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_team_status" value="1" 
                               {{ ($preferences['show_team_status'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Team Status</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_performance_metrics" value="1" 
                               {{ ($preferences['show_performance_metrics'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Performance Metrics</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_activity_feed" value="1" 
                               {{ ($preferences['show_activity_feed'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Activity Feed</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_priority_queue" value="1" 
                               {{ ($preferences['show_priority_queue'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Priority Queue</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_pipeline_metrics" value="1" 
                               {{ ($preferences['show_pipeline_metrics'] ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-3 text-sm text-gray-700">Pipeline Metrics</span>
                    </label>
                </div>
            </div>
            
            <!-- Default View -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Default Dashboard View</label>
                <p class="text-xs text-gray-600 mb-3">Choose your preferred dashboard layout</p>
                <select name="default_view" 
                        class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="enhanced" {{ ($preferences['default_view'] ?? 'enhanced') == 'enhanced' ? 'selected' : '' }}>Enhanced (Full Features)</option>
                    <option value="standard" {{ ($preferences['default_view'] ?? 'enhanced') == 'standard' ? 'selected' : '' }}>Standard (Simplified)</option>
                </select>
            </div>
            
            <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                    Save Preferences
                </button>
            </div>
        </form>
    </div>

    <!-- Security Section -->
    <div id="security" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Security</h2>
        
        <!-- Change Password -->
        <div class="mb-6 pb-6 border-b border-gray-200" x-data="{ showCurrentPassword: false, showNewPassword: false, showConfirmPassword: false }">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Change Password</h3>
            <p class="text-xs text-gray-600 mb-4">Update your account password to keep your account secure</p>
            <form method="POST" action="{{ route('customer-care.settings.change-password') }}" class="space-y-4">
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
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
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
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
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
                <div class="flex items-center justify-end">
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Forgot Password -->
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Password Reset</h3>
            <p class="text-xs text-gray-600 mb-4">If you've forgotten your password, you can request a reset link</p>
            <a href="{{ route('customer-care.password.request') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Request Password Reset Link
            </a>
        </div>
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

