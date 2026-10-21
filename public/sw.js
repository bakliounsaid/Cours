/*
 * Service worker de l'application (PWA).
 *
 * - Pages : toujours le réseau (données à jour, aucune page connectée mise en cache) ;
 *   hors connexion, la page /offline.html s'affiche.
 * - CSS, JS, images et polices : servis depuis le cache, puis mis à jour en arrière-plan.
 * - Requêtes Livewire, formulaires et tout ce qui n'est pas GET : jamais interceptés.
 *
 * Changer VERSION à chaque modification de ce fichier pour vider les anciens caches.
 */
const VERSION = 'v1';
const STATIC_CACHE = `educoeur-static-${VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
    OFFLINE_URL,
    '/favicon.svg',
    '/manifest.webmanifest',
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(keys.filter((key) => key.startsWith('educoeur-') && key !== STATIC_CACHE).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

const isStaticAsset = (url) =>
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/images/') ||
    url.hostname === 'fonts.googleapis.com' ||
    url.hostname === 'fonts.gstatic.com';

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Pages : réseau, sinon page hors ligne
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));

        return;
    }

    // Fichiers statiques : cache d'abord, mise à jour en arrière-plan
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(STATIC_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const network = fetch(request)
                    .then((response) => {
                        if (response.ok || response.type === 'opaque') {
                            cache.put(request, response.clone());
                        }

                        return response;
                    })
                    .catch(() => cached);

                return cached || network;
            }),
        );
    }
});
