declare function validateUser(user: string, pass: string): void;
declare const countAns: () => boolean;
declare var appMode: string;
declare var mobile: boolean;
declare var updates: bootstrap.Modal;
declare var tbl_indx: string;
declare var lockout: bootstrap.Modal
interface Lockouts {
    result: string;
    minutes: number;
}
/**
 * @fileoverview Adjust page according to form type
 * 
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned for responsiveness
 * @version 5.0 Upgraded security with encryption and 2FA
 */

$(function() {

var reg = mobile ? {top: 20, height: 510} : {top: 48, height: 540};
var log = mobile ? {top: 48, height: 340} : {top: 80, height: 380};
var ren = mobile ? {top: 20, height: 480} : {top: 80, height: 480};
var accept_btn = mobile ? '#maccept' : '#accept';
var reject_btn = mobile ? '#mreject' : '#reject';
if (mobile) {
    $('.mobinp').css({
        position: 'relative',
        top: '-16px'
    });
    $('.mobtxt').css({
        position: 'relative',
        top: '-12px'
    });
    $('#cookie_banner').hide();
    $('.form-check-label').css('padding-left', '1em');
}
var formtype = <string>$('#formtype').text();
var $container = $('#container');
// declared cookie choice:
$(accept_btn).on('click', function() {
    $('#cookie_banner').hide(); 
    $('#usrchoice').val("accept");
});
$(reject_btn).on('click', function() {
    $('#cookie_banner').hide();
    $('#usrchoice').val("reject");
});
/**
 * The code executed depends on which formtype is in play
 */
switch (formtype) {
    case 'join':
        // clear inputs on page load/reload 
        $('#fname').val("");
        $('#lname').val("");
        $('#uname').val("");
        $('#email').val("");
        $('#cookie_banner').hide();
        $container.css({
            top: reg.top,
            height: reg.height
        });
        $('#policylnk').on('click', function() {
            let plnk = '../php/postPDF.php?doc=../accounts/PrivacyPolicy.pdf';
            window.open(plnk, '_blank');
        });
        /**
         * For username problems, or duplicate email, notify user immediately
         *  NOTE: email validation is performed by HTML5, and again by server
         */
        var dup_email     = false;
        var space_in_name = false;
        var dup_name      = false;
        var min_length    = true;
        /**
         * Check for duplicate email
         */
        const duplicateEmail = () => {
            var umail = <string>$('#email').val();
            var ajaxdata = { email: umail };
            var dupcheck = '../accounts/dupCheck.php';
            $.ajax({
                url: dupcheck,
                data: ajaxdata,
                method: "post",
                dataType: "text",
                success: function(match) {
                    if (match === "NO") {
                        dup_email = false;
                        $('#email').css('color', 'black');
                    } else {
                        dup_email = true;
                        alert("This email is already in use");
                        $('#email').css('color', 'red');
                    }
                },
                error: function(_jqXHR, _textStatus, _errorThrown) {
                    dup_email = true;
                    $('#email').css('color', 'red');
                    if (appMode === 'development') {
                        let newDoc = document.open();
                        newDoc.write(_jqXHR.responseText);
                        newDoc.close();
                    } else { // production
                        let msg = "An error has occurred: " +
                            "We apologize for any inconvenience\n" +
                            "The webmaster has been notified; please try again later";
                        alert(msg);
                        let ajaxerr = "Trying to access dupCheck via duplicate email\n" +
                            "Error text: " + _textStatus + "; Error: " +
                            _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                        let errobj = {err: ajaxerr};
                        $.post('../php/ajaxError.php', errobj);
                    }
                }
            });
            return;
        }
        $('#email').on('blur', function() {
            duplicateEmail();
        });
        /**
         * Ensure the user name has no embedded spaces
         */
        const spacesInName =  () => {
            var uname = <string>$('#uname').val();
            if (uname.indexOf(' ') !== -1) {
                alert("No spaces in user name please");
                $('#uname').css('color', 'red');
                space_in_name = true;
            } else {
                space_in_name = false;
                $('#uname').css('color', 'black');
            }
            return;
        };
        /**
         * Make sure user name is unique;
         * NOTE: TypeScript won't allow a function's return value to be boolean!
         * This function will set a global, goodname, instead.
         */
        const uniqueuser =  () => {
            let data = $('#uname').val();
            let ajaxdata = { username: data };
            let dupCheck = '../accounts/dupCheck.php';
            $.ajax({
                url: dupCheck,
                data: ajaxdata,
                method: 'post',
                success: function (match) {
                    if (match === "NO") {
                        dup_name = false;
                        $('#uname').css('color', 'black');
                    }
                    else {
                        dup_name = true;
                        $('#uname').css('color', 'red');
                        alert("Please select another user name");
                    }
                },
                error: function (_jqXHR, _textStatus, _errorThrown) {
                    if (appMode === 'development') {
                        let newDoc = document.open();
                        newDoc.write(_jqXHR.responseText);
                        newDoc.close();
                    } else { // production
                        dup_name = true;
                        $('#uname').css('color', 'red');
                        let msg = "An error has occurred:  " +
                            "We apologize for any inconvenience\n" +
                            "The webmaster has been notified; please try again later";
                        alert(msg);
                        let ajaxerr = "Trying to get Users list from dupCheck.php (uniqueuser);\n" +
                            "Error text: " +_textStatus + "; Error: " + _errorThrown + 
                            ";\njqXHR: " + _jqXHR.responseText;
                        let errobj = {err: ajaxerr};
                        $.post('../php/ajaxError.php', errobj);
                    }
                }
            });
            return;
        };
        $('#uname').on('blur', function () {
            spacesInName();
            if (!space_in_name) {
                uniqueuser();
                let name = <string>$('#uname').val();
                if (name.length  > 0 && name.length < 6) {
                    min_length = false;
                    alert("You must choose a username with at least 6 characters");
                } else {
                    min_length = true;
                }
            }
        });
        // input fields: no blanks; no username spaces; valid email address;
        // no other faults
        $("#form").on('submit', function (ev) {
            ev.preventDefault();
            if (dup_name || space_in_name || dup_email || !min_length) {
                alert("Cannot proceed until all entries are corrected");
                return false;
            }
            let formdata = $('#form').serializeArray();
            let proposed_name = formdata[3]['value'];
            let proposed_email = formdata[4]['value'];
            $.ajax({
                url: 'create_user.php',
                data: formdata,
                dataType: 'text',
                method: 'post',
                success: function(result) {
                    if (result !== 'OK') {
                        let err = "Your registration could not be completed\n" +
                            "due to an error; The admin has been notified.";
                        alert(err)
                        let ajaxerr = {err: result};
                        $.post('../php/ajaxError.php', ajaxerr);
                    } else {
                        let email = $('#email').val();
                        let mail_data = {form: 'join', join: 'y', email: email};
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
                                    alert("There was a problem with the email you supplied\n" +
                                        "The admin has been notified");
                                    $('#email').css('color', 'red');
                                    let ajaxerr = "resetMail.php 'success' w/bad 'result'\n" +
                                        "User email not sent, but entry has " + 
                                        "been created in USERS: " + result;
                                    ajaxerr += "\nuser: " + proposed_name + " email: " +
                                        proposed_email;
                                    let errobj = {err: ajaxerr};
                                    $.post('../php/ajaxError.php', errobj);
                                }
                            },
                            error: function(_jqXHR, _textStatus, _errorThrown) {
                                if (appMode === 'development') {
                                    let newDoc = document.open();
                                    newDoc.write(_jqXHR.responseText);
                                    newDoc.close();
                                } else {
                                    let err = "An error was encountered while " +
                                        "attempting to send your email.\nYour " +
                                        "registration cannot be completed at this " + 
                                        "time.\nAn email has been sent to the admin" +
                                        " to correct the situation.";
                                    alert(err);
                                    let ajaxerr = "Server error: cleanup USERS\n" +
                                        "registrant; resetMail.php access failed:\n" +
                                        "Error text: " + _textStatus + "; Error: " +
                                        _errorThrown + "\njqXHR: " + _jqXHR.responseText +
                                        "\nName: " + proposed_name + "; email " + proposed_email;
                                    let errobj = {err: ajaxerr};
                                    $.post('../php/ajaxError.php', errobj);
                                }
                            }   
                        });
                    }
                },
                error: function(_jqXHR, _textStatus, _errorThrown) {
                    if (appMode === 'development') {
                        var newDoc = document.open();
                        newDoc.write(_jqXHR.responseText);
                        newDoc.close();
                    }
                    else { // production
                        var msg = "An error has occurred: " +
                            "We apologize for any inconvenience\n" +
                            "The webmaster has been notified; please try again later";
                        alert(msg);
                        var ajaxerr = "Trying to access create_user.php/registration;\n" +
                            "Error text: " + _textStatus + "; Error: " + 
                            _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                        var errobj = { err: ajaxerr };
                        $.post('../php/ajaxError.php', errobj);
                    }
                }
            });
            return true;
        });
        break;
    case 'renew':
        // clear inputs on page load/reload
        $('#password').val("");
        $('#confirm').val("");
        $('#ckbox').prop('checked', false);
        var ix = <string>$('#ix').text();
        tbl_indx = ix; // required in validateUser's #closesec function
        var pdet = new bootstrap.Modal(<HTMLElement>document.getElementById('show_pword_details'));
        var cban = new bootstrap.Modal(<HTMLElement>document.getElementById('cooky'));
        if (mobile) {
            cban.show();
        } else {
            $('#cookie_banner').show();
        }
        var login_renew = <string>$('#one-time').val();
        $container.css({
            top: ren.top,
            height: ren.height
        });
        /**
         * Populate the security questions with the user's answers, as
         * he/she may not review them prior to submitting, and they need
         * to be present for the answer check in 'formsubmit'.
         */
        $.post('usersQandA.php', {ix: ix}, function(contents) {
            $('#uques').empty();
            $('#uques').append(contents);
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
        // show details of password when 'weak'
        $('#showdet').on('click', function(ev) {
            ev.preventDefault();
            pdet.show();
        });
        // security modal buttons operation spec'd in validateUser.js
        $('#rvw').on('click', function(ev) {
            ev.preventDefault();
            updates.show();
        });
        // SUBMIT FORM
        $('#formsubmit').on('click', function(ev) {
            ev.preventDefault();
            if ($('#st').css('display') === 'none') {
                alert("You must use a strong password");
                return false;
            }
            let password = $('input[name=password]').val();
            if (password === '') {
                alert("You have not entered a passwsord");
                return false;
            }
            let cookies = $('#usrchoice').val();
            if (cookies === 'nochoice') {
                alert("Please accept or reject cookies");
                return false;
            }
            let confirm = $('#confirm').val();
            if (confirm === '') {
                alert("You must confirm your password");
                return false;
            } else if (confirm !== password) {
                alert("Your passwords do not match");
                return false;
            }  
            var acnt = 0;
            $('input[id^=q]').each(function() {  
                if ($(this).val() !== '') {
                    acnt++
                }
            });
            if (acnt !== 3) {
                alert("You must supply exactly 3 answers to security questions");
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
                        window.open('../index.html', '_self');
                    } else {
                        alert("Your one-time code was not located\n" +
                        "Please try again by entering the code in your email\n" +
                        "into the 'One-time code' box");
                        return;
                    }
                },
                error: function(_jqXHR, _textStatus, _errorThrown) {
                    if (appMode === 'development') {
                        var newDoc = document.open();
                        newDoc.write(_jqXHR.responseText);
                        newDoc.close();
                    }
                    else { // production
                        var msg = "An error has occurred: " +
                            "We apologize for any inconvenience\n" +
                            "The webmaster has been notified; please try again later";
                        alert(msg);
                        var ajaxerr = "Attempt to renew via create_user.php/renew\n" +
                            _textStatus + "; Error: " + _errorThrown + ";\njqXHR: " +
                            _jqXHR.responseText;
                        var errobj = { err: ajaxerr };
                        $.post('../php/ajaxError.php', errobj);
                    }
                }
            });
            return true;
        });
        break;
    case 'log':
        /** 
         * This form should show only when no lockouts exist for the user.
         * However, if locked out during attempted login, and user does a
         * page refresh, this code will continue to show the locked out state:
         * the 'lockout' var in local storage will have been set by original
         * page's validate.ts/js. If the original locked out login page is NOT
         * closed, 'lockout' will be cleared when lockout time has expired
         * (1 hour) -  validate.ts/js will clear it. If otherwise this page is
         * closed or the user attempts to login via another tab, the login code
         * in the new tab's menu (panelMenu.ts/js or navMenu.ts/js [mobile])
         * will prevent login until the lockout time has expired, at which point
         * the menu code will clear 'lockout'.
         */
        $container.css({
            top: log.top,
            height: log.height
        });
        var lostate = localStorage.getItem('lockout');
        $('#cookie_banner').hide();
        if (typeof lostate !== 'undefined' && lostate === 'yes') {
            $('#username').val("");
            $('#username').css('background-color', 'lightgray');
            $('#username').prop('disabled', true);
            $('#password').val("");
            $('#password').css('background-color', 'lightgray');
            $('#password').prop('disabled', true);
            $('#formsubmit').prop('disabled', 'disabled');
            $('.lomin').text("60");
            $('#lotime').css('display', 'inline');
            $('#logger').text("Reset Password");
            // auto reset fields if page still loaded
            var lotimeout = setInterval(function() {
                $.get("../accounts/lockStatus.php",function(result) {
                    if (result.status === 'ok') {
                        $('#username').css('background-color', 'transparent');
                        $('#username').prop('disabled', false);
                        $('#password').css('background-color', 'transparent');
                        $('#password').prop('disabled', false);
                        $('#formsubmit').prop('disabled', false);
                        lockout.hide(); // if still showing
                        localStorage.removeItem('lockout');
                        $('#lotime').css('display', 'none');
                        clearInterval(lotimeout);
                        alert("You may now login");
                        location.replace(location.href); 
                    } else {
                        $('#lomin').text(result.minutes);
                    }
                }, "json"); 
            },100000);
        } else {
            $('#form').on('submit', function(ev) {
                ev.preventDefault();
                let user = <string>$('#username').val();
                let pass = <string>$('#password').val();
                if (user === '' || pass === '') {
                    alert("Both username and password must be specified");
                    return false;
                }
                validateUser(user, pass);
                var nothing = <HTMLElement>document.getElementById("password");
                nothing.focus();
                return;
            });
        }
        break;
}
 
});