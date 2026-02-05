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
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    // We only want to handle GET requests
    if (event.request.method !== 'GET') return;

    // Strategy: Network First, falling back to cache for HTML
    if (event.request.mode === 'navigate' || (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, copy);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request).then((response) => {
                        return response || caches.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    // For everything else: Cache First, falling back to network
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request).then((networkResponse) => {
                // Optionally cache successfully fetched static assets
                if (networkResponse && networkResponse.status === 200) {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, copy);
                    });
                }
                return networkResponse;
            });
        })
    );
});
