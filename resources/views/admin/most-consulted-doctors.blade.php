<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Most Consulted Doctors - Admin</title>
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
            @include('admin.shared.header', ['title' => 'Most Consulted Doctors'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Doctors</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['total_doctors'] }}</p>
                                <p class="text-xs text-gray-500">Registered</p>
                            </div>
                            <div class="bg-purple-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Consultations</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['total_consultations']) }}</p>
                                <p class="text-xs text-gray-500">All Time</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Reviews</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['total_reviews']) }}</p>
                                <p class="text-xs text-gray-500">Published</p>
                            </div>
                            <div class="bg-yellow-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-emerald-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Average Rating</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ number_format($stats['avg_rating'], 1) }}</p>
                                <p class="text-xs text-gray-500">⭐ Stars</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filter Doctors
                        </h2>
                    </div>
                    <form method="GET" action="{{ route('admin.most-consulted-doctors') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Specialization</label>
                            <select name="specialization" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                                <option value="">All Specializations</option>
                                @foreach($specializations as $spec)
                                    <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Approval Status</label>
                            <select name="is_approved" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                                <option value="">All</option>
                                <option value="1" {{ request('is_approved') == '1' ? 'selected' : '' }}>Approved</option>
                                <option value="0" {{ request('is_approved') == '0' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('admin.most-consulted-doctors') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                @if($doctors->count() > 0)
                    <!-- Doctors Table -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Rank</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Doctor</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wide">Specialization</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Consultations</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Reviews</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Rating</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($doctors as $index => $doctor)
                                        @php
                                            $rank = ($doctors->currentPage() - 1) * $doctors->perPage() + $index + 1;
                                            $avgRating = $doctor->avg_rating ?? 0;
                                            $reviewsCount = $doctor->published_reviews_count ?? 0;
                                            $consultationsCount = $doctor->consultations_count ?? 0;
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-2">
                                                    @if($rank <= 3)
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white
                                                            @if($rank == 1) bg-yellow-500
                                                            @elseif($rank == 2) bg-gray-400
                                                            @else bg-amber-600
                                                            @endif">
                                                            {{ $rank }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs font-semibold text-gray-600">#{{ $rank }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    @if($doctor->photo_url)
                                                        <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-purple-100">
                                                    @else
                                                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center border-2 border-purple-200">
                                                            <span class="text-sm font-bold text-purple-600">{{ substr($doctor->name, 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900">{{ $doctor->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $doctor->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="text-xs font-medium text-purple-600">{{ $doctor->specialization ?? 'N/A' }}</span>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <span class="text-sm font-bold text-gray-900">{{ number_format($consultationsCount) }}</span>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <span class="text-sm font-semibold text-gray-700">{{ number_format($reviewsCount) }}</span>
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                @if($reviewsCount > 0)
                                                    <div class="flex items-center justify-center gap-1">
                                                        <div class="flex items-center text-yellow-400">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= floor($avgRating))
                                                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                    </svg>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <span class="text-xs font-semibold text-gray-700">{{ number_format($avgRating, 1) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400">No reviews</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-center">
                                                <a href="{{ route('admin.doctors.profile', $doctor->id) }}" 
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $doctors->links() }}
                    </div>
                @else
                    <!-- No Doctors Found -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Doctors Found</h3>
                        <p class="text-sm text-gray-500">Try adjusting your filters or check back later.</p>
                    </div>
                @endif
            </main>
        </div>
    </div>
</body>
</html>

