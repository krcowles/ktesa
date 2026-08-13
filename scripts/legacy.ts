/**
 * @fileoverview This script will be invoked on the landing page until
 * all members no longer have service workers or indexedDB files. 
 * 
 * @author: Ken Cowles
 * @version 1.0 1st release of new app rollout for mobile platforms
 */
const CACHES = {
    tiles: 'map_tiles',
    code: 'map_source'
};
const member = $('#member').text();
navigator.serviceWorker.getRegistration()
.then(async (registration) => {
    if (registration) {
        // uninstall current worker & delete IndexedDB caches
        registration.unregister().then(async (success) => {
            if (success) {
                const codeDeletion = await deleteNamedCache(CACHES.code);
                const tileDeletion = await deleteNamedCache(CACHES.tiles);
                if (codeDeletion !== '' || tileDeletion !== '') {
                    var issue_msg = '';
                    const cache_issue = document.getElementById('cache_issue') as HTMLDialogElement;
                    cache_issue.showModal();
                    $('#cache_ok_btn').on('click', () => {
                        cache_issue.close();
                    });
                    if (codeDeletion !== '') {
                        issue_msg += codeDeletion + "\n";
                    }
                    if (tileDeletion !== '') {
                        issue_msg += tileDeletion
                    }
                    const ajaxdata = {member: member, issue: issue_msg};
                    $.post("../php/legacyNotification.php", ajaxdata);
                }
            }
            else {
                const sw_not_deleted = document.getElementById('sw_issue') as HTMLDialogElement;
                sw_not_deleted.showModal();
                $('#sw_issue_btn').on('click', () => {
                    sw_not_deleted.close();
                });
                const ajaxdata = {member: member, issue: "Service worker not deleted"};
                $.post("../php/legacyNotification.php", ajaxdata);
            }
        });
    }
});
