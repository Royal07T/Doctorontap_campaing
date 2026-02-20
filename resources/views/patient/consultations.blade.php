@extends('layouts.patient')

@section('title', 'My Consultations')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-indigo-600 transition-colors">Home</a>
                <span class="mx-2">/</span>
                <span class="text-indigo-600 font-medium">My Consultations</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">My Consultations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and review your complete medical history</p>
        </div>
        <div>
            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 transition-all text-sm uppercase tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Book New Consultation
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Consultations -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Total Consultations</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $stats['total'] }}</h3>
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Favorite Doctors -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 hover:border-amber-200 transition-colors">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Favorite Doctors</p>
                    <h3 class="text-3xl font-black text-gray-900">{{ $favoriteDoctorsCount }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 border border-amber-100">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Next Appointment -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border-2 border-indigo-100 hover:border-indigo-200 transition-colors relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-600"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Next Appointment</p>
                    @if($nextAppointment && $nextAppointment->scheduled_at)
                        <h3 class="text-2xl font-black text-gray-900">
                            @if($nextAppointment->scheduled_at->isToday())
                                Today
                            @elseif($nextAppointment->scheduled_at->isTomorrow())
                                Tomorrow
                            @else
                                In {{ $nextAppointment->scheduled_at->diffInDays(now()) }} Days
                            @endif
                        </h3>
                        <p class="text-xs text-indigo-600 font-bold mt-1">{{ $nextAppointment->scheduled_at->format('M d, h:i A') }}</p>
                    @else
                        <h3 class="text-2xl font-black text-gray-400">None</h3>
                        <p class="text-xs text-gray-400 mt-1">No upcoming sessions</p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultations Sections -->
    <div class="space-y-6">
        <!-- Upcoming Consultations -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Upcoming Consultations</h2>
                            <p class="text-xs text-gray-500">{{ $upcomingConsultations->count() }} appointment(s) scheduled</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($upcomingConsultations->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($upcomingConsultations as $consultation)
                        @include('patient.partials.consultation-item', ['consultation' => $consultation])
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 border-2 border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">No Upcoming Consultations</h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                        You don't have any upcoming appointments scheduled.
                    </p>
                    <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-100 text-sm uppercase tracking-widest">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Book New Consultation
                    </a>
                </div>
            @endif
        </div>

        <!-- Missed Consultations -->
        @if($missedConsultations->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-rose-200 overflow-hidden">
            <div class="px-6 py-4 bg-rose-50 border-b border-rose-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Missed Consultations</h2>
                            <p class="text-xs text-gray-500">{{ $missedConsultations->count() }} appointment(s) missed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($missedConsultations as $consultation)
                    @include('patient.partials.consultation-item', ['consultation' => $consultation])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Past Consultations -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Past Consultations</h2>
                            <p class="text-xs text-gray-500">{{ $pastConsultations->count() }} completed session(s)</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($pastConsultations->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($pastConsultations as $consultation)
                        @include('patient.partials.consultation-item', ['consultation' => $consultation])
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 border-2 border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">No Past Consultations</h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                        You haven't completed any consultations yet.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
