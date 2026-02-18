<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Reports - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }
        .stat-card { transition: all 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false, period: 'monthly' }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'admin-reports'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Admin Reports'])

            <main class="flex-1 overflow-y-auto p-6">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Admin Reports</h2>
                        <p class="text-sm text-gray-500">Performance analytics & operational insights</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex bg-white rounded-lg border border-gray-200 text-xs">
                            <button @click="period='monthly'" :class="period==='monthly' ? 'purple-gradient text-white' : 'text-gray-600 hover:bg-gray-50'" class="px-3 py-1.5 rounded-lg font-medium transition">Last 30 Days</button>
                            <button @click="period='quarterly'" :class="period==='quarterly' ? 'purple-gradient text-white' : 'text-gray-600 hover:bg-gray-50'" class="px-3 py-1.5 rounded-lg font-medium transition">Quarterly</button>
                            <button @click="period='annual'" :class="period==='annual' ? 'purple-gradient text-white' : 'text-gray-600 hover:bg-gray-50'" class="px-3 py-1.5 rounded-lg font-medium transition">Annual</button>
                        </div>
                        <button class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Export PDF
                        </button>
                    </div>
                </div>

                {{-- 4 Stat Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+8.1%</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['operationalEfficiency'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Operational Efficiency</p>
                    </div>

                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+18.2%</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['totalRevenue'], 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total Revenue</p>
                    </div>

                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+4.2%</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['clinicalOutcomeRate'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Clinical Outcome Rate</p>
                    </div>

                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Action</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pendingSettlements'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Pending Settlements</p>
                    </div>
                </div>

                {{-- Charts Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    {{-- Revenue Growth Chart Area --}}
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Revenue Growth</h3>
                        <div class="h-48 bg-gradient-to-br from-purple-50 to-blue-50 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-10 h-10 mx-auto text-purple-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                <p class="text-xs text-purple-400">Revenue trend chart</p>
                                <p class="text-lg font-bold text-purple-700 mt-1">₦{{ number_format($stats['totalRevenue'], 0) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Shift Completion Rates --}}
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Consultation Completion Rates</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Scheduled</span><span>100%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-purple-600 h-2 rounded-full" style="width: 100%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Completed On-Time</span><span>{{ $stats['operationalEfficiency'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['operationalEfficiency'] }}%"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Pending</span><span>{{ 100 - $stats['operationalEfficiency'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2"><div class="bg-amber-400 h-2 rounded-full" style="width: {{ 100 - $stats['operationalEfficiency'] }}%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Operational Logs Table --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900">Recent Operational Logs</h3>
                        <a href="{{ route('admin.consultations') }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">View All →</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Doctor</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($recentLogs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-[10px] font-bold">
                                                {{ substr($log->first_name ?? 'P', 0, 1) }}
                                            </div>
                                            <span class="font-medium text-gray-800">{{ $log->first_name }} {{ $log->last_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">{{ $log->doctor->name ?? 'Unassigned' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full
                                            @if($log->status === 'completed') bg-green-100 text-green-700
                                            @elseif($log->status === 'pending') bg-amber-100 text-amber-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $log->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">No logs found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Insight Corner --}}
                <div class="mt-6 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl border border-purple-100 p-5">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Insight Corner</h4>
                            <p class="text-xs text-gray-600 mt-1">Operational efficiency has improved by 8.1% this period. Consider increasing caregiver staffing during peak hours (9AM-12PM) to further optimize patient wait times.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('admin.shared.preloader')
</body>
</html>
