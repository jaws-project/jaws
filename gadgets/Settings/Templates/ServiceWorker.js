<!-- BEGIN ServiceWorker -->
const cacheName = 'Jaws-{{pwa_version}}';
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll(
        [
          'libraries/jquery/jquery.min.js',
          'libraries/bootstrap.fuelux/js/bootstrap.fuelux.min.js',
          'include/Jaws/Resources/Jaws.js',
          'libraries/bootstrap.fuelux/css/bootstrap.fuelux.min{{.dir}}.css'
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
self.addEventListener('fetch', async function (event) {
    var reqResponse = fetch(event.request).then(
        function (response) {
            var clonedResponse = response.clone();
            if (response.ok) {
                // update the cache with the network response
                caches.open(cacheName).then(
                    function (cache) {
                        cache.put(event.request, clonedResponse);
                    }
                );
            }

            return response;
        }
    ).catch(
        function (error) {
            return caches.open(cacheName).then(
                function (cache) {
                    return cache.match(event.request, {ignoreSearch: true}).then(
                        function(response) {
                            if (!response && event.request.mode == 'navigate') {
                                // doesn't exists cache of request response
                                clients.get(event.clientId || event.resultingClientId).then(function (client) {
                                    // post offline message
                                    client.postMessage({
                                        message: '{{offline_message}}',
                                        redirectTo: client.url,
                                        requestedURL: event.request.url
                                    });
                                });
                                // set response to referrer page
                                response = caches.match(event.request.referrer, {'cacheName': cacheName, ignoreSearch: true});
                            }

                            return response;
                        }
                    );
                }
            );
        }
    );

    event.respondWith(reqResponse);
});

/*
 * Service Worker message listener
 */
self.addEventListener('message', function(event) {
    console.log('service-worker get a message!');
});
<!-- END ServiceWorker -->
<!-- BEGIN Registration -->
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(
        'service-worker.js?{{pwa_version}}',
        {scope: '{{base_url}}'}
    ).catch (function (error) {
        console.log('service-worker registration error: ', error);
    });

    navigator.serviceWorker.ready.then(
        function(registration) {
            //
        }
    );

    // Listen to messages coming from the service worker
    navigator.serviceWorker.addEventListener('message', function(event) {
        alert(event.data.message);
        location = event.data.redirectTo;
    });
}
<!-- END Registration -->