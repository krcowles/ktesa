"use strict";
/**
 * @fileoverview Adjust page according to form type
 *
 * @author Ken Cowles
 *
 * @version 2.0 Redesigned for responsiveness
 * @version 5.0 Upgraded security with encryption and 2FA
 * @version 6.0 Eliminating user choice of cookies: all members allow
 */
$(function () {
    /**
     * Check for user activity: all user pages (except for login/registration) use this
     * panelMenu.js script, hence it was deemed appropriate for inclusion here instead of
     * adding it as a separate module
     */
    var activity_timeout = 25 * 60 * 1000; // 25 minutes of inactivity
    var activity = setTimeout(function () {
        $.get('../accounts/logout.php');
        window.open('../accounts/session_expired.php', '_self');
    }, activity_timeout);
    $('body').on('mousemove', function () {
        clearTimeout(activity);
        activity = setTimeout(function () {
            $.get('../accounts/logout.php');
            window.open('../accounts/session_expired.php', '_self');
        }, activity_timeout);
    });
    $('body').on('keydown', function () {
        clearTimeout(activity);
        activity = setTimeout(function () {
            $.get('../accounts/logout.php');
            window.open('../accounts/session_expired.php', '_self');
        }, activity_timeout);
    });
    var reg = mobile ? { top: 12, height: 510 } : { top: 48, height: 540 };
    var log = mobile ? { top: 32, height: 380 } : { top: 80, height: 380 };
    var ren = mobile ? { top: 8, height: 480 } : { top: 80, height: 480 };
    if (mobile) {
        $('#cookie_banner').hide();
        $('.mobinp').css({
            position: 'relative',
            top: '-24px',
            height: '32px',
            marginBottom: '18px'
        });
        $('.mobtxt').css({
            position: 'relative',
            top: '-12px'
        });
        $('#logger').css('height', '60px');
    }
    var formtype = $('#formtype').text();
    var $container = $('#container');
    // Cookie banner actions:
    $('#policy').on('click', function () {
        var plnk = '../php/postPDF.php?doc=../accounts/PrivacyPolicy.pdf';
        window.open(plnk, '_blank');
    });
    $('#close_banner').on('click', function (ev) {
        ev.preventDefault();
        $('#cookie_banner').hide();
    });
    /**
     * The code executed depends on which formtype is in play
     */
    switch (formtype) {
        case 'join':
            $('#cookie_banner').hide();
            $('body').on('click', '#plink', function () {
                var plink = '../php/postPDF.php?doc=../accounts/PrivacyPolicy.pdf';
                window.open(plink, '_blank');
            });
            // clear inputs on page load/reload 
            $('#fname').val("");
            $('#lname').val("");
            $('#uname').val("");
            $('#email').val("");
            $container.css({
                top: reg.top,
                height: reg.height
            });
            if (mobile) {
                $('#center').text("Registration");
                $('#name_req').text("Username: 6 Characters");
                $('#name_req').css({
                    top: '-14px',
                    marginTop: '0px'
                });
                var link = "<a id='plink' href='#'>Privacy Policy</a><br /><br />";
                $('#sub').replaceWith(link);
                $('#club_member').css('top', '-10px');
            }
            /**
             * For username problems, or duplicate email, notify user immediately
             *  NOTE: email validation is performed by HTML5, and again by server
             */
            var dup_email = false;
            var space_in_name = false;
            var dup_name = false;
            var min_length = true;
            /**
             * Check for duplicate email
             */
            var duplicateEmail_1 = function () {
                var umail = $('#email').val();
                var ajaxdata = { email: umail };
                var dupcheck = '../accounts/dupCheck.php';
                $.ajax({
                    url: dupcheck,
                    data: ajaxdata,
                    method: "post",
                    dataType: "text",
                    success: function (match) {
                        if (match === "NO") {
                            dup_email = false;
                            $('#email').css('color', 'black');
                        }
                        else {
                            dup_email = true;
                            alert("This email is already in use");
                            $('#email').css('color', 'red');
                        }
                    },
                    error: function (_jqXHR, _textStatus, _errorThrown) {
                        dup_email = true;
                        $('#email').css('color', 'red');
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
                            var ajaxerr = "unifiedLogin.js: Trying to access dupCheck.php " +
                                "from duplicateEmail()\n" +
                                "Error text: " + _textStatus + "; Error: " +
                                _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                            var errobj = { err: ajaxerr };
                            $.post('../php/ajaxError.php', errobj);
                        }
                    }
                });
                return;
            };
            $('#email').on('blur', function () {
                duplicateEmail_1();
            });
            /**
             * Ensure the user name has no embedded spaces
             */
            var spacesInName_1 = function () {
                var uname = $('#uname').val();
                if (uname.indexOf(' ') !== -1) {
                    alert("No spaces in user name please");
                    $('#uname').css('color', 'red');
                    space_in_name = true;
                }
                else {
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
            var uniqueuser_1 = function () {
                var data = $('#uname').val();
                var ajaxdata = { username: data };
                var dupCheck = '../accounts/dupCheck.php';
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
                            var newDoc = document.open();
                            newDoc.write(_jqXHR.responseText);
                            newDoc.close();
                        }
                        else { // production
                            dup_name = true;
                            $('#uname').css('color', 'red');
                            var msg = "An error has occurred:  " +
                                "We apologize for any inconvenience\n" +
                                "The webmaster has been notified; please try again later";
                            alert(msg);
                            var ajaxerr = "unifiedLogin.js: Trying to get Users list from " +
                                "dupCheck.php in uniqueuser();\n" +
                                "Error text: " + _textStatus + "; Error: " + _errorThrown +
                                ";\njqXHR: " + _jqXHR.responseText;
                            var errobj = { err: ajaxerr };
                            $.post('../php/ajaxError.php', errobj);
                        }
                    }
                });
                return;
            };
            $('#uname').on('blur', function () {
                spacesInName_1();
                if (!space_in_name) {
                    uniqueuser_1();
                    var name_1 = $('#uname').val();
                    if (name_1.length > 0 && name_1.length < 6) {
                        min_length = false;
                        alert("You must choose a username with at least 6 characters");
                    }
                    else {
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
                //let clubber = $('#in_club').is(":checked") ? 'Y': 'N';
                var formdata = $('#form').serializeArray();
                var proposed_name = formdata[3]['value'];
                var proposed_email = formdata[4]['value'];
                $.ajax({
                    url: 'create_user.php',
                    data: formdata,
                    dataType: 'text',
                    method: 'post',
                    success: function (result) {
                        if (result !== 'OK') {
                            var err = "Your registration could not be completed\n" +
                                "due to an error; The admin has been notified.";
                            alert(err);
                            var ajaxerr = { err: result };
                            // there is no error callback in $.post
                            $.post('../php/ajaxError.php', ajaxerr);
                        }
                        else {
                            var email = $('#email').val();
                            var mail_data = { form: 'join', join: 'y', email: email };
                            // admin action to cleanup database if errors here
                            $.ajax({
                                url: 'resetMail.php',
                                method: 'post',
                                data: mail_data,
                                success: function (result) {
                                    if (result === 'OK') {
                                        var msg = 'Thank you for pre-registering. An email has been ' +
                                            'sent to your account - please check your Spam folder. Your ' +
                                            'registration is NOT complete until you click on the link in ' +
                                            'the email. You will then be able to enter your password and ' +
                                            'select security questions. You cannot pre-register again. ' +
                                            'Thank you for joining the nmhikes community!';
                                        alert(msg);
                                        window.open('../index.html', '_self');
                                    }
                                    else {
                                        alert("There was a problem with the email you supplied\n" +
                                            "The admin has been notified");
                                        $('#email').css('color', 'red');
                                        var ajaxerr = "unifiedLogin.js: attempting to access resetMail.php" +
                                            "[successfully] " + " but with bad 'result'\n" +
                                            "User email not sent, but entry has been created in USERS; result: " +
                                            result;
                                        ajaxerr += "\nuser: " + proposed_name + " email: " +
                                            proposed_email;
                                        var errobj = { err: ajaxerr };
                                        // there is no error callback in $.post
                                        $.post('../php/ajaxError.php', errobj);
                                    }
                                },
                                error: function (_jqXHR, _textStatus, _errorThrown) {
                                    if (appMode === 'development') {
                                        var newDoc = document.open();
                                        newDoc.write(_jqXHR.responseText);
                                        newDoc.close();
                                    }
                                    else {
                                        var err = "An error was encountered while " +
                                            "attempting to send your email.\nYour " +
                                            "registration cannot be completed at this " +
                                            "time.\nAn email has been sent to the admin" +
                                            " to correct the situation.";
                                        alert(err);
                                        var ajaxerr = "unifiedLogin.js: Server error: cleanup USERS\n" +
                                            "registrant; resetMail.php access failed:\n" +
                                            "Error text: " + _textStatus + "; Error: " +
                                            _errorThrown + "\njqXHR: " + _jqXHR.responseText +
                                            "\nName: " + proposed_name + "; email " + proposed_email;
                                        var errobj = { err: ajaxerr };
                                        $.post('../php/ajaxError.php', errobj);
                                    }
                                }
                            });
                        }
                    },
                    error: function (_jqXHR, _textStatus, _errorThrown) {
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
                            var ajaxerr = "unifiedLogin.js: Trying to access " +
                                "create_user.php/registration via form submit;\n" +
                                "Error text: " + _textStatus + "; Error: " +
                                _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                            var errobj = { err: ajaxerr };
                            // there is no error callback in $.post
                            $.post('../php/ajaxError.php', errobj);
                        }
                    }
                });
                return true;
            });
            break;
        case 'renew':
            if (mobile) {
                $('#rp').css('font-size', '18px');
                $('.mobtxt').css('font-size', '12px');
                $('#pexpl').css('font-size', '12px');
                $('#password').css('height', '28px');
                $('#confirm').css('height', '28px');
                $('#showdet').css('height', '28px');
                $('#showdet').css('line-height', '1rem');
                $('#showdet').css('font-size', '16px');
                $('#rvw').css('height', '28px');
                $('#rvw').css('font-size', '18px');
                $('#formsubmit').css('top', '-16px');
            }
            // cookie banner shows here (in particular for new registrants)
            // clear inputs on page load/reload
            $('#password').val("");
            $('#confirm').val("");
            $('#ckbox').prop('checked', false);
            var ix = $('#ix').text();
            tbl_indx = ix; // required in validateUser's #closesec function
            var pdet = new bootstrap.Modal(document.getElementById('show_pword_details'));
            var login_renew = $('#one-time').val();
            $container.css({
                top: ren.top,
                height: ren.height
            });
            /**
             * Populate the security questions with the user's answers, as
             * he/she may not review them prior to submitting, and they need
             * to be present for the answer check in 'formsubmit'.
             */
            $.post('usersQandA.php', { ix: ix }, function (contents) {
                $('#uques').empty();
                $('#uques').append(contents);
            });
            // toggle visibility of password:
            var cbox = document.getElementsByName('password');
            $('#ckbox').on('click', function () {
                if ($(this).is(':checked')) {
                    cbox[0].setAttribute("type", "text");
                }
                else {
                    cbox[0].setAttribute("type", "password");
                }
            });
            // show details of password when 'weak'
            $('#showdet').on('click', function (ev) {
                ev.preventDefault();
                pdet.show();
            });
            // security modal buttons operation spec'd in validateUser.js
            $('#rvw').on('click', function (ev) {
                ev.preventDefault();
                updates.show();
            });
            // SUBMIT FORM
            $('#formsubmit').on('click', function (ev) {
                ev.preventDefault();
                if ($('#st').css('display') === 'none') {
                    alert("You must use a strong password");
                    return false;
                }
                var password = $('input[name=password]').val();
                if (password === '') {
                    alert("You have not entered a passwsord");
                    return false;
                }
                var confirm = $('#confirm').val();
                if (confirm === '') {
                    alert("You must confirm your password");
                    return false;
                }
                else if (confirm !== password) {
                    alert("Your passwords do not match");
                    return false;
                }
                var acnt = 0;
                $('input[id^=q]').each(function () {
                    if ($(this).val() !== '') {
                        acnt++;
                    }
                });
                if (acnt !== 3) {
                    alert("You must supply exactly 3 answers to security questions");
                    return false;
                }
                var formdata = {
                    submitter: 'change',
                    code: login_renew,
                    password: password,
                    user: ix
                };
                $.ajax({
                    url: 'create_user.php',
                    method: 'post',
                    data: formdata,
                    dataType: 'text',
                    success: function (result) {
                        if (result === 'OK') {
                            alert("Your password has been updated\nAnd you are logged in");
                            window.open('../index.html', '_self');
                        }
                        else {
                            alert("Your one-time code was not located\n" +
                                "Please try again by entering the code in your email\n" +
                                "into the 'One-time code' box");
                            return;
                        }
                    },
                    error: function (_jqXHR, _textStatus, _errorThrown) {
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
                            var ajaxerr = "unifiedLogin.js: Attempting to renew via " +
                                "create_user.php/renew in form submit\n" +
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
                var lotimeout = setInterval(function () {
                    $.get("../accounts/lockStatus.php", function (result) {
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
                        }
                        else {
                            $('#lomin').text(result.minutes);
                        }
                    }, "json");
                }, 100000);
            }
            else {
                $('#form').on('submit', function (ev) {
                    ev.preventDefault();
                    var user = $('#username').val();
                    var pass = $('#password').val();
                    if (user === '' || pass === '') {
                        alert("Both username and password must be specified");
                        return false;
                    }
                    validateUser(user, pass);
                    var nothing = document.getElementById("password");
                    nothing.focus();
                    return;
                });
            }
            break;
    }
});
