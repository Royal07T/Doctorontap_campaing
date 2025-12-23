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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide">Latest Vital Signs</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @if($latestVitals->blood_pressure)
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Blood Pressure</p>
                    <p class="text-sm font-bold text-gray-800">{{ $latestVitals->blood_pressure }}</p>
                </div>
            @endif
            @if($latestVitals->heart_rate)
                <div class="text-center p-3 bg-red-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Heart Rate</p>
                    <p class="text-sm font-bold text-gray-800">{{ $latestVitals->heart_rate }} bpm</p>
                </div>
            @endif
            @if($latestVitals->temperature)
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Temperature</p>
                    <p class="text-sm font-bold text-gray-800">{{ $latestVitals->temperature }}°C</p>
                </div>
            @endif
            @if($latestVitals->weight)
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Weight</p>
                    <p class="text-sm font-bold text-gray-800">{{ $latestVitals->weight }} kg</p>
                </div>
            @endif
            @if($latestVitals->height)
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Height</p>
                    <p class="text-sm font-bold text-gray-800">{{ $latestVitals->height }} cm</p>
                </div>
            @endif
            @if($latestVitals->oxygen_saturation)
                <div class="text-center p-3 bg-indigo-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Oxygen Sat.</p>
                    <p class="text-sm font-bold text-gray-800">{{ $latestVitals->oxygen_saturation }}%</p>
                </div>
            @endif
        </div>
        <p class="text-xs text-gray-500 mt-3">Recorded: {{ $latestVitals->created_at->format('M d, Y H:i A') }}</p>
    </div>
@endif

<!-- Medical History Records -->
<div class="space-y-4">
    @if($medicalHistories->count() > 0)
        @foreach($medicalHistories as $history)
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
                <!-- Card Header -->
                <button @click="open = !open" class="w-full text-left focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex-1 flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $history->consultation->reference ?? 'N/A' }}</h3>
                                    @if($history->is_latest)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">Latest</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600">Dr. {{ $history->consultation->doctor->name ?? 'N/A' }} • {{ $history->consultation_date->format('M d, Y') }}</p>
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
                        <div>
                            <h4 class="text-xs font-semibold text-gray-900 mb-3 uppercase tracking-wide flex items-center">
                                <svg class="w-4 h-4 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Medical History
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @if($history->presenting_complaint)
                                    <div class="md:col-span-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Presenting Complaint</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->presenting_complaint }}</p>
                                    </div>
                                @endif

                                @if($history->history_of_complaint)
                                    <div class="md:col-span-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">History of Complaint</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->history_of_complaint }}</p>
                                    </div>
                                @endif

                                @if($history->past_medical_history)
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Past Medical History</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->past_medical_history }}</p>
                                    </div>
                                @endif

                                @if($history->family_history)
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Family History</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->family_history }}</p>
                                    </div>
                                @endif

                                @if($history->drug_history)
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Drug History</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->drug_history }}</p>
                                    </div>
                                @endif

                                @if($history->social_history)
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Social History</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->social_history }}</p>
                                    </div>
                                @endif

                                @if($history->allergies)
                                    <div class="md:col-span-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Allergies</p>
                                        <p class="text-xs text-gray-700 leading-relaxed whitespace-pre-line">{{ $history->allergies }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="mt-6">
            {{ $medicalHistories->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900 mb-2">No Medical Records Yet</h3>
            <p class="text-xs text-gray-500 mb-4">Your medical history will appear here after your consultations.</p>
        </div>
    @endif
</div>

<!-- Privacy Notice -->
<div class="mt-6 bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-4 w-4 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-xs text-purple-700">
                <strong>Privacy Notice:</strong> Your medical records are secure and confidential. Only you and authorized healthcare providers can access this information.
            </p>
        </div>
    </div>
</div>
@endsection

