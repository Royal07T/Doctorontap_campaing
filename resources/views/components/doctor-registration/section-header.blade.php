@props([
    'title',
    'subtitle' => '',
    'icon' => 'user',
])

@php
    $icons = [
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
        'briefcase' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        'lock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
    ];
    $iconPath = $icons[$icon] ?? $icons['user'];
    $bgClass = match($icon) {
        'user' => 'bg-violet-100 text-violet-600',
        'briefcase' => 'bg-violet-100 text-violet-600',
        'document' => 'bg-violet-100 text-violet-600',
        'lock' => 'bg-emerald-100 text-emerald-600',
        default => 'bg-violet-100 text-violet-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center mb-6']) }}>
    <div class="w-9 h-9 {{ $bgClass }} rounded-xl flex items-center justify-center mr-3 flex-shrink-0">
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
    </div>
    <div>
        <h3 class="text-lg font-semibold text-slate-900 tracking-tight">{{ $title }}</h3>
        @if($subtitle)
            <p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
</div>
