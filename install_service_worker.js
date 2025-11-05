const registerServiceWorker = async () => {
    if ("serviceWorker" in navigator) {
        /** 
         * After initial installation, re-installation will occur only
         * when the service_worker.js is byte-different than what
         * is currently installed. Its install event occurs only once.
         * NOTE: "/" refers to the root at nmhikes.com, not within
         * a test site, for example!
         */
        try {
            const registration = await navigator.serviceWorker
                // for localhost, scope: "/"; server, scope "nmhikes.com/""
                .register("/service_worker.js", {scope: "/"})
                .then((reg) => {
                    if (reg.installing) {
                        console.log("Service worker installing");
                    } else if (reg.waiting) {
                        console.log("Service worker installed");
                    } else if (reg.active) {
                        console.log("Service worker active");
                    } else {
                        console.log("Promise Pending...");
                    }
                    reg.addEventListener('updatefound', () => {
                        // An updated service worker while reg.installing
                        const newWorker = reg.installing;
                        switch (newWorker.state) {
                        case "installed":
                            console.log("Updated worker installed");
                            break;
                        case "activated":
                            console.log("Updated worker active");
                            break;
                        case "redundant":
                            console.log("Discarded: failed to install");
                        // "installing" - the install event has fired, but not yet complete
                        // "activating" - the activate event has fired, but not yet complete
                        // "redundant"  - discarded. Either failed install, or it's been
                        //                replaced by a newer version
                        }
                        newWorker.addEventListener('statechange', () => {
                            console.log("Updated with new service worker");
                        });
                    });
                })        
        } catch (error) {
            console.error(`Registration failed with ${error}`);
        }
    }
};
  
registerServiceWorker();
  