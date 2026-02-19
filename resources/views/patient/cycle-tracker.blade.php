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
                <button @click="resetTracker" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-semibold text-sm transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Tracker</span>
                </button>
                <button @click="logToday" class="inline-flex items-center gap-2 px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg uppercase tracking-tight">
                    <span>+ Log Today</span>
                </button>
            </div>
        </div>

        <!-- Set New Menstruation Date & Cycle Parameters -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Set New Menstruation Date</h2>
                    <p class="text-sm text-gray-500 mt-1">Create a new cycle entry and update all predictions</p>
                </div>
            </div>
            <form id="newMenstruationForm" action="{{ route('patient.menstrual-cycle.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Menstruation Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Menstruation Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date" 
                               value="{{ old('start_date', now()->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all"
                               required>
                        <p class="text-xs text-gray-500 mt-1">The first day of your period</p>
                    </div>

                    <!-- Period Duration -->
                    <div>
                        <label for="period_length" class="block text-sm font-semibold text-gray-700 mb-2">
                            Period Duration (Days)
                        </label>
                        <input type="number" 
                               name="period_length" 
                               id="period_length" 
                               value="{{ old('period_length', $averagePeriodLength ?? 5) }}"
                               min="1" 
                               max="10"
                               class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all"
                               placeholder="5">
                        <p class="text-xs text-gray-500 mt-1">How many days your period typically lasts</p>
                    </div>
                </div>

                <!-- Flow Intensity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="flow_intensity" class="block text-sm font-semibold text-gray-700 mb-2">
                            Flow Intensity
                        </label>
                        <select name="flow_intensity" 
                                id="flow_intensity"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                            <option value="">Select flow intensity</option>
                            <option value="light" {{ old('flow_intensity') === 'light' ? 'selected' : '' }}>Light</option>
                            <option value="moderate" {{ old('flow_intensity') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                            <option value="heavy" {{ old('flow_intensity') === 'heavy' ? 'selected' : '' }}>Heavy</option>
                        </select>
                    </div>

                    <!-- End Date (Optional) -->
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Period End Date (Optional)
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date" 
                               value="{{ old('end_date') }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Leave empty if period is still ongoing</p>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="2"
                              maxlength="500"
                              class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all"
                              placeholder="Any additional notes about this cycle...">{{ old('notes') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" 
                            onclick="document.getElementById('newMenstruationForm').reset()"
                            class="px-6 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Clear
                    </button>
                    <button type="submit" 
                            id="submitMenstruationBtn"
                            class="px-6 py-3 bg-rose-600 text-white text-sm font-semibold rounded-xl hover:bg-rose-700 transition-colors shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span id="submitText">Set Menstruation Date</span>
                        <span id="submitLoading" class="hidden">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Calendar Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-8 py-5 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <h2 class="text-lg font-semibold text-gray-900" x-text="currentMonthName + ' ' + currentYear"></h2>
                                <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                            <button @click="goToToday" class="text-xs font-semibold text-purple-600 hover:text-purple-700 bg-purple-50 px-3 py-1.5 rounded-lg hover:bg-purple-100 transition-colors">Today</button>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Calendar Grid -->
                        <div class="mb-4">
                            <!-- Day Headers -->
                            <div class="grid grid-cols-7 gap-1 mb-2" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));">
                                <div class="text-center text-xs font-bold text-gray-500 py-2">S</div>
                                <div class="text-center text-xs font-bold text-gray-500 py-2">M</div>
                                <div class="text-center text-xs font-bold text-gray-500 py-2">T</div>
                                <div class="text-center text-xs font-bold text-gray-500 py-2">W</div>
                                <div class="text-center text-xs font-bold text-gray-500 py-2">T</div>
                                <div class="text-center text-xs font-bold text-gray-500 py-2">F</div>
                                <div class="text-center text-xs font-bold text-gray-500 py-2">S</div>
                            </div>
                            
                            <!-- Calendar Days -->
                            <div class="grid grid-cols-7 gap-1" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));">
                                <template x-for="(day, index) in calendarDays" :key="index">
                                    <button type="button"
                                            @click="selectDate(day)"
                                            :class="[
                                                !day.isCurrentMonth ? 'text-gray-300' : '',
                                                isSelected(day) ? 'bg-purple-600 text-white font-bold ring-2 ring-purple-300' : 
                                                isToday(day) ? 'bg-purple-50 text-purple-700 font-semibold border-2 border-purple-300' :
                                                isPeriodDay(day) ? 'bg-pink-100 text-pink-700 hover:bg-pink-200' :
                                                isFertileDay(day) ? 'bg-green-50 text-green-700 hover:bg-green-100' :
                                                'text-gray-700 hover:bg-gray-50',
                                                'aspect-square rounded-lg transition-all text-sm flex items-center justify-center relative'
                                            ]"
                                            x-text="day.day">
                                        <template x-if="isPeriodDay(day)">
                                            <span class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-pink-500 rounded-full"></span>
                                        </template>
                                        <template x-if="isFertileDay(day)">
                                            <span class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-green-500 rounded-full"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Legend -->
                        <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-gray-100 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-pink-100 border border-pink-300 rounded"></div>
                                <span class="text-gray-600">Period</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-50 border border-green-300 rounded"></div>
                                <span class="text-gray-600">Fertile Window</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-purple-50 border-2 border-purple-300 rounded"></div>
                                <span class="text-gray-600">Today</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-purple-600 rounded"></div>
                                <span class="text-gray-600">Selected</span>
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
                    <div class="rounded-3xl p-6 relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%);">
                        <div class="absolute top-4 right-4 w-16 h-16 rounded-full flex items-center justify-center" style="background: rgba(255, 255, 255, 0.1);">
                            <svg class="w-8 h-8" fill="white" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #FCE7F3;">Health Tip</p>
                            <h3 class="text-2xl font-black mb-3" style="color: #FFFFFF;">OVULATION WINDOW</h3>
                            <p class="text-lg font-bold mb-2" style="color: #FFFFFF;" x-text="ovulationWindow"></p>
                            <p class="text-sm" style="color: #FDF2F8;">Most fertile expected in 4 days</p>
                        </div>
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
        <div class="mt-2 lg:mt-3 bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
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
                <button type="button" @click="showOtherSymptomPrompt = true" class="flex flex-col items-center justify-center w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 text-gray-400 hover:border-gray-400 hover:text-gray-600 transition-all">
                    <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="text-xs font-bold">Other</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Symptom Prompt Modal -->
<div x-show="showOtherSymptomPrompt" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
     style="display: none;">
    <div @click.away="showOtherSymptomPrompt = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="bg-white rounded-2xl p-6 w-96 max-w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Add Custom Symptom</h3>
        <input type="text" 
               x-model="customSymptomName"
               @keyup.enter="addCustomSymptom"
               @keydown.escape="showOtherSymptomPrompt = false"
               placeholder="Enter symptom name"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100">
        <div class="flex justify-end gap-3 mt-4">
            <button @click="showOtherSymptomPrompt = false" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">Cancel</button>
            <button @click="addCustomSymptom" :disabled="!customSymptomName.trim()" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">Add</button>
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
        showOtherSymptomPrompt: false,
        customSymptomName: '',

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
            this.updatePredictions();
            this.loadExistingLog();
        },

        updateCalendar() {
            const firstDay = new Date(this.currentYear, this.currentMonth, 1);
            const startDay = firstDay.getDay(); // 0 = Sunday, 1 = Monday, etc.
            
            const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            const prevMonthDays = new Date(this.currentYear, this.currentMonth, 0).getDate();

            const days = [];

            // Previous month days (fill in days before the first day of the month)
            // startDay: 0 = Sunday, 1 = Monday, 2 = Tuesday, etc.
            // We need to fill in 'startDay' number of days from previous month
            // If startDay = 0 (Sunday), no previous days needed
            // If startDay = 1 (Monday), we need 1 previous day (Sunday = last day of prev month)
            // If startDay = 2 (Tuesday), we need 2 previous days (Sunday, Monday)
            for (let i = startDay; i > 0; i--) {
                const prevDay = prevMonthDays - i + 1;
                days.push({
                    day: prevDay,
                    month: this.currentMonth === 0 ? 11 : this.currentMonth - 1,
                    year: this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear,
                    isCurrentMonth: false,
                    date: new Date(this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear, 
                                   this.currentMonth === 0 ? 11 : this.currentMonth - 1, 
                                   prevDay)
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

        goToToday() {
            const today = new Date();
            this.currentMonth = today.getMonth();
            this.currentYear = today.getFullYear();
            this.selectedDate = today;
            this.updateCalendar();
            this.updatePredictions();
            this.loadExistingLog();
        },

        getSelectedDateForInput() {
            return this.selectedDate.toISOString().split('T')[0];
        },

        setSelectedDateFromInput(value) {
            if (!value) {
                return;
            }

            const d = new Date(value + 'T00:00:00');
            this.selectedDate = d;
            this.currentMonth = d.getMonth();
            this.currentYear = d.getFullYear();
            this.updateCalendar();
            this.updatePredictions();
            this.loadExistingLog();
        },

        updatePredictions() {
            // Calculate days until next period (simplified logic)
            const today = new Date();
            const selected = new Date(this.selectedDate);
            const diffTime = selected - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            // Update days until next period
            this.daysUntilNextPeriod = Math.max(0, 21 - diffDays);
            
            // Update current cycle day
            this.currentCycleDay = Math.max(1, 28 - diffDays);
            
            // Update ovulation window (typically days 12-16 of cycle)
            const cycleStart = new Date(selected);
            cycleStart.setDate(cycleStart.getDate() - this.currentCycleDay + 1);
            const ovulationStart = new Date(cycleStart);
            ovulationStart.setDate(ovulationStart.getDate() + 11);
            const ovulationEnd = new Date(cycleStart);
            ovulationEnd.setDate(ovulationEnd.getDate() + 16);
            
            const options = { month: 'short', day: 'numeric' };
            this.ovulationWindow = `${ovulationStart.toLocaleDateString('en-US', options)} - ${ovulationEnd.toLocaleDateString('en-US', options)}`;
        },

        selectDate(dateObj) {
            this.selectedDate = new Date(dateObj.year, dateObj.month, dateObj.day);
            this.updatePredictions();
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
            // Calculate fertile window based on cycle data
            // Fertile window: 5 days before ovulation to 1 day after (typically days 9-15 of a 28-day cycle)
            if (this.cycles.length === 0) return false;
            
            const date = new Date(dateObj.year, dateObj.month, dateObj.day);
            const d = date.toISOString().split('T')[0];
            
            // Check each cycle to see if this date falls in the fertile window
            for (let cycle of this.cycles) {
                if (!cycle.start_date) continue;
                
                const cycleStart = new Date(cycle.start_date);
                const cycleLength = cycle.cycle_length || 28; // Default to 28 days
                const ovulationDay = Math.floor(cycleLength / 2); // Typically day 14 of 28-day cycle
                
                // Fertile window: 5 days before ovulation to 1 day after
                const fertileStart = new Date(cycleStart);
                fertileStart.setDate(fertileStart.getDate() + ovulationDay - 5);
                
                const fertileEnd = new Date(cycleStart);
                fertileEnd.setDate(fertileEnd.getDate() + ovulationDay + 1);
                
                const fertileStartStr = fertileStart.toISOString().split('T')[0];
                const fertileEndStr = fertileEnd.toISOString().split('T')[0];
                
                // Check if date is in fertile window and not during period
                if (d >= fertileStartStr && d <= fertileEndStr && !this.isPeriodDay(dateObj)) {
                    return true;
                }
            }
            
            return false;
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

        addCustomSymptom() {
            const name = this.customSymptomName.trim();
            if (!name) return;
            
            // Check if symptom already exists
            if (this.formData.symptoms.includes(name)) {
                this.showOtherSymptomPrompt = false;
                this.customSymptomName = '';
                return;
            }
            
            // Add to symptoms array
            this.formData.symptoms.push(name);
            
            // Reset modal
            this.showOtherSymptomPrompt = false;
            this.customSymptomName = '';
        },

        logToday() {
            this.goToToday();
            // Scroll to the daily log form
            const logForm = document.querySelector('.sticky.top-6');
            if (logForm) {
                logForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        date: this.selectedDate.toISOString().split('T')[0],
                        mood: this.formData.mood,
                        flow: this.formData.flow,
                        sleep: this.formData.sleep === '' || this.formData.sleep === null ? null : Math.round(Number(this.formData.sleep)),
                        water: this.formData.water === '' || this.formData.water === null ? null : Number(this.formData.water),
                        urination: this.formData.urination === 'low' ? 1 : (this.formData.urination === 'normal' ? 2 : (this.formData.urination === 'high' ? 3 : null)),
                        eating_habits: this.formData.eating === '' || this.formData.eating === null ? null : Number(this.formData.eating),
                        symptoms: this.formData.symptoms
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
        },

        async resetTracker() {
            if (!confirm('Are you sure you want to reset your cycle tracker? This will permanently delete all your menstrual cycles and daily logs. This action cannot be undone.')) {
                return;
            }

            if (!confirm('This is your final confirmation. All cycle data will be permanently deleted. Continue?')) {
                return;
            }

            try {
                @php
                    try {
                        $resetUrl = route('patient.cycle-tracker.reset');
                    } catch (\Exception $e) {
                        $resetUrl = url('/patient/cycle-tracker/reset');
                    }
                @endphp
                const response = await fetch('{{ $resetUrl }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    // Clear local data
                    this.cycles = [];
                    this.dailyLogs = [];
                    this.formData = {
                        mood: '',
                        flow: '',
                        sleep: 7.5,
                        water: 3,
                        urination: 'normal',
                        eating: 50,
                        symptoms: []
                    };
                    
                    // Reset predictions
                    this.currentCycleDay = 1;
                    this.daysUntilNextPeriod = 28;
                    this.ovulationWindow = 'Not available';
                    
                    // Reload page to refresh all data
                    if (window.showCustomAlert) {
                        window.showCustomAlert('Success', 'Cycle tracker has been reset successfully', 'success', () => {
                            window.location.reload();
                        });
                    } else {
                        alert('Cycle tracker has been reset successfully');
                        window.location.reload();
                    }
                } else {
                    if (window.showCustomAlert) {
                        window.showCustomAlert('Error', data.message || 'Failed to reset cycle tracker', 'error');
                    } else {
                        alert(data.message || 'Failed to reset cycle tracker');
                    }
                }
            } catch (error) {
                console.error('Error resetting tracker:', error);
                if (window.showCustomAlert) {
                    window.showCustomAlert('Error', 'Connection error. Please try again.', 'error');
                } else {
                    alert('Connection error. Please try again.');
                }
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newMenstruationForm');
    const submitBtn = document.getElementById('submitMenstruationBtn');
    const submitText = document.getElementById('submitText');
    const submitLoading = document.getElementById('submitLoading');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate end_date is after start_date if both are provided
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (endDate && endDate < startDate) {
                alert('End date must be after or equal to start date');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            
            // Get form data
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    successMsg.innerHTML = `
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>${data.message || 'Menstrual cycle recorded successfully!'}</span>
                    `;
                    document.body.appendChild(successMsg);
                    
                    // Reload page after 1.5 seconds to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Failed to record menstrual cycle');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                
                // Re-enable button
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            });
        });
    }
});
</script>
@endpush

@endsection
