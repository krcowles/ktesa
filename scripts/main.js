$( function() {  // wait until document is loaded...

// Globals:
var usr_type = 'unregistered';
var username;
var ajaxDone = false;
var valid1 = "Welcome back ";
var valid2 = "; you are now logged in...";
var valid;
var valstat;
var backdoor = false;

// URL targets: New Page Creation:
var createUrl = 'build/enterHike.php?hno=0&usr=';
// URL targets: Edit EHIKES items:
var editNew = 'build/hikeEditor.php?age=new&usr='; // 'new' => EHIKES
// URL targets: Edit HIKES items:
var editPub = 'build/hikeEditor.php?age=old&usr=';  // 'old' => HIKES
var mstrEdit = 'build/hikeEditor.php?age=old&usr=mstr&show=usr';
// URL for displaying hikes-in-edit:
var dispPg = 'build/editDisplay.php?usr=';
// URL target for admin tools:
var adminUrl = 'admin/admintools.php';

// For testing, un-comment as needed:
//setCookie('nmh_mstr','',0);
//setCookie('nmh_id','',0);

// on loading the page:
var mstrCookie = getCookie('nmh_mstr');
if (mstrCookie !== "") {
    usr_type = 'mstr'
    $('#logins').css('display','none');
    $('#loggedin').css('display','block');
    $('#reg').css('display','none');
    $('#mover').css('display','none');
}
var usrCookie = getCookie('nmh_id');
if (usrCookie !== '') {
    usr_type = usrCookie;
    valid = valid1 + usrCookie + valid2;
    $('#loggedin').prepend(valid);
    usr_login_display();
}
function validateUser(usr_name,usr_pass,setcookie) {
    $.ajax( {
        url: "admin/authenticate.php",
        data: {'nmhid': usr_name, 'nmpass': usr_pass},
        success: function(srchResults) {
            valstat = true;
            //console.log(srchResults);
            var srchStr = srchResults;
            if (srchStr.indexOf('LOCATED') >= 0) {
                usr_type = 'qualified';
            } else if (srchStr.indexOf('BADPASSWD') >= 0) {
                var msg = "The key you entered does not match " +
                    "your registered password;\nPlease try again";
                alert(msg);
                $('#upass').val('');
                valstat = false;
            } 
            else { // no such user in USERS table
                var msg = "Your registration info cannot be located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
                $('#usrid').val('');
                $('#upass').val('');
                valstat = false;
            }
            ajaxDone = true;
        }
    });
    var ajaxTimer = setInterval( function() {
        if (ajaxDone) {
            clearInterval(ajaxTimer);
            ajaxDone = false;
            if (usr_type === 'qualified') {
                valid = valid1 + $('#usrid').val() + valid2;
                $('#loggedin').prepend(valid);
                usr_login_display();
                if (setcookie) {
                    setCookie('nmh_id',usr_name,365);
                }
            }
        }
    }, 100);
}
function usr_login_display() {
    $('#logins').css('display','none');
    $('#reg').css('display','none');
    $('#mover').css('display','block');
    backdoor = true;
}
function display_usr_opts() { 
    $('#regusrs').css('display','block');
    $('#unpub').on('click', function() {;
        window.open(editNew + username + '&show=usr', target="_blank");
    });
    $('#pub').on('click', function() {
        window.open(editPub + username + '&show=usr', target="_blank");
    });
    $('#ude').on('click', function() {
        window.open(dispPg + username,"_blank");
    });
    $('#creator').on('click', function() {
        window.open(createUrl + username, target="_blank");
    });
    $('.hide').on('click', function() {
        $("input[type='password']").val('');
        $('#regusrs').css('display','none');
    });
}
// Set up cookies:
function setCookie(ckname,ckvalue,expdays) {
    var d = new Date();
    d.setTime(d.getTime() + (expdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = ckname + "=" + ckvalue + ";" + expires + ";path=/";
}
function getCookie(ckname) {
    var name = ckname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

$('#auxfrm').submit( function(ev) {
    ev.preventDefault();
    // master key requires no entry of user name:
    if ( ($('#upass').val() === '000ktesa9') || 
            (mstrCookie !== '') || $('#mstrpass').val() === '000ktesa9' ) {  // master key displays all
        $('#regusrs').css('display','none');
        $('#masters').css('display','block');
        $('#logins').css('display','none');
        $('#reg').css('display','none');
        $('#loggedin').css('display','none');
        $('#mover').css('display','none');
        $('#mstrnew').on('click', function() {
            window.open(editNew + 'mstr&show=usr', target="_blank");
        });
        $('#mstrold').on('click', function() {
            window.open(mstrEdit, target="_blank");
        });  
        $('#mde').on('click', function() {
            window.open(dispPg + 'mstr',"_blank");
        })
        $('#mstrcreate').on('click', function() {
            window.open(createUrl + 'mstr', target="_blank");
        });
        $('#admin').on('click', function() {
            var admintools = 'admin/admintools.php';
            window.open(admintools,"_blank");
        });
        $('.hide').on('click', function() {
            $('#loggedin').css('display','block');
            $('#reg').css('display','none');
            $('#masters').css('display','none');
            if (backdoor) {
                $('#mover').css('display','block');
            }
        });
        if (mstrCookie == '') {
            setCookie('nmh_mstr','ktesa',365);
            setCookie('nmh_id','',0); // one user at a time
        }
    } else {  // not master key
        var uid = $('#usrid').val();
        var upw = $('#upass').val();
        // uid will be converted to 'username' on successful validation
        var cookieEnabled = navigator.cookieEnabled;
        // deal with not enabled:
        if (!cookieEnabled) {  // no cookies means full validation each time
            if (uid == '' && upw !== '000ktesa9') {
                alert("Please enter a valid user name");
            } else if (upw == '') {
                alert("Please enter a valid registration key");
            } else {
                validateUser(uid,upw,false);
                if (valstat) {
                    username = uid;
                }
            }
        } else {  // cookies are enabled, now check for nmh_id:
            username = getCookie("nmh_id");
            /* NOTE: If the 'nmh_id' cookie is set, the user options
             * display regardless of the username supplied on the form,
             * and no password is required.
             */
            if (username === "") {  // no cookie: validation is required...
                validateUser(uid,upw,true);
                if (valstat) {
                    username = uid;
                    $('#upass').val('');
                    usr_login_display();
                } 
            } else {  // valid cookie present: proceed
                display_usr_opts();
            }
        }
    } // end of else not master key
});


}); // end of page-loading wait statement