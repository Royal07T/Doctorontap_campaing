<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Records - Admin</title>
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
        @include('admin.shared.sidebar', ['active' => 'patients'])

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            @include('admin.shared.header', ['title' => 'Patients'])

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <!-- Search & Filter Bar -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search & Filter
                        </h2>
                    </div>
                    <form method="GET" action="{{ route('admin.patients') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Search Patients</label>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search by name, email, phone, or reference..." 
                                   value="{{ request('search') }}"
                                   class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition">
                        </div>

                        <!-- Gender Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Gender</label>
                            <select name="gender" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 transition">
                                <option value="">All Genders</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('admin.patients') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Patients Cards -->
                <div class="space-y-4">
                                @forelse($patients as $patient)
                        <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                            <!-- Card Header -->
                            <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <div class="p-5 flex items-center justify-between">
                                    <div class="flex-1 flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-xs
                                                        {{ $patient->gender == 'male' ? 'bg-blue-500' : ($patient->gender == 'female' ? 'bg-pink-500' : 'bg-gray-500') }}">
                                                {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-gray-900">{{ $patient->first_name }} {{ $patient->last_name }}</h3>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                                                    {{ $patient->total_consultations }} visits
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600 font-mono">{{ $patient->reference }}</p>
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
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Contact</p>
                                            <p class="text-xs text-gray-900 mb-1">{{ $patient->email }}</p>
                                            <p class="text-xs text-gray-600 mb-2">{{ $patient->mobile }}</p>
                                            <a href="https://wa.me/{{ format_whatsapp_phone($patient->mobile) }}" target="_blank" 
                                               class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                                </svg>
                                                WhatsApp
                                            </a>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Demographics</p>
                                            <p class="text-xs text-gray-900 mb-1">Age: {{ $patient->age }} years</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                         {{ $patient->gender == 'male' ? 'bg-blue-100 text-blue-700' : 
                                                            ($patient->gender == 'female' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-700') }}">
                                                    {{ ucfirst($patient->gender) }}
                                                </span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Latest Visit</p>
                                            <p class="text-xs text-gray-900">{{ $patient->created_at->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $patient->created_at->diffForHumans() }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Assigned To</p>
                                            @if($patient->doctor)
                                                <p class="text-xs text-gray-700"><span class="font-medium text-purple-600">Doctor:</span> {{ $patient->doctor->full_name }}</p>
                                            @endif
                                            @if($patient->canvasser)
                                                <p class="text-xs text-gray-700"><span class="font-medium text-blue-600">Canvasser:</span> {{ $patient->canvasser->name }}</p>
                                            @endif
                                            @if($patient->nurse)
                                                <p class="text-xs text-gray-700"><span class="font-medium text-pink-600">Nurse:</span> {{ $patient->nurse->name }}</p>
                                            @endif
                                            @if(!$patient->doctor && !$patient->canvasser && !$patient->nurse)
                                                <p class="text-xs text-gray-400 italic">Not assigned</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                                            <button onclick="editPatient({{ $patient->id }}, {{ json_encode($patient) }})" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <a href="{{ route('admin.consultation.show', $patient->id) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View Details
                                            </a>
                                            <button onclick="deletePatient({{ $patient->id }})" 
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
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Patients Found</h3>
                            <p class="text-xs text-gray-500">Try adjusting your search or filters</p>
                        </div>
                                @endforelse

                    <!-- Pagination -->
                    @if($patients->hasPages())
                    <div class="mt-6">
                        {{ $patients->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-amber-100">
                <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 text-center mb-1.5">Confirm Action</h3>
            <p id="confirmMessage" class="text-xs text-gray-600 text-center mb-4 leading-relaxed"></p>
            <div class="flex gap-2">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 text-xs font-semibold bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button onclick="confirmAction()" class="flex-1 px-4 py-2 text-xs font-semibold text-white purple-gradient rounded-lg hover:opacity-90 transition">
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

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Patient</h3>
                    <button onclick="closeEditPatientModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="editPatientForm" onsubmit="handleEditPatient(event)">
                    <input type="hidden" id="editPatientId" name="id">
                    
                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                            <input type="text" id="editPatientFirstName" name="first_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                            <input type="text" id="editPatientLastName" name="last_name" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <!-- Contact Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" id="editPatientEmail" name="email" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                            <input type="text" id="editPatientPhone" name="mobile" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>

                    <!-- Demographics -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input type="date" id="editPatientDob" name="date_of_birth" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select id="editPatientGender" name="gender" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea id="editPatientAddress" name="address" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeEditPatientModal()" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
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

        // Edit Patient Functions
        function editPatient(id, patientData) {
            // Set patient ID
            document.getElementById('editPatientId').value = id;
            
            // Populate form fields
            document.getElementById('editPatientFirstName').value = patientData.first_name || '';
            document.getElementById('editPatientLastName').value = patientData.last_name || '';
            document.getElementById('editPatientEmail').value = patientData.email || '';
            document.getElementById('editPatientPhone').value = patientData.mobile || '';
            document.getElementById('editPatientDob').value = patientData.date_of_birth || '';
            document.getElementById('editPatientGender').value = patientData.gender || '';
            document.getElementById('editPatientAddress').value = patientData.address || '';
            
            // Show modal
            document.getElementById('editPatientModal').classList.remove('hidden');
        }

        function closeEditPatientModal() {
            document.getElementById('editPatientModal').classList.add('hidden');
            document.getElementById('editPatientForm').reset();
        }

        async function handleEditPatient(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const id = formData.get('id');
            
            // Build update data object
            const updateData = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                mobile: formData.get('mobile'),
                date_of_birth: formData.get('date_of_birth'),
                gender: formData.get('gender'),
                address: formData.get('address')
            };
            
            // Remove empty values
            Object.keys(updateData).forEach(key => {
                if (updateData[key] === '' || updateData[key] === null) {
                    delete updateData[key];
                }
            });

            try {
                const response = await fetch(`/super-admin/users/patient/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(updateData)
                });

                const data = await response.json();

                if (data.message) {
                    if (data.user) {
                        showAlertModal(data.message, 'success');
                        closeEditPatientModal();
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showAlertModal(data.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showAlertModal('An error occurred while updating the patient', 'error');
            }
        }
    </script>
    @include('admin.shared.preloader')
</body>
</html>

