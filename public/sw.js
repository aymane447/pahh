const CACHE_NAME = 'ticketflow-v1';
const OFFLINE_URL = '/offline';

const ASSETS_TO_CACHE = [
    '/',
    OFFLINE_URL,
    '/favicon.ico',
    '/favicon-app.png',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Strategy: Network First, falling back to cache
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    // Update cache with the latest version
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, copy);
                    });
                    return response;
                })
                .catch(() => {
                    // If network fails, try to get from cache
                    return caches.match(event.request).then((response) => {
                        return response || caches.match(OFFLINE_URL);
                    });
                })
        );
    } else {
        // For static assets: Cache First
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
            })
        );
    }
});
