const CACHE_NAME = 'akuna-v2';
const urlsToCache = [
  '/',
  '/index.html',
  '/manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          // Met à jour le cache en arrière-plan
          fetch(event.request).then(networkResponse => {
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, networkResponse);
            });
          });
          return response;
        }
        return fetch(event.request);
      })
      .catch(() => {
        // Page hors ligne personnalisée
        return caches.match('/offline.html');
      })
  );
});

// Background sync
self.addEventListener('sync', event => {
  if (event.tag === 'sync-akuna') {
    event.waitUntil(syncData());
  }
});

async function syncData() {
  try {
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({ type: 'SYNC_TRIGGERED' });
    });
  } catch (error) {
    console.error('Sync error:', error);
  }
}