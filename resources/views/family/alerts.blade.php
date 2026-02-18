@extends('layouts.family')

@section('page-title', 'Alerts & Documents')
@section('patient', $patient->name)
@section('patient-initial', strtoupper(substr($patient->first_name ?? $patient->name, 0, 1)))
@section('patient-id', 'ID: ' . str_pad($patient->id, 6, '0', STR_PAD_LEFT))
@section('support-label', 'Emergency Line')
@section('support-text', 'Available 24/7 for urgent issues')
@section('support-cta', 'Call Emergency')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Alerts & Documents</h1>
            <p class="text-sm text-gray-500 mt-0.5">Review care alerts and manage documents</p>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Left: Alerts Feed ─── --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">

                {{-- Filter tabs --}}
                <div class="px-5 py-3 border-b border-gray-100" x-data="{ tab: '{{ $filter }}' }">
                    <div class="flex items-center gap-1">
                        @foreach(['all' => 'All', 'critical' => 'Critical', 'vitals' => 'Vitals'] as $key => $label)
                        <a href="{{ route('family.alerts', ['filter' => $key]) }}"
                           class="px-4 py-1.5 rounded-full text-xs font-semibold transition
                           {{ $filter === $key ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Alert list --}}
                <div class="divide-y divide-gray-50">
                    @forelse($alerts as $alert)
                    <div class="px-5 py-4 flex items-start gap-3 hover:bg-gray-50/50 transition">
                        {{-- Icon --}}
                        @if($alert->flag_status === 'critical')
                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        @elseif($alert->flag_status === 'warning')
                        <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                        </div>
                        @else
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold {{ $alert->flag_status === 'critical' ? 'text-red-700' : ($alert->flag_status === 'warning' ? 'text-amber-700' : 'text-gray-800') }}">
                                        {{ $alert->flag_status === 'critical' ? 'CRITICAL' : ($alert->flag_status === 'warning' ? 'WARNING' : 'VITAL LOG') }}
                                    </p>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $alert->flag_status === 'critical' ? 'bg-red-100 text-red-700' : ($alert->flag_status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ ucfirst($alert->flag_status) }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $alert->created_at->format('M d, h:i A') }}</span>
                            </div>

                            {{-- Vital details --}}
                            <div class="mt-2 flex items-center gap-4">
                                @if($alert->oxygen_saturation)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-500">SpO₂</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $alert->oxygen_saturation }}%</span>
                                </div>
                                @endif
                                @if($alert->heart_rate)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-500">Pulse</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $alert->heart_rate }} bpm</span>
                                </div>
                                @endif
                                @if($alert->temperature)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-500">Temp</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $alert->temperature }}°C</span>
                                </div>
                                @endif
                                @if($alert->blood_pressure)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-500">BP</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $alert->blood_pressure }}</span>
                                </div>
                                @endif
                            </div>

                            @if($alert->flag_status === 'critical')
                            <div class="flex items-center gap-2 mt-3">
                                <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                                    Call Caregiver
                                </button>
                                <button class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200 transition">
                                    Acknowledge
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-12 text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-gray-400 font-medium">No alerts to display</p>
                        <p class="text-xs text-gray-300 mt-1">All vitals are within normal range</p>
                    </div>
                    @endforelse
                </div>

                {{-- Daily observations section --}}
                @if($observations->isNotEmpty())
                <div class="border-t border-gray-100">
                    <div class="px-5 py-3 bg-gray-50/50">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Daily Status Updates</p>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($observations as $obs)
                        <div class="px-5 py-4 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center flex-shrink-0">
                                <span class="text-lg">{{ $obs->emoji_code }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-semibold text-gray-800">Daily Status</p>
                                    <span class="text-xs text-gray-400">{{ $obs->created_at->format('M d, h:i A') }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Pain: {{ $obs->pain_level }}/10
                                    @if($obs->mobility_notes) · Mobility: {{ $obs->mobility_notes }} @endif
                                    @if($obs->general_notes) · {{ Str::limit($obs->general_notes, 80) }} @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ─── Right: Document Center ─── --}}
        <div class="space-y-4">

            {{-- Search --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search documents..." class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>

            {{-- Monthly Care Logs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Monthly Care Logs</h3>
                </div>
                <div class="p-3 space-y-1">
                    @forelse($documents['monthly_care_logs'] ?? [] as $doc)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 group transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-700 truncate">{{ $doc['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $doc['size'] }} · {{ $doc['date'] }}</p>
                            </div>
                        </div>
                        <button class="text-gray-400 hover:text-purple-600 opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 px-3 py-2">No care logs available yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Medical Receipts --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Medical Receipts</h3>
                </div>
                <div class="p-3 space-y-1">
                    @forelse($documents['medical_receipts'] ?? [] as $doc)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 group transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-700 truncate">{{ $doc['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $doc['size'] }} · {{ $doc['date'] }}</p>
                            </div>
                        </div>
                        <button class="text-gray-400 hover:text-purple-600 opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 px-3 py-2">No receipts uploaded.</p>
                    @endforelse
                </div>
            </div>

            {{-- Consent & Agreements --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Consent & Agreements</h3>
                </div>
                <div class="p-3 space-y-1">
                    @forelse($documents['consent_and_agreements'] ?? [] as $doc)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 group transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-700 truncate">{{ $doc['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $doc['size'] }} · {{ $doc['date'] }}</p>
                            </div>
                        </div>
                        <button class="text-gray-400 hover:text-purple-600 opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 px-3 py-2">No agreements on file.</p>
                    @endforelse
                </div>
            </div>

            {{-- Upload button --}}
            <button class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-dashed border-gray-200 text-sm font-semibold text-gray-500 hover:border-purple-300 hover:text-purple-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload Health Document
            </button>
        </div>
    </div>
</div>
@endsection
