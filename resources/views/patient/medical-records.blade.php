@extends('layouts.patient')

@section('title', 'Medical Records')

@section('content')
<!-- Statistics -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_records'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Records</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-emerald-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Vital Signs</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_vital_signs'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Recorded</p>
            </div>
            <div class="bg-emerald-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-gray-600 text-xs font-medium uppercase tracking-wide mb-1">Last</p>
                <p class="text-xl font-bold text-gray-900">
                    {{ $stats['last_consultation'] ? $stats['last_consultation']->format('M d, Y') : 'N/A' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Consultation</p>
            </div>
            <div class="bg-purple-50 p-3 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Latest Vital Signs -->
@if($latestVitals)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Latest Vital Signs</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @if($latestVitals->blood_pressure)
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Blood Pressure</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestVitals->blood_pressure }}</p>
                    </div>
                @endif
                @if($latestVitals->heart_rate)
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Heart Rate</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestVitals->heart_rate }} bpm</p>
                    </div>
                @endif
                @if($latestVitals->temperature)
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Temperature</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestVitals->temperature }}°C</p>
                    </div>
                @endif
                @if($latestVitals->weight)
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Weight</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestVitals->weight }} kg</p>
                    </div>
                @endif
                @if($latestVitals->height)
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Height</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestVitals->height }} cm</p>
                    </div>
                @endif
                @if($latestVitals->oxygen_saturation)
                    <div class="text-center p-4 bg-indigo-50 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Oxygen Sat.</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestVitals->oxygen_saturation }}%</p>
                    </div>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-4">Recorded: {{ $latestVitals->created_at->format('M d, Y H:i A') }}</p>
        </div>
    @endif

<!-- Medical History Records -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Medical History</h2>
    </div>

        @if($medicalHistories->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($medicalHistories as $history)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $history->consultation->reference ?? 'N/A' }}</h3>
                                <p class="text-sm text-gray-600">Dr. {{ $history->consultation->doctor->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $history->consultation_date->format('M d, Y') }}</p>
                            </div>
                            @if($history->is_latest)
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Latest</span>
                            @endif
                        </div>

                        <!-- Medical History Section -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Medical History
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                                <!-- Presenting Complaint -->
                                @if($history->presenting_complaint)
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-700 mb-1">Presenting Complaint</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->presenting_complaint }}</p>
                                    </div>
                                @endif

                                <!-- History of Complaint -->
                                @if($history->history_of_complaint)
                                    <div class="md:col-span-2">
                                        <p class="text-sm font-medium text-gray-700 mb-1">History of Complaint</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->history_of_complaint }}</p>
                                    </div>
                                @endif

                                <!-- Past Medical History -->
                                @if($history->past_medical_history)
                                <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Past Medical History</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->past_medical_history }}</p>
                                </div>
                            @endif

                                <!-- Family History -->
                                @if($history->family_history)
                                <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Family History</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->family_history }}</p>
                                </div>
                            @endif

                                <!-- Drug History -->
                                @if($history->drug_history)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Drug History</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->drug_history }}</p>
                                </div>
                            @endif

                                <!-- Social History -->
                                @if($history->social_history)
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-1">Social History</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->social_history }}</p>
                                </div>
                            @endif

                            <!-- Allergies -->
                            @if($history->allergies)
                                    <div class="md:col-span-2">
                                    <p class="text-sm font-medium text-gray-700 mb-1">Allergies</p>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $history->allergies }}</p>
                                </div>
                            @endif
                                </div>
                        </div>
                    </div>
                @endforeach
            </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $medicalHistories->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Medical Records Yet</h3>
            <p class="text-sm text-gray-500 mb-4">Your medical history will appear here after your consultations.</p>
        </div>
    @endif
</div>

<!-- Privacy Notice -->
<div class="mt-6 bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
    <div class="ml-3">
        <p class="text-sm text-purple-700">
            <strong>Privacy Notice:</strong> Your medical records are secure and confidential. Only you and authorized healthcare providers can access this information.
        </p>
    </div>
</div>
@endsection

