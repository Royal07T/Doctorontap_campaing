<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ expanded: true, showExtras: false }">
    <div class="flex items-center justify-between mb-4 cursor-pointer" @click="expanded = !expanded">
        <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <span class="mr-2 text-xl">😊</span>
            Mood &amp; Observation
        </h3>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div x-show="expanded" x-collapse>
        {{-- Success --}}
        @if($showSuccess)
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm"
             x-data="{ s: true }" x-show="s" x-init="setTimeout(() => { s = false; $wire.set('showSuccess', false) }, 3000)">
            Observation saved!
        </div>
        @endif

        <form wire:submit="save" class="space-y-4">
            {{-- Emoji Grid --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">How is the patient feeling? <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-5 gap-2">
                    @foreach($moodOptions as $code => $opt)
                    <button type="button"
                            wire:click="selectMood('{{ $code }}')"
                            class="flex flex-col items-center p-2 rounded-lg border-2 transition-all text-center
                                {{ $selectedMood === $code ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200' : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50' }}">
                        <span class="text-2xl">{{ $opt['emoji'] }}</span>
                        <span class="text-xs mt-1 text-gray-600 leading-tight">{{ $opt['label'] }}</span>
                    </button>
                    @endforeach
                </div>
                @error('selectedMood') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Pain Level Slider --}}
            <div x-data="{ pain: @entangle('painLevel').live }">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Pain Level: <span class="font-bold" x-text="pain !== null ? pain + '/10' : 'Not set'"></span>
                </label>
                <input type="range" min="0" max="10" step="1" x-model.number="pain"
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>None</span><span>Mild</span><span>Moderate</span><span>Severe</span><span>Worst</span>
                </div>
            </div>

            {{-- General Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">General Notes</label>
                <textarea wire:model="generalNotes" rows="2" placeholder="General observations..."
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"></textarea>
            </div>

            {{-- Extra fields toggle --}}
            <button type="button" @click="showExtras = !showExtras" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                <span x-text="showExtras ? '− Less details' : '+ More details (mobility, behavior)'"></span>
            </button>

            <div x-show="showExtras" x-collapse class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobility Notes</label>
                    <textarea wire:model="mobilityNotes" rows="2" placeholder="Walking ability, balance, assistance needed..."
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Behavior Notes <span class="text-xs text-gray-400">(encrypted)</span></label>
                    <textarea wire:model="behaviorNotes" rows="2" placeholder="Behavioral observations..."
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm"></textarea>
                </div>
            </div>

            <button type="submit"
                    class="w-full purple-gradient text-white py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition-opacity flex items-center justify-center"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Save Observation</span>
                <span wire:loading>Saving...</span>
            </button>
        </form>

        {{-- Recent observations --}}
        @if(count($recentObservations))
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-500 mb-2">Recent</p>
            <div class="flex items-center space-x-3 overflow-x-auto pb-1">
                @foreach($recentObservations as $obs)
                <div class="flex-shrink-0 text-center">
                    <span class="text-lg">{{ $obs['emoji'] }}</span>
                    <p class="text-xs text-gray-400">{{ $obs['time'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
