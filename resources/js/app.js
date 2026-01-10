import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Register Alpine plugins
Alpine.plugin(collapse);

// Make Alpine available globally
window.Alpine = Alpine;

// Initialize Laravel Echo for WebSockets
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': () => {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!token) {
                    console.warn('CSRF token not found in meta tag. Please refresh the page.');
                }
                return token || '';
            },
            'X-Requested-With': 'XMLHttpRequest',
        },
        withCredentials: true,
    },
});

// Start Alpine
Alpine.start();

// Initialize common functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any global JavaScript functionality here
    console.log('DoctorOnTap app initialized with Alpine.js + Livewire');
    
    // Add any global event listeners or initialization code
    initializeGlobalFeatures();
});

function initializeGlobalFeatures() {
    // Add any global features that should be available on all pages
    // For example: tooltips, global keyboard shortcuts, etc.
    
    // Global error handler for AJAX authentication errors
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && event.reason.message && event.reason.message.includes('Unexpected token')) {
            console.warn('Caught JSON parsing error, likely due to authentication redirect');
            // Don't prevent default, let the error be handled by individual fetch handlers
        }
    });
    
    // Override fetch to handle authentication errors globally
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args).then(response => {
            // If we get HTML instead of JSON, it's likely an authentication redirect
            if (response.status === 401 || response.status === 403) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    // This is likely a redirect to login page
                    window.location.href = '/admin/login';
                    return Promise.reject(new Error('Authentication required'));
                }
            }
            return response;
        });
    };
}
