/**
 * This service worker is installed in 'member_landing.html', hence
 * all map source code assets are cached in the 'code' cache prior to
 * saving or using an offline map. Later, when a map is being saved,
 * the maptiles calculated in saveOffline.js are cached in the 'tiles'
 * cache. When a map is used offline, both caches are utilized to
 * respond to fetch requests.
 */
const CACHE_NAMES = {
    tiles: 'map_tiles',
    code: 'map_source'
};
var save_tiles = false; // disable/enable 'puts' in the 'tiles' cache.

self.addEventListener("install", (event) => {
    //self.skipWaiting();  // Useful during debug
    event.waitUntil(
      caches
      .open(CACHE_NAMES.code)
        .then((cache) => {
            cache.addAll([  // TEST SITE DIR included here...
                "https://nmhikes.com/pages/useOffline.html",
                "https://nmhikes.com/styles/bootstrap.min.css",
                "https://nmhikes.com/styles/leaflet.css",
                "https://nmhikes.com/styles/useOffline.css",
                "https://nmhikes.com/scripts/jquery.js",
                "https://nmhikes.com/scripts/popper.min.js",
                "https://nmhikes.com/scripts/bootstrap.min.js",
                "https://nmhikes.com/scripts/ktesaOfflineDB.js",
                "https://nmhikes.com/scripts/useOffline.js",
                "https://nmhikes.com/scripts/leaflet.js",
            ])
        })
    );
});

self.addEventListener("activate", () => {
    self.clients.claim();
});
 
/**
 * This event handler is only active upon first entering the
 * 'member_landing.html' site, and then it is removed. It will allow
 * the cache preload to complete, and is then terminated. Thus, it
 * will not store additional fetches to other nmhikes.com pages.
 */
const putInPreload = async (request, response) => {
    const cache = await caches.open(CACHE_NAMES.code);
    await cache.put(request, response);
};
const preload = async (request) => {
    const networkResponse = await fetch(request);
    var resource_url = request.url;
    var url_string = resource_url.toString();
    if (request.method === "GET" && url_string.includes("nmhikes.com")) {
        putInPreload(request, networkResponse.clone());
    }
    return networkResponse;
};
const preloadHandler = (event) => {
    event.respondWith(preload(event.request));
};
self.addEventListener('fetch', preloadHandler);
// When done caching source code, remove the listener
setTimeout(() => {
    self.removeEventListener('fetch', preloadHandler);
}, 200);

/**
 * The 'tiles' cache will now be used to cache map tiles specified by
 * the 'saveOffline.js' routine. This handler will ignore adds to the
 * cache until a message is received during 'save maps' which will enable
 * caching of map tiles. When the 'save maps' routine is done, another
 * message is received to disable further caching. When not caching tiles,
 * -all- caches will be searched for a match when an item is fetched.
 */
const putInCache = async (request, response) => {
    const cache = await caches.open(CACHE_NAMES.tiles);
    await cache.put(request, response);
};
const cacheFirst = async (request) => {
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
const responseHandler = (event) => {
    event.respondWith(cacheFirst(event.request));
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
