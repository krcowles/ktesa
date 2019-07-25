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
        "1. You log in as a registered user;\n" +
        "2. Register via the 'Sign Me Up! link;\n" +
        "3. Enable cookies for future visits");
}
// If a user (or admin) is currently logged in, show 'Log Me Out' link
if ($('#registered_user').length || $('#master').length) {
    $('#logout').show();
} else {
    $('#logout').hide();
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
    $.when( validateUser(uid, pwd, true) ).then(function() {
        window.open('index.php', '_self');
    });
});
function validateUser(usr_name, usr_pass) {
    var deferred = new $.Deferred();
    $.ajax( {
        url: "admin/authenticate.php",
        data: {'usr_name': usr_name, 'usr_pass': usr_pass},
        success: function(srchResults) {
            var srchStr = srchResults;
            if (srchStr.indexOf('LOCATED') >= 0) {
                usr_type = 'qualified';
            } else if (srchStr.indexOf('BADPASSWD') >= 0) {
                var msg = "The password you entered does not match " +
                    "your registered password;\nPlease try again";
                alert(msg);
                $('#upass').val('');
            } 
            else { // no such user in USERS table
                var msg = "Your registration info cannot be uniquely located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
                $('#usrid').val('');
                $('#upass').val('');
                valstat = false;
            }
            deferred.resolve();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("Error encountered in validation: " +
                textStatus + "; Error: " + errorThrown);
            deferred.reject();
        }
    });
}
$('#opts').on('click', function() {
    if ($('#regusr').length) {
        display_usr_opts();
    } else if ($('#master').length) {
        display_mstr_opts();
    } else {
        alert("You must be logged in to view User Options");
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
        openPage(createPage, registered_user);
    });
    $('#active').on('click', function() {
        openPage(editPage, registered_user);
    });
    $('#usrpub').on('click', function() {
        openPage(publishedPage, registered_user);
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
        openPage(adminCreate, 'keyed');
    });
    $('#mstrnew').on('click', function() {
        openPage(adminEdit, 'keyed');
    });
    $('#mstrold').on('click', function() {
        openPage(adminPubl, 'keyed');
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
