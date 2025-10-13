var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01K71T4MNBA5ZWBMS12RQGPYKQ.png",
    "/storage/01K71T4MNKTQHY9K18EY7X0367.png",
    "/storage/01K71T4MNM0T5DFHGB50H1VDJ1.png",
    "/storage/01K71T4MNN7AXZ2EHC18X3T60J.png",
    "/storage/01K71T4MNN7AXZ2EHC18X3T60K.png",
    "/storage/01K71T4MNSBDS7Z68JYRH8R4DP.png",
    "/storage/01K71T4MNSBDS7Z68JYRH8R4DQ.png",
    "/storage/01K71T4MNW7SM97RF93RSRFVKP.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
