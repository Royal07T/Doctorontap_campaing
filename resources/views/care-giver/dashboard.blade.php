@extends('layouts.caregiver')

@section('title', 'Dashboard')
@section('page-title', 'Care Giver Dashboard')

@section('content')
    {{-- ─── Statistics Cards ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        @php
        $cards = [
            ['label' => 'Assigned Patients', 'value' => $stats['assigned_patients'], 'color' => 'purple', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['label' => 'Active Care Plans', 'value' => $stats['active_care_plans'], 'color' => 'blue', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['label' => 'Logs Today',        'value' => $stats['today_vitals'] + $stats['today_observations'], 'color' => 'emerald', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['label' => 'Critical Alerts',   'value' => $stats['critical_vitals'], 'color' => 'red',   'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        @endphp

        @foreach($cards as $card)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">{{ $card['label'] }}</p>
                    <p class="text-3xl font-bold text-{{ $card['color'] }}-600">{{ number_format($card['value']) }}</p>
                </div>
                <div class="w-12 h-12 bg-{{ $card['color'] }}-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-{{ $card['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ─── Shift Tasks (morning routine) ─── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
         x-data="{ tasks: {{ Js::from($shiftTasks) }} }">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-amber-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Today's Shift Tasks
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <template x-for="task in tasks" :key="task.id">
                <div class="p-3 rounded-lg border-2 cursor-pointer transition-all"
                     :class="task.done ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 hover:border-purple-300'"
                     @click="task.done = !task.done">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                             :class="task.done ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300'">
                            <svg x-show="task.done" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium" :class="task.done ? 'text-gray-400 line-through' : 'text-gray-900'" x-text="task.label"></p>
                            <p class="text-xs text-gray-400" x-text="task.time"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ─── Quick Actions ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('care_giver.patients.index') }}" class="flex items-center p-4 bg-white rounded-xl border-2 border-purple-200 hover:border-purple-500 hover:bg-purple-50 transition-all group">
            <div class="bg-purple-100 p-3 rounded-lg group-hover:bg-purple-200 transition-colors">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="font-semibold text-gray-900 group-hover:text-purple-600">View Patients</h3>
                <p class="text-sm text-gray-500">Manage assigned patients</p>
            </div>
        </a>

        @if($assignedPatients->isNotEmpty())
        <a href="{{ route('care_giver.patients.show', $assignedPatients->first()) }}" class="flex items-center p-4 bg-white rounded-xl border-2 border-blue-200 hover:border-blue-500 hover:bg-blue-50 transition-all group">
            <div class="bg-blue-100 p-3 rounded-lg group-hover:bg-blue-200 transition-colors">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="font-semibold text-gray-900 group-hover:text-blue-600">Record Vitals</h3>
                <p class="text-sm text-gray-500">Log vital signs for {{ $assignedPatients->first()->first_name }}</p>
            </div>
        </a>
        @endif

        <div class="flex items-center p-4 bg-white rounded-xl border-2 border-emerald-200">
            <div class="bg-emerald-100 p-3 rounded-lg">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="font-semibold text-gray-900">Compliance</h3>
                <p class="text-sm text-gray-500">{{ $stats['med_compliance'] }}% medication rate (7d)</p>
            </div>
        </div>
    </div>

    {{-- ─── Assigned Patients List ─── --}}
    @if($assignedPatients->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Your Patients</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($assignedPatients as $patient)
            <a href="{{ route('care_giver.patients.show', $patient) }}"
               class="block p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:shadow-md transition-all group">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-sm">
                        {{ substr($patient->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 group-hover:text-purple-600 truncate">{{ $patient->name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $patient->age ? $patient->age . 'y' : '' }}
                            {{ $patient->gender ? '· ' . ucfirst($patient->gender) : '' }}
                            · {{ ucfirst($patient->pivot->role ?? 'assigned') }}
                        </p>
                    </div>
                    @php
                        $latestVital = $patient->latestVitalSigns;
                        $flag = $latestVital?->flag_status;
                    @endphp
                    @if($flag === 'critical')
                        <span class="w-3 h-3 bg-red-500 rounded-full animate-pulse" title="Critical"></span>
                    @elseif($flag === 'warning')
                        <span class="w-3 h-3 bg-amber-500 rounded-full" title="Warning"></span>
                    @else
                        <span class="w-3 h-3 bg-emerald-500 rounded-full" title="Normal"></span>
                    @endif
                </div>

                @if($patient->activeCarePlan)
                <div class="mt-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $patient->activeCarePlan->plan_type === 'sovereign' ? 'bg-amber-100 text-amber-800' :
                           ($patient->activeCarePlan->plan_type === 'executive' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                        {{ ucfirst($patient->activeCarePlan->plan_type) }} Plan
                    </span>
                </div>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ─── Recent Activity ─── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Activity</h2>
        @if($recentActivity->count() > 0)
        <div class="space-y-3">
            @foreach($recentActivity as $activity)
            <div class="flex items-start space-x-3 p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                    <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500">No recent activity yet</p>
        </div>
        @endif
    </div>
@endsection
</html>

