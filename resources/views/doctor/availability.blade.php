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
                                <p class="text-xs text-gray-500 mb-2">Set your available days and time slots for each day of the week</p>
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-xs text-blue-700 leading-relaxed">
                                        <strong>24-Hour Availability:</strong> You can set availability for any time within 24 hours (00:00 to 23:59). 
                                        For example, you can set 18:00 to 06:00 for overnight availability.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-data="{
                                validateTime(day) {
                                    const startInput = document.getElementById('start-' + day);
                                    const endInput = document.getElementById('end-' + day);
                                    const warningBox = document.getElementById('warning-' + day);
                                    
                                    if (startInput && endInput && startInput.value && endInput.value) {
                                        const start = startInput.value;
                                        const end = endInput.value;
                                        
                                        // Convert to minutes for comparison
                                        const startMinutes = parseInt(start.split(':')[0]) * 60 + parseInt(start.split(':')[1]);
                                        const endMinutes = parseInt(end.split(':')[0]) * 60 + parseInt(end.split(':')[1]);
                                        
                                        if (warningBox) {
                                            if (endMinutes < startMinutes && endMinutes !== 0) {
                                                warningBox.classList.remove('hidden');
                                            } else {
                                                warningBox.classList.add('hidden');
                                            }
                                        }
                                    }
                                }
                            }">
                                @php
                                    $days = [
                                        'monday' => ['name' => 'Monday', 'icon' => '📅'],
                                        'tuesday' => ['name' => 'Tuesday', 'icon' => '📅'],
                                        'wednesday' => ['name' => 'Wednesday', 'icon' => '📅'],
                                        'thursday' => ['name' => 'Thursday', 'icon' => '📅'],
                                        'friday' => ['name' => 'Friday', 'icon' => '📅'],
                                        'saturday' => ['name' => 'Saturday', 'icon' => '📅'],
                                        'sunday' => ['name' => 'Sunday', 'icon' => '📅'],
                                    ];
                                @endphp

                                @foreach($days as $dayKey => $dayInfo)
                                    <div class="bg-white border-2 {{ ($schedule[$dayKey]['enabled'] ?? false) ? 'border-indigo-300 shadow-md' : 'border-gray-200' }} rounded-xl p-5 hover:shadow-lg transition-all duration-200">
                                        <!-- Day Header with Toggle -->
                                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                                            <div class="flex items-center gap-3">
                                                <span class="text-xl">{{ $dayInfo['icon'] }}</span>
                                                <h4 class="text-sm font-bold text-gray-900">{{ $dayInfo['name'] }}</h4>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       name="availability_schedule[{{ $dayKey }}][enabled]" 
                                                       value="1" 
                                                       {{ $schedule[$dayKey]['enabled'] ?? false ? 'checked' : '' }}
                                                       class="sr-only peer"
                                                       onchange="toggleDaySchedule('{{ $dayKey }}')">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>
                                        
                                        <!-- Time Inputs -->
                                        <div id="schedule-{{ $dayKey }}" class="space-y-3 {{ ($schedule[$dayKey]['enabled'] ?? false) ? '' : 'opacity-50 pointer-events-none' }}">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Start Time
                                                </label>
                                                <input type="time" 
                                                       name="availability_schedule[{{ $dayKey }}][start]" 
                                                       value="{{ $schedule[$dayKey]['start'] ?? '00:00' }}"
                                                       min="00:00"
                                                       max="23:59"
                                                       step="300"
                                                       @change="validateTime('{{ $dayKey }}')"
                                                       class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                                       id="start-{{ $dayKey }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    End Time
                                                </label>
                                                <input type="time" 
                                                       name="availability_schedule[{{ $dayKey }}][end]" 
                                                       value="{{ $schedule[$dayKey]['end'] ?? '23:59' }}"
                                                       min="00:00"
                                                       max="23:59"
                                                       step="300"
                                                       @change="validateTime('{{ $dayKey }}')"
                                                       class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"
                                                       id="end-{{ $dayKey }}">
                                            </div>
                                            
                                            <!-- Time Validation Warning -->
                                            <div id="warning-{{ $dayKey }}" class="hidden p-2.5 bg-red-50 border border-red-200 rounded-lg">
                                                <p class="text-xs text-red-700 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                    </svg>
                                                    End time should be after start time
                                                </p>
                                            </div>
                                            
                                            @if(($schedule[$dayKey]['enabled'] ?? false))
                                            <div class="pt-2 border-t border-gray-100">
                                                <p class="text-xs font-semibold text-indigo-600">
                                                    Available: {{ $schedule[$dayKey]['start'] ?? '00:00' }} - {{ $schedule[$dayKey]['end'] ?? '23:59' }}
                                                </p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Penalty Explanation Card -->
                        @if($doctor->missed_consultations_count > 0)
                        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-xl p-5 mb-6">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-amber-900 mb-2">Penalty System Explanation</h4>
                                    <p class="text-xs text-amber-800 leading-relaxed mb-2">
                                        You currently have <strong>{{ $doctor->missed_consultations_count }}</strong> missed consultation(s). 
                                        The system automatically sets doctors to unavailable after 3 missed consultations to maintain service quality.
                                    </p>
                                    <p class="text-xs text-amber-700">
                                        <strong>Remaining:</strong> {{ 3 - $doctor->missed_consultations_count }} consultation(s) before auto-unavailable status.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('doctor.dashboard') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
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

