@props([
    'name',
    'id' => null,
    'label' => 'Password',
    'toggleId' => 'password-eye',
])

@php
    $inputId = $id ?? $name;
    $baseClass = 'w-full px-3.5 py-2.5 pr-12 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 bg-white transition-all duration-200 ';
    $normalClass = 'border-slate-300 focus:border-violet-500 focus:ring-violet-500/20 ';
    $errorClass = '!border-red-400 focus:!border-red-500 focus:!ring-red-500/20 ';
    $inputClass = $baseClass . ($errors->has($name) ? $errorClass : $normalClass);
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 mb-1.5">
        {{ $label }} <span class="text-red-500">*</span>
    </label>
    <div class="relative">
        <input type="password"
               id="{{ $inputId }}"
               name="{{ $name }}"
               required
               minlength="8"
               placeholder="{{ $name === 'password' ? 'Minimum 8 characters (uppercase, lowercase, number)' : 'Re-enter password' }}"
               title="{{ $name === 'password' ? 'Password must be at least 8 characters and contain uppercase, lowercase, and number' : 'Please confirm your password' }}"
               class="{{ $inputClass }}">
        <button type="button"
                onclick="window.toggleDoctorPasswordVisibility('{{ $inputId }}', '{{ $toggleId }}')"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors p-1 rounded-lg"
                id="{{ $toggleId }}"
                aria-label="Toggle password visibility">
            <svg id="{{ $toggleId }}-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg id="{{ $toggleId }}-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>
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
