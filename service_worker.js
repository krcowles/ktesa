var save_tiles = false;
/**
 * Pre-load cache with only the 'use offline' page resources
 * NOTE: use of console.log can be unreliable
 */
self.addEventListener("install", (event) => {
    //self.skipWaiting();  // Useful during debug
    console.log("ROOT worker install");
    event.waitUntil(
      caches
      .open("offline")
        .then((cache) => {
            cache.addAll([  // TEST SITE DIR included here...
                "https://nmhikes.com/ld/pages/useOffline.html",
                "https://nmhikes.com/ld/styles/bootstrap.min.css",
                "https://nmhikes.com/ld/styles/leaflet.css",
                "https://nmhikes.com/ld/styles/useOffline.css",
                "https://nmhikes.com/ld/scripts/jquery.js",
                "https://nmhikes.com/ld/scripts/popper.min.js",
                "https://nmhikes.com/ld/scripts/bootstrap.min.js",
                "https://nmhikes.com/ld/scripts/ktesaOfflineDB.js",
                "https://nmhikes.com/ld/scripts/useOffline.js",
                "https://nmhikes.com/ld/scripts/leaflet.js",
            ])
        })
    );
});

self.addEventListener("activate", () => {
    self.clients.claim();
});
 
// Cache loader - multiple handlers require
const putInCache = async (request, response) => {
    const cache = await caches.open("offline");
    await cache.put(request, response);
};

/**
 * This event handler is only active upon first entering the
 * member_landing.html site, and then it is removed. It will allow
 * the cache preload to complete, and then the 'cache first' handler
 * will prevail.
 */
const preloadHandler = (event) => {
    event.respondWith(preload(event.request));
};
const preload = async (request) => {
    const networkResponse = await fetch(request);
    var resource_url = request.url;
    var url_string = resource_url.toString();
    if (request.method === "GET" && url_string.includes("nmhikes.com/ld")) {
        putInCache(request, networkResponse.clone());
    }
    return networkResponse;
};
self.addEventListener('fetch', preloadHandler);
setTimeout(() => {
    self.removeEventListener('fetch', preloadHandler);
}, 200);

/**
 * This 'cache first' handler will ignore adds to the cache until 
 * a message is received during 'save maps' which will enable caching
 * of map tiles. When the 'save maps' routine is done, another message
 * is received to disable further caching.
 */
const responseHandler = (event) => {
    event.respondWith(cacheFirst(event.request));
};
const cacheFirst = async (request) => {
    //console.log(request);
    const responseFromCache = await caches.match(request, {ignoreVary: true});
    if (responseFromCache) {
        return responseFromCache;
    }
    const responseFromNetwork = await fetch(request);
    if (request.method === 'GET' && save_tiles) {
        putInCache(request, responseFromNetwork.clone())
    }
    return responseFromNetwork;
};
self.addEventListener('fetch', responseHandler);

/**
 *  Messages are used to enable/disable map tile saves
 */
self.addEventListener('message', async (event) => {
    var command = event.data;
    if (command.action === 'Enable') {
        console.log("Received Enable");
        save_tiles = true;
    } else if (command.action === 'Disable') {
        console.log("Received Disable");
        save_tiles = false;
    } 
});
