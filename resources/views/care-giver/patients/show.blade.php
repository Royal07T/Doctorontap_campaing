@extends('layouts.caregiver')

@section('page-title', $patient->name . ' – Patient Profile')

@section('header-actions')
    <a href="{{ route('care_giver.patients.index') }}" class="text-white hover:text-purple-200 text-sm font-medium flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Patients
    </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ── Flash Messages ───────────────────────────── --}}
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Patient Bio Card ─────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Patient Information</h2>
            @if($carePlan)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                    @if($carePlan->plan_type === 'sovereign') bg-amber-100 text-amber-800
                    @elseif($carePlan->plan_type === 'executive') bg-blue-100 text-blue-800
                    @else bg-green-100 text-green-800 @endif">
                    <span class="w-1.5 h-1.5 rounded-full
                        @if($carePlan->plan_type === 'sovereign') bg-amber-500
                        @elseif($carePlan->plan_type === 'executive') bg-blue-500
                        @else bg-green-500 @endif"></span>
                    {{ ucfirst($carePlan->plan_type) }} Plan
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-0.5">Name</p>
                <p class="text-base font-medium text-gray-900">{{ $patient->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-0.5">Age</p>
                <p class="text-base font-medium text-gray-900">
                    {{ $patient->age ?? ($patient->date_of_birth ? now()->diffInYears($patient->date_of_birth) . ' years' : 'N/A') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-0.5">Gender</p>
                <p class="text-base font-medium text-gray-900">{{ ucfirst($patient->gender ?? 'N/A') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-0.5">Email</p>
                <p class="text-base font-medium text-gray-900">{{ $patient->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-0.5">Phone</p>
                <p class="text-base font-medium text-gray-900">{{ $patient->phone ?? 'N/A' }}</p>
            </div>
            @if($assignment)
            <div>
                <p class="text-sm text-gray-500 mb-0.5">Your Role</p>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                    @if($assignment->role === 'primary') bg-purple-100 text-purple-800
                    @elseif($assignment->role === 'secondary') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($assignment->role) }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Allergies & Medications (side by side) ──── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Allergies</h2>
            @if($patient->allergies)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">{{ $patient->allergies }}</div>
            @else
                <p class="text-sm text-gray-500">No known allergies recorded</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Current Medications</h2>
            @if($patient->current_medications)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">{{ $patient->current_medications }}</div>
            @else
                <p class="text-sm text-gray-500">No current medications recorded</p>
            @endif
        </div>
    </div>

    {{-- ── Livewire Components (plan-scoped) ─────────────────────────── --}}

    {{-- Vitals Chart --}}
    @if($features['vitals'])
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Vitals Trend</h2>
        @livewire('care-giver.vitals-chart', ['patient' => $patient])
    </div>
    @endif

    {{-- Two-column: Vitals Entry + Mood/Observation --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($features['vitals'])
            @livewire('care-giver.vitals-entry', ['patient' => $patient])
        @endif

        @if($features['observations'])
            @livewire('care-giver.emoji-mood-selector', ['patient' => $patient])
        @endif
    </div>

    {{-- Medication Tracker --}}
    @if($features['medication'])
    <div>
        @livewire('care-giver.medication-tracker', ['patient' => $patient])
    </div>
    @endif

    {{-- Diet Plan Manager (Sovereign) --}}
    @if($features['dietician'] ?? false)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @livewire('care-giver.diet-plan-manager', ['patient' => $patient, 'carePlanId' => $carePlan?->id])
    </div>
    @endif

    {{-- Physiotherapy Sessions (Sovereign) --}}
    @if($features['physiotherapy'] ?? false)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @livewire('care-giver.physio-session-manager', ['patient' => $patient, 'carePlanId' => $carePlan?->id])
    </div>
    @endif

    {{-- ── Feature badges (plan visibility) ──────── --}}
    @if($carePlan)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-3">Care Plan Features</h2>
        <div class="flex flex-wrap gap-2">
            @foreach(['vitals' => 'Vitals', 'observations' => 'Observations', 'medication' => 'Medication', 'physician_review' => 'Physician Review', 'weekly_reports' => 'Weekly Reports', 'dietician' => 'Dietician', 'physiotherapy' => 'Physiotherapy'] as $key => $label)
                <span class="inline-flex items-center gap-1.5 px-3 py-1. rounded-full text-xs font-medium
                    {{ $features[$key] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-50 text-gray-400 border border-gray-200 line-through' }}">
                    @if($features[$key])
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @endif
                    {{ $label }}
                </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Static Vitals Table (fallback / historical) ─ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Vital Signs History (Last 7 Days)</h2>

        @if($vitalSigns->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Blood Pressure</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">SpO2 (%)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Temp (°C)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Heart Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Weight</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Flag</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($vitalSigns as $vital)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $vital->created_at->format('M d, Y') }}
                            <span class="block text-xs text-gray-500">{{ $vital->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $vital->blood_pressure ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $vital->temperature ? $vital->temperature . '°C' : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $vital->heart_rate ? $vital->heart_rate . ' bpm' : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $vital->weight ? $vital->weight . ' kg' : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($vital->flag_status === 'critical')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Critical</span>
                            @elseif($vital->flag_status === 'warning')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Warning</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No vital signs recorded</h3>
            <p class="mt-1 text-sm text-gray-500">No vital signs have been recorded for this patient in the last 7 days.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

