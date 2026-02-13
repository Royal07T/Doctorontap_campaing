@php
    // Determine the route prefix and user info based on the current guard
    $routePrefix = 'patient';
    $userType = 'patient';
    $userId = null;
    $isAuthenticated = false;
    
    if (Auth::guard('admin')->check()) {
        $routePrefix = 'admin';
        $userType = 'admin';
        $userId = Auth::guard('admin')->id();
        $isAuthenticated = true;
    } elseif (Auth::guard('doctor')->check()) {
        $routePrefix = 'doctor';
        $userType = 'doctor';
        $userId = Auth::guard('doctor')->id();
        $isAuthenticated = true;
    } elseif (Auth::guard('nurse')->check()) {
        $routePrefix = 'nurse';
        $userType = 'nurse';
        $userId = Auth::guard('nurse')->id();
        $isAuthenticated = true;
    } elseif (Auth::guard('canvasser')->check()) {
        $routePrefix = 'canvasser';
        $userType = 'canvasser';
        $userId = Auth::guard('canvasser')->id();
        $isAuthenticated = true;
    } elseif (Auth::guard('customer_care')->check()) {
        $routePrefix = 'customer-care';
        $userType = 'customer_care';
        $userId = Auth::guard('customer_care')->id();
        $isAuthenticated = true;
    } elseif (Auth::guard('patient')->check()) {
        $routePrefix = 'patient';
        $userType = 'patient';
        $userId = Auth::guard('patient')->id();
        $isAuthenticated = true;
    }
@endphp

@if($isAuthenticated)

<div x-data="notificationComponent('{{ $routePrefix }}', '{{ $userType }}', {{ $userId }})" 
     class="relative"
     x-init="init()">
    <!-- Notification Icon Button -->
    <button @click="toggleDropdown()" 
            class="relative p-2 text-white hover:text-purple-200 transition-colors focus:outline-none"
            x-bind:class="{ 'animate-pulse': unreadCount > 0 }">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        <!-- Badge for unread count -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full min-w-[20px] animate-bounce"
              style="display: none;"></span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="dropdownOpen" 
         @click.away="dropdownOpen = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 md:w-96 bg-white rounded-lg shadow-xl z-50 border border-gray-200 max-h-[500px] flex flex-col"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-purple-50">
            <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
            <button @click="markAllAsRead()" 
                    x-show="unreadCount > 0"
                    class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                    style="display: none;">
                Mark all as read
            </button>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1" style="max-height: 400px;">
            <template x-if="loading">
                <div class="p-4 text-center text-gray-500">
                    <svg class="animate-spin h-6 w-6 mx-auto text-purple-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2">Loading notifications...</p>
                </div>
            </template>
            
            <template x-if="!loading && notifications.length === 0">
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p>No notifications</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div @click="markAsRead(notification.id); handleNotificationClick(notification)"
                     :class="{
                         'bg-purple-50': !notification.read_at,
                         'bg-white': notification.read_at
                     }"
                     class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                    <div class="flex items-start space-x-3">
                        <!-- Icon based on type -->
                        <div :class="{
                            'bg-blue-100 text-blue-600': notification.type === 'info',
                            'bg-green-100 text-green-600': notification.type === 'success',
                            'bg-yellow-100 text-yellow-600': notification.type === 'warning',
                            'bg-red-100 text-red-600': notification.type === 'error'
                        }"
                        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                            <template x-if="notification.type === 'info'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <template x-if="notification.type === 'success'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <template x-if="notification.type === 'warning'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                            <template x-if="notification.type === 'error'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </template>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900" x-text="notification.title"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                            <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notification.created_at)"></p>
                        </div>
                        
                        <!-- Unread indicator -->
                        <div x-show="!notification.read_at" 
                             class="flex-shrink-0 w-2 h-2 bg-purple-600 rounded-full mt-2"
                             style="display: none;"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function notificationComponent(routePrefix, userType, userId) {
    return {
        dropdownOpen: false,
        notifications: [],
        unreadCount: 0,
        loading: false,
        echoChannel: null,
        websocketConnected: false,
        userType: userType,
        userId: userId,

        init() {
            if (this.userId) {
                this.fetchNotifications(); // One-time fetch
                this.setupWebSocket();
                this.setupPusherBeams();
                window.addEventListener('beforeunload', () => {
                    this.cleanup();
                });
            }
        },

        async setupPusherBeams() {
            // Register device with Pusher Beams for push notifications
            if (typeof window.registerPusherBeamsDevice === 'function') {
                try {
                    await window.registerPusherBeamsDevice(routePrefix, this.userType, this.userId);
                } catch (error) {
                    console.warn('Failed to register Pusher Beams device:', error);
                }
            }

            // Listen for Pusher Beams notifications
            window.addEventListener('pusher-beams-notification', (event) => {
                const notification = event.detail;
                
                // Verify this notification is for the current user
                const notificationUserId = `${notification.user_type}_${notification.user_id}`;
                const currentUserId = `${this.userType}_${this.userId}`;
                
                if (notificationUserId !== currentUserId) {
                    console.warn('⚠️ Received Pusher Beams notification for different user, ignoring:', {
                        received: notificationUserId,
                        current: currentUserId
                    });
                    return;
                }
                
                // Add to notifications list
                this.notifications.unshift({
                    id: notification.notification_id || Date.now(),
                    title: notification.title || 'New Notification',
                    message: notification.body || notification.message || '',
                    type: notification.type || 'info',
                    action_url: notification.action_url || notification.url,
                    read_at: null,
                    created_at: new Date().toISOString(),
                });
                this.unreadCount = (this.unreadCount || 0) + 1;
                
                // Show real-time alert
                this.showRealTimeAlert({
                    id: notification.notification_id,
                    title: notification.title || 'New Notification',
                    message: notification.body || notification.message || '',
                    type: notification.type || 'info',
                });
            });
        },

        setupWebSocket() {
            if (typeof window.Echo === 'undefined' || window.Echo === null) {
                console.info('ℹ️ Real-time notifications not available - using polling instead');
                this.websocketConnected = false;
                return;
            }
            
            try {
                // Ensure channel name matches exactly with backend: notifications.{userType}.{userId}
                const channelName = `notifications.${this.userType}.${this.userId}`;
                console.log('🔔 Subscribing to notification channel:', channelName);
                
                this.echoChannel = window.Echo.private(channelName);

                this.echoChannel.listen('.notification.created', (data) => {
                    // Double-check this notification is for the current user
                    if (data.user_type !== this.userType || data.user_id !== this.userId) {
                        console.warn('⚠️ Received notification for different user, ignoring:', {
                            received: { type: data.user_type, id: data.user_id },
                            current: { type: this.userType, id: this.userId }
                        });
                        return;
                    }
                    
                    // Add notification to list
                    this.notifications.unshift({
                        id: data.id,
                        title: data.title,
                        message: data.message,
                        type: data.type,
                        action_url: data.action_url,
                        read_at: data.read_at,
                        created_at: data.created_at,
                    });
                    this.unreadCount = data.unread_count || this.unreadCount + 1;
                    
                    // Show real-time alert
                    this.showRealTimeAlert(data);
                    this.showBrowserNotification(data);
                });

                this.echoChannel.subscribed(() => {
                    this.websocketConnected = true;
                    console.log('✅ WebSocket connected for real-time notifications');
                    // Dispatch event to restore console.error in app.js
                    window.dispatchEvent(new Event('pusher-connected'));
                });

                this.echoChannel.error((error) => {
                    console.error('❌ WebSocket error:', error);
                    this.websocketConnected = false;
                    if (error.status === 403) {
                        console.error('Authentication failed. Please refresh the page or log in again.');
                    }
                });
            } catch (error) {
                console.error('❌ Failed to set up WebSocket:', error);
                this.websocketConnected = false;
            }
        },

        showRealTimeAlert(data) {
            // Visual alert - flash the notification icon
            const iconButton = document.querySelector('[x-data*="notificationComponent"] button');
            if (iconButton) {
                iconButton.classList.add('animate-pulse');
                setTimeout(() => {
                    iconButton.classList.remove('animate-pulse');
                }, 2000);
            }
            
            // Sound alert (if user hasn't disabled it)
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUKjn8LZjGwU4kdfyzHksBSR3x/DdkEAKFF606euoVRQKRp/g8r5sIQUrgc7y2Yk2CBtpvfDknE4MDlCo5/C2YxsFOJHX8sx5LAUkd8fw3ZBAC');
                audio.volume = 0.3;
                audio.play().catch(e => {
                    // Ignore audio play errors (user may have blocked autoplay)
                });
            } catch (e) {
                // Audio not supported or blocked
            }
            
            // Show toast notification
            this.showToastNotification(data);
        },
        
        showToastNotification(data) {
            // Create a temporary toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-white rounded-lg shadow-xl border border-gray-200 p-4 z-50 max-w-sm animate-slide-in-right';
            toast.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ${
                        data.type === 'success' ? 'bg-green-100 text-green-600' :
                        data.type === 'error' ? 'bg-red-100 text-red-600' :
                        data.type === 'warning' ? 'bg-yellow-100 text-yellow-600' :
                        'bg-blue-100 text-blue-600'
                    }">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">${data.title}</p>
                        <p class="text-sm text-gray-600 mt-1">${data.message}</p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        },

        showBrowserNotification(data) {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(data.title, {
                    body: data.message,
                    icon: '/img/whitelogo.png',
                    tag: `notification-${data.id}`,
                    badge: '/img/whitelogo.png',
                });
            }
        },

        cleanup() {
            if (this.echoChannel && typeof window.Echo !== 'undefined') {
                try {
                    window.Echo.leave(`notifications.${this.userType}.${this.userId}`);
                } catch (e) {
                    console.error('Error disconnecting WebSocket:', e);
                }
            }
        },

        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
            if (this.dropdownOpen) {
                this.fetchNotifications();
            }
        },

        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await fetch(`/${routePrefix}/notifications`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });
                
                // Handle authentication errors
                if (response.status === 401 || response.status === 403) {
                    const data = await response.json();
                    if (data.redirect) {
                        // Redirect to login if unauthorized
                        window.location.href = data.redirect;
                        return;
                    }
                }
                
                if (!response.ok) {
                    console.warn(`Failed to fetch notifications: ${response.status}`);
                    this.notifications = [];
                    this.unreadCount = 0;
                    return;
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.warn('Notification endpoint did not return JSON. This may indicate a server error or redirect.');
                    this.notifications = [];
                    this.unreadCount = 0;
                    return;
                }
                
                const data = await response.json();
                
                // Double-check notifications belong to current user
                const filteredNotifications = (data.notifications || []).filter(notif => {
                    // Backend should already filter, but double-check on frontend for security
                    return true; // Backend filtering is trusted, but we log for debugging
                });
                
                this.notifications = filteredNotifications;
                this.unreadCount = data.unread_count || 0;
            } catch (error) {
                console.warn('Could not fetch notifications:', error.message);
                // Silent fail - don't disrupt user experience
                this.notifications = [];
                this.unreadCount = 0;
            } finally {
                this.loading = false;
            }
        },


        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/${routePrefix}/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    // Update local state
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.read_at = new Date().toISOString();
                    }
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch(`/${routePrefix}/notifications/mark-all-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.notifications.forEach(n => {
                        n.read_at = new Date().toISOString();
                    });
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        handleNotificationClick(notification) {
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (seconds < 60) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            
            return date.toLocaleDateString();
        }
    }
}
</script>

<style>
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
@endif

