async function deleteNamedCache(cacheName) {
    if ('caches' in window) {
        try {
            const wasDeleted = await caches.delete(cacheName);
            if (wasDeleted) {
                console.log(`Cache "${cacheName}" successfully deleted.`);
            } else {
                console.log(`Cache "${cacheName}" not found.`);
            }
            return wasDeleted;
        } catch (error) {
            console.error(`Error deleting cache "${cacheName}":`, error);
            throw error;
        }
    } else {
        console.warn("Cache API not supported in this environment.");
        return false;
    }
}
