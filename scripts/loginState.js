"use strict";
/**
 * @fileoverview This script utilizes the getLogin.php activity to determine which
 * menu items are to be activated, and which are to blocked (grayed out). The
 * landing site has a different menu, the 'page' var is set to indicate this;
 * Note that mobile sites require defn of renewPassword, as does unifiedLogin.php
 *
 * @author Ken Cowles
 *
 * @version 1.0 Ported from menuControl.js for responsive design
 * @version 1.1 Typescripted
 * @version 2.0 Rescripted owing to changed bootstrap support for menus
 */
var cookies = navigator.cookieEnabled ? true : false;
var html_cookie = document.getElementById('cookie_state');
var user_cookie_state = html_cookie === null ? false : html_cookie.innerText;
// check to see if cookies are enabled for the browser
if (cookies) {
    /**
     * Note that for the unifiedLogin page, this code will not be executed
     */
    // Now examine the cookie_state: [admin not enabled on landing]
    if (user_cookie_state === 'NOLOGIN') {
        notLoggedInItems(); // cookies off or rejected
    }
    else if (user_cookie_state === 'NONE' || user_cookie_state === 'MULTIPLE') {
        alert("User registration not located");
        notLoggedInItems();
    }
    else if (user_cookie_state === 'EXPIRED') {
        alert("Your password has expired; You must re-register\n" +
            "to access membership priveleges");
        // delete user from db
        $.get('../accounts/logout.php?expire=Y');
        notLoggedInItems();
    }
    else if (user_cookie_state === 'RENEW') {
        alert("Your password will expire soon; Use 'Log in' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat messaging for other pages
        $.get('../accounts/logout.php');
        notLoggedInItems();
    }
    else if (user_cookie_state === 'OK') {
        loggedInItems();
    }
}
else { // cookies disabled
    var msg = "Cookies are disabled on this browser:\n" +
        "You will not be able to register or login\n" +
        "until cookies are enabled";
    alert(msg);
    notLoggedInItems();
}
/**
 * Turn on menu items for registered members
 */
function loggedInItems() {
    $("#membership option[value='login']").remove();
    $("#membership option[value='bam']").remove();
    return;
}
function notLoggedInItems() {
    $("#membership option[value='logout']").remove();
}
