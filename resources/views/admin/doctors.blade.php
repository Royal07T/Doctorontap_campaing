<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctors - Admin</title>
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
        @include('admin.shared.sidebar', ['active' => 'doctors'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Doctors'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Doctors</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-500">Registered</p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Available</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['available'] }}</p>
                        <p class="text-xs text-gray-500">Active</p>
                    </div>
                    <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Unavailable</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['unavailable'] }}</p>
                        <p class="text-xs text-gray-500">Inactive</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Consultations</p>
                        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['total_consultations'] }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            @if($stats['with_penalties'] > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">With Penalties</p>
                        <p class="text-xl font-bold text-red-600 mb-1">{{ $stats['with_penalties'] }}</p>
                        <p class="text-xs text-gray-500">Auto-Unavailable</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="mb-6 flex justify-end gap-2">
            <button onclick="openCampaignModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Send Campaign Alert
            </button>
            <button onclick="openAddDoctorModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New Doctor
            </button>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="mb-4 pb-4 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search & Filter
                </h2>
            </div>
            <form method="GET" action="{{ route('admin.doctors') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Name, email, phone, location..."
                           class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Availability</label>
                    <select name="is_available" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                        <option value="">All</option>
                        <option value="1" {{ request('is_available') == '1' ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ request('is_available') == '0' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Gender</label>
                    <select name="gender" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                        <option value="">All</option>
                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.doctors') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Doctors Cards -->
        <div class="space-y-4">
            @forelse($doctors as $doctor)
                <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                    <!-- Card Header -->
                    <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <div class="p-5 flex items-center justify-between">
                            <div class="flex-1 flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-xs bg-purple-600">
                                        {{ strtoupper(substr($doctor->first_name ?? 'D', 0, 1)) }}{{ strtoupper(substr($doctor->last_name ?? 'R', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <h3 class="text-sm font-semibold text-gray-900">{{ $doctor->full_name }}</h3>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full font-mono bg-gray-100 text-gray-700">
                                            #{{ $doctor->order }}
                                        </span>
                                        @if($doctor->is_auto_unavailable)
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold flex items-center gap-1" title="Auto-set unavailable due to {{ $doctor->missed_consultations_count ?? 0 }} missed consultations">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Penalty Applied
                                            </span>
                                        @elseif($doctor->missed_consultations_count > 0)
                                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold" title="{{ $doctor->missed_consultations_count }} missed consultation(s)">
                                                ⚠️ {{ $doctor->missed_consultations_count }} Missed
                                            </span>
                                        @endif
                                        @if($doctor->is_available)
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                                Available
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                                Unavailable
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600">{{ $doctor->specialization ?? 'No specialization' }} • NGN {{ number_format($doctor->consultation_fee, 2) }}</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                     :class="{ 'rotate-180': open }"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>

                    <!-- Dropdown Content -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         x-cloak
                         class="border-t border-gray-100 bg-gray-50"
                         style="display: none;">
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Gender</p>
                                    @if($doctor->gender)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $doctor->gender === 'Male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                            {{ $doctor->gender }}
                                        </span>
                                    @else
                                        <p class="text-xs text-gray-400">-</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Contact</p>
                                    @if($doctor->phone)
                                        <p class="text-xs text-gray-900">{{ $doctor->phone }}</p>
                                    @endif
                                    @if($doctor->email)
                                        <p class="text-xs text-gray-600">{{ $doctor->email }}</p>
                                    @endif
                                    @if(!$doctor->phone && !$doctor->email)
                                        <p class="text-xs text-gray-400">-</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Specialization</p>
                                    <p class="text-xs text-gray-900">{{ $doctor->specialization ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Location</p>
                                    @if($doctor->location)
                                        <span class="inline-flex px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                            {{ $doctor->location }}
                                        </span>
                                    @else
                                        <p class="text-xs text-gray-400">-</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Experience</p>
                                    <p class="text-xs text-gray-900">{{ $doctor->experience ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Languages</p>
                                    <p class="text-xs text-gray-900">{{ $doctor->languages ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Consultation Fee</p>
                                    <p class="text-xs font-bold text-emerald-600">NGN {{ number_format($doctor->consultation_fee, 2) }}</p>
                                </div>
                                @if($doctor->is_auto_unavailable || $doctor->missed_consultations_count > 0)
                                <div class="md:col-span-2">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Missed Consultations</p>
                                    <div class="p-3 rounded-lg {{ $doctor->is_auto_unavailable ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200' }}">
                                        @if($doctor->is_auto_unavailable)
                                            <div class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div>
                                                    <p class="text-xs font-semibold text-red-800 mb-1">Penalty Applied - Auto-Unavailable</p>
                                                    <p class="text-xs text-red-700">{{ $doctor->missed_consultations_count ?? 0 }} missed consultation(s). Threshold: 3.</p>
                                                    @if($doctor->unavailable_reason)
                                                        <p class="text-xs text-red-600 mt-1 italic">{{ $doctor->unavailable_reason }}</p>
                                                    @endif
                                                    @if($doctor->penalty_applied_at)
                                                        <p class="text-xs text-red-500 mt-1">Applied: {{ $doctor->penalty_applied_at->format('M d, Y h:i A') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div>
                                                    <p class="text-xs font-semibold text-yellow-800 mb-1">Warning: {{ $doctor->missed_consultations_count }} Missed Consultation(s)</p>
                                                    <p class="text-xs text-yellow-700">If this doctor misses 3 consultations, they will be automatically set to unavailable.</p>
                                                    @if($doctor->last_missed_consultation_at)
                                                        <p class="text-xs text-yellow-600 mt-1">Last missed: {{ $doctor->last_missed_consultation_at->format('M d, Y h:i A') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                                <a href="{{ route('admin.doctors.profile', $doctor->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a>
                                <button onclick='openEditDoctorModal(@json($doctor))'
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                                <button onclick="openSetPasswordModal({{ $doctor->id }}, '{{ addslashes($doctor->name) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Set Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="text-4xl mb-4">👨‍⚕️</div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">No Doctors Found</h3>
                    @if(request()->hasAny(['search', 'is_available', 'gender']))
                        <a href="{{ route('admin.doctors') }}" class="text-xs text-purple-600 hover:text-purple-800 mt-2 inline-block">
                            Clear filters
                        </a>
                    @endif
                </div>
            @endforelse

            <!-- Pagination -->
            @if($doctors->hasPages())
            <div class="mt-6">
                {{ $doctors->links() }}
            </div>
            @endif
        </div>

        <!-- Campaign Info -->
        <div class="mt-6 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl shadow-sm border border-purple-200 p-5">
            <div class="mb-3 pb-3 border-b border-purple-200">
                <h3 class="text-sm font-semibold text-purple-900 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Campaign Pricing Information
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-700 mb-1.5"><span class="font-semibold">Campaign:</span> <span class="text-purple-700">"Consult a Doctor and Pay Later"</span></p>
                    <p class="text-xs text-gray-700 mb-1.5"><span class="font-semibold">Standard Fee:</span> <span class="text-emerald-600 text-sm font-bold">NGN 3,000.00</span></p>
                    <p class="text-xs text-gray-600 leading-relaxed">All consultations are charged at NGN 3,000.00 regardless of doctor or specialization.</p>
                </div>
                <div class="bg-white rounded-lg p-3 border border-gray-200">
                    <p class="text-sm font-semibold text-purple-700 mb-2">Quick Stats</p>
                    <ul class="text-xs space-y-1">
                        <li class="flex justify-between"><span class="text-gray-600">Total Doctors:</span> <strong>{{ $stats['total'] }}</strong></li>
                        <li class="flex justify-between"><span class="text-gray-600">Currently Available:</span> <strong class="text-emerald-600">{{ $stats['available'] }}</strong></li>
                        <li class="flex justify-between"><span class="text-gray-600">Total Consultations:</span> <strong class="text-blue-600">{{ $stats['total_consultations'] }}</strong></li>
                        <li class="flex justify-between border-t border-gray-200 pt-1 mt-1"><span class="text-gray-600">Potential Revenue:</span> <strong class="text-purple-600">NGN {{ number_format($stats['total_consultations'] * 3000, 2) }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Password Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="purple-gradient text-white px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Set Doctor Password
                </h2>
                <button onclick="closePasswordModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="passwordForm" method="POST" class="p-6">
                @csrf
                <input type="hidden" id="passwordDoctorId" name="doctor_id">

                <div id="passwordFormMessage" class="hidden mb-4 p-3 rounded-lg"></div>

                <p class="text-sm text-gray-600 mb-4">Setting password for: <strong id="passwordDoctorName" class="text-gray-900"></strong></p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-red-500">*</span></label>
                        <input type="password" id="newPassword" name="password" required minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Minimum 8 characters">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" id="confirmPassword" name="password_confirmation" required minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Re-enter password">
                    </div>
                </div>

                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closePasswordModal()"
                            class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" id="passwordSubmitBtn"
                            class="flex-1 px-4 py-2.5 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="passwordSubmitBtnText">Set Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Doctor Modal -->
    <div id="doctorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="purple-gradient text-white px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h2 id="modalTitle" class="text-xl font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Doctor
                </h2>
                <button onclick="closeDoctorModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="doctorForm" method="POST" class="p-6">
                @csrf
                <input type="hidden" id="doctorId" name="id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Error/Success Messages -->
                <div id="formMessage" class="hidden mb-4 p-3 rounded-lg"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Dr. John Smith">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="doctor@example.com">
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="+234 800 000 0000">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                        <select id="gender" name="gender" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
                        <input type="text" id="specialization" name="specialization"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="General Practitioner">
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Lagos, Nigeria">
                    </div>

                    <!-- Experience -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                        <input type="text" id="experience" name="experience"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="10+ years">
                    </div>

                    <!-- Languages -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Languages</label>
                        <input type="text" id="languages" name="languages"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="English, Yoruba, Igbo">
                    </div>

                    <!-- Consultation Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Consultation Fee (NGN)</label>
                        <input type="number" id="consultation_fee" name="consultation_fee" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="3000.00">
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                        <input type="number" id="order" name="order"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="0" value="0">
                        <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                    </div>

                    <!-- MDCN License Current -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">MDCN License Current</label>
                        <select id="mdcn_license_current" name="mdcn_license_current"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Is the doctor's MDCN license current?</p>
                    </div>

                    <!-- Availability -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="is_available" name="is_available" value="1" checked
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-sm font-medium text-gray-700">Doctor is available for consultations</span>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeDoctorModal()"
                            class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            class="flex-1 px-4 py-2.5 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="submitBtnText">Save Doctor</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open Add Doctor Modal
        function openAddDoctorModal() {
            document.getElementById('modalTitle').textContent = 'Add New Doctor';
            document.getElementById('doctorForm').reset();
            document.getElementById('doctorId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('mdcn_license_current').value = 'no';
            document.getElementById('is_available').checked = true;
            document.getElementById('doctorForm').action = '{{ route('admin.doctors.store') }}';
            document.getElementById('submitBtnText').textContent = 'Save Doctor';
            document.getElementById('formMessage').classList.add('hidden');
            document.getElementById('doctorModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Open Edit Doctor Modal
        function openEditDoctorModal(doctor) {
            document.getElementById('modalTitle').innerHTML = `
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Doctor
            `;
            document.getElementById('doctorId').value = doctor.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('name').value = doctor.name || '';
            document.getElementById('email').value = doctor.email || '';
            document.getElementById('phone').value = doctor.phone || '';
            document.getElementById('gender').value = doctor.gender || '';
            document.getElementById('specialization').value = doctor.specialization || '';
            document.getElementById('location').value = doctor.location || '';
            document.getElementById('experience').value = doctor.experience || '';
            document.getElementById('languages').value = doctor.languages || '';
            document.getElementById('consultation_fee').value = doctor.consultation_fee || '';
            document.getElementById('order').value = doctor.order || 0;
            document.getElementById('mdcn_license_current').value = doctor.mdcn_license_current ? 'yes' : 'no';
            document.getElementById('is_available').checked = doctor.is_available;
            document.getElementById('doctorForm').action = `/admin/doctors/${doctor.id}`;
            document.getElementById('submitBtnText').textContent = 'Update Doctor';
            document.getElementById('formMessage').classList.add('hidden');
            document.getElementById('doctorModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close Modal
        function closeDoctorModal() {
            document.getElementById('doctorModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Handle Form Submission
        document.getElementById('doctorForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const formMessage = document.getElementById('formMessage');

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Saving...';
            formMessage.classList.add('hidden');

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Show success message
                    formMessage.className = 'mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200';
                    formMessage.textContent = data.message || 'Doctor saved successfully!';
                    formMessage.classList.remove('hidden');

                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Handle validation errors (422) or other errors
                    let errorMessage = data.message || 'An error occurred. Please try again.';

                    // If there are validation errors, display them
                    if (data.errors) {
                        const errorsList = Object.values(data.errors).flat();
                        errorMessage = errorsList.join('<br>');
                    }

                    formMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                    formMessage.innerHTML = errorMessage;
                    formMessage.classList.remove('hidden');

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtnText.textContent = document.getElementById('doctorId').value ? 'Update Doctor' : 'Save Doctor';
                }
            } catch (error) {
                console.error('Error:', error);
                formMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                formMessage.textContent = 'A network error occurred. Please try again.';
                formMessage.classList.remove('hidden');

                // Re-enable button
                submitBtn.disabled = false;
                submitBtnText.textContent = document.getElementById('doctorId').value ? 'Update Doctor' : 'Save Doctor';
            }
        });

        // Close modal when clicking outside
        document.getElementById('doctorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDoctorModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('doctorModal').style.display === 'flex') {
                closeDoctorModal();
            }
            if (e.key === 'Escape' && document.getElementById('passwordModal').style.display === 'flex') {
                closePasswordModal();
            }
        });

        // ── Set Password Modal ──
        function openSetPasswordModal(doctorId, doctorName) {
            document.getElementById('passwordDoctorId').value = doctorId;
            document.getElementById('passwordDoctorName').textContent = doctorName;
            document.getElementById('passwordForm').reset();
            document.getElementById('passwordDoctorId').value = doctorId;
            document.getElementById('passwordFormMessage').classList.add('hidden');
            document.getElementById('passwordModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) closePasswordModal();
        });

        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('passwordSubmitBtn');
            const btnText = document.getElementById('passwordSubmitBtnText');
            const msg = document.getElementById('passwordFormMessage');
            const pw = document.getElementById('newPassword').value;
            const pwc = document.getElementById('confirmPassword').value;

            if (pw !== pwc) {
                msg.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                msg.textContent = 'Passwords do not match.';
                msg.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btnText.textContent = 'Saving...';
            msg.classList.add('hidden');

            try {
                const doctorId = document.getElementById('passwordDoctorId').value;
                const formData = new FormData(this);
                const response = await fetch(`/admin/doctors/${doctorId}/set-password`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    msg.className = 'mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200';
                    msg.textContent = data.message || 'Password set successfully!';
                    msg.classList.remove('hidden');
                    setTimeout(() => closePasswordModal(), 1500);
                } else {
                    let errorMessage = data.message || 'An error occurred.';
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).flat().join('<br>');
                    }
                    msg.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                    msg.innerHTML = errorMessage;
                    msg.classList.remove('hidden');
                }
            } catch (error) {
                msg.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                msg.textContent = 'A network error occurred. Please try again.';
                msg.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Set Password';
            }
        });
    </script>
            </main>
        </div>
    </div>

    <!-- Campaign Notification Modal -->
    <div id="campaignModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-auto max-h-[95vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="purple-gradient text-white px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send Campaign Alert to All Doctors
                </h2>
                <button onclick="closeCampaignModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <!-- Info Banner -->
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-4 rounded">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-purple-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-purple-800 mb-1">About Campaign Alerts</h3>
                            <p class="text-sm text-purple-700">This will send an automated email notification to all <strong>{{ $stats['available'] }} active doctors</strong> informing them about the upcoming campaign. You can customize the details below.</p>
                        </div>
                    </div>
                </div>

                <form id="campaignForm">
                    @csrf
                    <!-- Error/Success Messages -->
                    <div id="campaignMessage" class="hidden mb-4 p-3 rounded-lg"></div>

                    <div class="space-y-4">
                        <!-- Campaign Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                            <input type="text" id="campaign_name" name="campaign_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                   placeholder="Healthcare Access Campaign"
                                   value="Healthcare Access Campaign">
                            <p class="text-xs text-gray-500 mt-1">The name of the campaign to display in the email</p>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="text" id="start_date" name="start_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                   placeholder="e.g., October 20, 2025"
                                   value="{{ date('F d, Y') }}">
                            <p class="text-xs text-gray-500 mt-1">When the campaign starts (formatted date)</p>
                        </div>

                        <!-- End Date (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date (Optional)</label>
                            <input type="text" id="end_date" name="end_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                   placeholder="e.g., December 31, 2025">
                            <p class="text-xs text-gray-500 mt-1">When the campaign ends (optional)</p>
                        </div>

                        <!-- Description (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Details (Optional)</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                      placeholder="Add any additional information about the campaign..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Extra information to include in the email (optional)</p>
                        </div>

                        <!-- Email Message Body -->
                        <div class="border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Email Message Body
                            </label>
                            <textarea id="email_body" name="email_body" rows="12"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 font-mono text-sm"
                                      placeholder="Edit the email message that will be sent to doctors...">We hope this message finds you well! We're excited to inform you about an upcoming campaign at DoctorOnTap.

What This Means for You:
• Increased Patient Volume: Expect a higher number of consultation requests
• Flexible Scheduling: Please update your availability to accommodate more patients
• Enhanced Opportunities: More consultations mean better earning potential
• Community Impact: Help us reach more patients in need of medical care

Action Required:
• Ensure your profile and availability are up to date
• Check your notification settings
• Be prepared for increased consultation requests
• Maintain quick response times for optimal patient care

If you have any questions or need support, our team is here to help. Feel free to reach out at any time.

Thank you for being a valued member of the DoctorOnTap medical team. Together, we're making healthcare more accessible!</textarea>
                            <p class="text-xs text-gray-500 mt-1">Customize the main message content that doctors will see in the email. Use line breaks for formatting.</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex gap-3 justify-end border-t pt-4">
                        <button type="button" onclick="closeCampaignModal()"
                                class="px-5 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="campaignSubmitBtn"
                                class="px-5 py-2.5 purple-gradient text-white font-semibold rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span id="campaignBtnText">Send to All Doctors</span>
                            <svg class="animate-spin w-5 h-5 hidden" id="campaignBtnLoading" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Campaign Modal Functions
        function openCampaignModal() {
            document.getElementById('campaignModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.getElementById('campaignMessage').classList.add('hidden');
            document.getElementById('campaignForm').reset();
            // Reset to default values
            document.getElementById('campaign_name').value = 'Healthcare Access Campaign';
            document.getElementById('start_date').value = '{{ date('F d, Y') }}';
            document.getElementById('email_body').value = `We hope this message finds you well! We're excited to inform you about an upcoming campaign at DoctorOnTap.

What This Means for You:
• Increased Patient Volume: Expect a higher number of consultation requests
• Flexible Scheduling: Please update your availability to accommodate more patients
• Enhanced Opportunities: More consultations mean better earning potential
• Community Impact: Help us reach more patients in need of medical care

Action Required:
• Ensure your profile and availability are up to date
• Check your notification settings
• Be prepared for increased consultation requests
• Maintain quick response times for optimal patient care

If you have any questions or need support, our team is here to help. Feel free to reach out at any time.

Thank you for being a valued member of the DoctorOnTap medical team. Together, we're making healthcare more accessible!`;
        }

        function closeCampaignModal() {
            document.getElementById('campaignModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Handle Campaign Form Submission
        document.getElementById('campaignForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('campaignSubmitBtn');
            const btnText = document.getElementById('campaignBtnText');
            const btnLoading = document.getElementById('campaignBtnLoading');
            const campaignMessage = document.getElementById('campaignMessage');

            // Disable button and show loading
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            campaignMessage.classList.add('hidden');

            try {
                const formData = new FormData(this);
                const response = await fetch('{{ route('admin.doctors.send-campaign') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    campaignMessage.className = 'mb-4 p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200';
                    campaignMessage.innerHTML = `
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-emerald-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-semibold">${data.message}</p>
                                ${data.details ? `<p class="text-sm mt-1">Sent: ${data.details.emails_sent} | Failed: ${data.details.emails_failed}</p>` : ''}
                            </div>
                        </div>
                    `;
                    campaignMessage.classList.remove('hidden');

                    // Close modal after 3 seconds
                    setTimeout(() => {
                        closeCampaignModal();
                    }, 3000);
                } else {
                    campaignMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                    campaignMessage.innerHTML = `
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>${data.message || 'Failed to send campaign notifications.'}</p>
                        </div>
                    `;
                    campaignMessage.classList.remove('hidden');

                    // Re-enable button
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                campaignMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                campaignMessage.textContent = 'A network error occurred. Please try again.';
                campaignMessage.classList.remove('hidden');

                // Re-enable button
                submitBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        });

        // Close campaign modal when clicking outside
        document.getElementById('campaignModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCampaignModal();
            }
        });

        // Close campaign modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('campaignModal').style.display === 'flex') {
                closeCampaignModal();
            }
        });
    </script>

    <!-- Overlay for mobile sidebar -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
         style="display: none;"></div>
    @include('admin.shared.preloader')
</body>
</html>

