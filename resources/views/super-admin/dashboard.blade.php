<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Dashboard - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false, pageLoading: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('super-admin.shared.sidebar', ['active' => 'dashboard'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('super-admin.shared.header', ['title' => 'Super Admin Dashboard'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
                    <!-- Total Admins -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Admins</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['total_admins']) }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Doctors -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Doctors</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['total_doctors']) }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Patients -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Patients</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['total_patients']) }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Consultations -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-amber-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Consultations</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['total_consultations']) }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Revenue</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">₦{{ number_format($stats['total_revenue'], 2) }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Today -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Activity</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['activity_logs_today']) }}</p>
                                <p class="text-xs text-gray-500">Today</p>
                            </div>
                            <div class="bg-indigo-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health & Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- System Health -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">System Health</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $systemHealth['database']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($systemHealth['database']['status']) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Cache</span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $systemHealth['cache']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($systemHealth['cache']['status']) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Queue</span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $systemHealth['queue']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : ($systemHealth['queue']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($systemHealth['queue']['status']) }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('super-admin.system-health.index') }}" class="mt-4 inline-block text-sm text-purple-600 hover:text-purple-800 font-medium">
                            View Details →
                        </a>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Recent Activity</h2>
                            <a href="{{ route('super-admin.activity-logs.index') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                View All →
                            </a>
                        </div>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @forelse($recentActivities as $activity)
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ ucfirst($activity->action) }}
                                            @if($activity->model_type)
                                                {{ class_basename($activity->model_type) }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $activity->user_type }} • {{ $activity->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No recent activity</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Critical Events -->
                @if($criticalEvents->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
                    <h2 class="text-lg font-bold text-red-900 mb-4">Critical Events (Last 24 Hours)</h2>
                    <div class="space-y-3">
                        @foreach($criticalEvents as $event)
                            <div class="flex items-start space-x-3 p-3 bg-red-50 rounded-lg border border-red-200">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-red-900">{{ ucfirst($event->action) }}</p>
                                    <p class="text-xs text-red-700">{{ $event->created_at->format('M d, Y H:i:s') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </main>
        </div>
    </div>
</body>
</html>

