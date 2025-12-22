@props(['message' => 'Processing request...'])

<div {{ $attributes->merge(['class' => 'fixed inset-0 z-[200] flex items-center justify-center']) }} x-cloak>
    <!-- Dark Backdrop with blur -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm shadow-inner transition-opacity"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>
    
    <!-- Spinner & Message Container -->
    <div class="relative flex flex-col items-center justify-center space-y-4"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <!-- Rotating Spinner -->
        <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>

        <!-- System Message -->
        <p class="text-white text-lg font-medium tracking-wide drop-shadow-sm">{{ $message }}</p>
    </div>
</div>

