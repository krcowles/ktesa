"use strict"
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
var lost_password = $('#email_password').detach();
var cookies = navigator.cookieEnabled ? true : false;
var user_cookie_state = document.getElementById('cookie_state').innerText;

// Always check this tag to place the correct menu text in 'Help' when user logged in
let uchoice = document.getElementById('cookies_choice');
if (typeof uchoice !== 'undefined' && uchoice !== null) {
    if ($('#cookies_choice').text() === 'accept') {
        $('#ctoggle').text("Reject Cookies");
    } else if ($('#cookies_choice').text() === 'reject') {
        $('#ctoggle').text("Accept Cookies");
    }
}

// check to see if cookies are enabled for the browser
if(cookies) {
    // Now examine the cookie_state:
    if (user_cookie_state === 'NOLOGIN') {
        notLoggedInItems(); // cookies off or rejected
    } else if (user_cookie_state === 'NONE') {
        alert("No user registration was located");
        notLoggedInItems();
    } else if (user_cookie_state === 'EXPIRED') {
        alert("Your password has expired; Use 'Log in' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat messaging for other pages
        $.get('../accounts/logout.php');
        notLoggedInItems();
    } else if (user_cookie_state === 'RENEW') {
        alert("Your password will expire soon; Use 'Log in' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat messaging for other pages
        $.get('../accounts/logout.php');
        notLoggedInItems();
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
}
else { // cookies disabled
    let msg = "Cookies are disabled on this browser:\n" +
        "You will not be able to register or login\n" +
        "until cookies are enabled";
    alert(msg);
    notLoggedInItems();
    $('#lin').addClass('ui-state-disabled');
    $('#join').addClass('ui-state-disabled');
    $('#forgot').addClass('ui-state-disabled');
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
function renewPassword(renew) {
    if (renew === 'renew') { // send email to reset password
        modal.open(
            {content: lost_password, height: '140px', width: '240px',
            id: 'renewpass'}
        );
    } else {
        // When a user does not renew membership,
        // his/her login info is removed from the USERS table 
        $.get({
            url: '../accounts/logout.php?expire=Y',
            success: function() {
                alert("You are permanently logged out\n" +
                    "To rejoin, select 'Become a member' from the menu");
                window.open("../index.html", "_self");
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
    $('#forgot').addClass('ui-state-disabled');
    $('#join').addClass('ui-state-disabled');
    $('#chgpass').removeClass('ui-state-disabled');
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
    $('#chgpass').addClass('ui-state-disabled');
    $('#forgot').removeClass('ui-state-disabled');
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
