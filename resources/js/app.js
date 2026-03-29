import './bootstrap';
import { initPusherBeams } from './beams';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Register Alpine plugins
Alpine.plugin(collapse);

// Make Alpine available globally
window.Alpine = Alpine;

// Laravel Echo + Pusher Channels (https://github.com/pusher/pusher-http-php)
window.Pusher = Pusher;

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'eu';

if (pusherKey) {
    try {
        const echoConfig = {
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
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
        };

        // Optional: self-hosted / custom host (e.g. Soketi). Leave unset for Pusher Cloud.
        const customHost = import.meta.env.VITE_PUSHER_HOST;
        if (customHost) {
            echoConfig.wsHost = customHost;
            echoConfig.wsPort = import.meta.env.VITE_PUSHER_PORT ?? 80;
            echoConfig.wssPort = import.meta.env.VITE_PUSHER_PORT ?? 443;
            echoConfig.disableStats = true;
            echoConfig.enabledTransports = ['ws', 'wss'];
        }

        window.Echo = new Echo(echoConfig);

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.warn('Pusher connection error (non-critical):', err);
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            console.warn('Pusher disconnected. Real-time features may fall back to polling.');
        });

        console.log('Laravel Echo initialized (Pusher Channels)');
    } catch (error) {
        console.warn('Failed to initialize Laravel Echo:', error);
        window.Echo = {
            private: () => ({ listen: () => {}, subscribed: () => {}, error: () => {} }),
            leave: () => {},
        };
    }
} else {
    console.warn('VITE_PUSHER_APP_KEY not set. Real-time broadcasting disabled.');
    window.Echo = {
        private: () => ({ listen: () => {}, subscribed: () => {}, error: () => {} }),
        leave: () => {},
    };
}

// Start Alpine
Alpine.start();

// Initialize common functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DoctorOnTap app initialized with Alpine.js + Livewire');
    initializeGlobalFeatures();
    initPusherBeams();
});

function initializeGlobalFeatures() {
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && event.reason.message && event.reason.message.includes('Unexpected token')) {
            console.warn('Caught JSON parsing error, likely due to authentication redirect');
        }
    });

    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args).then(response => {
            if (response.status === 401 || response.status === 403) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    window.location.href = '/admin/login';
                    return Promise.reject(new Error('Authentication required'));
                }
            }
            return response;
        });
    };
}
