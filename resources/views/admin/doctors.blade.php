<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctors - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar -->
    <nav class="purple-gradient shadow-lg">
        <div class="container mx-auto px-5 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('img/dashlogo.png') }}" alt="DoctorOnTap Logo" class="h-10 w-auto">
                    </a>
                    <span class="text-white font-bold text-xl">Doctors</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-white text-sm">👤 {{ Auth::guard('admin')->user()->name }}</span>
                    <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-purple-200 transition-colors">Dashboard</a>
                    <a href="{{ route('admin.consultations') }}" class="text-white hover:text-purple-200 transition-colors">Consultations</a>
                    <a href="{{ route('admin.payments') }}" class="text-white hover:text-purple-200 transition-colors">Payments</a>
                    <a href="{{ url('/') }}" class="text-white hover:text-purple-200 transition-colors">View Website</a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-white hover:text-red-300 transition-colors font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-5 py-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-purple-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total Doctors</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-emerald-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Available</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $stats['available'] }}</p>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-rose-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Unavailable</p>
                        <p class="text-2xl font-bold text-rose-600">{{ $stats['unavailable'] }}</p>
                    </div>
                    <div class="bg-rose-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-blue-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Consultations</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['total_consultations'] }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
            <form method="GET" action="{{ route('admin.doctors') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Name, email, phone, location..."
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Availability</label>
                    <select name="is_available" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All</option>
                        <option value="1" {{ request('is_available') == '1' ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ request('is_available') == '0' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Gender</label>
                    <select name="gender" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 bg-white">
                        <option value="">All</option>
                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-md transition-all">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.doctors') }}" class="px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-all">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Doctors Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-purple-600 text-white">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">#</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Name</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Gender</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Contact</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Specialization</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Location</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Experience</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Languages</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Fee</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($doctors as $doctor)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-700 font-semibold">{{ $doctor->order }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-800">{{ $doctor->name }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($doctor->gender)
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $doctor->gender === 'Male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                        {{ $doctor->gender === 'Male' ? 'Male' : 'Female' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs">
                                    @if($doctor->phone)
                                        <div class="text-gray-700">{{ $doctor->phone }}</div>
                                    @endif
                                    @if($doctor->email)
                                        <div class="text-gray-500">{{ $doctor->email }}</div>
                                    @endif
                                    @if(!$doctor->phone && !$doctor->email)
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $doctor->specialization ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($doctor->location)
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full font-medium">
                                        {{ $doctor->location }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $doctor->experience ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">{{ $doctor->languages ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-emerald-600">NGN {{ number_format($doctor->consultation_fee, 2) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($doctor->is_available)
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">
                                        Available
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full text-xs font-semibold">
                                        Unavailable
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                <div class="text-6xl mb-4">👨‍⚕️</div>
                                <p class="text-xl font-semibold">No doctors found</p>
                                @if(request()->hasAny(['search', 'is_available', 'gender']))
                                    <a href="{{ route('admin.doctors') }}" class="text-purple-600 hover:text-purple-800 mt-2 inline-block">
                                        Clear filters
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($doctors->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $doctors->links() }}
            </div>
            @endif
        </div>

        <!-- Campaign Info -->
        <div class="mt-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-sm border border-purple-200 p-5">
            <h3 class="text-lg font-bold text-purple-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Campaign Pricing Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                <div>
                    <p class="text-sm"><span class="font-semibold">Campaign:</span> <span class="text-purple-700">"Consult a Doctor and Pay Later"</span></p>
                    <p class="text-sm mt-1.5"><span class="font-semibold">Standard Fee:</span> <span class="text-emerald-600 text-xl font-bold">NGN 3,000.00</span></p>
                    <p class="text-xs text-gray-600 mt-1">All consultations are charged at NGN 3,000.00 regardless of doctor or specialization.</p>
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
</body>
</html>

