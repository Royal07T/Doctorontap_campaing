@extends('layouts.family')

@section('page-title', 'Family Portal Overview')
@section('patient', $patient->name)
@section('patient-initial', strtoupper(substr($patient->first_name ?? $patient->name, 0, 1)))
@section('patient-id', 'ID: ' . str_pad($patient->id, 6, '0', STR_PAD_LEFT))
@section('support-label', 'Assigned Caregiver')
@section('support-text', $caregiverStatus['name'])
@section('support-cta', 'Call Caregiver')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Family Portal Overview</h1>
            <p class="text-sm text-gray-500 mt-0.5">Real-time updates on your loved one's care</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                Live Updates Enabled
            </span>
        </div>
    </div>

    {{-- ─── 3 Status Cards ─── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Caregiver Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Caregiver Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                    {{ $caregiverStatus['online'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $caregiverStatus['online'] ? 'ON-SITE' : 'OFF-DUTY' }}
                </span>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ $caregiverStatus['name'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $caregiverStatus['credential'] }}
                @if($caregiverStatus['checked_in'])
                    · Checked in {{ $caregiverStatus['checked_in'] }}
                @endif
            </p>
        </div>

        {{-- Current Activity --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Current Activity</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                    {{ $currentActivity ? 'ACTIVE' : 'IDLE' }}
                </span>
            </div>
            @if($currentActivity)
                <p class="text-lg font-bold text-gray-900">{{ $currentActivity->emoji_code }} Daily Check</p>
                <p class="text-xs text-gray-500 mt-1">Updated {{ $currentActivity->created_at->diffForHumans() }}</p>
            @else
                <p class="text-sm text-gray-400">No activity logged yet today</p>
            @endif
        </div>

        {{-- Upcoming Task --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Upcoming Task</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                    PENDING
                </span>
            </div>
            @if($upcomingMed)
                <p class="text-lg font-bold text-gray-900">{{ $upcomingMed->medication_name ?? 'Medication' }}</p>
                <p class="text-xs text-gray-500 mt-1">Scheduled {{ $upcomingMed->scheduled_time?->format('h:i A') ?? 'today' }}</p>
            @else
                <p class="text-sm text-gray-400">No pending tasks</p>
            @endif
        </div>
    </div>

    {{-- ─── Two-column: Alerts Feed + Sidebar ─── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Alerts Feed --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900">Alerts Feed</h2>
                    <a href="{{ route('family.alerts') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800">View All &rarr;</a>
                </div>
                <div class="divide-y divide-gray-50">
                    {{-- Critical vitals --}}
                    @forelse($criticalVitals as $v)
                    <div class="px-5 py-4 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-red-700">Critical Vital Alert</p>
                                <span class="text-xs text-gray-400">{{ $v->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5">
                                BP {{ $v->blood_pressure ?? '—' }} · HR {{ $v->heart_rate ?? '—' }} · SpO₂ {{ $v->oxygen_saturation ?? '—' }}%
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <button class="px-3 py-1 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">Call Caregiver</button>
                                <button class="px-3 py-1 rounded-lg bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200 transition">Dismiss</button>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse

                    {{-- Recent vitals (non-critical) --}}
                    @foreach($recentLogs->where('flag_status', '!=', 'critical')->take(3) as $v)
                    <div class="px-5 py-4 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">Vital Signs Logged</p>
                                <span class="text-xs text-gray-400">{{ $v->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                BP {{ $v->blood_pressure ?? '—' }} · HR {{ $v->heart_rate ?? '—' }} · SpO₂ {{ $v->oxygen_saturation ?? '—' }}%
                                · <span class="font-medium {{ $v->flag_status === 'normal' ? 'text-emerald-600' : 'text-amber-600' }}">{{ ucfirst($v->flag_status) }}</span>
                            </p>
                        </div>
                    </div>
                    @endforeach

                    {{-- Recent observations --}}
                    @foreach($recentObservations->take(2) as $obs)
                    <div class="px-5 py-4 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center flex-shrink-0">
                            <span class="text-lg">{{ $obs->emoji_code }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-800">Daily Session {{ $obs->pain_level ? '· Pain ' . $obs->pain_level . '/10' : '' }}</p>
                                <span class="text-xs text-gray-400">{{ $obs->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($obs->mobility_notes) Mobility: {{ $obs->mobility_notes }} @endif
                                @if($obs->general_notes) · {{ Str::limit($obs->general_notes, 60) }} @endif
                            </p>
                        </div>
                    </div>
                    @endforeach

                    @if($criticalVitals->isEmpty() && $recentLogs->isEmpty() && $recentObservations->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-gray-400">No recent alerts. All clear!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">
            {{-- Document Center --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Document Center</h2>
                    <a href="{{ route('family.documents') }}" class="text-xs text-purple-600 font-semibold hover:text-purple-800">View All</a>
                </div>
                <div class="p-4 space-y-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Weekly Care Logs</p>
                    @php
                        $docSamples = [
                            ['name' => 'Week ' . now()->weekOfYear . ' Report', 'date' => now()->format('M d')],
                            ['name' => 'Week ' . (now()->weekOfYear - 1) . ' Report', 'date' => now()->subWeek()->format('M d')],
                            ['name' => 'Week ' . (now()->weekOfYear - 2) . ' Report', 'date' => now()->subWeeks(2)->format('M d')],
                        ];
                    @endphp
                    @foreach($docSamples as $doc)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span class="text-sm text-gray-700">{{ $doc['name'] }}</span>
                        </div>
                        <button class="text-gray-400 hover:text-purple-600 opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Family Access --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-bold text-gray-900">Family Access</h2>
                </div>
                <div class="p-4 space-y-3">
                    {{-- Current user --}}
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-sm">
                                {{ strtoupper(substr($member->first_name ?? $member->name, 0, 1)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $member->name }} (You)</p>
                            <p class="text-xs text-emerald-600">Online</p>
                        </div>
                    </div>

                    {{-- Other family members --}}
                    @foreach($familyAccess as $fm)
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-sm">
                                {{ strtoupper(substr($fm->first_name ?? $fm->name, 0, 1)) }}
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-gray-300 border-2 border-white"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $fm->name }}</p>
                            <p class="text-xs text-gray-400">{{ $fm->relationship ?? 'Family Member' }}</p>
                        </div>
                    </div>
                    @endforeach

                    <button class="w-full mt-2 flex items-center justify-center gap-1.5 py-2.5 rounded-lg border-2 border-dashed border-gray-200 text-sm font-semibold text-gray-500 hover:border-purple-300 hover:text-purple-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Invite Family Member
                    </button>
                </div>
            </div>

            {{-- Need Extra Care? --}}
            <div class="rounded-xl bg-gradient-to-br from-purple-600 to-indigo-700 p-5 text-white">
                <h3 class="text-sm font-bold">Need Extra Care?</h3>
                <p class="text-xs text-white/70 mt-1">Book additional services for your loved one</p>
                <div class="mt-4 space-y-2">
                    <button class="w-full py-2 rounded-lg bg-white/20 text-sm font-semibold hover:bg-white/30 transition">
                        🏥 Physiotherapy Visit
                    </button>
                    <button class="w-full py-2 rounded-lg bg-white/20 text-sm font-semibold hover:bg-white/30 transition">
                        👨‍⚕️ Specialist Consultation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
