/**
 * @fileoverview This script gets info provided by getLogin.php
 * [located on ktesaPanel.php] and utilizes it to enable/disable
 * menu options.
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned login for security improvement (script formerly
 * called getLogin.js)
 */
var cookies = navigator.cookieEnabled ? true : false;
var user_cookie_state = document.getElementById('cookie_state').innerText;
if (cookies) {
    // Always check this tag to place the correct menu text in 'Help' when user logged in
    let uchoice = document.getElementById('cookies_choice');
    if (typeof uchoice !== 'undefined' && uchoice !== null) {
        if ($('#cookies_choice').text() === 'accept'){
            $('#ctoggle').text("Reject Cookies");
        } else if ($('#cookies_choice').text() === 'reject') {
            $('#ctoggle').text("Accept Cookies");
        }
    }
    // Now examine the cookie_state:
    if (user_cookie_state === 'NOLOGIN') {
        notLoggedInItems();
    } else if (user_cookie_state === 'NONE') {
        alert("No user registration was located");
        notLoggedInItems();
    } else if (user_cookie_state === 'EXPIRED') {
        var ans = confirm("Your password has expired\n" + 
            "Would you like to renew?");
        if (ans) {
            renewPassword('renew', 'expired');
        } else {
            renewPassword('norenew', 'expired');
        }
    } else if (user_cookie_state === 'RENEW') {
        loggedInItems();
        var ans = confirm("Your password is about to expire\n" + 
            "Would you like to renew?");
        if (ans) {
            renewPassword('renew', 'valid');
        } else {
            renewPassword('norenew', 'valid');
        }
    } else if (user_cookie_state === 'MULTIPLE') {
        alert("Multiple accounts are registered for this cookie\n" +
            "\nPlease contact the site master");
        notLoggedInItems();
    } else if (user_cookie_state === 'OK') {
        loggedInItems();
        if ($('#admin').text() === "admin") {
            adminLoggedIn();
        } 
    }
} else { // cookies disabled
    let msg = "Cookies are disabled on this browser:\n" +
        "You will not be able to login, register, or edit/create hikes.\n" +
        "Please enable cookies to overcome this limitation"
    alert(msg);
    notLoggedInItems();
    $('#lin').addClass('ui-state-disabled');
    $('#join').addClass('ui-state-disabled');
    $('#ifadmin').css('display', 'none');
}
/**
 * IF a user cookie has either expired or is up for renewal,
 * he/she is provided the option to update the password,
 * set a new expiration date, and continue as a registered user.
 * User credentials have already been established at this point. 
 * 
 * @param {string} update 
 * @param {string} status 
 * 
 * @return {null}
 */
function renewPassword(renew, status) {
    if (renew === 'renew') { // complete login w/ new credentials
        window.open('../accounts/renew.php', '_self');
        return;
    } 
    if (status === 'valid') {
        // for this session only, temporarily extend 'expire' to prevent 'renew'
        // popups when accessing other site pages
        $.get('../accounts/tempExpire.php');
    } else {  // 'expired' 
        $.get({
            url: '../accounts/logout.php',
            success: function() {
                alert("You are logged out");
                notLoggedInItems();
                $('#ifadmin').css('display', 'none');
                window.open('../index.html', '_self');
            }
        });
    }
    return;
}
/**
 * Turn on menu items for registered members
 * 
 * @return {null}
 */
function loggedInItems() {
    $('#ifuser').css('display', 'block');
    $('#lin').addClass('ui-state-disabled');
    $('#lout').removeClass('ui-state-disabled');
    //$('#pubs').removeClass('ui-state-disabled'); -- removed for now
    $('#yours').removeClass('ui-state-disabled');
    //$('#viewEds').removeClass('ui-state-disabled'); -- removed for now
    $('#newPg').removeClass('ui-state-disabled');
    $('#edits').removeClass('ui-state-disabled');
    $('#epubs').removeClass('ui-state-disabled');
    //$('#pubReq').removeClass('ui-state-disabled'); -- removed for now
    $('#join').addClass('ui-state-disabled');
    return;
}
/**
 * Turn off menu items for resitered members
 * 
 * @return {null}
 */
function notLoggedInItems() {
    $('#ifuser').css('display', 'none');
    $('#lin').removeClass('ui-state-disabled');
    $('#lout').addClass('ui-state-disabled');
    //$('#pubs').addClass('ui-state-disabled'); -- removed for now
    $('#yours').addClass('ui-state-disabled');
    //$('#viewEds').addClass('ui-state-disabled'); -- removed for now
    $('#newPg').addClass('ui-state-disabled');
    $('#edits').addClass('ui-state-disabled');
    $('#epubs').addClass('ui-state-disabled');
    //$('#pubReq').addClass('ui-state-disabled'); -- removed for now
    $('#join').removeClass('ui-state-disabled');
    return;
}
/**
 * Enable admintools for admins
 * 
 * @return {null}
 */
function adminLoggedIn() {
    $('#ifadmin').css('display', 'block');
    return;
}
