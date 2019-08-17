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
        // to test: alert("You're back!");
        allcookies = decodeURIComponent(document.cookie);
        prevLgth = allcookies.length;
    }
});

$( function() {  // wait until document is loaded...

// registered user
var registered_user = $('#registered_user').text();
var createPage    = 0;
var editPage      = 1;
var publishedPage = 2;
var displayEdits  = 3;
// admin
var adminCreate   = 10;
var adminEdit     = 11;
var adminPubl     = 12;
var adminDisplay  = 13;
var admin         = 14;

// are cookies enabled on this browser?
var cookies = navigator.cookieEnabled ? true : false;
if (!cookies) {
    alert("Cookies appear to be disabled:\n" +
        "You will not be able to see the 'User Options' unless:\n" +
        "1. You log in as a registered user; or\n" +
        "2. Register via the 'Sign Me Up! link; or\n" +
        "3. Enable cookies for future visits");
}
// logout out on main page only
if ($('#logout').length) {
    $('#logout').on('click', function(evt) {
        evt.preventDefault();
        $.get('php/logout.php', function() {
            alert("You are logged out");
            window.open("index.php", "_self");
        });
    });
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
}
/**
 * User logins (no cookie present/cookies disabled;
 * 
 * After verifying that entries have been made in the 'User Name' and
 * 'User Password' input boxes, the 'validateUser()' function is called
 * to see if the entered data matches an existing user registration.
 * If the data does not match, or if the user exists but the password
 * entered does not match, a message is displayed to the user. 
 * If the user has made valid entries, the page displays a 'Welcome' 
 * message and enables the 'User Options' button.
 */
$('#users').submit( function(evt) {
    evt.preventDefault();
    // ensure all login data is present
    var uid = $('#usrid').val();
    var pwd = $('#upass').val();
    if (uid == '' && pwd == '') {
        alert("You must supply a registered registered_user and password");
        return;
    }
    if (uid == '') {
        alert("You must supply a registered registered_user");
        return;
    }
    if (pwd == '') {
        alert("You must supply a valid password");
        return;
    }
    validateUser(uid, pwd);
});
function validateUser(usr_name, usr_pass) {
    $.ajax( {
        url: "admin/authenticate.php",
        method: "POST",
        data: {'usr_name': usr_name, 'usr_pass': usr_pass},
        dataType: "text",
        success: function(srchResults) {
            var status = srchResults;
            if (status.indexOf('LOCATED') >= 0) {
                // document.cookie returning 0: abandoned cookie set approach
                if (!cookies) {
                    window.open('index.php?usr=' + usr_name, '_self');
                } else {
                    window.open('index.php', '_self');
                }
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
            } else { // no such user in USERS table
                var msg = "Your registration info cannot be uniquely located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
                $('#usrid').val('');
                $('#upass').val('');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error encountered in validation: " +
                textStatus + "; Error: " + errorThrown);
        }
    });
}
$('#opts').on('click', function() {
    if ($('#regusr').length) {
        display_usr_opts();
    } else if ($('#master').length) {
        display_mstr_opts();
    } 
});
function openPage(which, who) {
    var pg = 'php/opener.php?page=' + which + '&user=' + who;
    $.ajax(pg,
        {
            method: 'GET',
            dataType: 'html',
            success: function(results) {
                $('#addon').after(results);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert("Could not open page: " + textStatus + "; Error " + errorThrown);
            }
        }
    );
}
function display_usr_opts() { 
    $('#regusrs').css('display','block');
    // user buttons:
    $('#creator').on('click', function() {
        //openPage(createPage, registered_user);
        alert("No editing allowed at this time");
    });
    $('#active').on('click', function() {
        //openPage(editPage, registered_user);
        alert("No editing allowed at this time");
    });
    $('#usrpub').on('click', function() {
        //openPage(publishedPage, registered_user);
        alert("No editing allowed at this time");
    });
    $('#usrdisplay').on('click', function() {
        openPage(displayEdits, registered_user);
    });
    $('#hide').on('click', function() {
        $('#regusrs').css('display','none');
    });
}
function display_mstr_opts() {
    $('#masters').css('display','block');
    // admin buttons
    $('#mstrcreate').on('click', function() {
        //openPage(adminCreate, 'keyed');
        alert("No editing allowed at this time");
    });
    $('#mstrnew').on('click', function() {
        //openPage(adminEdit, 'keyed');
        alert("No editing allowed at this time");
    });
    $('#mstrold').on('click', function() {
        //openPage(adminPubl, 'keyed');
        alert("No editing allowed at this time");
    });  
    $('#mstrdisplay').on('click', function() {
        openPage(adminDisplay, 'keyed');
    })
    $('#admin').on('click', function() {
        openPage(admin, 'keyed');
    });
    $('#hide').on('click', function() {
        $('#masters').css('display','none');
    });
}

/**
 * This section manages the 'twisty' text on the bottom of the page
 */
function toggleTwisty(tid, ttxt, dashed) {
    var feature = $('#' + ttxt);
    var twisty  = $('#' + tid);
    var list = $('#' + dashed);
    if (twisty.hasClass('twisty-right')) {
        twisty.removeClass('twisty-right');
        twisty.addClass('twisty-down');
        feature.css('top', '-6px');
    } else {
        twisty.removeClass('twisty-down');
        twisty.addClass('twisty-right');
        feature.css('top', '-4px');
    }
    list.slideToggle();
}
$('#mapfeat').on('click', function() {
    toggleTwisty('m', 'mapfeat', 'mul');
});
$('#tblfeat').on('click', function() {
    toggleTwisty('t', 'tblfeat', 'tul');
});
$('#hikefeat').on('click', function() {
    toggleTwisty('h', 'hikefeat', 'hul');
});

// Go to ktesa sites:
$('#tbl').on('click', function() {
    window.open("pages/mapPg.php?tbl=T", "_self");
});
$('#tnm').on('click', function() {
    window.open("pages/mapPg.php?tbl=D", "_self");
});
$('#bigm').on('click', function() {
    window.open("pages/mapPg.php?tbl=M", "_self");
});

}); // end of page-loading wait statement
