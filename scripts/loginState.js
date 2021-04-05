"use strict";
/// <reference types="bootstrap" />
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
 */
var cookies = navigator.cookieEnabled ? true : false;
var html_cookie = document.getElementById('cookie_state');
var user_cookie_state = html_cookie === null ? false : html_cookie.innerText;
var renewp;
// check to see if cookies are enabled for the browser
if (cookies) {
    /**
     * Note that for the unifiedLogin page, this code will not be executed
     */
    // Now examine the cookie_state:
    if (user_cookie_state === 'NOLOGIN') {
        notLoggedInItems(); // cookies off or rejected
    }
    else if (user_cookie_state === 'NONE') {
        alert("No user registration was located");
        notLoggedInItems();
    }
    else if (user_cookie_state === 'EXPIRED') {
        alert("Your password has expired; Use 'Log in' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat messaging for other pages
        $.get('../accounts/logout.php');
        notLoggedInItems();
    }
    else if (user_cookie_state === 'RENEW') {
        alert("Your password will expire soon; Use 'Log in' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat messaging for other pages
        $.get('../accounts/logout.php');
        notLoggedInItems();
    }
    else if (user_cookie_state === 'MULTIPLE') {
        alert("Multiple accounts are registered for this cookie\n" +
            "\nPlease contact the site master");
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
    $('#login').removeClass('active');
    $('#login').addClass('disabled');
    $('#logout').removeClass('disabled');
    $('#logout').addClass('active');
    $('#bam').removeClass('active');
    $('#bam').addClass('disabled');
    $('#chg').removeClass('disabled');
    $('#chg').addClass('active');
    return;
}
/**
 * Turn off menu items for resitered members
 */
function notLoggedInItems() {
    $('#login').removeClass('disabled');
    $('#login').addClass('active');
    $('#logout').addClass('disabled');
    $('#logout').removeClass('active');
    $('#bam').removeClass('disabled');
    $('#bam').addClass('active');
    $('#chg').removeClass('active');
    $('#chg').addClass('disabled');
    $('admin').css('display', 'none');
    return;
}
/**
 * Enable admintools for admins
 */
function adminLoggedIn() {
    $('#admintools').css('display', 'block');
    return;
}
/**
 * IF a user cookie has either expired or is up for renewal,
 * he/she is provided the option to update the password,
 * set a new expiration date, and continue as a registered user.
 * User credentials have already been established at this point.
 */
var renewPassword = function (renew) {
    if (renew === 'renew') { // send email to reset password
        renewp = new bootstrap.Modal(document.getElementById('cpw'), {
            keyboard: false
        });
        renewp.show();
    }
    else {
        // When a user does not renew membership,
        // his/her login info is removed from the USERS table 
        $.get({
            url: '../accounts/logout.php?expire=Y',
            success: function () {
                alert("You are permanently logged out\n" +
                    "To rejoin, select 'Become a member' from the menu");
                window.open("../index.html", "_self");
            }
        });
    }
    return;
};
