/**
 * @fileoverview This script applies updates to the offline map
 *               code by 'restarting' the service worker & 
 *               deleting the code cache.
 * @author: Ken Cowles
 * @version 1.0 1st release of new update process
 */
const CACHE_NAMES = {
    tiles: 'map_tiles',
    code: 'map_source'
};
const user = $('#userid').text();
const latest_ver = $('#latest_ver').text();

// dialog box operation
const update_success = document.getElementById('update') as HTMLDialogElement;
const update_failure = document.getElementById('noupdate') as HTMLDialogElement;
$('#ok').on("click", () => {
    update_success!.close();
    window.open("./member_landing.html", "_self");
});
$('#notok').on("click", () => {
    update_failure!.close();
    window.open("./member_landing.html", "_self");
});
// failure vars
const error_id  = "ID:" + user + ": ";
const encodedEmail = "a3Jjb3dsZXMyOUBnbWFpbC5jb20="; 
const adminEmail = atob(encodedEmail);
let sendEmail = document.getElementById('adminmail') as HTMLSpanElement;
sendEmail.textContent = adminEmail;
let sendEmsg = document.getElementById('adminmsg') as HTMLSpanElement;

var update_status = '';
const displayErrors = (msg: string) => {
    let disp = error_id + msg;
    sendEmsg.textContent = disp;
    update_failure!.showModal();
};

// after uninstalling old worker (main routine)
async function install_service_worker() {
    console.log("Start install");
    try {
        await navigator.serviceWorker
        // for localhost, scope => "/"; for host, scope => "nmhikes.com/"
        .register("/service_worker.js", {scope: "/"})
        .then((reg) => {
            if (reg.installing) {
                console.log("Service worker installing");
            } else if (reg.waiting) {
                console.log("Service worker installed");
            } else if (reg.active) {
                console.log("Service worker active");
            }
            reg.addEventListener('updatefound', () => {
                // An updated service worker while reg.installing
                console.log("registration in process");
                const newWorker = reg.installing as ServiceWorker;
                switch (newWorker.state) {
                case "installed":
                    console.log("Updated worker installed");
                    break;
                case "activated":
                    console.log("Updated worker active");
                    break;
                case "redundant":
                    console.log("Discarded: failed to install");
                }
            });
            // no changes to update status
        });        
    } catch (error) {
            update_status += `Registration: ${error}`;
            console.error(`Registration failed with ${error}`);
            
    }
}

/**
 * This is the main routine which begins by determining if
 * the user currently has a service worker installed. If not,
 * install it - and done (latest code will be retrieved and
 * chached). Otherwise, the old worker must be uninstalled and
 * the code cache deleted before reinstalling the worker (which
 * re-loads the cache code).
 */
navigator.serviceWorker.getRegistration()
.then( async (registration) => {
    if (registration) {
        // uninstall current worker
        registration.unregister().then(async (success) => {
            if (success) {
                const deleteStatus = await deleteNamedCache(CACHE_NAMES.code);
                update_status += deleteStatus;
                if (update_status == '') {
                    // good so far...
                    await install_service_worker();
                    if (update_status == '') {
                        $.post('updateDbVersion.php',
                            {user: user, latest: latest_ver}, (result) => {
                                if (result !== 'ok') {
                                    displayErrors("DB update error; " + result);
                                } else {
                                    update_success?.showModal();
                                }
                            }, 'text');    
                    } else {
                        displayErrors(update_status);
                    }
                } else { // failed to delete cache:
                    displayErrors(update_status);
                }  
            } else { // no documentation for !success online
                displayErrors("Failed to register; ");
               
            }
        });
    } else {
        // Install it - latest code will be installed and cached
        await install_service_worker();
        if (update_status == '') {
            $.ajax({
                url: 'updateDbVersion.php',
                dataType: 'text',
                data: {user: user, latest: latest_ver},
                method: 'post',
                success: (result) => {
                    if (result !== 'ok') {
                        displayErrors("DB update error; " + result);
                    } else {
                        update_success.showModal();
                    }
                },
                error: (_jqXHR) => {
                    displayErrors(_jqXHR.responseText);
                }
            });     
        } else {
            displayErrors(update_status);
        }
    }
});
