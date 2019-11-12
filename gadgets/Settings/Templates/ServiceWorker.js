<!-- BEGIN ServiceWorker -->
self.importScripts(
    'libraries/localForage/localforage.min.js'
);

/*
 *  service worker version
*/
const ServiceWorkerVersion = '{{pwa_version}}';
const Notification_Default_Icon  = '{{notification_icon}}';

/*
 *
 */
const bodyText503 = "{{bodyText503}}";

/*
 *  base request page 
*/
var baseRequest;

(function() {
    /**
     * Javascript crc32 string prototype
    */
    this.crc32 = function(str) {
        var makeCRCTable = function(){
            var c;
            var crcTable = [];
            for(var n =0; n < 256; n++){
                c = n;
                for(var k =0; k < 8; k++){
                    c = ((c&1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1));
                }
                crcTable[n] = c;
            }
            return crcTable;
        }

        var crcTable = this.crcTable || (this.crcTable = makeCRCTable());
        var crc = 0 ^ (-1);
        for (var i = 0; i < str.length; i++ ) {
            crc = (crc >>> 8) ^ crcTable[(crc ^ str.charCodeAt(i)) & 0xFF];
        }

        return (crc ^ (-1)) >>> 0;
    };

    /**
     * Javascript PHP bin2hex string prototype
    */
    this.bin2hex = function(str) {
        let i = 0, l = str.length, chr, hex = '';

        for (i; i < l; i++) {
            chr = str.charCodeAt(i).toString(16);
            hex += chr.length < 2 ? '0' + chr : chr;
        }

        return hex;
    };

})(self);

/*
 * convert request to JSONed string
 */
function JSONRequest(request)
{
    var reqData = {
        init: {
            url: request.url,
            method: request.method,
            mode: request.mode,
            credentials: request.credentials,
            cache: request.cache,
            redirect: request.redirect,
            //referrer: request.referrer,
            headers: {}
        }
    };

    // get headers
    for (var entry of request.headers.entries()) {
        reqData.init.headers[entry[0]] = entry[1];
    }

    return request.text().then(
        function(body) {
            reqData.body = body;
            return Promise.resolve(JSON.stringify(reqData));
        }
    );
}

/*
 * convert response to JSONed string
 */
function JSONResponse(response)
{
    var resData = {
        init: {
            ok: response.ok,
            status: response.status,
            statusText: response.statusText,
            redirected: response.redirected,
            type: response.type,
            url: response.url,
            headers: {}
        }
    };

    // get headers
    for (var entry of response.headers.entries()) {
        resData.init.headers[entry[0]] = entry[1];
    }

    return response.text().then(
        function(body) {
            resData.body = body;
            return Promise.resolve(JSON.stringify(resData));
        }
    );
}

/*
 * set request/response cache
 */
function setRequestResponseCache(request, response)
{
    var ftok;
    JSONRequest(request).then(
        function (strRequest) {
            ftok = crc32(strRequest);
            return JSONResponse(response);
        }
    ).then(
        function (strResponse) {
            localforage.setItem(ftok.toString(), strResponse).catch(
                function(error) {
                    // runs if there were any errors
                    console.log(error);
                }
            );
        }
    );
}

/*
 * get request/response cache
 */
function getRequestResponseCache(request)
{
    return JSONRequest(request).then(
        function (strRequest) {
            return localforage.getItem(crc32(strRequest).toString());
        }
    ).then(
        function (strResponse) {
            strResponse = JSON.parse(strResponse);
            if (!strResponse || !strResponse.init.status) {
                throw new TypeError('Failed to fetch');
            }

            return new Response(strResponse.body, strResponse.init);
        }
    );
}

/*
 * service worker install event
 */
this.addEventListener('install', function(event) {
    //
});

self.addEventListener('activate', function(event) {
    localforage.clear().catch(
        function(error) {
            console.log(error);
        }
    );

    event.waitUntil(self.clients.claim());
});

/*
 * service worker fetch request event
 */
self.addEventListener('fetch', async function (event) {
    var clonedRequest = event.request.clone();
    if (event.request.url == self.registration.scope) {
        baseRequest = event.request.clone();
    }

    var reqResponse = fetch(event.request).then(
        function (response) {
            // set request response cache
            setRequestResponseCache(clonedRequest, response.clone());
            // return request response with network response
            return response;
        }
    ).catch(
        function (error) {
            // get request response from cache
            return getRequestResponseCache(clonedRequest).catch(
                function(error) {
                    // offline response
                    if (event.request.mode == 'navigate') {
                        return new Response(
                            bodyText503, {
                                status: 503,
                                statusText: 'Service Unavailable',
                                headers: {
                                    'content-type': "text/html; charset=utf-8"
                                }
                            }
                        );
                    } else {
                        return new Response('', {status: 503, statusText: 'Service Unavailable'});
                    }
                }
            );
        }
    );

    event.respondWith(reqResponse);
});

/*
 * Service Worker webpush message listener
 */
self.addEventListener('push', function(event)
{
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var title, options = {};

    try {
        var data = event.data.json();
        title = data.title;
        options.body    = data.body;
        options.dir     = data.dir     || 'auto';
        options.icon    = data.icon    || Notification_Default_Icon;
        options.vibrate = data.vibrate || [];
        options.data    = {
            'url': data.url || ''
        };
    } catch(error) {
        title = event.data.text();
    }

    self.registration.showNotification(title, options);
});

/*
 * Service Worker webpush notification click
 */
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    const promiseResult = clients.matchAll(
        {
            type: 'window',
            includeUncontrolled: true
        }
    ).then(
        function(windowClients) {
            let urlToOpen = self.location.base + '#' + bin2hex(event.notification.data.url);

            if (windowClients.length > 0) {
                return windowClients[0].navigate(urlToOpen);
            } else {
                return clients.openWindow(urlToOpen);
            }
        }
    ).then (
        function(windowClient) {
            return windowClient.focus();
        }
    )

    event.waitUntil(promiseResult);
});

/*
 * Service Worker message listener
 */
self.addEventListener('message', function(event) {
    self.location.base = event.data.base;
    event.source.script = event.data.script;
    event.source.standalone = event.data.standalone;
    //---
    event.target.base = event.data.base;
    event.target.standalone = event.data.standalone;
    //console.log('service-worker get a message from: ' + event.source.id);
});
<!-- END ServiceWorker -->