<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center space-x-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <span>Physiotherapy Sessions</span>
        </h3>
        <button wire:click="$toggle('showForm')" class="px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            {{ $showForm ? 'Cancel' : '+ New Session' }}
        </button>
    </div>

    @if(session('physio-success'))
        <div class="mb-3 px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs rounded-lg">{{ session('physio-success') }}</div>
    @endif

    {{-- Form --}}
    @if($showForm)
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-5">
        <h4 class="font-semibold text-indigo-800 mb-3">{{ $editingId ? 'Edit Session' : 'Schedule New Session' }}</h4>
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type *</label>
                    <select wire:model="sessionType" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="assessment">Assessment</option>
                        <option value="exercise">Exercise</option>
                        <option value="massage">Massage</option>
                        <option value="review">Review</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Scheduled At *</label>
                    <input type="datetime-local" wire:model="scheduledAt" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Duration (min)</label>
                    <input type="number" wire:model="durationMinutes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" min="5" max="300">
                </div>
            </div>

            {{-- Exercises --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gray-600">Exercises</label>
                    <button type="button" wire:click="addExercise" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Add Exercise</button>
                </div>
                @foreach($exercises as $idx => $ex)
                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500">Exercise {{ $idx + 1 }}</span>
                        <button type="button" wire:click="removeExercise({{ $idx }})" class="text-xs text-red-500">Remove</button>
                    </div>
                    <div class="grid grid-cols-5 gap-2">
                        <input type="text" wire:model="exercises.{{ $idx }}.name" placeholder="Name" class="border border-gray-300 rounded px-2 py-1.5 text-xs col-span-2">
                        <input type="number" wire:model="exercises.{{ $idx }}.sets" placeholder="Sets" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <input type="number" wire:model="exercises.{{ $idx }}.reps" placeholder="Reps" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <input type="text" wire:model="exercises.{{ $idx }}.duration" placeholder="Duration" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                    </div>
                </div>
                @endforeach
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Findings</label>
                    <textarea wire:model="findings" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Treatment Notes</label>
                    <textarea wire:model="treatmentNotes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pain Before (1-10)</label>
                    <input type="number" wire:model="painBefore" min="0" max="10" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pain After (1-10)</label>
                    <input type="number" wire:model="painAfter" min="0" max="10" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Mobility Score</label>
                    <select wire:model="mobilityScore" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select...</option>
                        <option value="poor">Poor</option>
                        <option value="fair">Fair</option>
                        <option value="good">Good</option>
                        <option value="excellent">Excellent</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Next Session Plan</label>
                <textarea wire:model="nextSessionPlan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" wire:click="resetForm" class="px-4 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                    {{ $editingId ? 'Update' : 'Schedule' }} Session
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Sessions List --}}
    <div class="space-y-3">
        @forelse($sessions as $session)
        @php
            $statusColors = [
                'scheduled'   => 'bg-blue-100 text-blue-700',
                'in_progress' => 'bg-amber-100 text-amber-700',
                'completed'   => 'bg-green-100 text-green-700',
                'cancelled'   => 'bg-red-100 text-red-700',
            ];
            $typeIcons = [
                'assessment' => '📋',
                'exercise'   => '💪',
                'massage'    => '🤲',
                'review'     => '📊',
            ];
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-sm transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        <span>{{ $typeIcons[$session->session_type] ?? '📋' }}</span>
                        <h4 class="font-semibold text-gray-800 text-sm capitalize">{{ $session->session_type }} Session</h4>
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-full {{ $statusColors[$session->status] ?? 'bg-gray-100' }}">
                            {{ ucfirst(str_replace('_', ' ', $session->status)) }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-4 mt-1.5 text-xs text-gray-500">
                        <span>📅 {{ $session->scheduled_at->format('M j, Y g:ia') }}</span>
                        @if($session->duration_minutes)
                            <span>⏱️ {{ $session->duration_minutes }} min</span>
                        @endif
                        @if($session->exercises && count($session->exercises))
                            <span>{{ count($session->exercises) }} exercises</span>
                        @endif
                        @if($session->pain_level_before && $session->pain_level_after)
                            <span class="{{ $session->painImproved() ? 'text-green-600' : 'text-red-600' }}">
                                Pain: {{ $session->pain_level_before }} → {{ $session->pain_level_after }}
                            </span>
                        @endif
                        @if($session->mobility_score)
                            <span class="capitalize">Mobility: {{ $session->mobility_score }}</span>
                        @endif
                    </div>
                    @if($session->findings)
                        <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($session->findings, 80) }}</p>
                    @endif
                </div>
                <div class="flex items-center space-x-1 ml-3">
                    @if($session->status === 'scheduled')
                        <button wire:click="complete({{ $session->id }})" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="Mark Complete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button wire:click="cancel({{ $session->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="Cancel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                    <button wire:click="edit({{ $session->id }})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <p class="text-sm">No physio sessions yet</p>
        </div>
        @endforelse
    </div>
</div>
