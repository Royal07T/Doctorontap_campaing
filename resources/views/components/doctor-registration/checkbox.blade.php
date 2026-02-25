@props([
    'label',
    'name',
    'id' => null,
    'value' => '1',
])

@php
    $inputId = $id ?? $name;
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    <div class="flex items-start p-4 bg-violet-50/60 border border-violet-200/60 rounded-xl">
        <input type="checkbox"
               id="{{ $inputId }}"
               name="{{ $name }}"
               value="{{ $value }}"
               {{ old($name) ? 'checked' : '' }}
               class="mt-1 mr-3 w-4 h-4 text-violet-600 border-slate-300 rounded focus:ring-violet-500 focus:ring-2 cursor-pointer">
        <label for="{{ $inputId }}" class="flex-1 text-sm font-medium text-slate-700 cursor-pointer">
            {{ $label }}
            @if(isset($description))
                <p class="mt-1 text-xs font-normal text-slate-600">{{ $description }}</p>
            @endif
        </label>
    </div>
    @error($name)
        <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
