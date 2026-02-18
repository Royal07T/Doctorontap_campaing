@extends('layouts.caregiver')

@section('page-title', 'Assigned Patients')

@section('header-actions')
    <span class="text-sm text-white/80">{{ now()->format('l, F j, Y') }}</span>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Patients Table ──────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Your Assigned Patients</h2>
            <p class="text-sm text-gray-500 mt-1">You can only view patients assigned to you</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Care Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($patients as $patient)
                    @php
                        $assignment = $patient->pivot ?? $patient->caregiverAssignments->first();
                        $role       = $assignment->role ?? 'N/A';
                        $plan       = $patient->activeCarePlan;
                    @endphp
                    <tr class="hover:bg-purple-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $patient->name }}</div>
                            <div class="text-xs text-gray-500">{{ $patient->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $patient->age ?? ($patient->date_of_birth ? now()->diffInYears($patient->date_of_birth) : 'N/A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst($patient->gender ?? 'N/A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($plan)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold
                                    @if($plan->plan_type === 'sovereign') bg-amber-100 text-amber-800
                                    @elseif($plan->plan_type === 'executive') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($plan->plan_type) }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">No plan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($role === 'primary') bg-purple-100 text-purple-800
                                @elseif($role === 'secondary') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('care_giver.patients.show', $patient) }}"
                               class="text-purple-600 hover:text-purple-900 font-medium transition-colors">
                                View Patient
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-lg font-medium">No assigned patients</p>
                            <p class="text-sm mt-1">You haven't been assigned to any patients yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $patients->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

