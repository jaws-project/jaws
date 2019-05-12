<!-- BEGIN ServiceWorker -->
self.importScripts(
    'libraries/localForage/localforage.min.js'
);

/*
 *  service worker version
*/
const ServiceWorkerVersion = 'a.{{pwa_version}}';

/*
 *  offline request page 
*/
var offlineRequest = new Request('offline.html');

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
            localforage.setItem(ftok.toString(), strResponse).then(function (value) {
                // value has been saved.
            }).catch(function(error) {
                // runs if there were any errors
                console.log(error);
            });
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
    fetch(offlineRequest).then(
        function (response) {
            setRequestResponseCache(offlineRequest, response.clone());
        }
    ).catch (
        function (error) {
            console.log(error);
        }
    );

    /*
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
    */
});

self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim());
});

/*
 * service worker fetch request event
 */
self.addEventListener('fetch', async function (event) {
    var clonedRequest = event.request.clone();
    self.clients.get(event.clientId || event.resultingClientId).then(function(client) {
        //console.log(client);
    });
    var reqResponse = fetch(event.request).then(
        function (response) {
            // set request response cache
            setRequestResponseCache(clonedRequest, response.clone());
            // return request response with network response
            return response;
        }
    ).catch(
        function (error) {
            // get request response cache
            return getRequestResponseCache(clonedRequest).catch(
                function(error) {
                    console.log(error);
                    // get offline(503) response cache
                    return getRequestResponseCache(offlineRequest);
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
    event.source.script = event.data.script;
    event.source.standalone = event.data.standalone;
    //---
    event.target.base = event.data.base;
    event.target.standalone = event.data.standalone;
    //console.log('service-worker get a message from: ' + event.source.id);
});
<!-- END ServiceWorker -->