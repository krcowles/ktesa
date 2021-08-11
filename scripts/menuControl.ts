/**
 * @fileoverview This script gets info provided by getLogin.php
 * [located on ktesaPanel.php] and utilizes it to enable/disable
 * menu options.
 * 
 * @author Ken Cowles
 * @version 2.0 Redesigned login for security improvement (script formerly
 * called getLogin.js)
 * @version 2.1 Added mobile platform detection for responsive design
 * @version 3.0 Typescripted, with some type errors corrected
 * @version 4.0 Reworked for new bootstrap design on non-mobile platforms
 */
var cookies = navigator.cookieEnabled ? true : false;
var cookie_info = <HTMLElement>document.getElementById('cookie_state');
var user_cookie_state = cookie_info.innerText;
var active_member = user_cookie_state !== "NOLOGIN" && user_cookie_state !== "NONE"
    && user_cookie_state !== "EXPIRED" && user_cookie_state !== "RENEW" 
    && user_cookie_state !== "MULTIPLE";
if (active_member) {
    // members will have the 'Contribute' menu items displayed
    $('#contrib').css('display', 'block');
    // enable admintools if admin logged in
    var adminState = document.getElementById('admin');
    if (adminState !== null) {
        $('#admintools').css('display', 'block');
    }
    // set the members Help menu item for changing cookies choice
    var uchoice = <HTMLElement>document.getElementById('cookies_choice');
    $('#change_cookies').css('display', 'block');
    if ($('#cookies_choice').text() === 'accept') {
        $('#usrcookies').text("Reject Cookies");
    } else if ($('#cookies_choice').text() === 'reject') {
        $('#usrcookies').text("Accept Cookies");
    }
}

// check to see if cookies are enabled for the browser
if(cookies) { // exception messages
    if (user_cookie_state === 'NONE') {
        alert("No user registration was located");
    } else if (user_cookie_state === 'EXPIRED') {
        alert("Your password has expired; Use 'Members->Login' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat this messaging for other pages
        $.get('../accounts/logout.php');
    } else if (user_cookie_state === 'RENEW') {
        alert("Your password will expire soon; Use 'Members->Login' to renew:\n" +
            "You are not currently logged in");
        // destroy user cookie to prevent repeat this messaging for other pages
        $.get('../accounts/logout.php');
    } else if (user_cookie_state === 'MULTIPLE') {
        alert("Multiple accounts are registered for this cookie\n" +
            "\nPlease contact the site master");     
    } 
}
else { // cookies disabled
    let msg = "Cookies are disabled on this browser:\n" +
        "You will not be able to register or login\n" +
        "until cookies are enabled";
    alert(msg);
}

/**
 * IF a user cookie has either expired or is up for renewal,
 * he/she is provided the option to update the password,
 * set a new expiration date, and continue as a registered user.
 * User credentials have already been established at this point. 
 */
function renewPassword(renew: string) {
    if (renew === 'renew') { // send email to reset password
       
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
