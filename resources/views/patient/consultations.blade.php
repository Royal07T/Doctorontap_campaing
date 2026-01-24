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

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-100 px-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('patient.consultations') }}"
                   class="{{ !request('status') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Upcoming
                </a>
                <a href="{{ route('patient.consultations', ['status' => 'completed']) }}"
                   class="{{ request('status') == 'completed' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Past Sessions
                </a>
                <a href="{{ route('patient.consultations', ['status' => 'cancelled']) }}"
                   class="{{ request('status') == 'cancelled' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm uppercase tracking-tight transition-colors">
                    Cancelled
                </a>
            </nav>
        </div>

        <!-- Pagination Info -->
        @if($consultations->total() > 0)
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
            <p class="text-xs text-gray-500">
                Showing <span class="font-bold text-gray-900">{{ $consultations->firstItem() }}</span> - <span class="font-bold text-gray-900">{{ $consultations->lastItem() }}</span> of <span class="font-bold text-gray-900">{{ $consultations->total() }}</span> consultations
            </p>
        </div>
        @endif

        <!-- Consultations List -->
        @if($consultations->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($consultations as $consultation)
                <div class="p-6 hover:bg-gray-50/50 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <!-- Doctor Info -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border-2 border-white shadow-sm flex-shrink-0">
                                @if($consultation->doctor && $consultation->doctor->photo_url)
                                    <img src="{{ $consultation->doctor->photo_url }}" 
                                         alt="{{ $consultation->doctor->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-indigo-50 flex items-center justify-center text-indigo-600 text-lg font-bold">
                                        {{ $consultation->doctor ? substr($consultation->doctor->name, 0, 1) : '?' }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">
                                    Dr. {{ $consultation->doctor->name ?? 'Unassigned' }}
                                </h4>
                                <p class="text-xs text-gray-500 truncate">{{ $consultation->doctor->specialization ?? 'General Practitioner' }}</p>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate">{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('M d, Y') : $consultation->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $consultation->scheduled_at ? $consultation->scheduled_at->format('h:i A') : 'Time Pending' }}</span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex-shrink-0">
                            @if($consultation->status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-widest border border-emerald-100">
                                    Completed
                                </span>
                            @elseif($consultation->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 uppercase tracking-widest border border-amber-100">
                                    Pending
                                </span>
                            @elseif($consultation->status === 'scheduled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-widest border border-indigo-100">
                                    Scheduled
                                </span>
                            @elseif($consultation->status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 uppercase tracking-widest border border-rose-100">
                                    Cancelled
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center gap-2">
                            @if($consultation->status === 'scheduled')
                                <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                                   class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-indigo-100 uppercase tracking-widest">
                                    Join Call
                                </a>
                            @elseif($consultation->status === 'completed')
                                <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold rounded-xl transition-colors border border-indigo-100 uppercase tracking-tight">
                                    View Summary
                                </a>
                            @elseif($consultation->status === 'cancelled')
                                <a href="{{ route('patient.doctors') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 text-xs font-bold rounded-xl transition-colors uppercase tracking-tight">
                                    Reschedule
                                </a>
                            @else
                                <a href="{{ route('patient.consultation.view', $consultation->id) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-medium rounded-xl transition-colors">
                                    Details
                                </a>
                            @endif
                            
                            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($consultations->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $consultations->withQueryString()->links() }}
            </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 border-2 border-gray-100">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No Consultations Found</h3>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request('status'))
                        You don't have any {{ request('status') }} consultations yet.
                    @else
                        You haven't booked any consultations yet. Get started by booking your first consultation with a doctor.
                    @endif
                </p>
                @if(request('status'))
                    <a href="{{ route('patient.consultations') }}" class="text-indigo-600 font-bold hover:underline text-sm uppercase tracking-widest">
                        View All Consultations
                    </a>
                @else
                    <a href="{{ route('patient.doctors') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-indigo-100 text-sm uppercase tracking-widest">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Book Your First Consultation
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
