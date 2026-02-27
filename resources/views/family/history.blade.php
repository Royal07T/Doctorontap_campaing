@extends('layouts.family')

@section('page-title', 'Service History')
@section('patient', $patient->name)
@section('patient-initial', strtoupper(substr($patient->first_name ?? $patient->name, 0, 1)))
@section('patient-id', 'ID: ' . str_pad($patient->id, 6, '0', STR_PAD_LEFT))
@section('support-label', 'Care Coordinator')
@section('support-text', 'Review your service records')
@section('support-cta', 'Contact Support')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Service History</h1>
            <p class="text-sm text-gray-500 mt-0.5">Complete timeline of all care activities</p>
        </div>
        <button class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-200 transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export History
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Left: Timeline ─── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Activity Timeline</h2>
                </div>
                <div class="p-5">
                    @if($serviceHistory->isNotEmpty())
                    <div class="relative">
                        <div class="absolute left-5 top-0 bottom-0 w-px bg-gray-200"></div>
                        <div class="space-y-6">
                            @foreach($serviceHistory as $log)
                            @php
                                $actionConfig = match($log->action) {
                                    'quick_vitals_entry' => ['icon' => '❤️', 'bg' => 'bg-red-50', 'label' => 'Vitals Recorded'],
                                    'daily_health_log' => ['icon' => '📋', 'bg' => 'bg-blue-50', 'label' => 'Daily Health Log'],
                                    'consultation_completed' => ['icon' => '🩺', 'bg' => 'bg-emerald-50', 'label' => 'Consultation Completed'],
                                    'medication_given' => ['icon' => '💊', 'bg' => 'bg-purple-50', 'label' => 'Medication Administered'],
                                    default => ['icon' => '📌', 'bg' => 'bg-gray-50', 'label' => ucfirst(str_replace('_', ' ', $log->action))],
                                };
                            @endphp
                            <div class="relative flex gap-4 pl-2">
                                <div class="w-10 h-10 rounded-full {{ $actionConfig['bg'] }} flex items-center justify-center z-10 flex-shrink-0">
                                    <span class="text-lg">{{ $actionConfig['icon'] }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900">{{ $actionConfig['label'] }}</p>
                                        <span class="text-xs text-gray-400">{{ $log->created_at->format('M d, h:i A') }}</span>
                                    </div>
                                    @if($log->details)
                                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit(is_string($log->details) ? $log->details : json_encode($log->details), 100) }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-0.5">By {{ $log->performed_by ?? 'Caregiver' }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="py-12 text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-gray-400 font-medium">No service history recorded yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── Right sidebar ─── --}}
        <div class="space-y-4">
            {{-- Account Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Account Summary</h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs text-gray-500">Monthly Spend</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $accountSummary['monthly_spend'] }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Next Auto-Pay</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $accountSummary['next_auto_pay'] }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Sessions Logged</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $accountSummary['sessions_logged'] }}</p>
                        </div>
                    </div>
                    <button class="w-full py-2.5 rounded-lg border border-purple-200 text-sm font-semibold text-purple-700 hover:bg-purple-50 transition">
                        Request Statement
                    </button>
                </div>
            </div>

            {{-- Quick Add Services --}}
            <div class="rounded-xl bg-gradient-to-br from-purple-600 to-indigo-700 p-5 text-white">
                <h3 class="text-sm font-bold">Add Services</h3>
                <p class="text-xs text-white/70 mt-1">Book additional care sessions</p>
                <div class="mt-4 space-y-2">
                    <button class="w-full py-2 rounded-lg bg-white/20 text-sm font-semibold hover:bg-white/30 transition">
                        💪 Book Physiotherapy
                    </button>
                    <button class="w-full py-2 rounded-lg bg-white/20 text-sm font-semibold hover:bg-white/30 transition">
                        🏥 Nursing Care
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
