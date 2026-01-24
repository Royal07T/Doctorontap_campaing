@extends('layouts.patient')

@section('title', 'Menstrual Cycle Tracker')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 md:px-6" x-data="cycleTracker()">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Menstrual Cycle Tracker</h1>
                <p class="text-sm text-gray-500 mt-1">Tracking your health metrics for better cycle prediction.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg uppercase tracking-tight">
                    <span>+ Log Today</span>
                </button>
                <button class="p-3 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Calendar Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Month Navigation -->
                    <div class="px-8 py-6 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <h2 class="text-xl font-black text-gray-900" x-text="currentMonthName + ' ' + currentYear"></h2>
                            <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-8">
                        <div class="grid grid-cols-7 gap-2">
                            <!-- Weekday Headers -->
                            <template x-for="day in ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']">
                                <div class="text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider py-3" x-text="day"></div>
                            </template>

                            <!-- Calendar Days -->
                            <template x-for="dateObj in calendarDays">
                                <div @click="selectDate(dateObj)" 
                                     class="relative aspect-square flex items-center justify-center cursor-pointer group"
                                     :class="!dateObj.isCurrentMonth ? 'opacity-30' : ''">
                                    
                                    <!-- Date Number -->
                                    <div class="relative z-10 w-10 h-10 flex items-center justify-center rounded-full transition-all"
                                         :class="{
                                             'bg-purple-600 text-white shadow-lg': isToday(dateObj),
                                             'bg-purple-100 text-purple-600': isSelected(dateObj) && !isToday(dateObj),
                                             'hover:bg-gray-100': !isToday(dateObj) && !isSelected(dateObj),
                                             'text-gray-900': dateObj.isCurrentMonth && !isToday(dateObj) && !isSelected(dateObj),
                                             'text-gray-400': !dateObj.isCurrentMonth
                                         }">
                                        <span class="text-sm font-semibold" x-text="dateObj.day"></span>
                                    </div>

                                    <!-- Period Indicator (Pink Dot) -->
                                    <template x-if="isPeriodDay(dateObj)">
                                        <div class="absolute bottom-1 w-1.5 h-1.5 rounded-full bg-pink-500"></div>
                                    </template>

                                    <!-- Predicted Fertile (Purple Dashed Border) -->
                                    <template x-if="isFertileDay(dateObj)">
                                        <div class="absolute inset-0 border-2 border-dashed border-purple-400 rounded-full"></div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Legend -->
                        <div class="flex items-center gap-6 mt-6 pt-6 border-t border-gray-100">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-pink-500"></div>
                                <span class="text-xs text-gray-600">Period</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full border-2 border-dashed border-purple-400"></div>
                                <span class="text-xs text-gray-600">Predicted Fertile</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-purple-600"></div>
                                <span class="text-xs text-gray-600">Today</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prediction Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Next Period Card -->
                    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-3xl p-6 text-white relative overflow-hidden">
                        <div class="absolute top-4 right-4 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-purple-100 mb-2">Prediction</p>
                        <h3 class="text-2xl font-black mb-3">NEXT PERIOD IN</h3>
                        <p class="text-4xl font-black mb-2" x-text="daysUntilNextPeriod + ' Days'"></p>
                        <p class="text-sm text-purple-100">Estimated <span x-text="formatNextPeriodDate()"></span></p>
                    </div>

                    <!-- Ovulation Window Card -->
                    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-3xl p-6 text-white relative overflow-hidden">
                        <div class="absolute top-4 right-4 w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-pink-100 mb-2">Health Tip</p>
                        <h3 class="text-2xl font-black mb-3">OVULATION WINDOW</h3>
                        <p class="text-lg font-bold mb-2" x-text="ovulationWindow"></p>
                        <p class="text-sm text-pink-100">Most fertile expected in 4 days</p>
                    </div>
                </div>
            </div>

            <!-- Daily Log Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 sticky top-6">
                    <div class="mb-6">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1" x-text="formatDayOfWeek()"></p>
                        <h3 class="text-xl font-black text-gray-900" x-text="formatSelectedDateShort()"></h3>
                        <p class="text-xs text-gray-500 mt-1" x-text="'Day ' + currentCycleDay + ' of cycle'"></p>
                    </div>

                    <form @submit.prevent="saveLog" class="space-y-6">
                        <!-- Current Mood -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Current Mood</label>
                            <div class="grid grid-cols-5 gap-2">
                                <template x-for="(mood, index) in moods">
                                    <button type="button" @click="formData.mood = mood.value"
                                            :class="formData.mood === mood.value ? 'bg-purple-50 border-purple-200 scale-110' : 'bg-gray-50 border-gray-200 hover:bg-gray-100'"
                                            class="aspect-square rounded-xl border-2 flex flex-col items-center justify-center transition-all p-2">
                                        <span class="text-2xl" x-text="mood.emoji"></span>
                                        <span class="text-[8px] font-bold text-gray-600 mt-1" x-text="mood.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Flow -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">
                                <svg class="w-4 h-4 inline text-pink-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                </svg>
                                Flow
                            </label>
                            <div class="flex items-center gap-3">
                                <template x-for="flow in ['Light', 'Medium', 'Heavy']">
                                    <button type="button" @click="formData.flow = flow"
                                            :class="formData.flow === flow ? 'bg-pink-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                            class="flex-1 py-2 rounded-lg text-xs font-bold transition-all"
                                            x-text="flow"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Sleep -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">
                                <svg class="w-4 h-4 inline text-indigo-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                                </svg>
                                Sleep
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="number" step="0.5" min="0" max="24" x-model="formData.sleep"
                                       class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="7.5">
                                <span class="text-sm font-bold text-gray-900">hrs</span>
                            </div>
                        </div>

                        <!-- Water -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">
                                <svg class="w-4 h-4 inline text-blue-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Water
                            </label>
                            <div class="flex items-center justify-center gap-2">
                                <template x-for="i in 5">
                                    <button type="button" @click="formData.water = i"
                                            :class="i <= formData.water ? 'bg-blue-500' : 'bg-gray-200'"
                                            class="w-3 h-3 rounded-full transition-all"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Urination -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">
                                <svg class="w-4 h-4 inline text-amber-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                                </svg>
                                Urination
                            </label>
                            <select x-model="formData.urination" class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Select frequency</option>
                                <option value="low">Low Frequency</option>
                                <option value="normal">Normal Frequency</option>
                                <option value="high">High Frequency</option>
                            </select>
                        </div>

                        <!-- Eating Habits -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Eating Habits</label>
                            <div class="relative pt-6">
                                <input type="range" min="0" max="100" x-model="formData.eating"
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                                <div class="flex justify-between mt-2">
                                    <span class="text-[10px] font-bold text-gray-400">Bad</span>
                                    <span class="text-[10px] font-bold text-gray-600">Balanced</span>
                                    <span class="text-[10px] font-bold text-emerald-600">Excellent</span>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <button type="submit" :disabled="saving"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg disabled:opacity-50 flex items-center justify-center gap-2">
                            <template x-if="!saving">
                                <span>Save Log Entry</span>
                            </template>
                            <template x-if="saving">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                        </button>
                    </form>

                    <!-- Last 3 Months Stats -->
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                            </svg>
                            <h4 class="text-sm font-bold text-gray-900">Last 3 Months</h4>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-gray-600">Average Cycle</span>
                                    <span class="text-sm font-black text-gray-900">{{ $averageCycleLength }} Days</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" :style="`width: ${({{ $averageCycleLength }} / 35) * 100}%`"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-gray-600">Average Period</span>
                                    <span class="text-sm font-black text-gray-900">5 Days</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" style="width: 71%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Track Symptoms Section -->
        <div class="mt-8 bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
            <h3 class="text-xl font-black text-gray-900 mb-6">Track Symptoms</h3>
            <div class="flex flex-wrap gap-4">
                <template x-for="symptom in symptoms">
                    <button type="button" @click="toggleSymptom(symptom.value)"
                            :class="formData.symptoms.includes(symptom.value) ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                            class="flex flex-col items-center justify-center w-24 h-24 rounded-2xl border-2 transition-all">
                        <span class="text-3xl mb-1" x-text="symptom.emoji"></span>
                        <span class="text-xs font-bold" x-text="symptom.label"></span>
                    </button>
                </template>
                <button type="button" class="flex flex-col items-center justify-center w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 text-gray-400 hover:border-gray-400 hover:text-gray-600 transition-all">
                    <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="text-xs font-bold">Other</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cycleTracker() {
    return {
        selectedDate: new Date(),
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear(),
        calendarDays: [],
        currentMonthName: '',
        saving: false,
        currentCycleDay: 6,
        daysUntilNextPeriod: 21,
        ovulationWindow: 'Jan 17 - Jan 23',

        // Data from server
        cycles: @json($cycles),
        dailyLogs: @json($dailyLogs),

        moods: [
            { value: 'amazing', emoji: '🤩', label: 'Amazing' },
            { value: 'happy', emoji: '😊', label: 'Happy' },
            { value: 'normal', emoji: '😐', label: 'Normal' },
            { value: 'low', emoji: '😔', label: 'Low' },
            { value: 'irritable', emoji: '😤', label: 'Irritable' }
        ],

        symptoms: [
            { value: 'headache', emoji: '🤕', label: 'Headache' },
            { value: 'cramps', emoji: '😖', label: 'Cramps' },
            { value: 'cravings', emoji: '🍪', label: 'Cravings' },
            { value: 'fatigue', emoji: '😴', label: 'Fatigue' },
            { value: 'acne', emoji: '😷', label: 'Acne' },
            { value: 'bloating', emoji: '🎈', label: 'Bloating' }
        ],

        formData: {
            mood: '',
            flow: '',
            sleep: 7.5,
            water: 3,
            urination: 'normal',
            eating: 50,
            symptoms: []
        },

        init() {
            this.updateCalendar();
            this.loadExistingLog();
        },

        updateCalendar() {
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const startDay = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1; // Adjust for Monday start
            
            const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            const prevMonthDays = new Date(this.currentYear, this.currentMonth, 0).getDate();

            const days = [];

            // Previous month days
            for (let i = startDay - 1; i >= 0; i--) {
                days.push({
                    day: prevMonthDays - i,
                    month: this.currentMonth === 0 ? 11 : this.currentMonth - 1,
                    year: this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear,
                    isCurrentMonth: false,
                    date: new Date(this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear, 
                                   this.currentMonth === 0 ? 11 : this.currentMonth - 1, 
                                   prevMonthDays - i)
                });
            }

            // Current month days
            for (let i = 1; i <= daysInMonth; i++) {
                days.push({
                    day: i,
                    month: this.currentMonth,
                    year: this.currentYear,
                    isCurrentMonth: true,
                    date: new Date(this.currentYear, this.currentMonth, i)
                });
            }

            // Next month days
            const remaining = 42 - days.length; // Always show 6 weeks
            for (let i = 1; i <= remaining; i++) {
                days.push({
                    day: i,
                    month: this.currentMonth === 11 ? 0 : this.currentMonth + 1,
                    year: this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear,
                    isCurrentMonth: false,
                    date: new Date(this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear,
                                   this.currentMonth === 11 ? 0 : this.currentMonth + 1,
                                   i)
                });
            }

            this.calendarDays = days;
            this.currentMonthName = new Intl.DateTimeFormat('en-US', { month: 'long' }).format(new Date(this.currentYear, this.currentMonth));
        },

        previousMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
            this.updateCalendar();
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
            this.updateCalendar();
        },

        selectDate(dateObj) {
            this.selectedDate = new Date(dateObj.year, dateObj.month, dateObj.day);
            this.loadExistingLog();
        },

        isSelected(dateObj) {
            return this.selectedDate.getDate() === dateObj.day && 
                   this.selectedDate.getMonth() === dateObj.month &&
                   this.selectedDate.getFullYear() === dateObj.year;
        },

        isToday(dateObj) {
            const today = new Date();
            return today.getDate() === dateObj.day && 
                   today.getMonth() === dateObj.month &&
                   today.getFullYear() === dateObj.year;
        },

        isPeriodDay(dateObj) {
            const date = new Date(dateObj.year, dateObj.month, dateObj.day);
            const d = date.toISOString().split('T')[0];
            return this.cycles.some(cycle => {
                const start = cycle.start_date;
                const end = cycle.end_date || new Date(new Date(start).getTime() + 5 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                return d >= start && d <= end;
            });
        },

        isFertileDay(dateObj) {
            // Implement fertile window logic (typically days 10-17 of cycle)
            return false; // Placeholder
        },

        formatDayOfWeek() {
            return new Intl.DateTimeFormat('en-US', { weekday: 'short' }).format(this.selectedDate).toUpperCase();
        },

        formatSelectedDateShort() {
            return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' }).format(this.selectedDate).toUpperCase();
        },

        formatNextPeriodDate() {
            const nextDate = new Date();
            nextDate.setDate(nextDate.getDate() + this.daysUntilNextPeriod);
            return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(nextDate);
        },

        toggleSymptom(symptom) {
            if (this.formData.symptoms.includes(symptom)) {
                this.formData.symptoms = this.formData.symptoms.filter(s => s !== symptom);
            } else {
                this.formData.symptoms.push(symptom);
            }
        },

        loadExistingLog() {
            const dStr = this.selectedDate.toISOString().split('T')[0];
            const log = this.dailyLogs.find(l => l.date.split('T')[0] === dStr);
            
            if (log) {
                this.formData = {
                    mood: log.mood || '',
                    flow: log.flow || '',
                    sleep: log.sleep || 7.5,
                    water: log.water || 3,
                    urination: log.urination || 'normal',
                    eating: log.eating || 50,
                    symptoms: log.symptoms || []
                };
            } else {
                this.formData = {
                    mood: '',
                    flow: '',
                    sleep: 7.5,
                    water: 3,
                    urination: 'normal',
                    eating: 50,
                    symptoms: []
                };
            }
        },

        async saveLog() {
            this.saving = true;
            try {
                const response = await fetch('{{ route("patient.menstrual-daily-log.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        date: this.selectedDate.toISOString().split('T')[0],
                        ...this.formData
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    const index = this.dailyLogs.findIndex(l => l.date.split('T')[0] === data.log.date.split('T')[0]);
                    if (index !== -1) {
                        this.dailyLogs[index] = data.log;
                    } else {
                        this.dailyLogs.unshift(data.log);
                    }
                    
                    if (window.showCustomAlert) {
                        window.showCustomAlert('Success', 'Entry saved successfully', 'success');
                    } else {
                        alert('Entry saved successfully');
                    }
                }
            } catch (error) {
                console.error('Error saving log:', error);
                alert('Connection error. Please try again.');
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>

<style>
.slider::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #9333EA;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #9333EA;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    border: none;
}
</style>
@endpush

@endsection
