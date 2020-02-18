// are cookies enabled on this browser?
var cookies = navigator.cookieEnabled ? true : false;
var login_name = document.getElementById('login_result').textContent;
// if a login_name appears, cookies are already enabled:
var user_cookie_state = document.getElementById('cookieStatus').textContent;
// defaults to 'OK' unless a registered user is logged in
if (cookies) {
    if (user_cookie_state === 'NONE') {
        alert("No user registration has been located for " + login_name);
        login_name = 'none';
    } else if (user_cookie_state === 'EXPIRED') {
        var ans = confirm("Your password has expired\n" + 
            "Would you like to renew?");
        if (ans) {
            renewPassword(login_name, 'renew', 'expired');
        } else {
            renewPassword(login_name, 'norenew', 'expired');
            login_name = 'none';
        }
    } else if (user_cookie_state === 'RENEW') {
        var ans = confirm("Your password is about to expire\n" + 
            "Would you like to renew?");
        if (ans) {
            renewPassword(login_name, 'renew', 'valid');
        } else {
            renewPassword(login_name, 'norenew', 'valid');
        }
    } else if (user_cookie_state === 'MULTIPLE') {
        alert("There are multiple accounts associated with " + login_name +
            "\nPlease contact the site master");
        login_name = 'none';
    } else if (user_cookie_state === 'OK') {
        if (login_name === 'none') {
            notLoggedInItems();
            $('#ifadmin').css('display', 'none');
        } else {
            loggedInItems();
            if (login_name === 'mstr') {
                adminLoggedIn();
            }
        }
    }
} else {  // cookies disabled
    alert("Cookies are disabled on this browser:\n" +
        "You will not be able login, register, or edit/create hikes.\n" +
        "Please enable cookies to overcome this limitation");
    notLoggedInItems();
    $('#lin').addClass('ui-state-disabled');
    $('#join').addClass('ui-state-disabled');
    $('#ifadmin').css('display', 'none');
}
function loggedInItems() {
    $('#lin').addClass('ui-state-disabled');
    $('#lout').removeClass('ui-state-disabled');
    //$('#pubs').removeClass('ui-state-disabled'); -- removed for now
    $('#yours').removeClass('ui-state-disabled');
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
    $('#yours').addClass('ui-state-disabled');
    $('#viewEds').addClass('ui-state-disabled');
    $('#newPg').addClass('ui-state-disabled');
    $('#edits').addClass('ui-state-disabled');
    $('#epubs').addClass('ui-state-disabled');
    //$('#pubReq').addClass('ui-state-disabled'); -- removed for now
    $('#join').removeClass('ui-state-disabled');
}
function adminLoggedIn() {
    $('#ifadmin').css('display', 'block');
}
// login authentication
function validateUser(usr_name, usr_pass) {
    $.ajax( {
        url: "../accounts/authenticate.php",
        method: "POST",
        data: {'usr_name': usr_name, 'usr_pass': usr_pass},
        dataType: "text",
        success: function(srchResults) {
            var status = srchResults;
            if (status.indexOf('ADMIN') >= 0) {
                loggedInItems();
                adminLoggedIn();
                alert("Admin logged in");
                window.location.reload(true);
            } else if (status.indexOf('LOCATED') >= 0) {
                loggedInItems();
                alert("You are logged in");
                window.location.reload(true);
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
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();         
        }
    });
}
// for renewing password/cookie
function renewPassword(user, update, status) {
    if (update === 'renew') {
       window.open('../accounts/renew.php?user=' + user, '_self');
    } else {
        // if still valid, refresh will display login, otherwise do nothing
        if (status === 'valid') {
            window.open('../index.html', '_self');
        }
    }
}
