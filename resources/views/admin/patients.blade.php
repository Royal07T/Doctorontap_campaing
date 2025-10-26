<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Records - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.shared.sidebar', ['active' => 'patients'])

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
                            <h1 class="text-xl font-bold text-white">Patient Records</h1>
                        </div>
                    </div>
                    <div class="text-white text-sm">
                        <span class="font-medium">Total Patients:</span> 
                        <span class="bg-white/20 px-3 py-1 rounded-full font-bold">{{ $patients->total() }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Search & Filter Bar -->
                <div class="bg-white rounded-xl shadow-md p-5 mb-6">
                    <form method="GET" action="{{ route('admin.patients') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Patients</label>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search by name, email, phone, or reference..." 
                                   value="{{ request('search') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>

                        <!-- Gender Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">All Genders</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition font-medium">
                                Apply Filters
                            </button>
                            <a href="{{ route('admin.patients') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Patients Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Patient Info</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Demographics</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Latest Visit</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Total Visits</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($patients as $patient)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Patient Info -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm
                                                        {{ $patient->gender == 'male' ? 'bg-blue-500' : ($patient->gender == 'female' ? 'bg-pink-500' : 'bg-gray-500') }}">
                                                {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $patient->first_name }} {{ $patient->last_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $patient->reference }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="text-gray-900 mb-1">📧 {{ $patient->email }}</p>
                                            <p class="text-gray-600">📱 {{ $patient->mobile }}</p>
                                            <a href="https://wa.me/{{ $patient->mobile }}" target="_blank" 
                                               class="inline-flex items-center gap-1 text-green-600 hover:text-green-700 mt-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                                </svg>
                                                <span class="text-xs">WhatsApp</span>
                                            </a>
                                        </div>
                                    </td>

                                    <!-- Demographics -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="text-gray-900 mb-1">
                                                <span class="font-medium">Age:</span> {{ $patient->age }} years
                                            </p>
                                            <p class="text-gray-600">
                                                <span class="font-medium">Gender:</span> 
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                             {{ $patient->gender == 'male' ? 'bg-blue-100 text-blue-800' : 
                                                                ($patient->gender == 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($patient->gender) }}
                                                </span>
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Latest Visit -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="text-gray-900 font-medium">{{ $patient->created_at->format('M d, Y') }}</p>
                                            <p class="text-gray-500 text-xs">{{ $patient->created_at->diffForHumans() }}</p>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1
                                                         {{ $patient->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                                            ($patient->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                            ($patient->status == 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst($patient->status) }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Assigned To -->
                                    <td class="px-6 py-4">
                                        <div class="text-xs space-y-1">
                                            @if($patient->doctor)
                                                <p class="text-gray-700">
                                                    <span class="font-medium text-purple-600">Doctor:</span> {{ $patient->doctor->full_name }}
                                                </p>
                                            @endif
                                            @if($patient->canvasser)
                                                <p class="text-gray-700">
                                                    <span class="font-medium text-blue-600">Canvasser:</span> {{ $patient->canvasser->name }}
                                                </p>
                                            @endif
                                            @if($patient->nurse)
                                                <p class="text-gray-700">
                                                    <span class="font-medium text-pink-600">Nurse:</span> {{ $patient->nurse->name }}
                                                </p>
                                            @endif
                                            @if(!$patient->doctor && !$patient->canvasser && !$patient->nurse)
                                                <p class="text-gray-400 italic">Not assigned</p>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Total Visits -->
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-800 font-bold">
                                            {{ $patient->total_consultations }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.consultation.show', $patient->id) }}" 
                                           class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-xs font-medium rounded-lg hover:bg-purple-700 transition">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="text-lg font-medium">No patients found</p>
                                        <p class="text-sm mt-1">Try adjusting your search or filters</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($patients->hasPages())
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        {{ $patients->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</body>
</html>

