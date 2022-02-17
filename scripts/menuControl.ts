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
 * @version 4.0 Reworked for new bootstrap navbar on non-mobile platforms
 * @version 5.0 Updated security w/encryption and 2FA
 */
var cookies = navigator.cookieEnabled ? true : false;
var cookie_info = <HTMLElement>document.getElementById('cookie_state');
var user_cookie_state = cookie_info.innerText;
var active_member = user_cookie_state !== "NOLOGIN" && user_cookie_state !== "NONE"
    && user_cookie_state !== "EXPIRED" && user_cookie_state !== "RENEW" 
    && user_cookie_state !== "MULTIPLE";
if (active_member) {
    var cookie_choice = <HTMLElement>document.getElementById('cookies_choice');
    var user_cookie_choice = cookie_choice.innerText;
    // members will have the 'Contribute' menu items displayed
    $('#contrib').css('display', 'block');
    // enable admintools if admin logged in
    var adminState = document.getElementById('admin');
    if (adminState !== null) {
        $('#admintools').css('display', 'block');
    }
    // set 'Members' choices
    $('#login').addClass('disabled');
    $('#bam').addClass('disabled');
    $('updte_sec').removeClass('disabled');
    // display appropriate change-cookie text
    var display_choice = user_cookie_choice === 'accept' ? 'Reject Cookies' : 'Accept Cookies';
    $('#usrcookies').text(display_choice);
} else {
    // disable appropriate 'Members' items
    $('#logout').addClass('disabled');
    $('#chg').addClass('disabled');
    $('#updte_sec').addClass('disabled');
    $('#change_cookies').css('display', 'none');
}

// check to see if cookies are enabled for the browser
if(cookies) { // exception messages only: auto login may still occur
    if (user_cookie_state === 'NONE' || user_cookie_state === 'MULTIPLE') {
        let msg = "User registration not located\nRe-register using 'Members->" +
            "Become a member";
        alert(msg);
    } else if (user_cookie_state === 'EXPIRED') {
        $.get('../accounts/logout.php?expire=Y');
        alert("Your password has expired; You must re-register");   
    } else if (user_cookie_state === 'RENEW') {
         // destroy user cookie to prevent repeating this message on other pages
        $.get('../accounts/logout.php');
        alert("Your password will expire soon; Use 'Members->Login' to renew:\n" +
            "You are currently logged out"); 
    }
}
else { // cookies disabled
    let msg = "Cookies are disabled on this browser:\n" +
        "You will not be able to register or login\n" +
        "until cookies are enabled";
    alert(msg);
}

