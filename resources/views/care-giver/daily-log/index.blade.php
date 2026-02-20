@extends('layouts.caregiver')

@section('title', 'Daily Health Log')
@section('page-title', 'Daily Health Log')

@section('content')
<div x-data="dailyHealthLog()" class="max-w-4xl mx-auto">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-purple-600 mb-1">🩺 Daily Health Log</p>
            <h2 class="text-2xl font-bold text-gray-900">Daily Log Entry</h2>
            <p class="mt-1 text-sm text-gray-500">
                Patient: <strong x-text="selectedPatientName">Select patient</strong>
                <template x-if="selectedPatientId">
                    <span> | ID: <span class="text-gray-400" x-text="'#' + selectedPatientId"></span></span>
                </template>
                <template x-if="draftSavedAt">
                    <span class="ml-2 text-orange-500 font-medium" x-text="'Draft saved ' + draftSavedAt"></span>
                </template>
            </p>
        </div>
        <div class="mt-3 sm:mt-0 flex items-center space-x-2 bg-gray-50 rounded-lg px-4 py-2 border border-gray-200">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-sm font-medium text-gray-700" x-text="currentDate"></span>
            <span class="text-gray-300">•</span>
            <span class="text-sm text-gray-500" x-text="currentTime"></span>
        </div>
    </div>

    {{-- Patient Selector --}}
    @if(isset($assignedPatients) && count($assignedPatients) > 0)
    <div class="mb-6">
        <label for="patient_select" class="block text-sm font-medium text-gray-700 mb-2">Select Patient</label>
        <select id="patient_select" x-model="selectedPatientId"
                @change="selectedPatientName = $event.target.options[$event.target.selectedIndex].text.split(' (ID')[0]"
                class="w-full sm:w-80 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
            <option value="">-- Choose a patient --</option>
            @foreach($assignedPatients as $patient)
            <option value="{{ $patient->id }}" {{ ($selectedPatient ?? null) == $patient->id ? 'selected' : '' }}>
                {{ $patient->name }} (ID: #{{ $patient->id }})
            </option>
            @endforeach
        </select>
    </div>
    @endif

    <form action="{{ route('care_giver.shift.daily-log') }}" method="POST" @submit="formSubmitting = true">
        @csrf
        <input type="hidden" name="patient_id" :value="selectedPatientId">

        <div class="space-y-6">

            {{-- ─── Mood & Behavior ─── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center space-x-2 mb-5">
                    <span class="text-xl">😊</span>
                    <h3 class="text-lg font-bold text-gray-900">Mood & Behavior</h3>
                </div>

                <div class="flex flex-wrap gap-3">
                    <template x-for="m in moods" :key="m.value">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="mood" :value="m.value" x-model="mood" class="sr-only peer">
                            <div class="flex flex-col items-center px-5 py-4 rounded-xl border-2 transition-all
                                        peer-checked:border-purple-600 peer-checked:bg-purple-50 peer-checked:shadow-md
                                        border-gray-200 hover:border-gray-300 hover:bg-gray-50 min-w-[80px]">
                                <span class="text-3xl mb-1" x-text="m.emoji"></span>
                                <span class="text-xs font-medium" :class="mood === m.value ? 'text-purple-700' : 'text-gray-600'" x-text="m.label"></span>
                            </div>
                        </label>
                    </template>
                </div>
                @error('mood')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ─── Vitals ─── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center space-x-2 mb-5">
                    <span class="text-xl">🏥</span>
                    <h3 class="text-lg font-bold text-gray-900">Vitals</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Blood Pressure --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Pressure</label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="number" name="bp_systolic" x-model="bpSystolic" placeholder="Sys"
                                       class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">mmHg</span>
                            </div>
                            <span class="text-gray-400 font-medium">/</span>
                            <div class="relative flex-1">
                                <input type="number" name="bp_diastolic" x-model="bpDiastolic" placeholder="Dia"
                                       class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">mmHg</span>
                            </div>
                        </div>
                    </div>

                    {{-- Blood Sugar --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Sugar</label>
                        <div class="relative">
                            <input type="number" name="blood_sugar" x-model="bloodSugar" placeholder="Value" step="0.1"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">mg/dL</span>
                        </div>
                    </div>

                    {{-- Body Temperature --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Body Temp</label>
                        <div class="relative">
                            <input type="number" name="temperature" x-model="temperature" placeholder="98.6" step="0.1"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">°F</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Physical Activity ─── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center space-x-2 mb-5">
                    <span class="text-xl">🚶</span>
                    <h3 class="text-lg font-bold text-gray-900">Physical Activity</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Steps --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Total Steps Today</label>
                        <div class="relative">
                            <input type="number" name="steps" x-model="steps" placeholder="0" min="0" max="50000"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Steps</span>
                        </div>
                    </div>

                    {{-- Assistance Level --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Assistance Level</label>
                        <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                            <template x-for="level in assistanceLevels" :key="level">
                                <button type="button"
                                        @click="assistanceLevel = level"
                                        :class="assistanceLevel === level
                                            ? 'bg-purple-600 text-white border-purple-600'
                                            : 'bg-white text-gray-600 hover:bg-gray-50'"
                                        class="flex-1 py-2.5 text-sm font-medium border-r border-gray-300 last:border-r-0 transition-colors"
                                        x-text="level">
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="assistance_level" :value="assistanceLevel">
                    </div>
                </div>
            </div>

            {{-- ─── Nutrition & Elimination ─── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl">🍽️</span>
                        <h3 class="text-lg font-bold text-gray-900">Nutrition & Elimination</h3>
                    </div>
                    <span class="text-lg font-bold text-purple-600" x-text="nutritionPercent + '%'"></span>
                </div>

                {{-- Eating Percentage --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Eating Percentage (Last Meal)</label>
                    <div class="flex gap-2">
                        <template x-for="pct in [0, 25, 50, 75, 100]" :key="pct">
                            <button type="button"
                                    @click="nutritionPercent = pct"
                                    :class="nutritionPercent === pct
                                        ? 'bg-purple-600 text-white border-purple-600 shadow-md'
                                        : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400 hover:bg-gray-50'"
                                    class="flex-1 py-2.5 rounded-lg border-2 text-sm font-semibold transition-all"
                                    x-text="pct + '%'">
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="nutrition_percent" :value="nutritionPercent">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Stool Log (Bristol Scale) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Stool Log (Bristol Scale)</label>
                        <select name="elimination_scale" x-model="eliminationScale"
                                class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                            <option value="">Select type...</option>
                            <option value="1">Type 1: Separate hard lumps</option>
                            <option value="2">Type 2: Lumpy & sausage-like</option>
                            <option value="3">Type 3: Sausage with cracks</option>
                            <option value="4">Type 4: Smooth & soft</option>
                            <option value="5">Type 5: Soft blobs</option>
                            <option value="6">Type 6: Fluffy, mushy</option>
                            <option value="7">Type 7: Watery, no solid</option>
                        </select>
                    </div>

                    {{-- Urination Frequency --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Urination Frequency</label>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="urinationFreq = Math.max(0, urinationFreq - 1)"
                                    class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 flex items-center justify-center transition font-bold text-lg">
                                −
                            </button>
                            <input type="number" name="urination_frequency" x-model="urinationFreq" min="0" max="30"
                                   class="w-16 text-center rounded-lg border border-gray-300 bg-gray-50 py-2.5 text-sm font-bold focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none">
                            <button type="button" @click="urinationFreq++"
                                    class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 flex items-center justify-center transition font-bold text-lg">
                                +
                            </button>
                            <span class="text-sm text-gray-500">times since last log</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Observations ─── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <span class="text-xl">📋</span>
                    <h3 class="text-lg font-bold text-gray-900">Observations</h3>
                </div>

                <textarea name="observation" x-model="observation" rows="4"
                          placeholder="Add any specific behavioral notes, changes in appetite, or concerns..."
                          class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none resize-none"></textarea>
            </div>

            {{-- ─── Submit Actions ─── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center space-x-2" x-show="isFormComplete">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-emerald-600 italic">Form is complete and ready to submit</span>
                    </div>
                    <div x-show="!isFormComplete" class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm text-amber-600 italic">Please select a patient and mood to submit</span>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button type="button" @click="saveDraft()"
                                class="inline-flex items-center px-5 py-2.5 border-2 border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition">
                            Save Draft
                        </button>
                        <button type="submit"
                                :disabled="!isFormComplete || formSubmitting"
                                :class="isFormComplete && !formSubmitting
                                    ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-lg shadow-purple-200'
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all">
                            <span x-text="formSubmitting ? 'Submitting...' : 'Submit Entry'"></span>
                            <svg x-show="!formSubmitting" class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function dailyHealthLog() {
    return {
        // Patient
        selectedPatientId: '{{ $selectedPatient ?? '' }}',
        selectedPatientName: '{{ $selectedPatientName ?? 'Select patient' }}',
        draftSavedAt: null,

        // Date/time
        currentDate: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
        currentTime: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),

        // Mood
        mood: '{{ old('mood', '') }}',
        moods: [
            { value: 'happy',    emoji: '😊', label: 'Happy' },
            { value: 'neutral',  emoji: '😐', label: 'Neutral' },
            { value: 'low',      emoji: '😢', label: 'Low' },
            { value: 'agitated', emoji: '😠', label: 'Agitated' },
            { value: 'confused', emoji: '😵', label: 'Confused' },
        ],

        // Vitals
        bpSystolic: '{{ old('bp_systolic', '') }}',
        bpDiastolic: '{{ old('bp_diastolic', '') }}',
        bloodSugar: '{{ old('blood_sugar', '') }}',
        temperature: '{{ old('temperature', '') }}',

        // Physical Activity
        steps: '{{ old('steps', '') }}',
        assistanceLevel: '{{ old('assistance_level', 'None') }}',
        assistanceLevels: ['None', 'Partial', 'Full'],

        // Nutrition
        nutritionPercent: {{ old('nutrition_percent', 0) }},
        eliminationScale: '{{ old('elimination_scale', '') }}',
        urinationFreq: {{ old('urination_frequency', 0) }},

        // Observations
        observation: '{{ old('observation', '') }}',

        // Form state
        formSubmitting: false,

        get isFormComplete() {
            return this.selectedPatientId !== '' && this.mood !== '';
        },

        saveDraft() {
            const data = {
                patient_id: this.selectedPatientId,
                mood: this.mood,
                steps: this.steps,
                nutrition_percent: this.nutritionPercent,
                elimination_scale: this.eliminationScale,
                observation: this.observation,
                temperature: this.temperature,
            };
            localStorage.setItem('caregiver_daily_log_draft', JSON.stringify(data));
            this.draftSavedAt = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        },

        init() {
            // Update time every minute
            setInterval(() => {
                this.currentTime = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            }, 60000);

            // Auto-save draft every 60 seconds
            setInterval(() => {
                if (this.selectedPatientId) {
                    this.saveDraft();
                }
            }, 60000);

            // Restore draft if available
            const draft = localStorage.getItem('caregiver_daily_log_draft');
            if (draft && !this.selectedPatientId) {
                try {
                    const d = JSON.parse(draft);
                    if (d.mood) this.mood = d.mood;
                    if (d.steps) this.steps = d.steps;
                    if (d.nutrition_percent) this.nutritionPercent = d.nutrition_percent;
                    if (d.elimination_scale) this.eliminationScale = d.elimination_scale;
                    if (d.observation) this.observation = d.observation;
                    if (d.temperature) this.temperature = d.temperature;
                    this.draftSavedAt = 'restored';
                } catch (e) {}
            }
        }
    }
}
</script>
@endpush
@endsection
