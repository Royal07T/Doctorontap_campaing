<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Command Center - DoctorOnTap Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); }
        .stat-card { transition: all 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'dashboard'])

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('admin.shared.header', ['title' => 'Command Center'])

            <main class="flex-1 overflow-y-auto p-6">
                {{-- Page Title --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Command Center</h2>
                        <p class="text-sm text-gray-500">Real-time operations overview</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ now()->format('l, F j, Y') }}</span>
                </div>

                {{-- 4 Stat Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    {{-- Active Caregivers --}}
                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+12.5%</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['active_caregivers']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Active Caregivers</p>
                    </div>
                    {{-- Patients Online --}}
                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span> LIVE
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_patients']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total Patients</p>
                    </div>
                    {{-- Pending Matchings --}}
                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Urgent</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_matchings'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Pending Matchings</p>
                    </div>
                    {{-- Monthly Revenue --}}
                    <div class="stat-card bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-medium text-gray-500">Monthly</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['monthly_revenue'], 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Monthly Revenue</p>
                    </div>
                </div>

                {{-- Main Grid: Left (Queue + Matching) | Right (Payments + Comms) --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Left Column (2/3) --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- Operational Queue --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900">Operational Queue</h3>
                                    @if($operationalQueue->count() > 0)
                                    <span class="text-[10px] font-bold text-white bg-red-500 px-2 py-0.5 rounded-full">{{ $operationalQueue->count() }} Critical</span>
                                    @endif
                                </div>
                                <a href="{{ route('admin.consultations') }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">View All →</a>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @forelse($operationalQueue as $item)
                                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xs font-bold">
                                            {{ substr($item->patient->name ?? 'P', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $item->patient->name ?? 'Unknown Patient' }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $item->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full
                                            @if($item->status === 'pending') bg-amber-100 text-amber-700
                                            @elseif($item->status === 'completed') bg-green-100 text-green-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                        <a href="{{ route('admin.consultations') }}" class="text-xs text-purple-600 hover:underline">View</a>
                                    </div>
                                </div>
                                @empty
                                <div class="px-5 py-8 text-center text-sm text-gray-400">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    No pending items in queue
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Caregiver Matching --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Caregiver Matching</h3>
                                <a href="{{ route('admin.care-givers') }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">Database →</a>
                            </div>
                            <div class="p-5">
                                @if($availableCaregivers->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($availableCaregivers->take(4) as $cg)
                                    <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-purple-200 transition">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ substr($cg->name ?? 'C', 0, 1) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-800 truncate">{{ $cg->name }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $cg->specialization ?? 'General Care' }}</p>
                                        </div>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex-shrink-0">Available</span>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <p class="text-sm text-gray-400 text-center py-4">No available caregivers</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Right Column (1/3) --}}
                    <div class="space-y-6">
                        {{-- Payment Hub --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-5 py-4 border-b border-gray-100">
                                <h3 class="text-sm font-bold text-gray-900">Payment Hub</h3>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Total Outstanding</span>
                                    <span class="text-sm font-bold text-gray-900">₦{{ number_format($paymentStats['total_outstanding'], 0) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Processed Today</span>
                                    <span class="text-sm font-bold text-green-600">₦{{ number_format($paymentStats['processed_today'], 0) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Failed Charges</span>
                                    <span class="text-sm font-bold text-red-600">{{ $paymentStats['failed_charges'] }}</span>
                                </div>
                                <a href="{{ route('admin.financial-hub.index') }}" class="block text-center text-xs text-purple-600 hover:text-purple-800 font-medium mt-2">View Financial Hub →</a>
                            </div>
                        </div>

                        {{-- Communication Widget --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Communication</h3>
                                <a href="{{ route('admin.comms-center') }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">Open →</a>
                            </div>
                            <div class="p-5 space-y-3">
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-green-50 border border-green-100">
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-gray-800">WhatsApp</p>
                                        <p class="text-[11px] text-gray-500">Connected & Active</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-gray-800">SMS Gateway</p>
                                        <p class="text-[11px] text-gray-500">Active</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- New Intake CTA --}}
                        <a href="{{ route('admin.patients') }}" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl purple-gradient text-white text-sm font-semibold shadow-lg hover:shadow-xl transition-all hover:scale-[1.02]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            New Intake
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    @include('admin.shared.preloader')
</body>
</html>

