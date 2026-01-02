@props(['message' => 'Loading...', 'subtext' => 'Please wait'])

<div x-show="isSubmitting || isLoading" 
     x-cloak
     class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center backdrop-blur-sm"
     style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all">
        <!-- Spinner -->
        <div class="flex justify-center mb-6">
            <div class="relative">
                <div class="w-16 h-16 border-4 border-purple-200 rounded-full"></div>
                <div class="w-16 h-16 border-4 border-purple-600 border-t-transparent rounded-full animate-spin absolute top-0 left-0"></div>
            </div>
        </div>
        
        <!-- Message -->
        <div class="text-center">
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $message }}</h3>
            <p class="text-sm text-gray-600">{{ $subtext }}</p>
        </div>
    </div>
</div>

