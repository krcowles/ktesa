declare function validateUser(user: string, pass: string): void;
declare var appMode: string;
/**
 * @fileoverview Adjust page according to form type
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned for responsiveness
 */
$(function() {

// declared cookie choice:
$('#accept').on('click', function() {
    $('#cookie_banner').hide(); 
    $('#usrchoice').val("accept");
});
$('#reject').on('click', function() {
    $('#cookie_banner').hide();
    $('#usrchoice').val("reject");
});

var reg = {top: 48, height: 570};
var log = {top: 80, height: 380};
var ren = {top: 80, height: 420};

var formtype = $('#formtype').text();
var $container = $('#container');

/**
 * The code executed depends on which formtype is in play
 */
switch (formtype) {
    case 'reg':
        $container.css({
            top: reg.top,
            height: reg.height
        });
        $('#policylnk').on('click', function() {
            let plnk = '../php/postPDF.php?doc=../accounts/PrivacyPolicy.pdf';
            window.open(plnk, '_blank');
        });
        // NOTE: email validation is performed by HTML5, and again by server
        /**
         * For username problems, notify user immediately
         */
        var namespace = false;
        var goodname = true;
        var php_bademail = false; // also other errors preventing submission
        var uniqueness = $.Deferred();
        /**
         * Ensure the user name has no embedded spaces
         * 
         * @return {null}
         */
        const spacesInName =  () => {
            var uname = <string>$('#uname').val();
            if (uname.indexOf(' ') !== -1) {
                alert("No spaces in user name please");
                $('#uname').css('color', 'red');
                namespace = true;
            } else {
                namespace = false;
                $('#uname').css('color', 'black');
            }
            return;
        };
        /**
         * Make sure user name is unique;
         * NOTE: TypeScript won't allow a function's return value to be boolean!
         * This function will set a global, goodname, instead.
         */
        var uniqueuser = function () {
            let data = $('#uname').val();
            let ajaxdata = { username: data };
            let current_users = 'getUsers.php';
            $.ajax(current_users, {
                data: ajaxdata,
                method: 'post',
                success: function (match) {
                    if (match === "NO") {
                        goodname = true;
                    }
                    else {
                        goodname = false;
                        $('#uname').css('color', 'red');
                    }
                    uniqueness.resolve();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (appMode === 'development') {
                        uniqueness.reject();
                        let newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    } else { // production
                        goodname = false;
                        $('#uname').css('color', 'red');
                        uniqueness.reject();
                        let msg = "The current list of usernames could not " +
                            "be retrieved\nto check for duplicates. " +
                            "We apologize for any inconvenience\n" +
                            "The webmaster has been notified; please try again later";
                        alert(msg);
                        let ajaxerr = "Trying to get Users list; Error text: " +
                            textStatus + "; Error: " + errorThrown;
                        let errobj = {err: ajaxerr};
                        $.post('../php/ajaxError.php', errobj);
                    }
                }
            });
        };
        $('#uname').on('blur', function () {
            spacesInName();
            if (!namespace) {
                uniqueuser();
                $.when(uniqueness).then(function () {
                    if (!goodname) {
                        alert("This user name is already taken");
                        $('#uname').css('color', 'red');
                    } else {
                        $('#uname').css('color', 'black');
                    }
                    uniqueness = $.Deferred(); // re-establish for next event
                });
            } 
        });
        $('#uname').on('focus', function () {
            $(this).css('color', 'black');
        });
        $('#email').on('focus', function() {
            $(this).css('color', 'black');
        });
        // input fields: no blanks; no username spaces; valid email address;
        // no other faults
        $("#form").on('submit', function (ev) {
            ev.preventDefault();
            if (!goodname || namespace || php_bademail) {
                alert("Cannot proceed until all entries are corrected");
                return false;
            }
            if ($('#cookie_banner').css('display') !== 'none') {
                alert("Please accept or reject cookis");
                return false;
            }
            let formdata = $('#form').serializeArray();
            let proposed_name = formdata[4]['value'];
            let proposed_email = formdata[5]['value'];
            $.ajax({
                url: 'create_user.php',
                data: formdata,
                dataType: 'text',
                method: 'post',
                success: function(result) {
                    if (result !== 'OK') {
                        if (result.indexOf("Email") !== -1) {
                            alert(result);
                            $('#email').css('color', 'red');
                        } else {
                            let err = "Your registration could not be completed\n" +
                                "due to an apparent database error\nThe admin " +
                                "has been notified.";
                            alert(err)
                            let ajaxerr = {err: result};
                            $.post('../php/ajaxError.php', ajaxerr);
                            php_bademail = true;
                        }
                    } else {
                        let email = $('#email').val();
                        let mail_data = {form: 'reg', email: email};
                        // admin action to cleanup database if errors here
                        $.ajax({
                            url: 'resetMail.php',
                            method: 'post',
                            data: mail_data,
                            success: function(result) {
                                if (result === 'OK') {
                                    alert("An email has been sent - it may take awhile\n" +
                                        "You can continue as a guest for now");
                                    window.open('../index.html', '_self');
                                } else {
                                    let mailmsg: string;
                                    if (result.indexOf('valid') !== -1) {
                                        mailmsg = result + ";\nYou will not be able to " +
                                            "complete your registration at this time;\nAn " +
                                            "email has been sent to the admin to correct " + 
                                            "the situation.";
                                        alert(mailmsg);
                                    } else if(result.indexOf('located') !== -1) {
                                        mailmsg = "Your email did not record properly:\n" +
                                        "You will not be able to complete your registtation " +
                                        "at this time.\nAn email has been sent to the admin" +
                                        " to correct the situation.";
                                        alert(mailmsg);
                                    }
                                    $('#email').css('color', 'red');
                                    php_bademail = true;
                                    // notify admin for db clean-up
                                    let ajaxerr = "User email not sent, but entry has " + 
                                        "been created in USERS: " + result;
                                    ajaxerr += "\nuser: " + proposed_name + " email: " +
                                        proposed_email;
                                    let errobj = {err: ajaxerr};
                                    $.post('../php/ajaxError.php', errobj);
                                }
                            },
                            error: function(jqXHR, _textStatus, _errorThrown) {
                                if (appMode === 'development') {
                                    let newDoc = document.open();
                                    newDoc.write(jqXHR.responseText);
                                    newDoc.close();
                                } else {
                                    let err = "An error was encountered while " +
                                        "attempting to send your email.\nYour " +
                                        "registration cannot be completed at this " + 
                                        "time.\nAn email has been sent to the admin" +
                                        " to correct the situation.";
                                    php_bademail = true;
                                    alert(err);
                                    let ajaxerr = "Server error: cleanup USERS";
                                    let errobj = {err: ajaxerr};
                                    $.post('../php/ajaxError.php', errobj);
                                    // handlers will generate error log email.
                                }
                            }   
                        });
                    }
                },
                error: function(jqXHR) {
                    let newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
            return true;
        });
        break;
    case 'renew':
        // no one-time code when logged in user renews
        var login_renew = $('input[name=code]').val();
        ren.height = login_renew == '' ? 380 : ren.height
        $container.css({
            top: ren.top,
            height: ren.height
        });
        // toggle visibility of password:
        var cbox = document.getElementsByName('password');
        $('#ckbox').on('click', function() {
            if ($(this).is(':checked')) {
                cbox[0].setAttribute("type", "text");
            } else {
                cbox[0].setAttribute("type", "password");
            }
        });
        $('#form').on('submit', function(ev) {
            ev.preventDefault();
            let password = $('input[name=password]').val();
            let cookies = $('#usrchoice').val();
            if (cookies === 'nochoice') {
                alert("Please accept or reject cookies");
                return false;
            }
            let confirm = $('#confirm').val();
            if (confirm !== password) {
                alert("Your passwords do not match");
                return false;
            }
            let formdata = {
                submitter: 'change',
                code: login_renew,
                password: password,
                cookies: cookies
            };
            $.ajax({
                url: 'create_user.php',
                method: 'post',
                data: formdata,
                dataType: 'text',
                success: function(result) {
                    if (result === 'OK') {
                        alert("Your password has been updated\nAnd you are logged in");
                        window.open('../index.html');
                        // NOTE: current window cannot be closed because it was
                        // not opened by javascript
                    } else {
                        alert("Your one-time code was not located\n" +
                        "Please try again by entering the code in your email\n" +
                        "into the 'One-time code' box");
                        return;
                    }
                },
                error: function(jqXHR) {
                    let newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
            return true;
        });
        break;
    case 'log':
        // NOTE: expired or renew password scenario handled in menus.js
        $container.css({
            top: log.top,
            height: log.height
        });
        $('#cookie_banner').hide();
        $('#form').on('submit', function(ev) {
            ev.preventDefault();
            let user = <string>$('#username').val();
            let pass = <string>$('#password').val();
            validateUser(user, pass);
        });
        $('#send').on('click', function(ev) {
            ev.preventDefault();
            let email = $('#forgot').val(); 
            if (email == '') {
                alert("You must enter a valid email address");
                return false;
            }
            let data = {form: 'req', email: email};
            $.ajax({
                url: '../accounts/resetMail.php',
                data: data,
                dataType: 'text',
                method: 'post',
                success: function(result) {
                    if (result === 'OK') {
                        alert("An email has been sent: these sometimes " +
                            "take awhile\nYou are logged out and can log in" +
                            " again\nwhen your email is received");
                        $.get({
                            url: '../accounts/logout.php',
                            success: function() {
                                    window.open('../index.html', '_self');
                            }
                        });
                    } else {
                       alert(result);
                    }
                },
                error: function(jqXHR) {
                    var newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
            return true;
        });
        break;
}
 
});