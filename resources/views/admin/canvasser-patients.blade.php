<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Canvasser Patients - Admin</title>
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
        @include('admin.shared.sidebar', ['active' => 'canvasser-patients'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Canvasser Patients'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-purple-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total Patients</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $stats['total_patients'] }}</p>
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
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Verified</p>
                                <p class="text-2xl font-bold text-emerald-600">{{ $stats['verified_patients'] }}</p>
                            </div>
                            <div class="bg-emerald-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-amber-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Unverified</p>
                                <p class="text-2xl font-bold text-amber-600">{{ $stats['unverified_patients'] }}</p>
                            </div>
                            <div class="bg-amber-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-blue-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">With Consultations</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['patients_with_consultations'] }}</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">No Consultations</p>
                                <p class="text-2xl font-bold text-gray-600">{{ $stats['patients_without_consultations'] }}</p>
                            </div>
                            <div class="bg-gray-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <form method="GET" action="{{ admin_route('admin.canvasser-patients') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Patient name, email, or phone"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <!-- Canvasser Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Canvasser</label>
                            <select name="canvasser_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">All Canvassers</option>
                                @foreach($canvassers as $canvasser)
                                    <option value="{{ $canvasser->id }}" {{ request('canvasser_id') == $canvasser->id ? 'selected' : '' }}>
                                        {{ $canvasser->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Verification Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                            <select name="verification_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="unverified" {{ request('verification_status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                                Apply Filters
                            </button>
                            <a href="{{ admin_route('admin.canvasser-patients') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Patients Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Registered Patients ({{ $patients->total() }})</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age & Gender</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultations</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($patients as $patient)
                                <tr class="hover:bg-gray-50">
                                    <!-- Patient -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold">
                                                {{ substr($patient->name, 0, 1) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $patient->name }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ $patient->email }}</p>
                                        <p class="text-xs text-gray-500">{{ $patient->phone }}</p>
                                    </td>

                                    <!-- Age & Gender -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm text-gray-900">{{ $patient->age }} years</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1
                                            {{ $patient->gender == 'male' ? 'bg-blue-100 text-blue-800' : 
                                               ($patient->gender == 'female' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($patient->gender) }}
                                        </span>
                                    </td>

                                    <!-- Registered By -->
                                    <td class="px-6 py-4">
                                        @if($patient->canvasser)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                                    {{ substr($patient->canvasser->name, 0, 1) }}
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-sm font-medium text-indigo-600">{{ $patient->canvasser->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $patient->canvasser->email }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-sm">Not assigned</span>
                                        @endif
                                    </td>

                                    <!-- Registration Date -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <p class="text-gray-900 font-medium">{{ $patient->created_at->format('M d, Y') }}</p>
                                            <p class="text-gray-500 text-xs">{{ $patient->created_at->format('h:i A') }}</p>
                                            <p class="text-gray-400 text-xs">{{ $patient->created_at->diffForHumans() }}</p>
                                        </div>
                                    </td>

                                    <!-- Consultations -->
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-800 font-bold">
                                            {{ $patient->consultations_count ?? 0 }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($patient->is_verified)
                                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold">
                                                Verified
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold">
                                                Unverified
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="deletePatient({{ $patient->id }})" 
                                                class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="mt-2 text-lg font-semibold">No patients found</p>
                                        <p class="text-sm">Try adjusting your filters</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($patients->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $patients->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

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
            <div id="alertIconContainer" class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100">
                <svg id="alertIcon" class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <p id="alertMessage" class="text-gray-800 text-center mb-6"></p>
            <button onclick="closeAlertModal()" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium transition-colors">
                OK
            </button>
        </div>
    </div>

    <script>
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

        // Delete Patient
        async function deletePatient(id) {
            showConfirmModal(
                'Are you sure you want to delete this patient record? The record will be archived and can be restored if needed.',
                async () => {
                    try {
                        const response = await fetch(`/admin/patients/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            showAlertModal(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1500);
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

        function showAlertModal(message, type = 'error') {
            const modal = document.getElementById('alertModal');
            const icon = document.getElementById('alertIcon');
            const iconContainer = document.getElementById('alertIconContainer');
            const text = document.getElementById('alertMessage');
            
            text.textContent = message;
            
            if (type === 'success') {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
                iconContainer.className = 'flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-green-100';
                icon.className = 'w-6 h-6 text-green-600';
            } else {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                iconContainer.className = 'flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100';
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
    @include('admin.shared.preloader')
</body>
</html>

