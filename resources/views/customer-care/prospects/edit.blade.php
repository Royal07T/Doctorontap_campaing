@extends('layouts.customer-care')

@section('title', 'Edit Prospect - Customer Care')

@php
    $headerTitle = 'Edit Prospect';
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('customer-care.prospects.show', $prospect) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Prospect
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Prospect</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('customer-care.prospects.update', $prospect) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $prospect->first_name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('first_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $prospect->last_name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('last_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Number *</label>
                    <input type="tel" name="mobile_number" value="{{ old('mobile_number', $prospect->mobile_number) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('mobile_number')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email (Optional)</label>
                    <input type="email" name="email" value="{{ old('email', $prospect->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gender</label>
                    <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select gender</option>
                        <option value="Male" {{ old('gender', $prospect->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $prospect->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $prospect->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                    <select id="state" name="state" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select state</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ old('state', $selectedState) == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('state')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                    <select id="location" name="location" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ !$selectedState ? 'bg-gray-50' : '' }}" {{ !$selectedState ? 'disabled' : '' }}>
                        <option value="">{{ $selectedState ? 'Select city' : 'Select state first' }}</option>
                    </select>
                    @error('location')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Source</label>
                    <select name="source" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select source</option>
                        <option value="call" {{ old('source', $prospect->source) === 'call' ? 'selected' : '' }}>Call</option>
                        <option value="booth" {{ old('source', $prospect->source) === 'booth' ? 'selected' : '' }}>Booth</option>
                        <option value="referral" {{ old('source', $prospect->source) === 'referral' ? 'selected' : '' }}>Referral</option>
                        <option value="website" {{ old('source', $prospect->source) === 'website' ? 'selected' : '' }}>Website</option>
                        <option value="other" {{ old('source', $prospect->source) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('source')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="New" {{ old('status', $prospect->status) === 'New' ? 'selected' : '' }}>New</option>
                        <option value="Contacted" {{ old('status', $prospect->status) === 'Contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="Converted" {{ old('status', $prospect->status) === 'Converted' ? 'selected' : '' }}>Converted</option>
                        <option value="Closed" {{ old('status', $prospect->status) === 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $prospect->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('customer-care.prospects.show', $prospect) }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors shadow-sm hover:shadow-md">
                    Update Prospect
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('state');
    const locationSelect = document.getElementById('location');
    const oldState = @json(old('state', $selectedState));
    const oldLocation = @json(old('location', $selectedCity));
    
    if (stateSelect && locationSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            locationSelect.innerHTML = '<option value="">Loading cities...</option>';
            locationSelect.disabled = true;
            locationSelect.classList.add('bg-gray-50');
            
            if (stateId) {
                fetch(`{{ route('customer-care.prospects.cities-by-state', ['stateId' => ':stateId']) }}`.replace(':stateId', stateId))
                    .then(response => response.json())
                    .then(cities => {
                        locationSelect.innerHTML = '<option value="">Select city</option>';
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.name;
                            option.textContent = city.name;
                            if (oldLocation === city.name) {
                                option.selected = true;
                            }
                            locationSelect.appendChild(option);
                        });
                        locationSelect.disabled = false;
                        locationSelect.classList.remove('bg-gray-50');
                    })
                    .catch(error => {
                        console.error('Error loading cities:', error);
                        locationSelect.innerHTML = '<option value="">Error loading cities</option>';
                    });
            } else {
                locationSelect.innerHTML = '<option value="">Select state first</option>';
                locationSelect.disabled = true;
                locationSelect.classList.add('bg-gray-50');
            }
        });
        
        // If state is already selected, load cities
        if (oldState) {
            stateSelect.value = oldState;
            stateSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush
@endsection

