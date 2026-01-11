<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activity Logs - Super Admin</title>
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
        @include('super-admin.shared.sidebar', ['active' => 'activity-logs'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('super-admin.shared.header', ['title' => 'Activity Logs'])

            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-xs text-gray-500 uppercase mb-1">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-xs text-gray-500 uppercase mb-1">Today</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today']) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-xs text-gray-500 uppercase mb-1">This Week</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['this_week']) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-xs text-gray-500 uppercase mb-1">This Month</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['this_month']) }}</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <form method="GET" action="{{ route('super-admin.activity-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                            <select name="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Types</option>
                                <option value="admin" {{ request('user_type') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="doctor" {{ request('user_type') === 'doctor' ? 'selected' : '' }}>Doctor</option>
                                <option value="patient" {{ request('user_type') === 'patient' ? 'selected' : '' }}>Patient</option>
                                <option value="nurse" {{ request('user_type') === 'nurse' ? 'selected' : '' }}>Nurse</option>
                                <option value="canvasser" {{ request('user_type') === 'canvasser' ? 'selected' : '' }}>Canvasser</option>
                                <option value="customer_care" {{ request('user_type') === 'customer_care' ? 'selected' : '' }}>Customer Care</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                            <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Actions</option>
                                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                                <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                                <option value="viewed" {{ request('action') === 'viewed' ? 'selected' : '' }}>Viewed</option>
                                <option value="impersonated" {{ request('action') === 'impersonated' ? 'selected' : '' }}>Impersonated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                Filter
                            </button>
                        </div>
                    </form>
                    @if(request()->anyFilled(['user_type', 'action', 'start_date', 'end_date', 'search']))
                        <div class="mt-4">
                            <a href="{{ route('super-admin.activity-logs.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
                                Clear Filters
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Export Button -->
                <div class="mb-4 flex justify-end">
                    <a href="{{ route('super-admin.activity-logs.export', request()->all()) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Export CSV
                    </a>
                </div>

                <!-- Logs Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->created_at->format('M d, Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ ucfirst($log->user_type) }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ $log->user_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                {{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($log->model_type)
                                                {{ class_basename($log->model_type) }}
                                                @if($log->model_id)
                                                    #{{ $log->model_id }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->ip_address ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('super-admin.activity-logs.show', $log->id) }}" 
                                               class="text-purple-600 hover:text-purple-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No activity logs found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($logs, 'links'))
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</body>
</html>

