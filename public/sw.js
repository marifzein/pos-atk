// Service Worker Zero-Cache: Memenuhi syarat PWA tanpa menyimpan cache apa pun
self.addEventListener("install", (event) => {
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    // Sapu bersih jika pernah ada sisa cache di browser
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(keys.map((key) => caches.delete(key)));
        }),
    );
    self.clients.claim();
});

// Semua request GET (cari barang, ajax pelanggan, blade) maupun POST transaksi
// LANGSUNG ditembakkan ke VPS (Network Only)
self.addEventListener("fetch", (event) => {
    event.respondWith(fetch(event.request));
});
