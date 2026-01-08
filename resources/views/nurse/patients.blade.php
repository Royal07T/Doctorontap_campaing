<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Search Patients - Nurse Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                        {{ substr(Auth::guard('nurse')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ Auth::guard('nurse')->user()->name }}</p>
                        <p class="text-xs text-gray-500">Nurse</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('nurse.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('nurse.patients') }}" class="flex items-center space-x-3 px-4 py-3 text-white purple-gradient rounded-lg font-medium transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Search Patients</span>
                </a>

                <div class="border-t border-gray-200 my-2"></div>

                <a href="{{ url('/') }}" target="_blank" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-purple-50 rounded-lg font-medium transition-all hover:text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span>View Website</span>
                </a>

                <form method="POST" action="{{ route('nurse.logout') }}">
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
                            <h1 class="text-xl font-bold text-white">Search Patients</h1>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Icon -->
                        <x-notification-icon />
                        <span class="text-sm text-white hidden md:block">{{ now()->format('l, F j, Y') }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                <!-- Search Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    @if(!request('search'))
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-purple-800">
                                <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <strong>Privacy Note:</strong> Patient records will only be displayed after you search for them.
                            </p>
                        </div>
                    @endif
                    <form method="GET" action="{{ route('nurse.patients') }}" class="flex gap-4">
                        <div class="flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Enter name, email, or phone number..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100"
                                   required>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                Search
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Patients Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-purple-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Gender</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Latest Vitals</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($patients as $patient)
                                    <tr class="hover:bg-purple-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $patient->name }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $patient->phone }}</div>
                                            <div class="text-xs text-gray-500">{{ $patient->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ ucfirst($patient->gender) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($patient->latestVitalSigns)
                                                <div class="text-xs">
                                                    <span class="font-medium">BP:</span> {{ $patient->latestVitalSigns->blood_pressure ?? 'N/A' }},
                                                    <span class="font-medium">SpO2:</span> {{ $patient->latestVitalSigns->oxygen_saturation ? $patient->latestVitalSigns->oxygen_saturation . '%' : 'N/A' }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">No vitals recorded</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button onclick="viewPatient({{ $patient->id }})"
                                                    class="text-purple-600 hover:text-purple-900 font-medium mr-3 transition-colors">
                                                View History
                                            </button>
                                            <button onclick="recordVitals({{ $patient->id }}, '{{ $patient->name }}')"
                                                    class="text-blue-600 hover:text-blue-900 font-medium transition-colors">
                                                Record Vitals
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                            @if(request('search'))
                                                <p class="text-lg font-medium">No patients found</p>
                                                <p class="text-sm mt-1">Try searching with a different keyword</p>
                                            @else
                                                <p class="text-lg font-medium">Search for a patient to get started</p>
                                                <p class="text-sm mt-1">Enter a patient's name, email, or phone number in the search box above</p>
                                            @endif
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

    <!-- View Patient History Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-6 border w-full max-w-6xl shadow-2xl rounded-2xl bg-white my-10">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900">Patient Vital Signs History</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="patientHistory" class="space-y-4"></div>
        </div>
    </div>

    <!-- Record Vital Signs Modal -->
    <div id="vitalsModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-6 border w-full max-w-4xl shadow-2xl rounded-2xl bg-white my-10">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Record Vital Signs</h3>
                    <p class="text-sm text-gray-600 mt-1" id="patientNameDisplay"></p>
                </div>
                <button onclick="closeModal('vitalsModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="vitalsForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="hidden" id="patient_id" name="patient_id">
                
                <!-- Blood Pressure -->
                <div>
                    <label for="blood_pressure" class="block text-sm font-semibold text-gray-700 mb-2">Blood Pressure (mmHg)</label>
                    <input type="text" id="blood_pressure" name="blood_pressure"
                           placeholder="120/80"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                    <p class="text-xs text-gray-500 mt-1">Format: Systolic/Diastolic (e.g., 120/80)</p>
                </div>

                <!-- Oxygen Saturation -->
                <div>
                    <label for="oxygen_saturation" class="block text-sm font-semibold text-gray-700 mb-2">Oxygen Saturation (SpO2 %)</label>
                    <input type="number" id="oxygen_saturation" name="oxygen_saturation"
                           min="0" max="100" step="0.1"
                           placeholder="98"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Temperature -->
                <div>
                    <label for="temperature" class="block text-sm font-semibold text-gray-700 mb-2">Temperature (°C)</label>
                    <input type="number" id="temperature" name="temperature"
                           min="30" max="45" step="0.1"
                           placeholder="36.5"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Blood Sugar -->
                <div>
                    <label for="blood_sugar" class="block text-sm font-semibold text-gray-700 mb-2">Blood Sugar (mg/dL)</label>
                    <input type="number" id="blood_sugar" name="blood_sugar"
                           min="0" max="1000" step="0.1"
                           placeholder="100"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Height -->
                <div>
                    <label for="height" class="block text-sm font-semibold text-gray-700 mb-2">Height (cm)</label>
                    <input type="number" id="height" name="height"
                           min="0" max="300" step="0.1"
                           placeholder="170"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Weight -->
                <div>
                    <label for="weight" class="block text-sm font-semibold text-gray-700 mb-2">Weight (kg)</label>
                    <input type="number" id="weight" name="weight"
                           min="0" max="500" step="0.1"
                           placeholder="70"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Heart Rate -->
                <div>
                    <label for="heart_rate" class="block text-sm font-semibold text-gray-700 mb-2">Heart Rate (bpm)</label>
                    <input type="number" id="heart_rate" name="heart_rate"
                           min="0" max="300"
                           placeholder="75"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Respiratory Rate -->
                <div>
                    <label for="respiratory_rate" class="block text-sm font-semibold text-gray-700 mb-2">Respiratory Rate (breaths/min)</label>
                    <input type="number" id="respiratory_rate" name="respiratory_rate"
                           min="0" max="100"
                           placeholder="16"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Any additional observations or notes..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100"></textarea>
                </div>

                <div class="md:col-span-2 flex space-x-3 pt-4">
                    <button type="submit" id="submitBtn"
                            class="flex-1 px-6 py-4 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors text-lg">
                        <span id="btnText">Save Vital Signs</span>
                        <span id="btnLoading" class="hidden">
                            <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    <button type="button" onclick="closeModal('vitalsModal')"
                            class="px-6 py-4 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPatientId = null;

        function viewPatient(id) {
            fetch(`/nurse/patients/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const patient = data.patient;
                        let historyHTML = `
                            <div class="bg-purple-50 p-4 rounded-lg mb-6">
                                <h4 class="font-bold text-lg text-gray-900 mb-2">${patient.name}</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div><span class="font-semibold">Email:</span> ${patient.email}</div>
                                    <div><span class="font-semibold">Phone:</span> ${patient.phone}</div>
                                    <div><span class="font-semibold">Gender:</span> ${patient.gender}</div>
                                    <div><span class="font-semibold">Amount Paid:</span> ₦${patient.total_amount_paid}</div>
                                </div>
                            </div>
                        `;

                        if (patient.vital_signs.length > 0) {
                            historyHTML += '<div class="space-y-4">';
                            patient.vital_signs.forEach(vital => {
                                historyHTML += `
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-center mb-3">
                                            <h5 class="font-semibold text-gray-900">${vital.recorded_at}</h5>
                                            <span class="text-xs text-gray-500">Recorded by: ${vital.recorded_by}</span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">Blood Pressure</div>
                                                <div class="font-semibold">${vital.blood_pressure || 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">SpO2</div>
                                                <div class="font-semibold">${vital.oxygen_saturation ? vital.oxygen_saturation + '%' : 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">Temperature</div>
                                                <div class="font-semibold">${vital.temperature ? vital.temperature + '°C' : 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">Blood Sugar</div>
                                                <div class="font-semibold">${vital.blood_sugar ? vital.blood_sugar + ' mg/dL' : 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">Height</div>
                                                <div class="font-semibold">${vital.height ? vital.height + ' cm' : 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">Weight</div>
                                                <div class="font-semibold">${vital.weight ? vital.weight + ' kg' : 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">BMI</div>
                                                <div class="font-semibold">${vital.bmi || 'N/A'}</div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <div class="text-xs text-gray-600">Heart Rate</div>
                                                <div class="font-semibold">${vital.heart_rate ? vital.heart_rate + ' bpm' : 'N/A'}</div>
                                            </div>
                                        </div>
                                        ${vital.notes ? `<div class="mt-3 p-3 bg-blue-50 rounded"><div class="text-xs text-gray-600 mb-1">Notes:</div><div class="text-sm">${vital.notes}</div></div>` : ''}
                                    </div>
                                `;
                            });
                            historyHTML += '</div>';
                        } else {
                            historyHTML += '<p class="text-center text-gray-500 py-8">No vital signs recorded yet for this patient.</p>';
                        }

                        document.getElementById('patientHistory').innerHTML = historyHTML;
                        document.getElementById('viewModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    showAlertModal('Failed to load patient history', 'error');
                    console.error(error);
                });
        }

        function recordVitals(patientId, patientName) {
            currentPatientId = patientId;
            document.getElementById('patient_id').value = patientId;
            document.getElementById('patientNameDisplay').textContent = 'Patient: ' + patientName;
            document.getElementById('vitalsModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            if (modalId === 'vitalsModal') {
                document.getElementById('vitalsForm').reset();
                currentPatientId = null;
            }
        }

        document.getElementById('vitalsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('{{ route('nurse.vital-signs.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and ask if nurse wants to send email
                    showEmailConfirmationModal(data.vital_sign.id, data.vital_sign.patient_email);
                } else {
                    showAlertModal(data.message || 'Failed to record vital signs', 'error');
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            });
        });

        // Modal System for Confirmations and Alerts
        let confirmCallback = null;

        function showEmailConfirmationModal(vitalSignId, patientEmail) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
            modal.innerHTML = `
                <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Vital Signs Recorded Successfully!</h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Would you like to send a detailed report to <strong>${patientEmail}</strong>?
                        </p>
                        <div class="flex space-x-3">
                            <button onclick="sendVitalSignsEmail(${vitalSignId}, this)" 
                                    class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors font-medium">
                                <span class="send-email-text">Send Email Report</span>
                                <span class="send-email-loading hidden">Sending...</span>
                            </button>
                            <button onclick="closeEmailModal(this)" 
                                    class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
                                Skip for Now
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function sendVitalSignsEmail(vitalSignId, button) {
            const textSpan = button.querySelector('.send-email-text');
            const loadingSpan = button.querySelector('.send-email-loading');
            
            textSpan.classList.add('hidden');
            loadingSpan.classList.remove('hidden');
            button.disabled = true;

            fetch(`/nurse/vital-signs/${vitalSignId}/send-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlertModal(data.message, 'success');
                    closeEmailModal(button);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlertModal(data.message || 'Failed to send email', 'error');
                    textSpan.classList.remove('hidden');
                    loadingSpan.classList.add('hidden');
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertModal('An error occurred while sending email', 'error');
                textSpan.classList.remove('hidden');
                loadingSpan.classList.add('hidden');
                button.disabled = false;
            });
        }

        function closeEmailModal(button) {
            const modal = button.closest('.fixed');
            if (modal) {
                modal.remove();
            }
        }

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
</body>
</html>

