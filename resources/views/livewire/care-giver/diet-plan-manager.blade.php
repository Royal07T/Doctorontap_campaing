<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center space-x-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Diet Plans</span>
        </h3>
        <button wire:click="$toggle('showForm')" class="px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700">
            {{ $showForm ? 'Cancel' : '+ New Diet Plan' }}
        </button>
    </div>

    @if(session('diet-success'))
        <div class="mb-3 px-3 py-2 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg">{{ session('diet-success') }}</div>
    @endif

    {{-- Create/Edit Form --}}
    @if($showForm)
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-5">
        <h4 class="font-semibold text-green-800 mb-3">{{ $editingId ? 'Edit Diet Plan' : 'New Diet Plan' }}</h4>
        <form wire:submit="save" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                    <input type="text" wire:model="title" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                    @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Target Calories</label>
                    <input type="number" wire:model="targetCalories" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. 2000">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <textarea wire:model="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Start Date *</label>
                    <input type="date" wire:model="startDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                    <input type="date" wire:model="endDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dietary Restrictions (comma-separated)</label>
                    <input type="text" wire:model="restrictions" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. No sugar, Low sodium">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Supplements (comma-separated)</label>
                    <input type="text" wire:model="supplements" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Vitamin D, Omega-3">
                </div>
            </div>

            {{-- Meals --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gray-600">Meals</label>
                    <button type="button" wire:click="addMeal" class="text-xs text-green-600 hover:text-green-800 font-medium">+ Add Meal</button>
                </div>
                @foreach($meals as $idx => $meal)
                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-500">Meal {{ $idx + 1 }}</span>
                        <button type="button" wire:click="removeMeal({{ $idx }})" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <input type="text" wire:model="meals.{{ $idx }}.name" placeholder="Meal name" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <input type="time" wire:model="meals.{{ $idx }}.time" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <input type="text" wire:model="meals.{{ $idx }}.items" placeholder="Items (comma-sep)" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                        <input type="number" wire:model="meals.{{ $idx }}.calories" placeholder="Calories" class="border border-gray-300 rounded px-2 py-1.5 text-xs">
                    </div>
                </div>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dietician Notes</label>
                <textarea wire:model="dieticianNotes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" wire:click="resetForm" class="px-4 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200">Cancel</button>
                <button type="submit" class="px-5 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    {{ $editingId ? 'Update' : 'Create' }} Diet Plan
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Diet Plans List --}}
    <div class="space-y-3">
        @forelse($dietPlans as $plan)
        <div class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-sm transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2">
                        <h4 class="font-semibold text-gray-800 text-sm">{{ $plan->title }}</h4>
                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-full {{ $plan->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($plan->status) }}
                        </span>
                    </div>
                    @if($plan->description)
                        <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($plan->description, 80) }}</p>
                    @endif
                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                        <span>{{ $plan->start_date->format('M j') }} — {{ $plan->end_date?->format('M j, Y') ?? 'Ongoing' }}</span>
                        @if($plan->target_calories)
                            <span>🎯 {{ $plan->target_calories }} cal</span>
                        @endif
                        @if($plan->meals && count($plan->meals) > 0)
                            <span>🍽️ {{ count($plan->meals) }} meals</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-1 ml-3">
                    <button wire:click="edit({{ $plan->id }})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button wire:click="toggleStatus({{ $plan->id }})" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Toggle Status">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm">No diet plans created yet</p>
        </div>
        @endforelse
    </div>
</div>
