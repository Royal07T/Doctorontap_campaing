@extends('layouts.doctor')

@section('title', 'Availability Settings')
@section('header-title', 'Availability Settings')

@section('content')
                <div class="max-w-4xl mx-auto">
                    <!-- Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Manage Your Availability</h2>
                        <p class="text-gray-600 mt-2">Set your availability schedule for appointments</p>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <ul class="list-disc list-inside text-sm text-red-700">
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
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 mb-1">General Availability</h3>
                                    <p class="text-sm text-gray-600">Toggle your overall availability status</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_available" value="1" {{ $doctor->is_available ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-700">
                                    <strong>Note:</strong> When enabled, patients can see you in the doctor listings and book appointments with you. When disabled, you won't appear in search results.
                                </p>
                            </div>
                        </div>

                        <!-- Weekly Schedule -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Weekly Schedule</h3>
                            <p class="text-sm text-gray-600 mb-6">Set your available days and time slots for each day of the week</p>
                            
                            <div class="space-y-4">
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
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-colors">
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
                                                <h4 class="text-base font-semibold text-gray-800">{{ $dayName }}</h4>
                                            </div>
                                        </div>
                                        
                                        <div id="schedule-{{ $dayKey }}" class="grid grid-cols-2 gap-4 {{ ($schedule[$dayKey]['enabled'] ?? false) ? '' : 'opacity-50 pointer-events-none' }}">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                                <input type="time" 
                                                       name="availability_schedule[{{ $dayKey }}][start]" 
                                                       value="{{ $schedule[$dayKey]['start'] ?? '09:00' }}"
                                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                                       id="start-{{ $dayKey }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                                <input type="time" 
                                                       name="availability_schedule[{{ $dayKey }}][end]" 
                                                       value="{{ $schedule[$dayKey]['end'] ?? '17:00' }}"
                                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                                                       id="end-{{ $dayKey }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('doctor.dashboard') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors">
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

