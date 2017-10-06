$( function() {  // wait until document is loaded...

// Globals:
var usr_type = 'unregistered';
var ajaxDone = false;

function validateUser(usr_name,usr_pass,setcookie) {
    $.ajax( {
        url: "admin/authenticate.php",
        data: {'nmhid': usr_name, 'nmpass': usr_pass},
        success: function(srchResults) {
            console.log(srchResults);
            var srchStr = srchResults;
            if (srchStr.indexOf('LOCATED') >= 0) {
                usr_type = 'qualified';
            } else if (srchStr.indexOf('BADPASSWD') >= 0) {
                var msg = "The key you entered does not match " +
                    "your registered password;\nPlease try again";
                alert(msg);
                $('#upass').val('');
            } 
            else {
                var msg = "Your registration info cannot be located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
            }
            ajaxDone = true;
        }
    });
    var ajaxTimer = setInterval( function() {
        if (ajaxDone) {
            clearInterval(ajaxTimer);
            ajaxDone = false;
            if (usr_type === 'qualified') {
                var valid = 'Welcome back ' + $('#usrid').val() +
                    "; you are now logged in...";
                $('#loggedin').append(valid);
                user_opts();
                if (setcookie) {
                    setCookie('nmh_id',usr_name,365);
                }
            }
        }
    }, 100);
}
function user_opts() {
    $('#regusrs').css('display','block');
    $('#masters').css('display','none');
    $('#creator').on('click', function() {
        var createUrl = 'build/newHike.php';
        window.open(createUrl, target="_blank");
    });
    $('#editor').on('click', function() {
        var editUrl = 'build/hikeEditor.php';
        window.open(editUrl, target="_blank");
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
    if ( $('#upass').val() === '000ktesa9') {  // master key displays all
        $('#regusrs').css('display','none');
        $('#masters').css('display','block');
        $('#mstrcreate').on('click', function() {
            var createUrl = 'build/newHike.php';
            window.open(createUrl, target="_blank");
        });
        $('#mstredit').on('click', function() {
            var editUrl = 'build/hikeEditor.php';
            window.open(editUrl, target="_blank");
        });
        $('#indxpg').on('click', function() {
            var indxurl = 'build/indexEditor.php';
            window.open(indxurl, target="_blank");
        });  
        $('#admin').on('click', function() {
            var admintools = 'admin/admintools.php';
            window.open(admintools,"_blank");
        });
        $('.hide').on('click', function() {
            $("input[type='password']").val('');
            $('#masters').css('display','none');
        });
    } else {  // not master key
        var uid = $('#usrid').val();
        var upw = $('#upass').val();
        var cookieEnabled = navigator.cookieEnabled;
        // deal with not enabled:
        if (!cookieEnabled) {  // cant set 'em, so repeat as needed by user
            if (uid == '' && upw !== '000ktesa9') {
                alert("Please enter a valid user name");
            } else if (upw == '') {
                alert("Please enter a valid registration key");
            } else {  // something is there = check it...
                validateUser(uid,upw,false);
            }
        } else {  // cookies are enabled, now check for nmh_id:
            var username = getCookie("nmh_id");
            if (username === "") {  // no cookie: validation is required...
                validateUser(uid,upw,true);
                $('#upass').val('');
            } else {  // valid cookie present: proceed
                user_opts();
                // delete cookie during test phase
                // setCookie('nmh_id','',0);
            }
        }
    } // end of else not master key
});

}); // end of page-loading wait statement