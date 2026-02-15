import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import pusherBeamsService from './pusher-beams';

// Register Alpine plugins
Alpine.plugin(collapse);

// Make Alpine available globally
window.Alpine = Alpine;

// Initialize Laravel Echo for WebSockets using Pusher (optional - graceful degradation)
window.Pusher = Pusher;

try {
    // Only initialize if Pusher keys are configured
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
    const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1';
    
    if (pusherKey) {
        // Suppress expected Pusher connection retry errors during initial connection
        const originalError = console.error;
        let connectionEstablished = false;
        let retryErrorCount = 0;
        const maxRetryErrors = 5; // Allow a few retry errors before suppressing
        
        // Temporarily suppress connection retry errors during initial connection phase
        console.error = function(...args) {
            const message = args[0]?.toString() || '';
            const errorString = args.join(' ');
            
            // Suppress expected WebSocket retry errors during initial connection
            if (!connectionEstablished && (
                message.includes('WebSocket is closed before the connection is established') ||
                (errorString.includes('WebSocket connection to') && errorString.includes('failed') && 
                 errorString.includes('ws-mt1.pusher.com'))
            )) {
                retryErrorCount++;
                // Only suppress if we haven't seen too many (to avoid hiding real issues)
                if (retryErrorCount <= maxRetryErrors) {
                    return; // Suppress this expected retry error
                }
            }
            // Log all other errors normally
            originalError.apply(console, args);
        };
        
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: pusherCluster,
            forceTLS: true,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': () => {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        return token || '';
                    },
                    'X-Requested-With': 'XMLHttpRequest',
                },
                withCredentials: true,
            },
        });
        
        // Restore console.error after connection is established or timeout
        const restoreError = () => {
            console.error = originalError;
            connectionEstablished = true;
        };
        
        // Restore after 5 seconds (connection should be established by then)
        setTimeout(restoreError, 5000);
        
        // Also restore when connection is confirmed (via notification component)
        window.addEventListener('pusher-connected', restoreError, { once: true });
        
        console.log('✅ Pusher WebSocket connection initialized');
    } else {
        console.info('ℹ️ Pusher not configured - notifications will work via polling');
        window.Echo = undefined;
    }
} catch (error) {
    console.warn('⚠️ Pusher connection failed - notifications will work via polling:', error.message);
    window.Echo = undefined;
}

// Start Alpine
Alpine.start();

// Initialize common functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any global JavaScript functionality here
    console.log('DoctorOnTap app initialized with Alpine.js + Livewire');
    
    // Add any global event listeners or initialization code
    initializeGlobalFeatures();
    
    // Initialize Pusher Beams if configured
    initializePusherBeams();
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

/**
 * Initialize Pusher Beams for push notifications
 */
async function initializePusherBeams() {
    const instanceId = import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID;
    
    if (!instanceId) {
        console.info('ℹ️ Pusher Beams not configured - push notifications disabled');
        return;
    }

    // Check if user is authenticated by looking for notification component or user data
    // We'll initialize when the notification component calls it
    // For now, just initialize the client
    try {
        await pusherBeamsService.initialize(instanceId);
        console.log('✅ Pusher Beams client initialized');
        
        // Make it available globally for notification component
        window.pusherBeamsService = pusherBeamsService;
    } catch (error) {
        console.warn('⚠️ Failed to initialize Pusher Beams:', error);
    }
}

/**
 * Register device with Pusher Beams
 * Called by notification component when user is authenticated
 */
window.registerPusherBeamsDevice = async function(routePrefix, userType, userId) {
    const instanceId = import.meta.env.VITE_PUSHER_BEAMS_INSTANCE_ID;
    
    if (!instanceId) {
        return false;
    }

    try {
        // Initialize if not already done
        if (!pusherBeamsService.isInitialized) {
            await pusherBeamsService.initialize(instanceId);
        }

        // Get authentication token from backend
        const tokenResponse = await fetch(`/${routePrefix}/pusher-beams/token`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'same-origin'
        });

        if (!tokenResponse.ok) {
            console.warn('Failed to get Pusher Beams token');
            return false;
        }

        const tokenData = await tokenResponse.json();
        if (!tokenData.success || !tokenData.token) {
            console.warn('Invalid Pusher Beams token response');
            return false;
        }

        // Create user ID in format: user_type_user_id
        const beamsUserId = `${userType}_${userId}`;

        // Start Pusher Beams with token
        const started = await pusherBeamsService.start(
            tokenData.token,
            beamsUserId,
            `${window.location.origin}/pusher-beams/auth`,
            {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        );

        if (started) {
            console.log('✅ Pusher Beams device registered for user:', beamsUserId);
            
            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        }

        return started;
    } catch (error) {
        console.error('❌ Failed to register Pusher Beams device:', error);
        return false;
    }
};
