<!-- BEGIN ServiceWorker -->
const cacheName = 'Jaws-{{layout}}-{{pwa_version}}';
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll(
        [
          '{{base_url}}',
          '{{base_url}}libraries/jquery/jquery.min.js',
          '{{base_url}}libraries/bootstrap.fuelux/js/bootstrap.fuelux.min.js',
          '{{base_url}}include/Jaws/Resources/Jaws.js',
          '{{base_url}}libraries/bootstrap.fuelux/css/bootstrap.fuelux.min{{.dir}}.css'
        ]
      ).then(() => self.skipWaiting());
    })
  );
});

self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim());
});

/*
 * Fetch request
 */
self.addEventListener('fetch', function(event) {
    event.respondWith(
        fetch(event.request).then(function (response) {
            caches.open(cacheName).then(function (cache) {
                cache.put(event.request, response)
            });
            return response.clone();
        }).catch(function() {
            return caches.match(event.request, {'cacheName': cacheName, ignoreSearch: true});
        })
    );
});
<!-- END ServiceWorker -->
<!-- BEGIN Registration -->
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(
        'service-worker.js?layout={{layout}}',
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
}
<!-- END Registration -->