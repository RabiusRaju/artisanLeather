// Artisan Leather Admin — Service Worker
const CACHE_NAME  = 'al-admin-v3';
const STATIC_CACHE = 'al-static-v3';

// Assets to cache on install
const PRECACHE_URLS = [
  '/admin',
  '/manifest.json',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
];

// ── Install: pre-cache shell ──────────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      return cache.addAll(PRECACHE_URLS).catch(() => {
        // Silently fail on install — don't block SW activation
      });
    })
  );
  self.skipWaiting();
});

// ── Activate: clean old caches ────────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME && key !== STATIC_CACHE)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ── Fetch: smart caching strategy ─────────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET and non-same-origin
  if (request.method !== 'GET' || url.origin !== location.origin) return;

  // API calls → Network first, fall back to cache
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(networkFirst(request));
    return;
  }

  // Filament static assets (CSS, JS, fonts, images) → Cache first
  if (
    url.pathname.startsWith('/css/') ||
    url.pathname.startsWith('/js/') ||
    url.pathname.startsWith('/fonts/') ||
    url.pathname.match(/\.(png|jpg|webp|ico|svg|woff2?)$/)
  ) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Admin HTML pages → Stale while revalidate
  if (url.pathname.startsWith('/admin')) {
    event.respondWith(staleWhileRevalidate(request));
    return;
  }
});

// ── Cache strategies ──────────────────────────────────────────────────────

async function networkFirst(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request);
    return cached || offlinePage();
  }
}

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    return new Response('Asset unavailable offline', { status: 503 });
  }
}

async function staleWhileRevalidate(request) {
  const cache  = await caches.open(CACHE_NAME);
  const cached = await cache.match(request);

  const fetchPromise = fetch(request).then(response => {
    if (response.ok) cache.put(request, response.clone());
    return response;
  }).catch(() => null);

  return cached || await fetchPromise || offlinePage();
}

function offlinePage() {
  return new Response(
    `<!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Offline — Artisan Leather</title>
    <style>
      body{font-family:system-ui,sans-serif;background:#120D05;color:#F5EDD8;display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px}
      h1{font-size:2rem;color:#C9A84C;margin-bottom:1rem}
      p{color:#9B7B3D;font-size:1rem;max-width:300px;line-height:1.6}
      button{margin-top:2rem;background:#C9A84C;color:#120D05;border:none;padding:12px 28px;font-size:.9rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;cursor:pointer}
    </style></head>
    <body>
      <h1>You're Offline</h1>
      <p>The admin panel needs an internet connection. Please check your network and try again.</p>
      <button onclick="location.reload()">Try Again</button>
    </body></html>`,
    { headers: { 'Content-Type': 'text/html' } }
  );
}

// ── Push notifications (future) ───────────────────────────────────────────
self.addEventListener('push', event => {
  if (!event.data) return;
  const data = event.data.json();
  self.registration.showNotification(data.title || 'Artisan Leather', {
    body:  data.body  || '',
    icon:  '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    data:  data.url ? { url: data.url } : {},
  });
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  if (event.notification.data?.url) {
    clients.openWindow(event.notification.data.url);
  }
});
