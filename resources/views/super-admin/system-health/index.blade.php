<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Health - Super Admin</title>
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
        @include('super-admin.shared.sidebar', ['active' => 'system-health'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'System Health'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-6xl mx-auto">
                    <!-- Real-time Data Indicator -->
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-sm font-medium text-blue-800">Real-time system health checks</span>
                        </div>
                        <span class="text-xs text-blue-600">Last checked: {{ now()->format('H:i:s') }}</span>
                    </div>

                    <!-- Health Status Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <!-- Database -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Database</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $health['database']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($health['database']['status']) }}
                                </span>
                            </div>
                            @if(isset($health['database']['response_time_ms']))
                                <p class="text-sm text-gray-600">Response Time: {{ $health['database']['response_time_ms'] }}ms</p>
                            @endif
                            @if(isset($health['database']['error']))
                                <p class="text-sm text-red-600 mt-2">{{ $health['database']['error'] }}</p>
                            @endif
                        </div>

                        <!-- Cache -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Cache</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $health['cache']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($health['cache']['status']) }}
                                </span>
                            </div>
                            @if(isset($health['cache']['error']))
                                <p class="text-sm text-red-600">{{ $health['cache']['error'] }}</p>
                            @endif
                        </div>

                        <!-- Queue -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Queue</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $health['queue']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : ($health['queue']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($health['queue']['status']) }}
                                </span>
                            </div>
                            @if(isset($health['queue']['queue_size']))
                                <p class="text-sm text-gray-600">Queue Size: {{ number_format($health['queue']['queue_size']) }}</p>
                            @endif
                            @if(isset($health['queue']['failed_jobs_24h']))
                                <p class="text-sm text-gray-600">Failed (24h): {{ number_format($health['queue']['failed_jobs_24h']) }}</p>
                            @endif
                        </div>

                        <!-- Storage -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Storage</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $health['storage']['status'] === 'healthy' ? 'bg-green-100 text-green-800' : ($health['storage']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($health['storage']['status']) }}
                                </span>
                            </div>
                            @if(isset($health['storage']['usage_percent']))
                                <div class="mt-2">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">Usage</span>
                                        <span class="text-gray-900 font-semibold">{{ number_format($health['storage']['usage_percent'], 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $health['storage']['usage_percent'] > 90 ? 'bg-red-600' : ($health['storage']['usage_percent'] > 75 ? 'bg-yellow-500' : 'bg-green-600') }}" 
                                             style="width: {{ $health['storage']['usage_percent'] }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ number_format($health['storage']['used_gb'], 2) }} GB / {{ number_format($health['storage']['total_gb'], 2) }} GB
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Metrics -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Queue Metrics</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 mb-1">Queue Size</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['queue_size']) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 mb-1">Failed Jobs (24h)</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['failed_jobs_24h']) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500 mb-1">Cache Driver</p>
                                <p class="text-lg font-bold text-gray-900">{{ ucfirst($metrics['cache_hits']['driver'] ?? 'unknown') }}</p>
                                @if(isset($metrics['cache_hits']['hits']) && $metrics['cache_hits']['hits'] > 0)
                                    <p class="text-xs text-gray-500 mt-1">Hits: {{ number_format($metrics['cache_hits']['hits']) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

