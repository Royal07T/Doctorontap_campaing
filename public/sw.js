const CACHE_NAME = 'doctorontap-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Assets to cache on install
const STATIC_CACHE = [
  '/',
  '/offline.html',
  '/img/sitelogo.png',
  '/img/whitelogo.png',
  '/img/favicon.png',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[ServiceWorker] Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[ServiceWorker] Caching static assets');
        return cache.addAll(STATIC_CACHE);
      })
      .then(() => {
        console.log('[ServiceWorker] Skip waiting');
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[ServiceWorker] Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[ServiceWorker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('[ServiceWorker] Claiming clients');
      return self.clients.claim();
    })
  );
});

// Fetch event - network first, falling back to cache
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip chrome extension requests
  if (event.request.url.startsWith('chrome-extension://')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Clone the response
        const responseToCache = response.clone();
        
        // Cache successful responses
        if (response.status === 200) {
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        
        return response;
      })
      .catch(() => {
        // Network failed, try cache
        return caches.match(event.request)
          .then((response) => {
            if (response) {
              return response;
            }
            
            // If requesting an HTML page, return offline page
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match(OFFLINE_URL);
            }
            
            // For other requests, return a generic offline response
            return new Response('Offline - content not available', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});

// Background sync for forms (future enhancement)
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-consultations') {
    event.waitUntil(syncConsultations());
  }
});

async function syncConsultations() {
  // Implement background sync logic here
  console.log('[ServiceWorker] Background sync triggered');
}

// Push notifications
self.addEventListener('push', (event) => {
  console.log('[ServiceWorker] Push notification received', event);
  
  let notificationData = {
    title: 'DoctorOnTap',
    body: 'New notification from DoctorOnTap',
    icon: '/img/pwa/icon-192x192.png',
    badge: '/img/pwa/icon-72x72.png',
    tag: 'doctorontap-notification',
    data: {},
    requireInteraction: false,
  };

  // Parse notification data
  if (event.data) {
    try {
      // Try to parse as JSON (Pusher Beams format)
      const jsonData = event.data.json();
      
      // Pusher Beams sends notification data in web.notification format
      if (jsonData.web && jsonData.web.notification) {
        notificationData.title = jsonData.web.notification.title || notificationData.title;
        notificationData.body = jsonData.web.notification.body || notificationData.body;
        notificationData.icon = jsonData.web.notification.icon || notificationData.icon;
        notificationData.badge = jsonData.web.notification.badge || notificationData.badge;
        notificationData.tag = jsonData.web.notification.tag || `notification-${jsonData.web.data?.notification_id || Date.now()}`;
      }
      
      // Extract data payload (for action_url, notification_id, etc.)
      if (jsonData.web && jsonData.web.data) {
        notificationData.data = jsonData.web.data;
        // Store action_url in data for click handler
        if (jsonData.web.data.action_url) {
          notificationData.data.url = jsonData.web.data.action_url;
        }
      }
      
      // Fallback: if data is at root level
      if (jsonData.title) {
        notificationData.title = jsonData.title;
      }
      if (jsonData.body) {
        notificationData.body = jsonData.body;
      }
      if (jsonData.icon) {
        notificationData.icon = jsonData.icon;
      }
      if (jsonData.data) {
        notificationData.data = { ...notificationData.data, ...jsonData.data };
        if (jsonData.data.action_url) {
          notificationData.data.url = jsonData.data.action_url;
        }
      }
      
    } catch (e) {
      // If JSON parsing fails, try text format
      try {
        const textData = event.data.text();
        if (textData) {
          notificationData.body = textData;
        }
      } catch (textError) {
        console.error('[ServiceWorker] Failed to parse push notification data:', textError);
      }
    }
  }

  // Add vibrate pattern for mobile devices
  notificationData.vibrate = [200, 100, 200];

  console.log('[ServiceWorker] Showing notification:', notificationData);

  event.waitUntil(
    self.registration.showNotification(notificationData.title, {
      body: notificationData.body,
      icon: notificationData.icon,
      badge: notificationData.badge,
      tag: notificationData.tag,
      data: notificationData.data,
      vibrate: notificationData.vibrate,
      requireInteraction: notificationData.requireInteraction,
      // Add actions if needed
      actions: notificationData.data?.action_url ? [
        {
          action: 'open',
          title: 'View',
        },
        {
          action: 'close',
          title: 'Close',
        }
      ] : undefined,
    })
  );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
  console.log('[ServiceWorker] Notification clicked', event);
  
  event.notification.close();

  // Determine URL to open
  let urlToOpen = '/';
  
  if (event.action === 'open' || !event.action) {
    // Get URL from notification data
    if (event.notification.data && event.notification.data.url) {
      urlToOpen = event.notification.data.url;
    } else if (event.notification.data && event.notification.data.action_url) {
      urlToOpen = event.notification.data.action_url;
    }
    
    // Ensure URL is absolute or relative to current origin
    if (!urlToOpen.startsWith('http://') && !urlToOpen.startsWith('https://') && !urlToOpen.startsWith('/')) {
      urlToOpen = '/' + urlToOpen;
    }
  }

  event.waitUntil(
    clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    }).then((clientList) => {
      // Check if there's already a window/tab open with the target URL
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      
      // If no matching window, open a new one
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

