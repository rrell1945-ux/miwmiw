const VERSION = 'mimiw-v4';
const CORE_CACHE = `${VERSION}-core`;
const RUNTIME_CACHE = `${VERSION}-runtime`;

const CORE_ASSETS = [
    '/manifest.json',
    '/icons/favicon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CORE_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !key.startsWith(VERSION))
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // API & data requests (events, periods, notifications, dll):
    // selalu ambil dari jaringan saat online agar data tidak basi,
    // fallback ke cache hanya ketika offline.
    event.respondWith(networkFirst(request));
});

function isStaticAsset(url) {
    return /\.(js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|eot|ico)(\?.*)?$/i.test(url.pathname);
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const copy = response.clone();
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, copy);
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        return caches.match('/dashboard') || new Response('Offline', { status: 503 });
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.ok) {
        const copy = response.clone();
        const cache = await caches.open(RUNTIME_CACHE);
        cache.put(request, copy);
    }
    return response;
}

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
