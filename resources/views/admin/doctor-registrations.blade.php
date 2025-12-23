<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Doctor Registrations - Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .purple-gradient {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="doctorRegistrationsPage()">
    <div class="flex h-screen overflow-hidden">
        @include('admin.shared.sidebar', ['active' => 'doctor-registrations'])

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
                            <h1 class="text-xl font-bold text-white">Doctor Registrations</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-white">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

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

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-yellow-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Pending Approval</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $doctors->where('is_approved', false)->count() }}</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-emerald-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Approved</p>
                                <p class="text-2xl font-bold text-emerald-600">{{ $doctors->where('is_approved', true)->count() }}</p>
                            </div>
                            <div class="bg-emerald-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-purple-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total Registrations</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $doctors->total() }}</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter and Search Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                    <form method="GET" action="{{ route('admin.doctor-registrations') }}" class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search by name, email, specialization..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div class="sm:w-48">
                            <select name="status"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">All Registrations</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Only</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved Only</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="px-6 py-2 purple-gradient text-white font-semibold rounded-lg hover:opacity-90 transition-all">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </button>
                    </form>
                </div>

                <!-- Doctor Registrations Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor Info</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Experience</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Range</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MDCN License</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($doctors as $doctor)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <span class="text-purple-600 font-semibold text-sm">
                                                        {{ strtoupper(substr($doctor->first_name ?? $doctor->name, 0, 1)) }}{{ strtoupper(substr($doctor->last_name ?? '', 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $doctor->full_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $doctor->email }}</div>
                                                <div class="text-xs text-gray-400">{{ $doctor->phone }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $doctor->specialization }}</div>
                                        <div class="text-xs text-gray-500">{{ $doctor->role }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $doctor->experience }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $doctor->consultation_fee_range }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($doctor->mdcn_license_current)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Current
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Expired
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($doctor->is_approved)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Approved
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="viewDoctor({{ $doctor->id }})"
                                                class="text-purple-600 hover:text-purple-900 mr-3">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        @if(!$doctor->is_approved)
                                        <button @click="approveDoctor({{ $doctor->id }})"
                                                class="text-green-600 hover:text-green-900 mr-3">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>

                                        <button @click="rejectDoctor({{ $doctor->id }})"
                                                class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-medium">No doctor registrations found</p>
                                        <p class="text-sm">Try adjusting your filters or wait for new applications</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($doctors->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $doctors->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- View Doctor Modal -->
    <div x-show="showViewModal"
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 p-4"
         @click.self="showViewModal = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
             @click.stop>
            <div class="purple-gradient p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Doctor Registration Details</h2>
                <button @click="showViewModal = false" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6" x-html="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Approve Doctor Modal -->
    <div x-show="showApproveModal"
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 p-4"
         @click.self="showApproveModal = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full"
             @click.stop>
            <div class="purple-gradient p-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-white">Approve Doctor Registration</h2>
                <button @click="showApproveModal = false" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <p class="text-gray-700 mb-4">Set the consultation fee for <strong x-text="doctorName"></strong>:</p>
                    
                    <!-- Fee Options -->
                    <div class="space-y-4">
                        <!-- Use Default Fee -->
                        <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-purple-50"
                               :class="useDefaultFee ? 'border-purple-500 bg-purple-50' : 'border-gray-300'">
                            <input type="radio" 
                                   x-model="useDefaultFee" 
                                   :value="true"
                                   class="mt-1 mr-3 text-purple-600 focus:ring-purple-500">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">Use Default Fee</div>
                                <div class="text-sm text-gray-600">₦{{ number_format($defaultFee, 0) }} (System default)</div>
                            </div>
                        </label>

                        <!-- Use Custom Fee -->
                        <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-purple-50"
                               :class="!useDefaultFee ? 'border-purple-500 bg-purple-50' : 'border-gray-300'">
                            <input type="radio" 
                                   x-model="useDefaultFee" 
                                   :value="false"
                                   class="mt-1 mr-3 text-purple-600 focus:ring-purple-500">
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">Set Custom Fee</div>
                                <div class="text-sm text-gray-600 mb-2">Doctor's suggested: <span x-text="'₦' + suggestedFee.toLocaleString()"></span></div>
                                <input type="number" 
                                       x-model="customFee"
                                       :disabled="useDefaultFee"
                                       placeholder="Enter custom fee"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       :class="useDefaultFee ? 'bg-gray-100' : ''">
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button @click="showApproveModal = false"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="confirmApproval()"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Approve Doctor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function doctorRegistrationsPage() {
            return {
                pageLoading: false,
                showViewModal: false,
                showApproveModal: false,
                modalContent: '',
                sidebarOpen: false,
                currentDoctorId: null,
                doctorName: '',
                useDefaultFee: true,
                customFee: 0,
                suggestedFee: 0,

                viewDoctor(id) {
                    // Fetch doctor details
                    fetch(`/admin/doctor-registrations/${id}/view`)
                        .then(response => {
                            // Handle authentication errors
                            if (response.status === 401) {
                                return response.json().then(data => {
                                    if (data.redirect) {
                                        window.location.href = data.redirect;
                                        return;
                                    }
                                    throw new Error(data.message || 'Authentication required');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const doctor = data.doctor;
                            this.modalContent = `
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-lg font-semibold mb-3">Personal Information</h3>
                                        <div class="space-y-2 text-sm">
                                            <p><span class="font-semibold">Name:</span> ${doctor.full_name}</p>
                                            <p><span class="font-semibold">Gender:</span> ${doctor.gender}</p>
                                            <p><span class="font-semibold">Email:</span> ${doctor.email}</p>
                                            <p><span class="font-semibold">Phone:</span> ${doctor.phone}</p>
                                            <p><span class="font-semibold">Location:</span> ${doctor.location}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold mb-3">Professional Information</h3>
                                        <div class="space-y-2 text-sm">
                                            <p><span class="font-semibold">Specialization:</span> ${doctor.specialization}</p>
                                            <p><span class="font-semibold">Experience:</span> ${doctor.experience}</p>
                                            <p><span class="font-semibold">Role:</span> ${doctor.role}</p>
                                            <p><span class="font-semibold">Place of Work:</span> ${doctor.place_of_work}</p>
                                            <p><span class="font-semibold">Languages:</span> ${doctor.languages}</p>
                                            <p><span class="font-semibold">Suggested Consultation Fee:</span> ₦${doctor.consultation_fee ? parseFloat(doctor.consultation_fee).toLocaleString() : 'N/A'}</p>
                                            <p><span class="font-semibold">MDCN License:</span> ${doctor.mdcn_license_current ? '<span class="text-green-600">✓ Current</span>' : '<span class="text-yellow-600">Processing/Not Current</span>'}</p>
                                        </div>
                                    </div>
                                    ${doctor.days_of_availability ? `
                                    <div class="col-span-full">
                                        <h3 class="text-lg font-semibold mb-3">Availability Schedule</h3>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-sm text-gray-700 whitespace-pre-line">${doctor.days_of_availability}</p>
                                        </div>
                                    </div>
                                    ` : ''}
                                    ${doctor.certificate_path || doctor.certificate_data ? `
                                    <div class="col-span-full">
                                        <h3 class="text-lg font-semibold mb-3">MDCN License / Medical Certificate</h3>
                                        <a href="/admin/doctors/${doctor.id}/certificate" target="_blank" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            View/Download License
                                        </a>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <svg class="w-4 h-4 inline mr-1 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Certificate stored securely in database
                                        </p>
                                    </div>
                                    ` : `
                                    <div class="col-span-full">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <p class="text-sm text-yellow-800">
                                                <strong>⚠️ No certificate uploaded yet.</strong><br>
                                                Doctor can upload their MDCN license after approval.
                                            </p>
                                        </div>
                                    </div>
                                    `}
                                </div>
                            `;
                            this.showViewModal = true;
                        })
                        .catch(error => {
                            showAlertModal('Failed to load doctor details', 'error');
                            console.error(error);
                        });
                },

                approveDoctor(id) {
                    // Fetch doctor details first
                    fetch(`/admin/doctor-registrations/${id}/view`)
                        .then(response => {
                            // Handle authentication errors
                            if (response.status === 401) {
                                return response.json().then(data => {
                                    if (data.redirect) {
                                        window.location.href = data.redirect;
                                        return;
                                    }
                                    throw new Error(data.message || 'Authentication required');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const doctor = data.doctor;
                            this.currentDoctorId = id;
                            this.doctorName = doctor.full_name;
                            this.suggestedFee = doctor.suggested_fee || 5000;
                            this.customFee = this.suggestedFee;
                            this.useDefaultFee = true;
                            this.showApproveModal = true;
                        })
                        .catch(error => {
                            showAlertModal('Failed to load doctor details', 'error');
                            console.error(error);
                        });
                },

                confirmApproval() {
                    const payload = {
                        use_default_fee: this.useDefaultFee ? 1 : 0,
                        custom_fee: this.useDefaultFee ? null : this.customFee
                    };

                    fetch(`/admin/doctor-registrations/${this.currentDoctorId}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => {
                        // Handle authentication errors
                        if (response.status === 401) {
                            return response.json().then(data => {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                throw new Error(data.message || 'Authentication required');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showAlertModal(data.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlertModal(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showAlertModal('Failed to approve doctor', 'error');
                        console.error(error);
                    });
                },

                rejectDoctor(id) {
                    showConfirmModal('Are you sure you want to reject this doctor registration? This action cannot be undone and will permanently delete the application.', () => {
                        fetch(`/admin/doctor-registrations/${id}/reject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => {
                            // Handle authentication errors
                            if (response.status === 401) {
                                return response.json().then(data => {
                                    if (data.redirect) {
                                        window.location.href = data.redirect;
                                        return;
                                    }
                                    throw new Error(data.message || 'Authentication required');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showAlertModal(data.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showAlertModal(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            showAlertModal('Failed to reject doctor', 'error');
                            console.error(error);
                        });
                    });
                }
            };
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

