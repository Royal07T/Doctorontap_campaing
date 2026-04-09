<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ expanded: true }">
    <div class="flex items-center justify-between mb-4 cursor-pointer" @click="expanded = !expanded">
        <h3 class="text-lg font-bold text-gray-900 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            Medication Tracker
            <span class="ml-2 text-xs font-normal px-2 py-0.5 rounded-full
                {{ $compliance >= 80 ? 'bg-emerald-100 text-emerald-700' : ($compliance >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                {{ $compliance }}% (7d)
            </span>
        </h3>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div x-show="expanded" x-collapse>
        {{-- Success --}}
        @if($showSuccess)
        <div class="mb-3 p-2 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm"
             x-data="{ s: true }" x-show="s" x-init="setTimeout(() => { s = false; $wire.set('showSuccess', false) }, 3000)">
            Medication added!
        </div>
        @endif

        {{-- Today's schedule --}}
        <div class="space-y-2 mb-4">
            @forelse($todayMeds as $med)
            <div class="flex items-center justify-between p-3 rounded-lg border
                {{ $med->status === 'given' ? 'border-emerald-200 bg-emerald-50' : ($med->status === 'missed' ? 'border-red-200 bg-red-50' : ($med->isOverdue() ? 'border-amber-200 bg-amber-50' : 'border-gray-200')) }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $med->medication_name }}</p>
                    <p class="text-xs text-gray-500">{{ $med->dosage }} &middot; {{ $med->scheduled_time->format('h:i A') }}</p>
                </div>
                <div class="flex items-center space-x-1 ml-2">
                    @if($med->status === 'pending')
                        <button wire:click="markGiven({{ $med->id }})" title="Given"
                                class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <button wire:click="markMissed({{ $med->id }})" title="Missed"
                                class="p-1.5 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <button wire:click="markSkipped({{ $med->id }})" title="Skipped"
                                class="p-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </button>
                    @else
                        <span class="text-xs font-medium px-2 py-1 rounded-full
                            {{ $med->status === 'given' ? 'bg-emerald-200 text-emerald-800' : ($med->status === 'missed' ? 'bg-red-200 text-red-800' : 'bg-gray-200 text-gray-700') }}">
                            {{ ucfirst($med->status) }}
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-gray-400">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <p class="text-sm">No medications scheduled today</p>
            </div>
            @endforelse
        </div>

        {{-- Add medication button / form --}}
        @if(!$showForm)
        <button wire:click="$set('showForm', true)"
                class="w-full py-2 border-2 border-dashed border-purple-300 rounded-lg text-purple-600 hover:bg-purple-50 text-sm font-medium transition-colors">
            + Add Medication
        </button>
        @else
        <div class="border border-purple-200 rounded-lg p-4 bg-purple-50/50 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Medication Name *</label>
                    <input type="text" wire:model="medicationName" placeholder="e.g. Metformin"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                    @error('medicationName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Dosage *</label>
                    <input type="text" wire:model="dosage" placeholder="e.g. 500mg"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                    @error('dosage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Scheduled Time</label>
                    <input type="datetime-local" wire:model="scheduledTime"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                    <input type="text" wire:model="medNotes" placeholder="Take after meals"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                </div>
            </div>
            <div class="flex space-x-2">
                <button wire:click="addMedication"
                        class="flex-1 purple-gradient text-white py-2 rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>Save</span>
                    <span wire:loading>Saving...</span>
                </button>
                <button wire:click="$set('showForm', false)"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 text-sm hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </div>
        @endif
    </div>
</div>
