@extends('layouts.family')

@section('page-title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Patient Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $patient->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $patient->age ?? ($patient->date_of_birth ? now()->diffInYears($patient->date_of_birth) . ' years' : '') }}
                    {{ $patient->gender ? '· ' . ucfirst($patient->gender) : '' }}
                </p>
            </div>
            @if($carePlan)
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    @if($carePlan->plan_type === 'sovereign') bg-amber-100 text-amber-800
                    @elseif($carePlan->plan_type === 'executive') bg-blue-100 text-blue-800
                    @else bg-green-100 text-green-800 @endif">
                    {{ ucfirst($carePlan->plan_type) }} Plan
                </span>
            @endif
        </div>
    </div>

    {{-- Today's Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Vitals Today</p>
            <p class="text-2xl font-bold text-teal-600 mt-1">{{ $todayVitals }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Critical Flags</p>
            <p class="text-2xl font-bold {{ $criticalCount > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">{{ $criticalCount }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Medications</p>
            <p class="text-2xl font-bold text-teal-600 mt-1">{{ $medsGiven }}/{{ $medsTotal }}</p>
            <p class="text-xs text-gray-400">given today</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Latest Mood</p>
            @if($latestMood)
                <p class="text-2xl mt-1">{{ $latestMood->emoji_code }}</p>
                <p class="text-xs text-gray-400">{{ $latestMood->created_at->diffForHumans() }}</p>
            @else
                <p class="text-sm text-gray-400 mt-1">No observation yet</p>
            @endif
        </div>
    </div>

    {{-- Recent Vitals --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Vital Signs (7 Days)</h3>
        @if($recentVitals->count())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-teal-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold text-teal-700 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-teal-700 uppercase">BP</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-teal-700 uppercase">HR</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-teal-700 uppercase">SpO2</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-teal-700 uppercase">Temp</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-teal-700 uppercase">Flag</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentVitals as $v)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $v->created_at->format('M d, h:i A') }}</td>
                        <td class="px-4 py-2">{{ $v->blood_pressure ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $v->heart_rate ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $v->oxygen_saturation ? $v->oxygen_saturation.'%' : '—' }}</td>
                        <td class="px-4 py-2">{{ $v->temperature ? $v->temperature.'°C' : '—' }}</td>
                        <td class="px-4 py-2">
                            @if($v->flag_status === 'critical')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Critical</span>
                            @elseif($v->flag_status === 'warning')
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
            <p class="text-gray-400 text-sm">No vital signs recorded in the last 7 days.</p>
        @endif
    </div>

    {{-- Recent Observations --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Observations</h3>
        @if($recentObservations->count())
        <div class="space-y-3">
            @foreach($recentObservations as $obs)
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                <span class="text-2xl">{{ $obs->emoji_code }}</span>
                <div class="flex-1">
                    <p class="text-sm text-gray-700">
                        Pain: {{ $obs->pain_level }}/10
                        @if($obs->mobility_notes) · Mobility: {{ $obs->mobility_notes }} @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ $obs->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
            <p class="text-gray-400 text-sm">No observations recorded yet.</p>
        @endif
    </div>
</div>
@endsection
