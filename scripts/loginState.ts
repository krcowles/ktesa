/**
 * @fileoverview This script utilizes the getLogin.php activity to determine
 * which menu items are to be activated, and which are to blocked (grayed out).
 * The landing site uses a select bar instead of the navbar, so the landing
 * site has code to enable/disable options.
 * Note that mobile sites require defn of renewPassword, as does unifiedLogin.php
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Ported from menuControl.js for responsive design
 * @version 1.1 Typescripted
 * @version 2.0 Rescripted owing to changed bootstrap support for menus
 */
var page_type = location.href;
var cookies_allowed = navigator.cookieEnabled ? true : false;
var html_cookie = document.getElementById('cookie_state');
var user_cookie_state = html_cookie === null ? false : <string>html_cookie.innerText;

// check to see if cookies are enabled for the browser
if(cookies_allowed) {
    /**
     * Note that for the unifiedLogin page, this code will not be executed
     */
    // Now examine the cookie_state: [admin not enabled on landing]
    if (user_cookie_state === 'NOLOGIN') {
        notLoggedInItems(); // cookies off or rejected
    } else if (user_cookie_state === 'NONE'  || user_cookie_state === 'MULTIPLE') {
        alert("User registration not located");
        notLoggedInItems();
    } else if (user_cookie_state === 'EXPIRED') {
        alert("Your password has expired; You must re-register\n" +
            "to access membership priveleges");
        // delete user from db
        $.get('../accounts/logout.php?expire=Y&mobile=T');
        notLoggedInItems();
    } else if (user_cookie_state === 'RENEW') {
        alert("Your password will expire soon; You must reregister");
        // destroy user cookie to prevent repeat messaging for other pages
        $.get('../accounts/logout.php?mobile=T');
        notLoggedInItems();
    } else if (user_cookie_state === 'OK') {
        loggedInItems();
    }
}
else { // cookies disabled
    let msg = "Cookies are disabled on this browser:\n" +
        "You will not be able to register or login\n" +
        "until cookies are enabled";
    alert(msg);
    notLoggedInItems();
}

/**
 * Turn on menu items for registered members
 */
function loggedInItems() {
    if (page_type.indexOf('landing') !== -1) {
        $("#membership option[value='login']").attr("disabled", "disabled");
        $("#membership option[value='bam']").attr("disabled", "disabled");
        $("#membership option[value=logout]").removeAttr("disabled");
    } else {
        $('#login').addClass('disabled');
        $('#bam').addClass('disabled');
        $('#logout').removeClass('disabled');
        $('#chg').removeClass('disabled');
    }
    return;
}
// Non-members or not logged in
function notLoggedInItems() {
    if (page_type.indexOf('landing') !== -1) {
        $("#membership option[value='login']").removeAttr("disabled");
        $("#membership option[value='bam']").removeAttr("disabled");
        $("#membership option[value='logout']").attr("disabled", "disabled");
    } else {
        $('#login').removeClass('disabled');
        $('#bam').removeClass('disabled');
        $('#logout').addClass('disabled');
        $('#chg').addClass('disabled');
    }
}
