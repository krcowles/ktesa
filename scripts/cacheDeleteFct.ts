async function deleteNamedCache(cacheName: string): Promise<string> {
    var msg = '';
    if ('caches' in window) {
        try {
            const wasDeleted = await caches.delete(cacheName);
            if (wasDeleted) {
                return msg; // return empty to prevent interpreting as error
            } else {
                //msg = `Cache "${cacheName}" not found.`
                return msg; // return empty to prevent interpreting as error
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
