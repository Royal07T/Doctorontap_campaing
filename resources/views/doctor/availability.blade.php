@extends('layouts.doctor')

@section('title', 'Availability Settings')
@section('header-title', 'Availability Settings')

@section('content')
                <div class="max-w-4xl mx-auto">
                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-gray-900">Manage Your Availability</h2>
                        <p class="text-xs text-gray-500 mt-1">Set your availability schedule for appointments</p>
                    </div>

                    @if(session('success'))
                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-4 w-4 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                <p class="text-xs font-medium text-emerald-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('doctor.availability.update') }}" class="space-y-6">
                        @csrf

                        <!-- General Availability Toggle -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-1">General Availability</h3>
                                    <p class="text-xs text-gray-500">Toggle your overall availability status</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_available" value="1" {{ $doctor->is_available ? 'checked' : '' }} 
                                           {{ $doctor->is_auto_unavailable ? 'disabled' : '' }} 
                                           class="sr-only peer" 
                                           {{ $doctor->is_auto_unavailable ? 'title="You are currently auto-set to unavailable due to missed consultations. Please contact support."' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 {{ $doctor->is_auto_unavailable ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                                </label>
                            </div>
                            
                            @if($doctor->is_auto_unavailable)
                            <div class="p-3 bg-red-50 rounded-lg border border-red-200 mb-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-semibold text-red-800 mb-1">Auto-Set to Unavailable</p>
                                        <p class="text-xs text-red-700 leading-relaxed">
                                            You have been automatically set to unavailable due to {{ $doctor->missed_consultations_count ?? 0 }} missed consultation(s). 
                                            @if($doctor->unavailable_reason)
                                                {{ $doctor->unavailable_reason }}
                                            @else
                                                Please contact support to resolve this issue.
                                            @endif
                                        </p>
                                        @if($doctor->penalty_applied_at)
                                        <p class="text-xs text-red-600 mt-1 italic">
                                            Penalty applied: {{ $doctor->penalty_applied_at->format('M d, Y h:i A') }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            @if($doctor->missed_consultations_count > 0 && !$doctor->is_auto_unavailable)
                            <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200 mb-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-semibold text-yellow-800 mb-1">Warning: Missed Consultations</p>
                                        <p class="text-xs text-yellow-700 leading-relaxed">
                                            You have {{ $doctor->missed_consultations_count }} missed consultation(s). 
                                            If you miss 3 consultations, you will be automatically set to unavailable.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-xs text-blue-700 leading-relaxed">
                                    <strong>Note:</strong> When enabled, patients can see you in the doctor listings and book appointments with you. When disabled, you won't appear in search results.
                                </p>
                            </div>
                        </div>

                        <!-- Weekly Schedule -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                            <div class="mb-5 pb-4 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-1">Weekly Schedule</h3>
                                <p class="text-xs text-gray-500">Set your available days and time slots for each day of the week</p>
                            </div>
                            
                            <div class="space-y-3">
                                @php
                                    $days = [
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday',
                                    ];
                                @endphp

                                @foreach($days as $dayKey => $dayName)
                                    <div class="border border-gray-200 rounded-xl p-4 hover:border-purple-300 transition-colors bg-gray-50/50">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" 
                                                           name="availability_schedule[{{ $dayKey }}][enabled]" 
                                                           value="1" 
                                                           {{ $schedule[$dayKey]['enabled'] ?? false ? 'checked' : '' }}
                                                           class="sr-only peer"
                                                           onchange="toggleDaySchedule('{{ $dayKey }}')">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                                </label>
                                                <h4 class="text-xs font-semibold text-gray-900 uppercase tracking-wide">{{ $dayName }}</h4>
                                            </div>
                                        </div>
                                        
                                        <div id="schedule-{{ $dayKey }}" class="grid grid-cols-2 gap-3 {{ ($schedule[$dayKey]['enabled'] ?? false) ? '' : 'opacity-50 pointer-events-none' }}">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1.5 uppercase tracking-wide">Start Time</label>
                                                <input type="time" 
                                                       name="availability_schedule[{{ $dayKey }}][start]" 
                                                       value="{{ $schedule[$dayKey]['start'] ?? '09:00' }}"
                                                       class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 transition"
                                                       id="start-{{ $dayKey }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1.5 uppercase tracking-wide">End Time</label>
                                                <input type="time" 
                                                       name="availability_schedule[{{ $dayKey }}][end]" 
                                                       value="{{ $schedule[$dayKey]['end'] ?? '17:00' }}"
                                                       class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 transition"
                                                       id="end-{{ $dayKey }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('doctor.dashboard') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-5 py-2.5 purple-gradient hover:opacity-90 text-white text-sm font-semibold rounded-lg transition">
                                Save Availability
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        function toggleDaySchedule(day) {
            const checkbox = document.querySelector(`input[name="availability_schedule[${day}][enabled]"]`);
            const scheduleDiv = document.getElementById(`schedule-${day}`);
            
            if (checkbox.checked) {
                scheduleDiv.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                scheduleDiv.classList.add('opacity-50', 'pointer-events-none');
            }
        }
    </script>
@endsection

@push('scripts')
    @include('components.custom-alert-modal')
@endpush

