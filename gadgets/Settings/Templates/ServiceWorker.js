<!-- BEGIN ServiceWorker -->
const cacheName = 'jaws-{{pwa_version}}';
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll([
        '{{base_url}}',
        <!-- BEGIN scripts -->'{{base_url}}{{script}}',<!-- END scripts -->
        '{{base_url}}libraries/bootstrap.fuelux/css/bootstrap.fuelux.min{{.dir}}.css?{{pwa_version}}',
        '{{base_url}}{{theme_url}}style{{.dir}}.css?{{pwa_version}}'
      ]).then(() => self.skipWaiting());
    })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.open(cacheName)
      .then(cache => cache.match(event.request, {ignoreSearch: true}))
      .then(response => {
      return response || fetch(event.request);
    })
  );
});
<!-- END ServiceWorker -->
<!-- BEGIN Registration -->
navigator.serviceWorker.register(
    'service-worker.js',
    { scope: '{{base_url}}' }
).then(function(registration) {
        console.log('Service Worker Registered');
    }
);

navigator.serviceWorker.ready.then(
    function(registration) {
        console.log('Service Worker Ready');
    }
);
<!-- END Registration -->