// public/sw.js - Service Worker PWA SMK Al-Farizi
const CACHE_NAME = 'smk-alfarizi-presensi-v1';
const ASSETS_TO_CACHE = [
  './',
  'css/app.css',
  'js/scanner.js',
  'manifest.json'
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE).catch(() => {});
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  // Network first, fallback to cache for static resources
  if (e.request.method !== 'GET' || e.request.url.includes('/api/') || e.request.url.includes('ajax')) {
    return;
  }

  e.respondWith(
    fetch(e.request).catch(() => caches.match(e.request))
  );
});
