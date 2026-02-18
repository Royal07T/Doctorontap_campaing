<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
     x-data="{ expanded: true }">

    <div class="flex items-center justify-between mb-4 cursor-pointer" @click="expanded = !expanded">
        <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Record Vital Signs
        </h3>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div x-show="expanded" x-collapse>
        {{-- Success message --}}
        @if($showSuccess)
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center text-emerald-700 text-sm"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.set('showSuccess', false) }, 4000)">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Vital signs recorded successfully!
        </div>
        @endif

        {{-- Flag status banner --}}
        @if($flagMessage)
        <div class="mb-4 p-3 rounded-lg flex items-center text-sm font-medium
            {{ $flagStatus === 'critical' ? 'bg-red-50 border border-red-300 text-red-800' : 'bg-amber-50 border border-amber-300 text-amber-800' }}">
            @if($flagStatus === 'critical')
                <svg class="w-5 h-5 mr-2 flex-shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            @else
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            @endif
            {{ $flagMessage }}
        </div>
        @endif

        <form wire:submit="save" class="space-y-4">
            {{-- Blood Pressure (required) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure <span class="text-red-500">*</span></label>
                <div class="flex items-center space-x-2">
                    <div class="flex-1">
                        <input type="number" wire:model.blur="blood_pressure_systolic" placeholder="Systolic"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"
                               min="60" max="260">
                    </div>
                    <span class="text-gray-500 font-bold">/</span>
                    <div class="flex-1">
                        <input type="number" wire:model.blur="blood_pressure_diastolic" placeholder="Diastolic"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"
                               min="30" max="160">
                    </div>
                    <span class="text-xs text-gray-400">mmHg</span>
                </div>
                @error('blood_pressure_systolic') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @error('blood_pressure_diastolic') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Two-column grid --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heart Rate</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="heart_rate" placeholder="72"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm pr-12"
                               min="30" max="220">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">bpm</span>
                    </div>
                    @error('heart_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SpO2</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="oxygen_saturation" placeholder="98"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm pr-8"
                               min="50" max="100" step="0.1">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">%</span>
                    </div>
                    @error('oxygen_saturation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperature</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="temperature" placeholder="36.6"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm pr-8"
                               min="30" max="45" step="0.1">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">°C</span>
                    </div>
                    @error('temperature') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Blood Sugar</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="blood_sugar" placeholder="110"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm pr-14"
                               min="20" max="600" step="0.1">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">mg/dL</span>
                    </div>
                    @error('blood_sugar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Resp. Rate</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="respiratory_rate" placeholder="16"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm pr-14"
                               min="5" max="60">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">br/m</span>
                    </div>
                    @error('respiratory_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight</label>
                    <div class="relative">
                        <input type="number" wire:model.blur="weight" placeholder="70"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm pr-8"
                               min="1" max="500" step="0.1">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">kg</span>
                    </div>
                    @error('weight') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea wire:model="notes" rows="2" placeholder="Additional observations..."
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"></textarea>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full purple-gradient text-white py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition-opacity flex items-center justify-center"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <svg wire:loading class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading.remove>Save Vital Signs</span>
                <span wire:loading>Saving...</span>
            </button>
        </form>
    </div>
</div>
