@props([
    'label',
    'name',
    'id' => null,
    'required' => false,
])

@php
    $inputId = $id ?? $name;
    $baseClass = 'w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 bg-white transition-all duration-200 ';
    $normalClass = 'text-slate-900 ';
    $errorClass = '!border-red-400 !focus:border-red-500 !focus:ring-red-500/20 ';
    $selectClass = $baseClass . ($errors->has($name) ? $errorClass : $normalClass);
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 mb-1.5">
        {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
    </label>
    <select id="{{ $inputId }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->except('class')->merge(['class' => $selectClass]) }}>
        {{ $slot }}
    </select>
    @if(isset($hint))
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
