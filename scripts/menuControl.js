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
 * @version 2.1 Added mobile platform detection for responsive design
 */
window.mobileAndTabletCheck = function() {
    let check = false;
    (function(a){
        if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
};
var mobile = mobileAndTabletCheck();
var lost_password = $('#email_password').detach();
var cookies = navigator.cookieEnabled ? true : false;
var user_cookie_state = document.getElementById('cookie_state').innerText;

// Always check this tag to place the correct menu text in 'Help' when user logged in
var uchoice = document.getElementById('cookies_choice');
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
