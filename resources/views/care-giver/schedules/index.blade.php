@extends('layouts.caregiver')

@section('title', 'Schedules')
@section('page-title', 'Schedules')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Schedule</h2>
            <p class="mt-1 text-sm text-gray-500">View your upcoming shifts, appointments, and patient assignments.</p>
        </div>
        <div class="mt-3 sm:mt-0 flex items-center space-x-2 bg-gray-50 rounded-lg px-4 py-2 border border-gray-200">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-sm font-medium text-gray-700">{{ now()->format('M d, Y') }}</span>
        </div>
    </div>

    {{-- This Week's Schedule --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">This Week</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @php
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $today = now()->format('l');
            @endphp
            @foreach($days as $day)
            <div class="flex items-center px-5 py-4 {{ $day === $today ? 'bg-purple-50' : 'hover:bg-gray-50' }} transition">
                <div class="w-24 flex-shrink-0">
                    <p class="text-sm font-semibold {{ $day === $today ? 'text-purple-700' : 'text-gray-900' }}">{{ $day }}</p>
                    @if($day === $today)
                    <span class="text-xs font-medium text-purple-500">Today</span>
                    @endif
                </div>
                <div class="flex-1 ml-4">
                    @if($assignedPatients->count() > 0 && in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']))
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">08:00 AM - 06:00 PM</span>
                        <span class="text-sm text-gray-600">{{ $assignedPatients->first()->name ?? 'Patient Care' }}</span>
                    </div>
                    @else
                    <span class="text-sm text-gray-400 italic">No shifts scheduled</span>
                    @endif
                </div>
                @if($day === $today)
                <span class="inline-flex items-center rounded-full bg-emerald-500 px-2.5 py-0.5 text-xs font-bold text-white">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse mr-1"></span>
                    Active
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Assigned Patients Summary --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Assigned Patients</h3>
        @if($assignedPatients->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($assignedPatients as $patient)
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($patient->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $patient->name }}</p>
                    <p class="text-xs text-gray-500">ID: #{{ $patient->id }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No patients currently assigned.</p>
        @endif
    </div>
</div>
@endsection
