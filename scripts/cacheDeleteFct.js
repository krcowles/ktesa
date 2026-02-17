async function deleteNamedCache(cacheName) {
    var msg = '';
    if ('caches' in window) {
        try {
            const wasDeleted = await caches.delete(cacheName);
            if (wasDeleted) {
                return msg; // empty to prevent interpreting as error
            } else {
                msg = `Cache "${cacheName}" not found.`
                console.error(msg);
                return msg;
            }
        } catch (error) {
            msg = `Error deleting cache "${cacheName}":`
            console.error(msg, error);
            return msg;
        }
    } else {
        msg = "Cache API not supported in this environment."
        console.error(msg);
        return msg;
    }
}
