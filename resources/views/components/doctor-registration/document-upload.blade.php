@props([
    'name' => 'certificate',
    'label' => 'Upload MDCN License or Medical Certificate',
    'accept' => '.pdf,.jpg,.jpeg,.png',
    'required' => true,
])

@php
    $baseClass = 'w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 cursor-pointer transition-colors ';
    $errorClass = $errors->has($name) ? 'border-red-500' : '';
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    {{-- Security reassurance --}}
    <div class="bg-violet-50/70 border border-violet-200/60 rounded-xl p-4 mb-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-violet-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317A11.954 11.954 0 012.166 4.999z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-slate-800 mb-0.5">Your documents are secure</h4>
                <p class="text-xs text-slate-600">
                    Uploads are encrypted and used only for verification. We do not accept credentials by email, WhatsApp, or social media. Accepted: PDF, JPG, PNG (max 5MB).
                </p>
            </div>
        </div>
    </div>

    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-300 transition-colors duration-200 {{ $errorClass }}">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <p class="text-xs text-gray-500 mb-3">Click to browse or drag and drop</p>
        <input type="file"
               id="{{ $name }}"
               name="{{ $name }}"
               accept="{{ $accept }}"
               {{ $required ? 'required' : '' }}
               class="{{ $baseClass }}">
    </div>
    @error($name)
        <p class="mt-2 text-xs text-red-500 flex items-center justify-center gap-1">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
