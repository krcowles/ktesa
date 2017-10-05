$( function() {  // wait until document is loaded...

// Globals:
var usr_type = 'unregistered';
var ajaxDone = false;

// Set up cookies:
function setCookie(ckname,ckvalue,expdays) {
    var d = new Date();
    d.setTime(d.getTime() + (expdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
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
function checkCookie() {
    var username = getCookie("nmh_id");
    if (username != "") {
        alert("Welcome back " + username);
    } else {
        not_registered();
        /*
        username = prompt("Please enter your name:", "");
        if (username != "" && username != null) {
            setCookie("nmh_id", username, 365);
        }
        */
    }
}
function not_registered() {
    var msg = "Your registration info cannot be located:\n" +
            "Please click on the 'Sign me up!' link to register";
    alert(msg);
}

$('#auxfrm').submit( function(ev) {
    ev.preventDefault();
    // check for existence of user in cookies (if enabled):
    if ( $('#upass').val() === '000ktesa9') {  // master key
        $('#regusrs').css('display','none');
        $('#masters').css('display','block');
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
        var cookieEnabled = navigator.cookieEnabled;
        // deal with not enabled:
        if (cookieEnabled) {
            if ( $('#usrid').val() == '' && $('#upass').val() !== '000ktesa9' ) {
                alert("Please enter a valid user name");
            } else if ( $('#upass').val() == '' ) {
                alert("Please enter a valid registration key");
            } else {  // something is there = check it...
                if ( $('#upass').val() == '000ktesa9' ) {
                    type = 'master';
                    alert("Site Master found");
                } else {
                    // ajax in a mysql search for this user
                    var uid = $('#usrid').val();
                    var upw = $('#upass').val();
                            $('#usrid').val() + '&pass=' + $('#upass').val();
                    $.ajax( {
                        url: "admin/authenticate.php",
                        data: {'nmhid': uid, 'nmpass': upw},
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
                                not_registered();
                            }
                            ajaxDone = true;
                        }
                    });
                }
            }
        } else {  // cookies are enabled, now check for nmh_id:
            alert("Cookies are ON");
        }
        var ajaxTimer = setInterval( function() {
            if (ajaxDone) {
                clearInterval(ajaxTimer);
                if (usr_type === 'qualified') {
                    var valid = 'Welcome back ' + $('#usrid').val() + "!";
                    $('#loggedin').append(valid);
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
            }
        }, 20);
    } // end of else not master key
});
   
    /*
    var keyval = this.regkey.value;
    if (keyval == '1948') {
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
    } else if (keyval == '000ktesa9') {
        $('#regusrs').css('display','none');
        $('#masters').css('display','block');
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
    } else {
        $("input[type='password']").val('');
        window.alert("Key not recognized");
    }
    return false;
});
// IF KEY SUCCESSFUL:
$('#turnon').on('click', function() {
	$('#more').css('display','block');
	$(this).css('display','none');
});
$('#turnoff').on('click', function() {
	$('#more').css('display','none');
	$('#turnon').css('display','block');
});
*/

}); // end of page-loading wait statement