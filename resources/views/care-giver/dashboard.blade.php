@extends('layouts.caregiver')

@section('title', 'Daily Dashboard')
@section('page-title', 'Daily Dashboard')

@section('content')
    <div class="space-y-6" x-data="{
        activeTab: 'am',
        tasks: {{ Js::from($shiftTasks) }},
        handoverNotes: {{ Js::from($handoverNotes) }},
        autoSaveTimer: null,
        lastSaved: '{{ $handoverLastSaved }}',
        get filteredTasks() {
            return this.tasks.filter(t => t.period === this.activeTab);
        },
        get completedCount() {
            return this.filteredTasks.filter(t => t.done).length;
        },
        get totalFiltered() {
            return this.filteredTasks.length;
        },
        get allCompleted() {
            return this.tasks.filter(t => t.done).length;
        },
        get progressPercent() {
            return this.tasks.length ? Math.round((this.allCompleted / this.tasks.length) * 100) : 0;
        },
        toggleTask(id) {
            const task = this.tasks.find(t => t.id === id);
            if (task) task.done = !task.done;
        }
    }">

        {{-- ─── Today's Shift Header ─── --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Today's Shift</h1>
                <div class="mt-1 flex items-center space-x-2 text-sm text-gray-500">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Active since {{ $activeShift['started_at']->format('h:i A') }} &bull; {{ $activeShift['duration'] }} elapsed</span>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                    Clock Out
                </button>
                <button type="button" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                    End Shift Report
                </button>
            </div>
        </div>

        {{-- ─── Two-Column Layout ─── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- ════════════════════════════════════════════════════════ --}}
            {{-- LEFT COLUMN — Patient Info + Task Tabs                   --}}
            {{-- ════════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- Patient Info Card --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5">
                        <div class="relative">
                            {{-- Mini map — top-right corner --}}
                            <div class="absolute top-0 right-0 w-36 h-24 rounded-lg bg-gray-200 overflow-hidden">
                                <img src="https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/-89.6501,39.7817,13,0/320x200?access_token=pk.placeholder" alt="Map" class="w-full h-full object-cover" onerror="this.style.display='none';this.parentNode.innerHTML='<div class=\'flex items-center justify-center h-full text-gray-400 text-xs\'>Map preview</div>';">
                            </div>

                            {{-- Patient details --}}
                            <div class="pr-40">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700">ACTIVE CASE</span>
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ strtoupper($activeShift['plan']) }}</span>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900">{{ $activeShift['patient'] }}</h2>
                                <p class="mt-1 flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $activeShift['location'] }}
                                </p>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="button" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    View Map
                                </button>
                                <a href="{{ route('care_giver.communication.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    Contact Family
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Task Tabs + Checklist --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    {{-- Tab bar --}}
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'am'" :class="activeTab === 'am' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                                AM Tasks
                            </button>
                            <button @click="activeTab = 'midday'" :class="activeTab === 'midday' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                                Midday Tasks
                            </button>
                            <button @click="activeTab = 'pm'" :class="activeTab === 'pm' ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                                PM Tasks
                            </button>
                        </nav>
                    </div>

                    <div class="p-5">
                        {{-- Progress bar --}}
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Shift Progress</span>
                                <span class="text-sm font-semibold text-emerald-600" x-text="progressPercent + '% Complete'"></span>
                            </div>
                            <div class="h-2.5 rounded-full bg-gray-200">
                                <div class="h-2.5 rounded-full bg-purple-600 transition-all duration-500" :style="{ width: progressPercent + '%' }"></div>
                            </div>
                        </div>

                        {{-- Task list --}}
                        <div class="space-y-1">
                            <template x-for="task in filteredTasks" :key="task.id">
                                <div class="flex items-center p-3 rounded-lg cursor-pointer transition-colors"
                                     :class="task.done ? 'bg-emerald-50' : 'hover:bg-gray-50'"
                                     @click="toggleTask(task.id)">
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="w-6 h-6 rounded-md border-2 flex items-center justify-center transition-colors"
                                             :class="task.done ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300'">
                                            <svg x-show="task.done" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium" :class="task.done ? 'text-gray-400 line-through' : 'text-gray-900'" x-text="task.label"></p>
                                        <p class="text-xs" :class="task.done ? 'text-emerald-500' : 'text-gray-400'" x-text="task.done ? 'Completed at ' + task.time : 'Scheduled for ' + task.time"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="filteredTasks.length === 0">
                                <p class="text-sm text-gray-400 text-center py-6">No tasks for this period.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════ --}}
            {{-- RIGHT COLUMN — Quick Vitals + Handover Notes             --}}
            {{-- ════════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Quick Vitals Entry --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center space-x-2 mb-5">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <h3 class="text-lg font-bold text-gray-900">Quick Vitals Entry</h3>
                    </div>

                    @if($activeShift['patient_id'])
                    <form action="{{ route('care_giver.shift.quick-vitals') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="patient_id" value="{{ $activeShift['patient_id'] }}">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Heart Rate (BPM)</label>
                                <input type="number" name="heart_rate" value="{{ $lastRecordedVitals['heart_rate'] ?? '' }}" placeholder="72" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Temp (°F)</label>
                                <input type="number" name="temperature" step="0.1" value="{{ $lastRecordedVitals['temperature'] ?? '' }}" placeholder="98.6" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Blood Pressure (SYS/DIA)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="bp_systolic" placeholder="120" class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none" />
                                <span class="text-gray-400 font-medium">/</span>
                                <input type="number" name="bp_diastolic" placeholder="80" class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">SPO2 (%)</label>
                            <input type="number" name="spo2" step="0.1" value="{{ $lastRecordedVitals['spo2'] ?? '' }}" placeholder="98" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none" />
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Log Vitals
                        </button>
                    </form>

                    {{-- Last recorded --}}
                    @if($lastRecordedVitals['time'])
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Last Recorded</p>
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>{{ $lastRecordedVitals['time'] }}</span>
                            <span class="font-medium">{{ $lastRecordedVitals['heart_rate'] ?? '—' }} BPM &bull; {{ $lastRecordedVitals['bp'] ?? '—' }} &bull; {{ $lastRecordedVitals['spo2'] ?? '—' }}%</span>
                        </div>
                    </div>
                    @endif
                    @else
                    <p class="text-sm text-gray-500">Assign a patient to log quick vitals.</p>
                    @endif
                </div>

                {{-- Handover Notes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5" x-data="{
                    notes: {{ Js::from($handoverNotes) }},
                    expanded: false,
                    saving: false,
                    savedAt: '{{ $handoverLastSaved }}',
                }">
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <h3 class="text-lg font-bold text-gray-900">Handover Notes</h3>
                    </div>

                    <textarea x-model="notes"
                              :rows="expanded ? 8 : 3"
                              placeholder="Patient seems slightly more mobile today than yesterday. Drank full glass of water with medication..."
                              class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none resize-none transition-all"></textarea>

                    <div class="mt-3 flex items-center justify-between">
                        <p class="text-xs text-gray-400">
                            <span x-text="'Autosaved at ' + savedAt"></span>
                        </p>
                        <button @click="expanded = !expanded" class="text-xs font-semibold uppercase tracking-wide text-purple-600 hover:text-purple-800 transition-colors" x-text="expanded ? 'COLLAPSE' : 'EXPAND'">
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

