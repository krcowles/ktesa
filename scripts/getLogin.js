/*
 * The technique used to determine when a cookie gets set is to compare the
 * length of the cookie string to what it was on page load. There are no
 * methods associated with cookies for detecting the setting of (or deletion
 * of) a cookie. Since a user may leave the page, acquire another cookie,
 * and then return to this page, a means is provided to re-calculate the
 * cookie length in that case. The following cross-browser technique is utilized:
 * https://howchoo.com/g/mdg5otdhmzk/determine-if-a-tab-has-focus-in-javascript
 * Note that this cannot be placed inside 'document ready'.
 */
var hidden, visibilityChange, state;
var allcookies = decodeURIComponent(document.cookie);
var prevLgth = allcookies.length;
// Set the name of the hidden property and the change event for visibility
if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
    hidden = "hidden";
    visibilityChange = "visibilitychange";
    state = "visibilityState";
} else if (typeof document.mozHidden !== "undefined") {
    hidden = "mozHidden";
    visibilityChange = "mozvisibilitychange";
    state = "mozVisibilityState";
} else if (typeof document.msHidden !== "undefined") {
    hidden = "msHidden";
    visibilityChange = "msvisibilitychange";
    state = "msVisibilityState";
} else if (typeof document.webkitHidden !== "undefined") {
    hidden = "webkitHidden";
    visibilityChange = "webkitvisibilitychange";
    state = "webkitVisibilityState";
}
document.addEventListener(visibilityChange, function() {
    if (document[state] == 'visible') {
        allcookies = decodeURIComponent(document.cookie);
        prevLgth = allcookies.length;
    }
});
// are cookies enabled on this browser?
var cookies = navigator.cookieEnabled ? true : false;
var cookie_state = cookies ? 'ON' : 'OFF';
var login_name = document.getElementById('login_result').textContent;
if (login_name !== 'none') {
    loggedInItems();
    if (login_name === 'mstr') {
        adminLoggedIn();
    }
} else {
    if (!cookies) {
        alert("Cookies appear to be disabled:\n" +
            "You will not be able create/edit hikes unless:\n" +
            "1. If a registered user, login via the 'Log in' menu item;\n" +
            "2. Else, register via the 'Become a member' menu item; or\n" +
            "3. Enable cookies for future visits");
    }
    notLoggedInItems();
}
function loggedInItems() {
    $('#lin').addClass('ui-state-disabled');
    $('#lout').removeClass('ui-state-disabled');
    //$('#pubs').removeClass('ui-state-disabled'); -- removed for now
    $('#viewEds').removeClass('ui-state-disabled');
    $('#newPg').removeClass('ui-state-disabled');
    $('#edits').removeClass('ui-state-disabled');
    $('#epubs').removeClass('ui-state-disabled');
    //$('#pubReq').removeClass('ui-state-disabled'); -- removed for now
    $('#join').addClass('ui-state-disabled');
}
function notLoggedInItems() {
    $('#lin').removeClass('ui-state-disabled');
    $('#lout').addClass('ui-state-disabled');
    //$('#pubs').addClass('ui-state-disabled'); -- removed for now
    $('#viewEds').addClass('ui-state-disabled');
    $('#newPg').addClass('ui-state-disabled');
    $('#edits').addClass('ui-state-disabled');
    $('#epubs').addClass('ui-state-disabled');
    //$('#pubReq').addClass('ui-state-disabled'); -- removed for now
    $('#join').removeClass('ui-state-disabled');
}
function adminLoggedIn() {
    $('#ifadmin').css('display', 'block');
    return;
}
// login authentication
function validateUser(usr_name, usr_pass) {
    $.ajax( {
        url: "../admin/authenticate.php",
        method: "POST",
        data: {'usr_name': usr_name, 'usr_pass': usr_pass,
            'browser_cookies': cookie_state},
        dataType: "text",
        success: function(srchResults) {
            var status = srchResults;
            if (status.indexOf('ADMIN') >= 0) {
                loggedInItems();
                adminLoggedIn();
                alert("Admin logged in");
            } else if (status.indexOf('LOCATED') >= 0) {
                loggedInItems();
                alert("You are logged in");
            } else if (status.indexOf('RENEW') >=0) {
                // in this case, the old cookie has been set pending renewal
                var renew = confirm("Your password is about to expire\n" + 
                    "Would you like to renew?");
                if (renew) {
                    renewPassword(usr_name, 'renew', 'valid');
                } else {
                    renewPassword(usr_name, 'norenew', 'valid');
                }
            } else if (status.indexOf('EXPIRED') >= 0) {
                var renew = confirm("Your password has expired\n" +
                    "Would you like to renew?");
                if (renew) {
                    renewPassword(usr_name, 'renew', 'expired');
                } else {
                    renewPassword(usr_name, 'norenew', 'expired');
                }
            } else if (status.indexOf('BADPASSWD') >= 0) {
                var msg = "The password you entered does not match " +
                    "your registered password;\nPlease try again";
                alert(msg);
                $('#upass').val('');
                textResult =  "bad_password";
            } else { // no such user in USERS table
                var msg = "Your registration info cannot be uniquely located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
                $('#usrid').val('');
                $('#upass').val('');
                textResult =  "no_user";
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error encountered in validation: " +
                textStatus + "; Error: " + errorThrown);
        }
    });
    return;
}
// for renewing password/cookie
function renewPassword(user, update, status) {
    if (update === 'renew') {
       window.open('php/renew.php?user=' + user, '_self');
    } else {
        // if still valid, refresh will display login, otherwise do nothing
        if (status === 'valid') {
            window.open('index.php', '_self');
        }
    }
    return;
}
