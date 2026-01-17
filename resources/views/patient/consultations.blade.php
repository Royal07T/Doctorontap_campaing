@extends('layouts.patient')

@section('title', 'My Consultations')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('patient.dashboard') }}" class="hover:text-purple-600">Home</a>
                <span class="mx-2">/</span>
                <span class="text-purple-600">My Consultations</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">My Consultations</h1>
            <p class="text-gray-500 text-sm mt-1">Manage and review your complete medical history</p>
        </div>
        <div>
            <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl shadow-lg shadow-purple-200 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Book New Consultation
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Consultations -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Consultations</p>
                <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Favorite Doctors -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Favorite Doctors</p>
                <h3 class="text-3xl font-bold text-gray-900">{{ $favoriteDoctorsCount }}</h3>
            </div>
            <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center text-yellow-500">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </div>
        </div>

        <!-- Next Appointment -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-600 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Next Appointment</p>
                @if($nextAppointment && $nextAppointment->scheduled_at)
                    <h3 class="text-2xl font-bold text-gray-900">
                        @if($nextAppointment->scheduled_at->isToday())
                            Today
                        @elseif($nextAppointment->scheduled_at->isTomorrow())
                            Tomorrow
                        @else
                            In {{ $nextAppointment->scheduled_at->diffInDays(now()) }} Days
                        @endif
                    </h3>
                    <p class="text-xs text-purple-600 font-medium mt-1">{{ $nextAppointment->scheduled_at->format('M d, h:i A') }}</p>
                @else
                    <h3 class="text-2xl font-bold text-gray-900">None</h3>
                    <p class="text-xs text-gray-400 mt-1">No upcoming sessions</p>
                @endif
            </div>
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('patient.consultations') }}"
               class="{{ !request('status') ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                View All
            </a>
            <a href="{{ route('patient.consultations', ['status' => 'scheduled']) }}"
               class="{{ request('status') == 'scheduled' || request('status') == 'pending' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Upcoming
            </a>
            <a href="{{ route('patient.consultations', ['status' => 'completed']) }}"
               class="{{ request('status') == 'completed' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Past Sessions
            </a>
            <a href="{{ route('patient.consultations', ['status' => 'cancelled']) }}"
               class="{{ request('status') == 'cancelled' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Cancelled
            </a>
        </nav>
    </div>

    <!-- Consultations List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($consultations->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($consultations as $consultation)
                <div class="p-6 hover:bg-gray-50 transition-colors flex flex-col md:flex-row md:items-center gap-6">
                    <!-- Doctor Info -->
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        <img src="{{ $consultation->doctor->photo_url ?? asset('img/default-avatar.png') }}" 
                             alt="{{ $consultation->doctor->name ?? 'Doctor' }}" 
                             class="w-12 h-12 rounded-xl object-cover border border-gray-100 bg-gray-50">
                        <div class="min-w-0">
                            <h4 class="text-base font-bold text-gray-900 truncate">Dr. {{ $consultation->doctor->name ?? 'Unassigned' }}</h4>
                            <p class="text-sm text-gray-500 truncate">{{ $consultation->doctor->specialization ?? 'General Practitioner' }}</p>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="flex flex-col min-w-0 flex-1">
                        <div class="flex items-center gap-2 text-gray-900 font-medium text-sm">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $consultation->scheduled_at ? $consultation->scheduled_at->format('F d, Y') : $consultation->created_at->format('F d, Y') }}
                        </div>
                        <div class="flex items-center gap-2 text-gray-500 text-xs mt-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $consultation->scheduled_at ? $consultation->scheduled_at->format('h:i A') . ' - ' . $consultation->scheduled_at->addMinutes(30)->format('h:i A') : 'Time Pending' }}
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="w-full md:w-32 flex justify-start md:justify-center">
                        @if($consultation->status === 'completed')
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 uppercase tracking-wide">
                                Completed
                            </span>
                        @elseif($consultation->status === 'pending')
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-600 uppercase tracking-wide">
                                Pending
                            </span>
                        @elseif($consultation->status === 'scheduled')
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600 uppercase tracking-wide">
                                Scheduled
                            </span>
                        @elseif($consultation->status === 'cancelled')
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-600 uppercase tracking-wide">
                                Cancelled
                            </span>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <div class="flex justify-end w-full md:w-auto">
                        @if($consultation->status === 'scheduled')
                            <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="inline-flex items-center px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm shadow-purple-200">
                                Join Call
                            </a>
                        @elseif($consultation->status === 'completed')
                            <div class="flex gap-2">
                                <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="inline-flex items-center px-4 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 text-sm font-medium rounded-lg transition-colors">
                                    View Summary
                                </a>
                                <button class="p-2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <a href="{{ route('patient.consultation.view', $consultation->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors">
                                Details
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $consultations->withQueryString()->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No Consultations Found</h3>
                <p class="text-gray-500 mb-6">You don't have any consultations in this category yet.</p>
                @if(request('status'))
                    <a href="{{ route('patient.consultations') }}" class="text-purple-600 font-medium hover:underline">View all consultations</a>
                @else
                    <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-colors">
                        Book Your First Consultation
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
