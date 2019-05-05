<!-- BEGIN ServiceWorker -->
const cacheName = 'Jaws-{{pwa_version}}';
/*
 * service worker install event
 */
this.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(cacheName).then(
            function(cache) {
                return cache.addAll([
                    '',
                    'offline',
                    'libraries/jquery/jquery.min.js',
                    'libraries/bootstrap.fuelux/js/bootstrap.fuelux.min.js',
                    'include/Jaws/Resources/Jaws.js',
                    'libraries/bootstrap.fuelux/css/bootstrap.fuelux.min{{.dir}}.css'
                ]).then(() => self.skipWaiting());
            }
        )
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim());
});

/*
 * service worker fetch request event
 */
self.addEventListener('fetch', async function (event) {
    if (event.request.method == 'GET') {
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
                        return cache.match(event.request).then(
                            function(response) {
                                if (!response) {
                                    // set response to referrer page
                                    response = cache.match('offline');
                                }

                                return response;
                            }
                        );
                    }
                );
            }
        );

        event.respondWith(reqResponse);
    }
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