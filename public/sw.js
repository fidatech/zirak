// Zirak service worker: app shell + static assets cached, pages network-first with offline fallback.
const CACHE = 'zirak-v2';
const OFFLINE_CACHE = 'zirak-offline'; // chapters the student saved; kept across updates
const OFFLINE_URL = '/offline';

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll([OFFLINE_URL, '/manifest.webmanifest', '/icons/icon-192.png'])));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE && k !== OFFLINE_CACHE).map((k) => caches.delete(k)))));
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET' || new URL(req.url).origin !== location.origin) return;
    const url = new URL(req.url);

    // Built assets are fingerprinted: cache first.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(caches.match(req).then((hit) => hit || fetch(req).then((res) => {
            const copy = res.clone();
            caches.open(CACHE).then((c) => c.put(req, copy));
            return res;
        })));
        return;
    }

    // Pages: network first. Very slow connection and a saved copy exists? Show the copy after 6 seconds.
    if (req.mode === 'navigate') {
        event.respondWith(caches.match(req).then((saved) => {
            const network = fetch(req).then((res) => {
                if (url.pathname.includes('/chapters/') && res.ok && !res.redirected) {
                    const copy = res.clone();
                    caches.open(CACHE).then((c) => c.put(req, copy));
                }
                return res;
            });
            // Prefer the offline page saved with the student's chapters (their language and menu).
            const fallback = () => saved || caches.match(OFFLINE_URL, { cacheName: OFFLINE_CACHE }).then((hit) => hit || caches.match(OFFLINE_URL));
            if (!saved) return network.catch(fallback);
            const slow = new Promise((resolve) => setTimeout(() => resolve(saved), 6000));
            return Promise.race([network.catch(fallback), slow]);
        }));
    }
});
