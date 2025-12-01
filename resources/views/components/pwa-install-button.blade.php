<!-- PWA Install Button Component -->
<div 
    x-data="{ 
        showInstall: false,
        isPWA: false,
        init() {
            // Check if already running as PWA
            this.isPWA = window.matchMedia('(display-mode: standalone)').matches || 
                        window.navigator.standalone === true;
            
            // Listen for install prompt
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                if (!this.isPWA) {
                    this.showInstall = true;
                }
            });
            
            // Handle successful installation
            window.addEventListener('appinstalled', () => {
                this.showInstall = false;
                this.isPWA = true;
                console.log('PWA installed successfully');
            });
        },
        async install() {
            if (!this.deferredPrompt) {
                return;
            }
            
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            console.log('User choice:', outcome);
            
            this.deferredPrompt = null;
            this.showInstall = false;
        },
        dismiss() {
            this.showInstall = false;
        }
    }"
    x-show="showInstall && !isPWA"
    x-transition
    class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 max-w-md w-full mx-4"
    style="display: none;">
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-start space-x-4">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <img src="{{ asset('img/pwa/icon-72x72.png') }}" alt="App Icon" class="w-12 h-12 rounded-lg">
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Install DoctorOnTap
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Install our app for quick access and offline support
                </p>
                
                <!-- Actions -->
                <div class="mt-3 flex items-center space-x-3">
                    <button 
                        @click="install"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Install
                    </button>
                    
                    <button 
                        @click="dismiss"
                        class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        Not now
                    </button>
                </div>
            </div>
            
            <!-- Close button -->
            <button 
                @click="dismiss"
                class="flex-shrink-0 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

