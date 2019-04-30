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
self.addEventListener('fetch', function (event) {
    var freshResource = fetch(event.request).then(function (response) {
        var clonedResponse = response.clone();
        if (response.ok) {
            // update the cache with the network response
            caches.open(cacheName).then(function (cache) {
                cache.put(event.request, clonedResponse);
            });
        }
        return response;
    });

    var cachedResource = caches.open(cacheName).then(function (cache) {
        return cache.match(event.request, {ignoreSearch: true}).then(function(response) {
            return response || freshResource;
        });
    }).catch(function (e) {
        return caches.match(event.request.referrer, {'cacheName': cacheName, ignoreSearch: true});
    });

    event.respondWith(cachedResource);
});

/*
 * Service Worker message
 */
self.addEventListener('message', function(event) {
    console.log(event);
    //alert(event.data.alert);
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