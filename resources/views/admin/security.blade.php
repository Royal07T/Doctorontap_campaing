<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Monitoring - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.shared.sidebar', ['active' => 'security'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="purple-gradient shadow-lg z-10">
                <div class="flex items-center justify-between px-6 py-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = true" class="lg:hidden text-white hover:text-purple-200 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center space-x-3">
                            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap" class="h-8 w-auto lg:hidden">
                            <h1 class="text-xl font-bold text-white">Security Monitoring</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Security Monitoring</h1>
                        <p class="mt-1 text-sm text-gray-600">Real-time security threat detection and monitoring</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-{{ $threatLevel['color'] }}-500"></div>
                            <span class="text-sm font-medium text-gray-700">{{ ucfirst($threatLevel['level']) }} Threat Level</span>
                        </div>
                        <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Threat Level Alert -->
        @if($threatLevel['level'] !== 'low')
        <div class="mb-6">
            <div class="bg-{{ $threatLevel['color'] }}-50 border-l-4 border-{{ $threatLevel['color'] }}-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-{{ $threatLevel['color'] }}-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-{{ $threatLevel['color'] }}-700">
                            <strong>{{ $threatLevel['message'] }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Security Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Critical Events</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $securityStats['critical_events'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">High Severity</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $securityStats['high_events'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Events</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $securityStats['total_events'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Unique IPs</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $securityStats['unique_ips'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attack Types -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Attack Types (24h)</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">SQL Injection</span>
                            <span class="text-sm font-medium text-red-600">{{ $securityStats['sql_injection_attempts'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">XSS Attempts</span>
                            <span class="text-sm font-medium text-orange-600">{{ $securityStats['xss_attempts'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Sensitive File Access</span>
                            <span class="text-sm font-medium text-yellow-600">{{ $securityStats['sensitive_file_access'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Rapid Requests</span>
                            <span class="text-sm font-medium text-blue-600">{{ $securityStats['rapid_requests'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Suspicious User Agents</span>
                            <span class="text-sm font-medium text-purple-600">{{ $securityStats['suspicious_user_agent'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Severity Distribution</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Critical</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $securityStats['critical_events'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">High</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $securityStats['high_events'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Medium</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $securityStats['medium_events'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600">Low</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $securityStats['low_events'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Security Events -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Security Events</h3>
                    <div class="flex space-x-2">
                        <select id="eventTypeFilter" class="text-sm border-gray-300 rounded-md">
                            <option value="all">All Types</option>
                            <option value="sql_injection_attempt">SQL Injection</option>
                            <option value="xss_attempt">XSS Attempts</option>
                            <option value="sensitive_file_access">File Access</option>
                            <option value="rapid_requests">Rapid Requests</option>
                            <option value="suspicious_user_agent">Suspicious UA</option>
                        </select>
                        <select id="severityFilter" class="text-sm border-gray-300 rounded-md">
                            <option value="all">All Severities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                        <button onclick="loadEvents()" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Filter
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="eventsTableBody" class="bg-white divide-y divide-gray-200">
                            @foreach($recentEvents as $event)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('M j, Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ucwords(str_replace('_', ' ', $event['event_type'])) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $event['severity'] === 'critical' ? 'red' : ($event['severity'] === 'high' ? 'orange' : ($event['severity'] === 'medium' ? 'yellow' : 'green')) }}-100 text-{{ $event['severity'] === 'critical' ? 'red' : ($event['severity'] === 'high' ? 'orange' : ($event['severity'] === 'medium' ? 'yellow' : 'green')) }}-800">
                                        {{ ucfirst($event['severity']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $event['ip'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="max-w-xs truncate">
                                        @if(isset($event['input_value']))
                                            {{ Str::limit($event['input_value'], 50) }}
                                        @elseif(isset($event['url']))
                                            {{ Str::limit($event['url'], 50) }}
                                        @else
                                            {{ Str::limit($event['user_agent'], 50) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="blockIp('{{ $event['ip'] }}')" class="text-red-600 hover:text-red-900">
                                        Block IP
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div id="blockIpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Block IP Address</h3>
            <form id="blockIpForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">IP Address</label>
                    <input type="text" id="blockIpAddress" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <input type="text" id="blockReason" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                    <select id="blockDuration" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="1">1 hour</option>
                        <option value="6">6 hours</option>
                        <option value="24">24 hours</option>
                        <option value="168">1 week</option>
                        <option value="720">1 month</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeBlockModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Block IP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

function loadEvents() {
    const eventType = document.getElementById('eventTypeFilter').value;
    const severity = document.getElementById('severityFilter').value;
    
    fetch(`/admin/security/events?type=${eventType}&severity=${severity}`)
        .then(response => {
            // Handle authentication errors
            if (response.status === 401) {
                return response.json().then(data => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    throw new Error(data.message || 'Authentication required');
                });
            }
            return response.json();
        })
        .then(data => {
            const tbody = document.getElementById('eventsTableBody');
            tbody.innerHTML = '';
            
            data.events.forEach(event => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${new Date(event.timestamp).toLocaleString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${event.event_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-${event.severity === 'critical' ? 'red' : event.severity === 'high' ? 'orange' : event.severity === 'medium' ? 'yellow' : 'green'}-100 text-${event.severity === 'critical' ? 'red' : event.severity === 'high' ? 'orange' : event.severity === 'medium' ? 'yellow' : 'green'}-800">
                            ${event.severity.charAt(0).toUpperCase() + event.severity.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${event.ip}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="max-w-xs truncate">
                            ${event.input_value ? event.input_value.substring(0, 50) : event.url ? event.url.substring(0, 50) : event.user_agent.substring(0, 50)}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="blockIp('${event.ip}')" class="text-red-600 hover:text-red-900">
                            Block IP
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        });
}

function blockIp(ip) {
    document.getElementById('blockIpAddress').value = ip;
    document.getElementById('blockIpModal').classList.remove('hidden');
}

function closeBlockModal() {
    document.getElementById('blockIpModal').classList.add('hidden');
}

document.getElementById('blockIpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const ip = document.getElementById('blockIpAddress').value;
    const reason = document.getElementById('blockReason').value;
    const duration = document.getElementById('blockDuration').value;
    
    fetch('/admin/security/block-ip', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            ip: ip,
            reason: reason,
            duration: duration
        })
    })
    .then(response => {
        // Handle authentication errors
        if (response.status === 401) {
            return response.json().then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                throw new Error(data.message || 'Authentication required');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            CustomAlert.success(data.message);
            closeBlockModal();
        } else {
            CustomAlert.error('Error: ' + data.message);
        }
    });
});

// Auto-refresh every 30 seconds
setInterval(refreshData, 30000);
</script>
            </main>
        </div>
    </div>
    @include('components.custom-alert-modal')
</body>
</html>
