@extends('layouts.doctor')

@section('title', 'Clinical Hub')
@section('header-title', 'Clinical Hub')

@push('styles')
<style>
    /* Clinical Hub 3-column layout */
    .clinical-hub-grid {
        display: grid;
        grid-template-columns: 240px 1fr 280px;
        gap: 0;
        min-height: calc(100vh - 140px);
    }
    @media (max-width: 1280px) {
        .clinical-hub-grid {
            grid-template-columns: 220px 1fr 260px;
        }
    }
    @media (max-width: 1024px) {
        .clinical-hub-grid {
            grid-template-columns: 1fr;
            min-height: auto;
        }
        .triage-sidebar, .clinical-actions-sidebar {
            border: none !important;
        }
    }
    .triage-sidebar { border-right: 1px solid #e5e7eb; }
    .clinical-actions-sidebar { border-left: 1px solid #e5e7eb; }
    .risk-flag-card { transition: all 0.2s; }
    .risk-flag-card:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .escalate-card { transition: all 0.2s; cursor: pointer; }
    .escalate-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .bp-chart-placeholder {
        background: linear-gradient(180deg, transparent 0%, #f5f3ff 100%);
    }
    .patient-item.active { background: #f5f3ff; border-left: 3px solid #7c3aed; }
    .patient-item:not(.active) { border-left: 3px solid transparent; }
</style>
@endpush

@section('content')
@php
    $doctor = Auth::guard('doctor')->user();
@endphp

<div class="clinical-hub-grid bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{
    selectedPatientId: {{ $selectedPatient?->id ?? 'null' }},
    activeTab: 'triage'
}">

    {{-- ══════════════════════════════════════════════════════
         LEFT PANEL — Patient Triage
    ══════════════════════════════════════════════════════ --}}
    <div class="triage-sidebar bg-white flex flex-col overflow-hidden">
        {{-- Header --}}
        <div class="px-4 pt-5 pb-3 flex items-center justify-between border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Patient Triage</h2>
            @if($highRiskCount > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">{{ $highRiskCount }} High Risk</span>
            @endif
        </div>

        {{-- Patient List --}}
        <div class="flex-1 overflow-y-auto">
            @forelse($triagePatients as $tp)
            <button
                @click="selectedPatientId = {{ $tp->id }}"
                :class="selectedPatientId === {{ $tp->id }} ? 'active' : 'hover:bg-gray-50'"
                class="patient-item w-full text-left px-4 py-3.5 border-b border-gray-50 transition-colors flex items-start gap-3">
                {{-- Avatar --}}
                <div class="relative flex-shrink-0">
                    @if($tp->photo_url ?? false)
                        <img src="{{ $tp->photo_url }}" class="w-9 h-9 rounded-full object-cover" alt="{{ $tp->name }}">
                    @else
                        <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xs">
                            {{ strtoupper(substr($tp->name ?? $tp->first_name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                    {{-- Risk dot --}}
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white
                        {{ ($tp->critical_vitals_count ?? 0) > 0 ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $tp->name ?? ($tp->first_name . ' ' . $tp->last_name) }}</p>
                    <p class="text-[10px] text-gray-500 truncate">{{ $tp->chronic_conditions ?? $tp->allergies ?? 'Patient' }}</p>
                    @if(($tp->critical_vitals_count ?? 0) > 0)
                    <div class="flex items-center gap-1 mt-1">
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">BP Alert</span>
                    </div>
                    @endif
                </div>
            </button>
            @empty
            <div class="p-8 text-center">
                <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                </div>
                <p class="text-xs text-gray-400">No patients in triage</p>
            </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-gray-100">
            <a href="{{ route('doctor.consultations') }}" class="block w-full text-center text-xs font-semibold text-purple-600 hover:text-purple-700 transition py-2 rounded-lg hover:bg-purple-50">
                View All Patients
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         CENTER PANEL — Main Content Area
    ══════════════════════════════════════════════════════ --}}
    <div class="flex-1 overflow-y-auto bg-gray-50">

        {{-- Navigation Tabs --}}
        <div class="bg-white border-b border-gray-200 px-6 pt-3">
            <div class="flex items-center gap-6">
                <button @click="activeTab = 'triage'" :class="activeTab === 'triage' ? 'text-purple-700 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'" class="text-sm font-semibold pb-3 px-1 transition">Triage Dashboard</button>
                <button @click="activeTab = 'referrals'" :class="activeTab === 'referrals' ? 'text-purple-700 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'" class="text-sm font-semibold pb-3 px-1 transition">Specialist Referrals</button>
                <button @click="activeTab = 'investigations'" :class="activeTab === 'investigations' ? 'text-purple-700 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent'" class="text-sm font-semibold pb-3 px-1 transition">Investigations</button>
            </div>
        </div>

        {{-- ═══ TAB: Triage Dashboard ═══ --}}
        <div x-show="activeTab === 'triage'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6 space-y-6">

            {{-- ── Patient Profile Card ── --}}
            @if($selectedPatient)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex items-start gap-4">
                        @if($selectedPatient->photo_url ?? false)
                            <img src="{{ $selectedPatient->photo_url }}" class="w-16 h-16 rounded-xl object-cover border-2 border-gray-100" alt="{{ $selectedPatient->name }}">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xl border-2 border-gray-100">
                                {{ strtoupper(substr($selectedPatient->name ?? $selectedPatient->first_name ?? '?', 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-lg font-bold text-gray-900">{{ $selectedPatient->name ?? ($selectedPatient->first_name . ' ' . $selectedPatient->last_name) }}</h2>
                                @if(($selectedPatient->critical_vitals_count ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wider">High Risk Flag</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">
                                ID: #DT-{{ str_pad($selectedPatient->id, 4, '0', STR_PAD_LEFT) }}
                                &bull; {{ ucfirst($selectedPatient->gender ?? 'N/A') }},
                                {{ $selectedPatient->date_of_birth ? \Carbon\Carbon::parse($selectedPatient->date_of_birth)->age : '—' }} Years
                                &bull; {{ $selectedPatient->chronic_conditions ?? 'No conditions on file' }}
                            </p>
                            <div class="flex items-center gap-4 mt-2 text-[11px] text-gray-400">
                                @if($selectedPatientVitals)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Last Sync: {{ $selectedPatientVitals->created_at->diffForHumans(null, true, true) }} ago
                                </span>
                                @endif
                                @if($selectedPatientCaregiver)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Caregiver: {{ $selectedPatientCaregiver->name ?? ($selectedPatientCaregiver->first_name . ' ' . $selectedPatientCaregiver->last_name) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('doctor.consultations') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Consult
                        </a>
                        <button class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg text-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Clinical Log
                        </button>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 mb-1">No Patient Selected</h3>
                <p class="text-xs text-gray-500">Select a patient from the triage panel or start a new consultation.</p>
                <a href="{{ route('doctor.consultations') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-purple-600 text-white text-xs font-semibold rounded-lg hover:bg-purple-700 transition">View Consultations</a>
            </div>
            @endif

            {{-- ── Escalate & Refer ── --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Escalate & Refer
                    </h3>
                    <span class="text-[10px] text-gray-400 font-medium">Quick escalation pathways</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Specialty Consultation --}}
                    <div class="escalate-card bg-white rounded-xl border border-gray-200 p-4 hover:border-purple-300">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 mb-1">Specialty Consultation</h4>
                        <p class="text-[10px] text-gray-500 leading-relaxed">Escalate to cardiology, neurology, etc.</p>
                    </div>
                    {{-- Recommend Investigation --}}
                    <div class="escalate-card bg-white rounded-xl border border-gray-200 p-4 hover:border-teal-300">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 mb-1">Recommend Investigation</h4>
                        <p class="text-[10px] text-gray-500 leading-relaxed">Order blood labs, MRI, or CT scans</p>
                    </div>
                    {{-- Dietician Referral --}}
                    <div class="escalate-card bg-white rounded-xl border border-gray-200 p-4 hover:border-orange-300">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 mb-1">Dietician Referral</h4>
                        <p class="text-[10px] text-gray-500 leading-relaxed">Nutrition and metabolic planning</p>
                    </div>
                    {{-- Psychotherapist Referral --}}
                    <div class="escalate-card bg-white rounded-xl border border-gray-200 p-4 hover:border-blue-300">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 mb-1">Psychotherapist Referral</h4>
                        <p class="text-[10px] text-gray-500 leading-relaxed">Mental health & cognitive support</p>
                    </div>
                </div>
            </div>

            {{-- ── Patient Risk Flags ── --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-red-500">&#10038;</span>
                        Patient Risk Flags
                    </h3>
                    <button class="text-[11px] text-purple-600 font-semibold hover:text-purple-700 transition">Clear Resolved</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @if($selectedPatientVitals)
                    {{-- Vitals Threshold --}}
                    <div class="risk-flag-card bg-white rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-red-100 text-red-700">Vitals Threshold</span>
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <p class="text-2xl font-black text-gray-900 mb-0.5">
                            {{ $selectedPatientVitals->blood_pressure ?? '—/—' }}
                            <span class="text-xs font-normal text-gray-500">mmHg</span>
                        </p>
                        <p class="text-[10px] text-red-600 leading-relaxed">
                            @if(($selectedPatientVitals->flag_status ?? '') === 'critical')
                                Blood pressure reading flagged as critical
                            @else
                                Last recorded blood pressure value
                            @endif
                        </p>
                    </div>
                    {{-- Heart Rate --}}
                    <div class="risk-flag-card bg-white rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-orange-100 text-orange-700">Heart Rate</span>
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <p class="text-2xl font-black text-gray-900 mb-0.5">
                            {{ $selectedPatientVitals->heart_rate ?? '—' }}
                            <span class="text-xs font-normal text-gray-500">BPM</span>
                        </p>
                        <p class="text-[10px] text-orange-600 leading-relaxed">
                            @if(($selectedPatientVitals->heart_rate ?? 0) > 90)
                                Elevated resting heart rate detected
                            @else
                                Heart rate within normal range
                            @endif
                        </p>
                    </div>
                    {{-- Adherence --}}
                    <div class="risk-flag-card bg-white rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">Adherence</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        @php
                            $missedMed = $selectedPatientMeds->where('status', 'missed')->first();
                        @endphp
                        <p class="text-lg font-black text-gray-900 mb-0.5">
                            {{ $missedMed ? 'Missed Dosage' : 'On Track' }}
                        </p>
                        <p class="text-[10px] text-slate-600 leading-relaxed">
                            @if($missedMed)
                                {{ $missedMed->medication_name ?? 'Medication' }} not logged by caregiver
                            @else
                                All medications administered on schedule
                            @endif
                        </p>
                    </div>
                    @else
                    <div class="md:col-span-3 bg-white rounded-xl border border-gray-200 p-6 text-center">
                        <p class="text-xs text-gray-400">No risk flag data available for this patient</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Charts Row ── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Blood Pressure History --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-start justify-between mb-1">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Blood Pressure History</h4>
                            <p class="text-[10px] text-gray-400 mt-0.5">Last 7 days monitoring</p>
                        </div>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <button class="px-2.5 py-1 text-[10px] font-semibold bg-gray-100 text-gray-700">Weekly</button>
                            <button class="px-2.5 py-1 text-[10px] font-semibold text-gray-400 hover:bg-gray-50">Monthly</button>
                        </div>
                    </div>
                    <div class="mt-4 h-32 bp-chart-placeholder rounded-lg flex items-end justify-center px-4 pb-2 relative">
                        @if($selectedPatient)
                        @php
                            $bpHistory = $selectedPatient->vitalSigns()
                                ->where('created_at', '>=', now()->subDays(7))
                                ->whereNotNull('blood_pressure')
                                ->orderBy('created_at')
                                ->limit(7)
                                ->get();
                        @endphp
                        @if($bpHistory->count() > 1)
                        <svg class="w-full h-full" viewBox="0 0 300 100" preserveAspectRatio="none">
                            <polyline fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                points="@foreach($bpHistory as $i => $bp){{ ($i / max($bpHistory->count()-1, 1)) * 280 + 10 }},{{ 90 - (((int)explode('/', $bp->blood_pressure ?? '120/80')[0] - 100) / 80) * 70 }} @endforeach" />
                        </svg>
                        @else
                        <p class="text-[10px] text-gray-400 absolute inset-0 flex items-center justify-center">No BP data in last 7 days</p>
                        @endif
                        @else
                        <p class="text-[10px] text-gray-400 absolute inset-0 flex items-center justify-center">Select a patient to view</p>
                        @endif
                    </div>
                </div>
                {{-- Blood Glucose --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-start justify-between mb-1">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Blood Glucose (Fasting)</h4>
                            @if($selectedPatientVitals && ($selectedPatientVitals->blood_sugar ?? null))
                            <p class="text-[10px] text-gray-400 mt-0.5">Average: {{ $selectedPatientVitals->blood_sugar }} mg/dL</p>
                            @else
                            <p class="text-[10px] text-gray-400 mt-0.5">No glucose data</p>
                            @endif
                        </div>
                        @if($selectedPatientVitals && ($selectedPatientVitals->blood_sugar ?? null))
                        @php $bsVal = (float) $selectedPatientVitals->blood_sugar; @endphp
                        <span class="flex items-center gap-1 text-[10px] font-bold {{ $bsVal <= 125 ? 'text-emerald-600' : 'text-red-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $bsVal <= 125 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ $bsVal <= 125 ? 'Within Target' : 'Above Target' }}
                        </span>
                        @endif
                    </div>
                    <div class="mt-4 h-32 rounded-lg flex items-center justify-center">
                        @if($selectedPatient)
                        @php
                            $glucoseHistory = $selectedPatient->vitalSigns()
                                ->where('created_at', '>=', now()->subDays(14))
                                ->whereNotNull('blood_sugar')
                                ->orderBy('created_at')
                                ->limit(10)
                                ->get();
                        @endphp
                        @if($glucoseHistory->count() > 0)
                        <svg class="w-full h-full" viewBox="0 0 300 100" preserveAspectRatio="none">
                            <rect x="0" y="25" width="300" height="40" fill="#ecfdf5" rx="2"/>
                            <line x1="0" y1="25" x2="300" y2="25" stroke="#bbf7d0" stroke-width="0.5" stroke-dasharray="4,4"/>
                            <line x1="0" y1="65" x2="300" y2="65" stroke="#bbf7d0" stroke-width="0.5" stroke-dasharray="4,4"/>
                            @foreach($glucoseHistory as $i => $gs)
                            @php
                                $cx = ($i / max($glucoseHistory->count()-1, 1)) * 270 + 15;
                                $cy = 90 - (((float)$gs->blood_sugar - 60) / 150) * 80;
                                $cy = max(10, min(90, $cy));
                            @endphp
                            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3.5" fill="#7c3aed" opacity="0.7"/>
                            @endforeach
                        </svg>
                        @else
                        <p class="text-[10px] text-gray-400">No glucose data available</p>
                        @endif
                        @else
                        <p class="text-[10px] text-gray-400">Select a patient to view</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══ TAB: Specialist Referrals ═══ --}}
        <div x-show="activeTab === 'referrals'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6 space-y-6">

            {{-- Active Referrals --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Active Referrals
                    </h3>
                    <button class="text-xs font-semibold text-purple-600 hover:text-purple-700 px-3 py-1.5 bg-purple-50 rounded-lg transition">+ New Referral</button>
                </div>

                <div class="space-y-3">
                    {{-- Demo Referral 1 --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">Cardiology Consultation</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Patient: Rice Emma &bull; Persistent elevated BP &gt; 160/95 mmHg</p>
                                    <p class="text-[10px] text-gray-400 mt-1">Referred to: Dr. Adeyemi (Cardiologist) &bull; Lagos University Teaching Hospital</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-orange-100 text-orange-700">Pending</span>
                                <span class="text-[10px] text-gray-400">2 days ago</span>
                            </div>
                        </div>
                    </div>

                    {{-- Demo Referral 2 --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">Neurology Assessment</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Patient: Sarah Brown &bull; Recurring migraines with visual aura, 3x/week</p>
                                    <p class="text-[10px] text-gray-400 mt-1">Referred to: Dr. Okafor (Neurologist) &bull; National Hospital Abuja</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">Accepted</span>
                                <span class="text-[10px] text-gray-400">5 days ago</span>
                            </div>
                        </div>
                    </div>

                    {{-- Demo Referral 3 --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">Endocrinology Review</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Patient: Makenna Schuppe &bull; HbA1c trending up, fasting glucose 142 mg/dL</p>
                                    <p class="text-[10px] text-gray-400 mt-1">Referred to: Dr. Balogun (Endocrinologist) &bull; General Hospital Ikeja</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-purple-100 text-purple-700">Scheduled</span>
                                <span class="text-[10px] text-gray-400">1 week ago</span>
                            </div>
                        </div>
                    </div>

                    {{-- Demo Referral 4 --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">Physiotherapy Referral</h4>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Patient: Bekky Rollings &bull; Chronic lower back pain, 6 months duration</p>
                                    <p class="text-[10px] text-gray-400 mt-1">Referred to: PhysioFirst Clinic &bull; Victoria Island, Lagos</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-gray-100 text-gray-600">Completed</span>
                                <span class="text-[10px] text-gray-400">2 weeks ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Referral Summary Stats --}}
            <div class="grid grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-gray-900">4</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Total Referrals</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-orange-600">1</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Pending</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-purple-600">1</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Scheduled</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-emerald-600">2</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Completed</p>
                </div>
            </div>
        </div>

        {{-- ═══ TAB: Investigations ═══ --}}
        <div x-show="activeTab === 'investigations'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="p-6 space-y-6">

            {{-- Ordered Investigations --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Ordered Investigations
                    </h3>
                    <button class="text-xs font-semibold text-teal-600 hover:text-teal-700 px-3 py-1.5 bg-teal-50 rounded-lg transition">+ Order New</button>
                </div>

                {{-- Investigation Table --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-500">Investigation</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-500">Patient</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-500">Ordered</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-gray-500">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">Full Blood Count (FBC)</p>
                                            <p class="text-[10px] text-gray-400">Haematology Panel</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">Rice Emma</td>
                                <td class="px-4 py-3 text-[11px] text-gray-500">Feb 16, 2026</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-orange-100 text-orange-700">Ongoing</span></td>
                                <td class="px-4 py-3 text-[11px] text-gray-400">Awaiting lab</td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">Chest X-Ray (PA View)</p>
                                            <p class="text-[10px] text-gray-400">Radiology</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">Sarah Brown</td>
                                <td class="px-4 py-3 text-[11px] text-gray-500">Feb 14, 2026</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">Completed</span></td>
                                <td class="px-4 py-3"><button class="text-[11px] font-semibold text-purple-600 hover:text-purple-700">View Report</button></td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">Lipid Profile</p>
                                            <p class="text-[10px] text-gray-400">Clinical Chemistry</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">Makenna Schuppe</td>
                                <td class="px-4 py-3 text-[11px] text-gray-500">Feb 12, 2026</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">Completed</span></td>
                                <td class="px-4 py-3">
                                    <div class="text-[10px]">
                                        <span class="text-gray-700">TC: 245</span>
                                        <span class="text-red-600 font-bold ml-1">&uarr;</span>
                                        <span class="text-gray-500 ml-1">LDL: 165</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">HbA1c</p>
                                            <p class="text-[10px] text-gray-400">Glycated Haemoglobin</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">Bekky Rollings</td>
                                <td class="px-4 py-3 text-[11px] text-gray-500">Feb 10, 2026</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-700">Completed</span></td>
                                <td class="px-4 py-3">
                                    <div class="text-[10px]">
                                        <span class="text-gray-700">7.2%</span>
                                        <span class="text-orange-600 font-bold ml-1">Above target</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-teal-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">Renal Function Test</p>
                                            <p class="text-[10px] text-gray-400">Urea, Creatinine, eGFR</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700">Test Patient</td>
                                <td class="px-4 py-3 text-[11px] text-gray-500">Feb 17, 2026</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-blue-100 text-blue-700">Sample Collected</span></td>
                                <td class="px-4 py-3 text-[11px] text-gray-400">Expected tomorrow</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Investigation Summary --}}
            <div class="grid grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-gray-900">5</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Total Ordered</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-orange-600">1</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Ongoing</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-blue-600">1</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Sample Collected</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                    <p class="text-2xl font-black text-emerald-600">3</p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">Results Ready</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         RIGHT PANEL — Clinical Actions
    ══════════════════════════════════════════════════════ --}}
    <div class="clinical-actions-sidebar bg-white flex flex-col overflow-hidden">
        <div class="px-4 pt-5 pb-3 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Clinical Actions</h2>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-5">
            {{-- New Prescription --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">New Prescription</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 mb-1">Medication Name</label>
                        <input type="text" placeholder="e.g. Amlodipine" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-300">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Dosage</label>
                            <input type="text" placeholder="5 mg" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent placeholder-gray-300">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Frequency</label>
                            <select class="w-full rounded-lg border border-gray-200 px-2 py-2 text-xs focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white text-gray-700">
                                <option>Once Daily</option>
                                <option>Twice Daily</option>
                                <option>Three Times</option>
                                <option>As Needed</option>
                            </select>
                        </div>
                    </div>
                    {{-- Sync toggle --}}
                    <div class="flex items-center justify-between" x-data="{ syncCaregiver: true }">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="text-[10px] font-semibold text-gray-600">Sync with Caregiver</span>
                        </div>
                        <button @click="syncCaregiver = !syncCaregiver" type="button"
                            :class="syncCaregiver ? 'bg-purple-600' : 'bg-gray-200'"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                            <span :class="syncCaregiver ? 'translate-x-4' : 'translate-x-0.5'"
                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Pending Referrals --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3">Pending Referrals</p>
                <div class="space-y-2">
                    @forelse($priorityConsultations->take(3) as $pc)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ $pc->first_name }} {{ $pc->last_name }}</p>
                            <p class="text-[10px] text-gray-500 truncate">Ref: {{ $pc->reference }}</p>
                        </div>
                        <span class="ml-2 px-2 py-0.5 rounded text-[9px] font-bold uppercase
                            {{ $pc->status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $pc->status === 'pending' ? 'Ongoing' : ucfirst($pc->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="p-3 bg-gray-50 rounded-lg text-center">
                        <p class="text-[10px] text-gray-400">No pending referrals</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Bottom fixed --}}
        <div class="px-4 py-4 border-t border-gray-100">
            <button class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl text-xs transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Update & Sync
            </button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush
