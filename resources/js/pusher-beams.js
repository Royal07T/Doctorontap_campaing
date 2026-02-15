/**
 * Pusher Beams Web SDK Integration
 * Handles push notification registration and management
 */

import * as PusherPushNotifications from "@pusher/push-notifications-web";

class PusherBeamsService {
    constructor() {
        this.beamsClient = null;
        this.instanceId = null;
        this.isInitialized = false;
        this.isRegistered = false;
    }

    /**
     * Initialize Pusher Beams client
     * @param {string} instanceId - Pusher Beams instance ID
     */
    async initialize(instanceId) {
        if (!instanceId) {
            console.warn('Pusher Beams: Instance ID not provided');
            return false;
        }

        if (this.isInitialized && this.instanceId === instanceId) {
            console.log('Pusher Beams: Already initialized');
            return true;
        }

        try {
            this.instanceId = instanceId;
            this.beamsClient = new PusherPushNotifications.Client({
                instanceId: instanceId
            });

            this.isInitialized = true;
            console.log('✅ Pusher Beams client initialized');
            return true;
        } catch (error) {
            console.error('❌ Failed to initialize Pusher Beams:', error);
            return false;
        }
    }

    /**
     * Start Pusher Beams and register device
     * @param {string} token - Authentication token from backend
     * @param {string} userId - User ID in format: {user_type}_{user_id}
     * @param {string} authUrl - Backend auth endpoint URL
     * @param {object} authHeaders - Headers for authentication
     */
    async start(token, userId, authUrl, authHeaders = {}) {
        if (!this.isInitialized) {
            console.error('Pusher Beams: Not initialized. Call initialize() first.');
            return false;
        }

        if (this.isRegistered) {
            console.log('Pusher Beams: Device already registered');
            return true;
        }

        try {
            // Start Beams
            await this.beamsClient.start();
            console.log('✅ Pusher Beams started');

            // Set user ID with authentication token
            if (token && userId) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                await this.beamsClient.setUserId(userId, {
                    url: authUrl || `${window.location.origin}/pusher-beams/auth`,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        ...authHeaders
                    }
                });
                console.log('✅ Pusher Beams user ID set:', userId);
            }

            // Set up notification event listener
            this.setupNotificationListener();

            this.isRegistered = true;
            return true;
        } catch (error) {
            console.error('❌ Failed to start Pusher Beams:', error);
            return false;
        }
    }

    /**
     * Set up notification event listener
     */
    setupNotificationListener() {
        if (!this.beamsClient) {
            return;
        }

        // Listen for notifications when app is in foreground
        this.beamsClient.on('notification', (notification) => {
            console.log('📬 Pusher Beams notification received:', notification);
            
            // Show browser notification if permission granted
            if ('Notification' in window && Notification.permission === 'granted') {
                this.showBrowserNotification(notification);
            }

            // Dispatch custom event for notification component
            window.dispatchEvent(new CustomEvent('pusher-beams-notification', {
                detail: notification
            }));
        });
    }

    /**
     * Show browser notification (for foreground notifications)
     */
    showBrowserNotification(notification) {
        const title = notification.title || 'DoctorOnTap';
        const body = notification.body || notification.message || 'New notification';
        const icon = notification.icon || '/img/whitelogo.png';
        const tag = `pusher-beams-${notification.notification_id || Date.now()}`;

        if ('Notification' in window && Notification.permission === 'granted') {
            const browserNotification = new Notification(title, {
                body: body,
                icon: icon,
                tag: tag,
                badge: '/img/pwa/icon-72x72.png',
                data: {
                    notification_id: notification.notification_id,
                    action_url: notification.action_url || notification.url,
                    ...notification
                }
            });

            // Handle click
            browserNotification.onclick = () => {
                if (notification.action_url || notification.url) {
                    window.focus();
                    window.location.href = notification.action_url || notification.url;
                }
                browserNotification.close();
            };
        }
    }

    /**
     * Add device to interest
     * @param {string} interest - Interest name
     */
    async addInterest(interest) {
        if (!this.beamsClient) {
            console.error('Pusher Beams: Not initialized');
            return false;
        }

        try {
            await this.beamsClient.addDeviceInterest(interest);
            console.log('✅ Added device to interest:', interest);
            return true;
        } catch (error) {
            console.error('❌ Failed to add interest:', error);
            return false;
        }
    }

    /**
     * Remove device from interest
     * @param {string} interest - Interest name
     */
    async removeInterest(interest) {
        if (!this.beamsClient) {
            console.error('Pusher Beams: Not initialized');
            return false;
        }

        try {
            await this.beamsClient.removeDeviceInterest(interest);
            console.log('✅ Removed device from interest:', interest);
            return true;
        } catch (error) {
            console.error('❌ Failed to remove interest:', error);
            return false;
        }
    }

    /**
     * Get device interests
     */
    async getInterests() {
        if (!this.beamsClient) {
            console.error('Pusher Beams: Not initialized');
            return [];
        }

        try {
            const interests = await this.beamsClient.getDeviceInterests();
            return interests;
        } catch (error) {
            console.error('❌ Failed to get interests:', error);
            return [];
        }
    }

    /**
     * Stop and unregister device
     */
    async stop() {
        if (!this.beamsClient) {
            return;
        }

        try {
            await this.beamsClient.stop();
            this.isRegistered = false;
            console.log('✅ Pusher Beams stopped');
        } catch (error) {
            console.error('❌ Failed to stop Pusher Beams:', error);
        }
    }

    /**
     * Clear all device interests
     */
    async clearInterests() {
        if (!this.beamsClient) {
            return;
        }

        try {
            const interests = await this.getInterests();
            for (const interest of interests) {
                await this.removeInterest(interest);
            }
        } catch (error) {
            console.error('❌ Failed to clear interests:', error);
        }
    }
}

// Create singleton instance
const pusherBeamsService = new PusherBeamsService();

// Export for use in other modules
export default pusherBeamsService;

// Also make available globally
window.PusherBeamsService = pusherBeamsService;

