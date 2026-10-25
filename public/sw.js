/**
 * SIKKN Service Worker
 *
 * Cache strategy:
 * - Static assets (CSS, JS, fonts, images): Cache-first with network fallback
 * - Navigation requests (HTML pages): Network-first with cache fallback
 * - API/Livewire requests: Network-only (no caching)
 */

const CACHE_VERSION = 'sikkn-v1';

const PRECACHE_URLS = [
    '/offline',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/favicon.svg',
    '/favicon.ico',
];

// Install: precache critical assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// Activate: clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_VERSION)
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

// Fetch: route requests to appropriate strategy
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip Livewire update requests, CSRF, and API calls
    if (
        url.pathname.startsWith('/livewire/') ||
        url.pathname.startsWith('/api/') ||
        url.pathname === '/sanctum/csrf-cookie' ||
        request.headers.get('X-Livewire')
    ) {
        return;
    }

    // Static assets: cache-first
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Navigation requests: network-first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }
});

/**
 * Determine if a URL points to a static asset.
 */
function isStaticAsset(url) {
    const staticExtensions = [
        '.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg',
        '.ico', '.woff', '.woff2', '.ttf', '.eot', '.webp',
    ];
    return (
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/') ||
        staticExtensions.some((ext) => url.pathname.endsWith(ext))
    );
}

/**
 * Cache-first strategy: serve from cache, fall back to network and update cache.
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('', { status: 408, statusText: 'Offline' });
    }
}

/**
 * Network-first strategy for navigation: try network, fall back to cache,
 * then fall back to offline page.
 */
async function networkFirstNavigation(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_VERSION);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;

        // Show offline page
        const offlinePage = await caches.match('/offline');
        if (offlinePage) return offlinePage;

        return new Response(
            '<html><body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#171717;color:#fff;font-family:sans-serif"><div style="text-align:center"><h1>Offline</h1><p>Periksa koneksi internet Anda</p></div></body></html>',
            { headers: { 'Content-Type': 'text/html' }, status: 503 }
        );
    }
}
