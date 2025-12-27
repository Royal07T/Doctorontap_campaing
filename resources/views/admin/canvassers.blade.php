<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Canvassers - Admin</title>
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
        @include('admin.shared.sidebar', ['active' => 'canvassers'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Canvassers'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Success/Error Messages -->
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Total Canvassers</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ $canvassers->total() }}</p>
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
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Active</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ $canvassers->where('is_active', true)->count() }}</p>
                                <p class="text-xs text-gray-500">Active</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 p-5 border-l-4 border-gray-500">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-1.5">Inactive</p>
                                <p class="text-xl font-bold text-gray-900 mb-1">{{ $canvassers->where('is_active', false)->count() }}</p>
                                <p class="text-xs text-gray-500">Inactive</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search & Filter
                        </h2>
                    </div>
                    <form method="GET" action="{{ route('admin.canvassers') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or phone..."
                                   class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Status</label>
                            <select name="status" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white transition">
                                <option value="">All</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">From Date</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition">
                        </div>

                        <!-- Submit -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('admin.canvassers') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Add New Canvasser Button -->
                <div class="mb-6 flex justify-end">
                    <button onclick="openAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New Canvasser
                    </button>
                </div>

                <!-- Canvassers Cards -->
                <div class="space-y-4">
                                @forelse($canvassers as $canvasser)
                        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                            <!-- Card Header -->
                            <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex-1 flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-xs">
                                                {{ substr($canvasser->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-gray-900">{{ $canvasser->name }}</h3>
                                        @if($canvasser->is_active)
                                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold">Active</span>
                                        @else
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Inactive</span>
                                        @endif
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">{{ $canvasser->consultations_count ?? 0 }} patients</span>
                                            </div>
                                            <p class="text-xs text-gray-600">{{ $canvasser->email }} • {{ $canvasser->phone ?? 'No phone' }}</p>
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
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Email</p>
                                            <p class="text-xs text-gray-900">{{ $canvasser->email }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Phone</p>
                                            <p class="text-xs text-gray-900">{{ $canvasser->phone ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Patients Registered</p>
                                            <p class="text-xs text-gray-900 font-semibold">{{ $canvasser->consultations_count ?? 0 }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Created By</p>
                                            <p class="text-xs text-gray-900">
                                        @if($canvasser->createdBy)
                                            <span class="text-purple-600 font-medium">{{ $canvasser->createdBy->name }}</span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Created</p>
                                            <p class="text-xs text-gray-900">{{ $canvasser->created_at->format('M d, Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Last Login</p>
                                            <p class="text-xs text-gray-900">{{ $canvasser->last_login_at ? $canvasser->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                                            <button onclick='openEditModal(@json($canvasser))' 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                                Edit
                                            </button>
                                            <button onclick="toggleStatus({{ $canvasser->id }}, {{ $canvasser->is_active ? 'false' : 'true' }})" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white {{ $canvasser->is_active ? 'bg-gray-600' : 'bg-emerald-600' }} rounded-lg hover:opacity-90 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                                {{ $canvasser->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button onclick="deleteRecord({{ $canvasser->id }})" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                                Delete
                                            </button>
                                        </div>
                                </div>
                            </div>
                        </div>
                                @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <div class="text-4xl mb-4">👥</div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Canvassers Found</h3>
                        </div>
                                @endforelse

                    <!-- Pagination -->
                    @if($canvassers->hasPages())
                    <div class="mt-6">
                        {{ $canvassers->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Canvasser Modal -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
            <!-- Modal Header -->
            <div class="purple-gradient text-white px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h2 id="modalTitle" class="text-xl font-bold">Add New Canvasser</h2>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="form" method="POST" class="p-6">
                @csrf
                <input type="hidden" id="recordId" name="id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Error/Success Messages -->
                <div id="formMessage" class="hidden mb-4 p-3 rounded-lg"></div>

                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="John Doe">
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="canvasser@example.com">
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="phone" name="phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           placeholder="+234 800 000 0000">
                </div>

                <!-- Password -->
                <div class="mb-4" id="passwordField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500" id="passwordRequired">*</span>
                        <span class="text-xs text-gray-500" id="passwordOptional" style="display:none;">(Leave blank to keep current)</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Minimum 8 characters">
                        <button type="button" onclick="togglePasswordVisibility('password', 'passwordToggle')" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg id="passwordToggle" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4" id="confirmPasswordField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Re-enter password">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'passwordConfirmationToggle')" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg id="passwordConfirmationToggle" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Active Status -->
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700">Canvasser is active</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            class="flex-1 px-4 py-2.5 purple-gradient text-white text-sm font-semibold rounded-lg hover:shadow-lg transition-all">
                        <span id="submitBtnText">Create Canvasser</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open Add Modal
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Canvasser';
            document.getElementById('form').reset();
            document.getElementById('recordId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('is_active').checked = true;
            document.getElementById('password').required = true;
            document.getElementById('passwordRequired').style.display = 'inline';
            document.getElementById('passwordOptional').style.display = 'none';
            document.getElementById('form').action = '{{ route("admin.canvassers.store") }}';
            document.getElementById('submitBtnText').textContent = 'Create Canvasser';
            document.getElementById('formMessage').classList.add('hidden');
            document.getElementById('modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Open Edit Modal
        function openEditModal(record) {
            document.getElementById('modalTitle').textContent = 'Edit Canvasser';
            document.getElementById('recordId').value = record.id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('name').value = record.name || '';
            document.getElementById('email').value = record.email || '';
            document.getElementById('phone').value = record.phone || '';
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
            document.getElementById('password').required = false;
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('passwordOptional').style.display = 'inline';
            document.getElementById('is_active').checked = record.is_active;
            document.getElementById('form').action = `/admin/canvassers/${record.id}`;
            document.getElementById('submitBtnText').textContent = 'Update Canvasser';
            document.getElementById('formMessage').classList.add('hidden');
            document.getElementById('modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        // Close Modal
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Toggle Password Visibility
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }

        // Toggle Status
        async function toggleStatus(id, newStatus) {
            showConfirmModal(
                `Are you sure you want to ${newStatus ? 'activate' : 'deactivate'} this canvasser?`,
                async () => {
                    try {
                        const response = await fetch(`/admin/canvassers/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ is_active: newStatus })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            showAlertModal(data.message || 'An error occurred', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlertModal('A network error occurred', 'error');
                    }
                }
            );
        }

        // Delete Record
        async function deleteRecord(id) {
            showConfirmModal(
                'Are you sure you want to delete this canvasser? This action cannot be undone.',
                async () => {
                    try {
                        const response = await fetch(`/admin/canvassers/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.location.reload();
                        } else {
                            showAlertModal(data.message || 'An error occurred', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlertModal('A network error occurred', 'error');
                    }
                }
            );
        }

        // Handle Form Submission
        document.getElementById('form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const formMessage = document.getElementById('formMessage');
            
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Saving...';
            formMessage.classList.add('hidden');
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    formMessage.className = 'mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200';
                    formMessage.textContent = data.message || 'Canvasser saved successfully!';
                    formMessage.classList.remove('hidden');
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    formMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                    formMessage.textContent = data.message || 'An error occurred. Please try again.';
                    formMessage.classList.remove('hidden');
                    
                    submitBtn.disabled = false;
                    submitBtnText.textContent = document.getElementById('recordId').value ? 'Update Canvasser' : 'Create Canvasser';
                }
            } catch (error) {
                console.error('Error:', error);
                formMessage.className = 'mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200';
                formMessage.textContent = 'A network error occurred. Please try again.';
                formMessage.classList.remove('hidden');
                
                submitBtn.disabled = false;
                submitBtnText.textContent = document.getElementById('recordId').value ? 'Update Canvasser' : 'Create Canvasser';
            }
        });

        // Close modal when clicking outside
        document.getElementById('modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Modal System for Confirmations and Alerts
        let confirmCallback = null;

        function showConfirmModal(message, onConfirm) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            confirmCallback = onConfirm;
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            confirmCallback = null;
        }

        function confirmAction() {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        }

        function showAlertModal(message, type = 'error') {
            const modal = document.getElementById('alertModal');
            const icon = document.getElementById('alertIcon');
            const text = document.getElementById('alertMessage');
            
            text.textContent = message;
            
            if (type === 'success') {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                icon.parentElement.className = 'flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-green-100';
                icon.className = 'w-6 h-6 text-green-600';
            } else {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                icon.parentElement.className = 'flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100';
                icon.className = 'w-6 h-6 text-red-600';
            }
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAlertModal() {
            document.getElementById('alertModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-yellow-100">
                <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirm Action</h3>
            <p id="confirmMessage" class="text-gray-600 text-center mb-6"></p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmAction()" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100">
                <svg id="alertIcon" class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <p id="alertMessage" class="text-gray-600 text-center mb-6"></p>
            <button onclick="closeAlertModal()" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                OK
            </button>
        </div>
    </div>
    @include('admin.shared.preloader')
</body>
</html>

