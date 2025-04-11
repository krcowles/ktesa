"use strict";
/**
 * @fileoverview This script gets info provided by getLogin.php
 * [located on ktesaPanel.php] and utilizes it to enable/disable
 * menu options.
 *
 * @author Ken Cowles
 * @version 5.0 Updated security w/encryption and 2FA
 * @version 5.1 Add disable to 'Continue Edits' and 'Publish Request' as approp.
 * @version 5.2 Added 'Membership Benefits' icon to navbar
 * @version 6.0 Remove members choice to accept/reject cookies
 */
var cookies_allowed = navigator.cookieEnabled ? true : false;
var cookie_info = document.getElementById('cookie_state');
var user_cookie_state = cookie_info.innerText;
var active_member = user_cookie_state !== "NOLOGIN" && user_cookie_state !== "NONE"
    && user_cookie_state !== "EXPIRED" && user_cookie_state !== "RENEW"
    && user_cookie_state !== "MULTIPLE";
if (active_member) {
    var club = $('#club_member').text() === 'Y' ? true : false;
    // members will have the 'Contribute' menu items displayed
    $('#contrib').css('display', 'block');
    // hide the member benefits star
    $('#memspace').css('display', 'none');
    $('#benefits').css('display', 'none');
    // enable admintools if admin logged in
    var adminState = document.getElementById('admin');
    if (adminState !== null) {
        $('#admintools').css('display', 'block');
    }
    // set 'Members' choices
    $('#login').addClass('disabled');
    $('#bam').addClass('disabled');
    $('#updte_sec').removeClass('disabled');
    if (club) {
        $('#hiking_club').show();
    }
    else {
        $('#hiking_club').hide();
    }
    // Look to see if user has any active edit pages
    var uhikes = parseInt($('#uhikes').text());
    if (uhikes === 0) {
        $('#conteditpg').addClass('disabled');
        $('#pubreqpg').addClass('disabled');
    }
    else {
        $('#conteditpg').removeClass('disabled');
        $('#pubreqpg').removeClass('disabled');
    }
}
else { // NOT MEMBER
    // disable appropriate 'Members' items
    $('#logout').addClass('disabled');
    $('#chg').addClass('disabled');
    $('#updte_sec').addClass('disabled');
    $('#favpg').addClass('disabled');
    $('#hiking_club').hide();
}
// check to see if cookies are enabled for the browser
if (cookies_allowed) { // exception messages only: auto login may still occur
    if (user_cookie_state === 'NONE' || user_cookie_state === 'MULTIPLE') {
        var msg = "User registration not located\nRe-register using 'Members->" +
            "Become a member";
        alert(msg);
    }
    else if (user_cookie_state === 'EXPIRED') {
        $.get('../accounts/logout.php?expire=Y');
        alert("Your password has expired; You must re-register");
    }
    else if (user_cookie_state === 'RENEW') {
        // destroy user cookie to prevent repeating this message on other pages
        $.get('../accounts/logout.php');
        alert("Your password will expire soon; Use 'Members->Login' to renew:\n" +
            "You are currently logged out");
    }
}
else { // cookies disabled
    var msg = "Cookies are disabled on this browser:" +
        "You will not be able to register, login, or edit" +
        " pages as a member until cookies are enabled";
    alert(msg);
}
