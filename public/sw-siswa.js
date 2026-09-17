// sw-siswa.js — Service Worker untuk PWA Siswa SMK Al-Farizi
// Cache strategy: Cache-first untuk aset statis, Network-first untuk API

const CACHE_NAME = 'siswa-pwa-v4';
const OFFLINE_URL = '/siswa/login';

// Aset yang di-cache saat install
const PRECACHE_ASSETS = [
    '/siswa',
    '/siswa/login',
    '/css/app.css',
    '/manifest.json',
];

// ============ INSTALL ============
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(PRECACHE_ASSETS).catch(() => {});
        }).then(() => self.skipWaiting())
    );
});

// ============ ACTIVATE ============
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// ============ FETCH ============
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET dan request ke API (network-first)
    if (event.request.method !== 'GET') return;
    if (url.pathname.includes('/api/v1/')) {
        event.respondWith(networkFirst(event.request));
        return;
    }

    // Cache-first untuk aset statis
    if (url.pathname.match(/\.(css|js|png|jpg|jpeg|svg|ico|woff2?)$/)) {
        event.respondWith(cacheFirst(event.request));
        return;
    }

    // Network-first dengan fallback offline untuk halaman
    event.respondWith(networkFirst(event.request));
});

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Offline', { status: 503 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok && request.method === 'GET') {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached || caches.match(OFFLINE_URL) || new Response('Offline', { status: 503 });
    }
}

// ============ PUSH NOTIFICATION (FCM) ============
self.addEventListener('push', event => {
    if (!event.data) return;

    let payload = {};
    let title = 'SMK Al-Farizi';
    let body = '';
    let url = '/siswa';
    
    try { 
        payload = event.data.json(); 
        // Firebase FCM payload format
        if (payload.notification) {
            title = payload.notification.title || title;
            body = payload.notification.body || body;
        } else {
            title = payload.title || title;
            body = payload.body || body;
        }
        
        if (payload.data && payload.data.url) {
            url = payload.data.url;
        } else if (payload.url) {
            url = payload.url;
        }
    }
    catch { 
        body = event.data.text(); 
    }

    const options = {
        body: body,
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: payload.tag || 'notif-siswa',
        data: { url: url },
        actions: [
            { action: 'buka', title: '📱 Buka App' },
            { action: 'tutup', title: 'Tutup' },
        ],
        requireInteraction: false,
        vibrate: [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ============ NOTIFICATION CLICK ============
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'tutup') return;

    const targetUrl = event.notification.data?.url || '/siswa';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            for (const client of clientList) {
                if (client.url.includes('/siswa') && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
