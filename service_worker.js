const offline_index = 'https://nmhikes.com/offline_index.html';
const index = "https://nmhikes.com/ld/index.html";
self.addEventListener("install", (event) => {
    //self.skipWaiting();  // Useful during debug
    console.log("ROOT worker install");
    event.waitUntil(
      caches
      .open("offline")
        .then((cache) => {
            cache.addAll([  // test site dir included here...
                "https://nmhikes.com/pages/landing.html",
                "https://nmhikes.com/images/hikers.png",
                "https://nmhikes.com/images/trail.png",
                "https://nmhikes.com/images/Tbl.png",
                "https://nmhikes.com/images/MapsNmrkrs.png",
                "https://nmhikes.com/images/Save.png",
                "https://nmhikes.com/images/Use.png",
                "https://nmhikes.com/styles/landing.css",
                "https://nmhikes.com/styles/bootstrap.min.css",
                "https://nmhikes.com/scripts/jquery.js",
                "https://nmhikes.com/scripts/bootstrap.min.js",
                "https://nmhikes.com/scripts/viewMgr.js",
                "https://nmhikes.com/scripts/landing.js",
                "https://nmhikes.com/scripts/loginState.js",
                "https://nmhikes.com/pages/useOffline.html",
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

/**
 * To add the correct version of index.html to cache, which is located
 * on the server at /offline_index.html, a fetch of the latter is made,
 * then the url is changed to /index.html before adding to cache
 */
caches.open("offline").then( (cache) => {
    fetch(offline_index).then((response) => {
        return cache.put(index, response);
    });
});

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
 * This is primarily the case when creating the offline map.
 */
const putInCache = async (request, response) => {
    //console.log("Caching ", response);
    const cache = await caches.open("offline");
    await cache.put(request, response);
};
