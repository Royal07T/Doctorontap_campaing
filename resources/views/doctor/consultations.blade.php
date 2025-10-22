<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Consultations - Doctor Dashboard</title>
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
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <!-- Sidebar Header -->
            <div class="purple-gradient p-5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="h-8 w-auto">
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User Info -->
            <div class="p-5 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::guard('doctor')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">Dr. {{ Auth::guard('doctor')->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::guard('doctor')->user()->specialization ?? 'Doctor' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('doctor.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('doctor.consultations') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>My Consultations</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>View Website</span>
                </a>

                <form method="POST" action="{{ route('doctor.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

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
                            <h1 class="text-xl font-bold text-white">My Consultations</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white hidden md:block">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Search and Filter -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <form method="GET" action="{{ route('doctor.consultations') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Name, email, or reference"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                            <select id="status"
                                    name="status"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                Apply Filters
                            </button>
                            <a href="{{ route('doctor.consultations') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Consultations Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-purple-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($consultations as $consultation)
                                    <tr class="hover:bg-purple-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 font-mono">{{ $consultation->reference ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $consultation->first_name }} {{ $consultation->last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $consultation->age }} yrs, {{ ucfirst($consultation->gender) }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $consultation->mobile }}</div>
                                            <div class="text-xs text-gray-500">{{ $consultation->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($consultation->status == 'completed') bg-green-100 text-green-800
                                                @elseif($consultation->status == 'scheduled') bg-blue-100 text-blue-800
                                                @elseif($consultation->status == 'cancelled') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($consultation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $consultation->created_at->format('M d, Y') }}
                                            <div class="text-xs text-gray-400">{{ $consultation->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button onclick="viewConsultation({{ $consultation->id }})"
                                                    class="text-purple-600 hover:text-purple-900 font-medium mr-3 transition-colors">
                                                View
                                            </button>
                                            <button onclick="updateStatus({{ $consultation->id }})"
                                                    class="text-blue-600 hover:text-blue-900 font-medium mr-3 transition-colors">
                                                Update
                                            </button>
                                            @if($consultation->status !== 'completed')
                                            <button onclick="createTreatmentPlan({{ $consultation->id }})"
                                                    class="text-green-600 hover:text-green-900 font-medium transition-colors">
                                                Treatment Plan
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-lg font-medium">No consultations found</p>
                                            <p class="text-sm mt-1">Try adjusting your search or filters</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($consultations->hasPages())
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            {{ $consultations->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- View Consultation Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-6 border w-full max-w-4xl shadow-2xl rounded-2xl bg-white my-10">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900">Consultation Details</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="consultationDetails" class="space-y-4"></div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900">Update Status</h3>
                <button onclick="closeModal('statusModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="statusForm" onsubmit="submitStatusUpdate(event)">
                <div class="space-y-5">
                    <div>
                        <label for="newStatus" class="block text-sm font-semibold text-gray-700 mb-2">New Status <span class="text-red-500">*</span></label>
                        <select id="newStatus" name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="4" placeholder="Add any notes about this consultation..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100"></textarea>
                    </div>
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                            Update Status
                        </button>
                        <button type="button" onclick="closeModal('statusModal')" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentConsultationId = null;

        function viewConsultation(id) {
            fetch(`/doctor/consultations/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const consultation = data.consultation;
                        const details = `
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-purple-700 text-sm uppercase mb-2">Reference Number</h4>
                                    <p class="text-gray-900 font-medium font-mono">${consultation.reference}</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-purple-700 text-sm uppercase mb-2">Status</h4>
                                    <p class="text-gray-900 font-medium">${consultation.status}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Patient Name</h4>
                                    <p class="text-gray-900 font-medium">${consultation.patient_name}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Age & Gender</h4>
                                    <p class="text-gray-900 font-medium">${consultation.age} yrs, ${consultation.gender}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Phone</h4>
                                    <p class="text-gray-900 font-medium">${consultation.mobile}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Email</h4>
                                    <p class="text-gray-900 font-medium break-all">${consultation.email}</p>
                                </div>
                                <div class="md:col-span-2 bg-blue-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-blue-700 text-sm uppercase mb-2">Symptoms/Problem</h4>
                                    <p class="text-gray-900">${consultation.symptoms || 'N/A'}</p>
                                </div>
                                <div class="md:col-span-2 bg-green-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-green-700 text-sm uppercase mb-2">Doctor Notes</h4>
                                    <p class="text-gray-900">${consultation.doctor_notes || 'No notes added yet'}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Payment Status</h4>
                                    <p class="text-gray-900 font-medium">${consultation.payment_status}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Created At</h4>
                                    <p class="text-gray-900 font-medium">${consultation.created_at}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Registered By</h4>
                                    <p class="text-gray-900 font-medium">${consultation.canvasser}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-gray-700 text-sm uppercase mb-2">Nurse Assigned</h4>
                                    <p class="text-gray-900 font-medium">${consultation.nurse}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('consultationDetails').innerHTML = details;
                        document.getElementById('viewModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('View consultation error:', error);
                    showAlertModal('Failed to load consultation details', 'error');
                });
        }

        function updateStatus(id) {
            currentConsultationId = id;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            if (modalId === 'statusModal') {
                document.getElementById('statusForm').reset();
                currentConsultationId = null;
            }
        }

        function submitStatusUpdate(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            
            fetch(`/doctor/consultations/${currentConsultationId}/update-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    status: formData.get('status'),
                    notes: formData.get('notes')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertModal(data.message, 'success');
                    closeModal('statusModal');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlertModal(data.message, 'error');
                }
            })
            .catch(error => {
                showAlertModal('Failed to update status', 'error');
                console.error(error);
            });
        }

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

        // Treatment Plan Functions

        function createTreatmentPlan(consultationId) {
            currentConsultationId = consultationId;
            document.getElementById('treatmentPlanModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function submitTreatmentPlan(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = {};
            
            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (key.includes('[') && key.includes(']')) {
                    // Handle array fields like prescribed_medications[0][name]
                    const parts = key.split('[');
                    const field = parts[0];
                    const index = parts[1].split(']')[0];
                    const subfield = parts[2].split(']')[0];
                    
                    if (!data[field]) data[field] = [];
                    if (!data[field][index]) data[field][index] = {};
                    data[field][index][subfield] = value;
                } else {
                    data[key] = value;
                }
            }
            
            // Remove empty medication and referral entries
            if (data.prescribed_medications) {
                data.prescribed_medications = data.prescribed_medications.filter(med => 
                    med.name && med.dosage && med.frequency && med.duration
                );
            }
            
            if (data.referrals) {
                data.referrals = data.referrals.filter(ref => 
                    ref.specialist && ref.reason && ref.urgency
                );
            }
            
            // Submit to server
            fetch(`/doctor/consultations/${currentConsultationId}/treatment-plan`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertModal(data.message, 'success');
                    closeModal('treatmentPlanModal');
                    // Refresh the page or update the consultation status
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlertModal(data.message, 'error');
                }
            })
            .catch(error => {
                showAlertModal('Failed to create treatment plan', 'error');
                console.error(error);
            });
        }

        function addMedication() {
            const container = document.getElementById('medicationsContainer');
            const count = container.children.length;
            
            const medicationDiv = document.createElement('div');
            medicationDiv.className = 'medication-item grid grid-cols-1 md:grid-cols-4 gap-3 p-4 border border-gray-200 rounded-lg';
            medicationDiv.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Medication Name</label>
                    <input type="text" name="prescribed_medications[${count}][name]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., Paracetamol">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dosage</label>
                    <input type="text" name="prescribed_medications[${count}][dosage]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., 500mg">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Frequency</label>
                    <input type="text" name="prescribed_medications[${count}][frequency]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., Twice daily">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                    <input type="text" name="prescribed_medications[${count}][duration]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., 7 days">
                </div>
            `;
            
            container.appendChild(medicationDiv);
        }

        function addReferral() {
            const container = document.getElementById('referralsContainer');
            const count = container.children.length;
            
            const referralDiv = document.createElement('div');
            referralDiv.className = 'referral-item grid grid-cols-1 md:grid-cols-3 gap-3 p-4 border border-gray-200 rounded-lg';
            referralDiv.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Specialist</label>
                    <input type="text" name="referrals[${count}][specialist]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., Cardiologist">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                    <input type="text" name="referrals[${count}][reason]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="Reason for referral">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Urgency</label>
                    <select name="referrals[${count}][urgency]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
            `;
            
            container.appendChild(referralDiv);
        }
    </script>

    <!-- Treatment Plan Modal -->
    <div id="treatmentPlanModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-5 mx-auto p-6 border w-full max-w-6xl shadow-2xl rounded-2xl bg-white my-5">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900">Create Treatment Plan</h3>
                <button onclick="closeModal('treatmentPlanModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="treatmentPlanForm" onsubmit="submitTreatmentPlan(event)">
                <div class="space-y-6">
                    <!-- Diagnosis -->
                    <div>
                        <label for="diagnosis" class="block text-sm font-semibold text-gray-700 mb-2">Diagnosis <span class="text-red-500">*</span></label>
                        <textarea id="diagnosis" name="diagnosis" required rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100" placeholder="Enter your medical diagnosis..."></textarea>
                    </div>

                    <!-- Treatment Plan -->
                    <div>
                        <label for="treatment_plan" class="block text-sm font-semibold text-gray-700 mb-2">Treatment Plan <span class="text-red-500">*</span></label>
                        <textarea id="treatment_plan" name="treatment_plan" required rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100" placeholder="Describe the treatment plan in detail..."></textarea>
                    </div>

                    <!-- Medications Section -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prescribed Medications</label>
                        <div id="medicationsContainer" class="space-y-3">
                            <div class="medication-item grid grid-cols-1 md:grid-cols-4 gap-3 p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Medication Name</label>
                                    <input type="text" name="prescribed_medications[0][name]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., Paracetamol">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Dosage</label>
                                    <input type="text" name="prescribed_medications[0][dosage]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., 500mg">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Frequency</label>
                                    <input type="text" name="prescribed_medications[0][frequency]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., Twice daily">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                                    <input type="text" name="prescribed_medications[0][duration]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., 7 days">
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="addMedication()" class="mt-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                            + Add Another Medication
                        </button>
                    </div>

                    <!-- Follow-up Instructions -->
                    <div>
                        <label for="follow_up_instructions" class="block text-sm font-semibold text-gray-700 mb-2">Follow-up Instructions</label>
                        <textarea id="follow_up_instructions" name="follow_up_instructions" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100" placeholder="Instructions for follow-up care..."></textarea>
                    </div>

                    <!-- Lifestyle Recommendations -->
                    <div>
                        <label for="lifestyle_recommendations" class="block text-sm font-semibold text-gray-700 mb-2">Lifestyle Recommendations</label>
                        <textarea id="lifestyle_recommendations" name="lifestyle_recommendations" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100" placeholder="Diet, exercise, and lifestyle recommendations..."></textarea>
                    </div>

                    <!-- Referrals Section -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Referrals</label>
                        <div id="referralsContainer" class="space-y-3">
                            <div class="referral-item grid grid-cols-1 md:grid-cols-3 gap-3 p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Specialist</label>
                                    <input type="text" name="referrals[0][specialist]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="e.g., Cardiologist">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Reason</label>
                                    <input type="text" name="referrals[0][reason]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500" placeholder="Reason for referral">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Urgency</label>
                                    <select name="referrals[0][urgency]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-purple-500">
                                        <option value="routine">Routine</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="emergency">Emergency</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="addReferral()" class="mt-2 px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                            + Add Another Referral
                        </button>
                    </div>

                    <!-- Next Appointment -->
                    <div>
                        <label for="next_appointment_date" class="block text-sm font-semibold text-gray-700 mb-2">Next Appointment Date</label>
                        <input type="date" id="next_appointment_date" name="next_appointment_date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <label for="additional_notes" class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
                        <textarea id="additional_notes" name="additional_notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100" placeholder="Any additional notes or recommendations..."></textarea>
                    </div>

                    <!-- Payment Notice -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-yellow-800">Payment Required</h4>
                                <p class="text-sm text-yellow-700 mt-1">The patient will need to make payment before they can access this treatment plan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModal('treatmentPlanModal')" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                            Create Treatment Plan
                        </button>
                    </div>
                </div>
            </form>
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
</body>
</html>
