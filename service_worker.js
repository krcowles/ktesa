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

self.addEventListener("activate", (event) => {
    //event.waitUntil(deleteOldCaches()); // cache contains tiles!
    self.clients.claim();
});
  
const deleteOldCaches = async () => {
    const cacheKeepList = ["test"];
    const keyList = await caches.keys();
    const cachesToDelete = keyList.filter((key) => !cacheKeepList.includes(key));
    await Promise.all(cachesToDelete.map(deleteCache));
};
const deleteCache = async (key) => {
    await caches.delete(key);
}; 

self.addEventListener("fetch", (event) => {
    event.respondWith(cacheFirst(event.request));
});
/**
 * Check the cache for assets before attempting to fetch
 */
const cacheFirst = async (request) => {
    const responseFromCache = await caches.match(request, {ignoreVary: true});
    if (responseFromCache) {
        //console.log("Found cache match");
        return responseFromCache;
    }
    // get index.html from server at offline_index.html / modify url
    const responseFromNetwork = await fetch(request);
    fetch_url = request.url;
    str_url = fetch_url.toString();
    if (request.method === "GET" && str_url.indexOf("openstreet") !== -1) {
        console.log("Saving access to tile ", request.url);
        putInCache(request, responseFromNetwork.clone());
    }
    return responseFromNetwork;
};
/**
 * Fetched leaflet tile server assets not in the cache are placed there;
 * This happens when creating the offline map [saveOffline.php]
 */
const putInCache = async (request, response) => {
    const cache = await caches.open("offline");
    await cache.put(request, response);
};
