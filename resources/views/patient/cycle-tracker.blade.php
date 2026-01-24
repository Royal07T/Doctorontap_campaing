@extends('layouts.patient')

@section('title', 'Menstrual Cycle Tracker')

@section('content')
<div class="max-w-7xl mx-auto" x-data="cycleTracker()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Calendar Column -->
        <div class="lg:col-span-2">
            <div class="bg-[#1a1a1a] rounded-[2.5rem] shadow-2xl border border-white/5 overflow-hidden">
                <!-- Header -->
                <div class="p-8 border-b border-white/5">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-2 text-white/90 cursor-pointer group">
                            <span class="text-xl font-bold tracking-tight">Calendars</span>
                            <svg class="w-5 h-5 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <button class="text-white/60 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between px-4">
                        <button @click="previousMonth" class="text-white/40 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <h2 class="text-2xl font-black text-white tracking-widest uppercase" x-text="currentMonthName"></h2>
                        <button @click="nextMonth" class="text-white/40 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Calendar Content -->
                <div class="p-8">
                    <div class="grid grid-cols-8 gap-1">
                        <!-- Empty corner for week index header -->
                        <div></div>
                        <!-- Weekdays -->
                        <template x-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']">
                            <div class="text-center text-xs font-bold text-white/40 py-4" x-text="day"></div>
                        </template>

                        <!-- Calendar Grid with Week Index -->
                        <template x-for="(week, weekIndex) in calendarStructure">
                            <div class="contents group/week">
                                <!-- Week Index -->
                                <div class="flex items-center justify-center py-2 px-1">
                                    <div class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/40 text-sm font-bold" x-text="weekIndex + 1"></div>
                                </div>
                                
                                <!-- Days in Week -->
                                <template x-for="dateObj in week">
                                    <div @click="selectDate(dateObj)" 
                                         class="relative py-4 flex flex-col items-center justify-center cursor-pointer transition-all duration-300"
                                         :class="{
                                             'bg-white/5 rounded-2xl': isDateInSelectedWeek(dateObj)
                                         }">
                                        
                                        <!-- Highlight circle for today/selected -->
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center relative z-10 transition-all duration-300"
                                             :class="{
                                                 'bg-[#677e6b] text-white': isToday(dateObj),
                                                 'bg-white/10': isSelected(dateObj) && !isToday(dateObj),
                                                 'text-white/20': !dateObj.isCurrentMonth,
                                                 'text-white': dateObj.isCurrentMonth && !isToday(dateObj)
                                             }">
                                            <span class="text-sm font-bold" x-text="dateObj.day"></span>
                                        </div>

                                        <!-- Period indicator -->
                                        <template x-if="isPeriodDay(dateObj)">
                                            <div class="absolute bottom-2 w-1 h-1 rounded-full bg-rose-500"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Stats/Legend -->
                <div class="bg-white/5 p-8 border-t border-white/5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Next Period</p>
                            <p class="text-lg font-black text-rose-500">
                                @if($nextPeriodPrediction)
                                    {{ $nextPeriodPrediction->format('M d') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Current Phase</p>
                            <p class="text-lg font-black text-purple-500">{{ $currentCycle ? 'Menstrual' : 'Follicular' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Avg. Cycle</p>
                            <p class="text-lg font-black text-blue-500">{{ $averageCycleLength }} Days</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Total Logs</p>
                            <p class="text-lg font-black text-emerald-500">{{ count($dailyLogs) }} Records</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sidebar Column (Daily Log) -->
        <div class="space-y-6">
            <div class="bg-[#1a1a1a] rounded-[2.5rem] p-8 shadow-2xl border border-white/5 sticky top-6">
                <div class="mb-8">
                    <h3 class="text-xl font-black text-white tracking-tight" x-text="formatSelectedDate()"></h3>
                    <p class="text-sm text-white/40 font-medium leading-relaxed mt-2">Update your daily symptoms and feelings.</p>
                </div>

                <form @submit.prevent="saveLog" class="space-y-6">
                    <!-- Flow Intensity -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-1">Flow Intensity</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="f in ['light', 'moderate', 'heavy']">
                                <button type="button" @click="formData.flow = f" 
                                        :class="formData.flow === f ? 'bg-rose-600 text-white' : 'bg-white/5 text-white/40 hover:bg-white/10'"
                                        class="py-3 rounded-2xl text-xs font-bold transition-all capitalize" x-text="f"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Mood Selection -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-1">Your Mood</label>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="m in ['Happy', 'Sad', 'Calm', 'Stressed', 'Tired', 'Energetic', 'Graceful', 'Sensitive']">
                                <button type="button" @click="toggleMood(m)"
                                        :class="formData.mood === m ? 'bg-purple-600 text-white' : 'bg-white/5 text-white/40 hover:bg-white/10'"
                                        class="py-3 rounded-2xl text-[10px] font-bold transition-all" x-text="m"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Symptoms -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-1">Symptoms</label>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="s in ['Cramps', 'Headache', 'Bloating', 'Acne', 'Backache', 'Tender Breasts']">
                                <button type="button" @click="toggleSymptom(s)"
                                        :class="formData.symptoms.includes(s) ? 'bg-blue-600 text-white' : 'bg-white/5 text-white/40 hover:bg-white/10'"
                                        class="py-3 rounded-2xl text-[10px] font-bold transition-all" x-text="s"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-white/40 uppercase tracking-widest px-1">Daily Notes</label>
                        <textarea x-model="formData.notes" rows="3" 
                                  class="w-full bg-white/5 border-white/10 rounded-2xl text-sm p-4 text-white focus:ring-2 focus:ring-rose-500 focus:bg-white/10 transition-all placeholder-white/20"
                                  placeholder="How are you feeling?"></textarea>
                    </div>

                    <button type="submit" 
                            :disabled="saving"
                            class="w-full bg-rose-600 text-white font-bold py-5 rounded-2xl hover:bg-rose-700 transition-all shadow-xl shadow-rose-900/20 flex items-center justify-center space-x-2 disabled:opacity-50">
                        <template x-if="!saving">
                            <span>Save Entry</span>
                        </template>
                        <template x-if="saving">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                    </button>
                </form>
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
        calendarStructure: [],
        currentMonthName: '',
        saving: false,

        // Data from server
        cycles: @json($cycles),
        dailyLogs: @json($dailyLogs),

        formData: {
            flow: '',
            mood: '',
            symptoms: [],
            notes: ''
        },

        init() {
            this.updateCalendar();
            this.loadExistingLog();
        },

        updateCalendar() {
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            
            // Get the first Sunday before or on the 1st of the month
            const startDate = new Date(firstDay);
            startDate.setDate(1 - firstDay.getDay());

            // Build weeks until we cover the whole month (fixed 6 weeks/42 days for consistency)
            const structure = [];
            let currentWeek = [];
            let tempDate = new Date(startDate);

            for (let i = 0; i < 42; i++) {
                currentWeek.push({
                    day: tempDate.getDate(),
                    month: tempDate.getMonth(),
                    year: tempDate.getFullYear(),
                    isCurrentMonth: tempDate.getMonth() === this.currentMonth,
                    date: new Date(tempDate)
                });

                if (currentWeek.length === 7) {
                    structure.push(currentWeek);
                    currentWeek = [];
                }
                tempDate.setDate(tempDate.getDate() + 1);
            }

            this.calendarStructure = structure;
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

        isDateInSelectedWeek(dateObj) {
            // Find which week the selected date is in
            const week = this.calendarStructure.find(w => 
                w.some(d => d.day === this.selectedDate.getDate() && 
                            d.month === this.selectedDate.getMonth() &&
                            d.year === this.selectedDate.getFullYear())
            );
            
            return week && week.some(d => d.day === dateObj.day && 
                                       d.month === dateObj.month &&
                                       d.year === dateObj.year);
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

        formatSelectedDate() {
            return new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).format(this.selectedDate);
        },

        toggleMood(mood) {
            this.formData.mood = this.formData.mood === mood ? '' : mood;
        },

        toggleSymptom(s) {
            if (this.formData.symptoms.includes(s)) {
                this.formData.symptoms = this.formData.symptoms.filter(sym => sym !== s);
            } else {
                this.formData.symptoms.push(s);
            }
        },

        loadExistingLog() {
            const dStr = this.selectedDate.toISOString().split('T')[0];
            const log = this.dailyLogs.find(l => l.date.split('T')[0] === dStr);
            
            if (log) {
                this.formData = {
                    flow: log.flow || '',
                    mood: log.mood || '',
                    symptoms: log.symptoms || [],
                    notes: log.notes || ''
                };
            } else {
                this.formData = {
                    flow: '',
                    mood: '',
                    symptoms: [],
                    notes: ''
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
@endpush

@endsection
