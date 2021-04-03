"use strict";
/**
 * @fileoverview Adjust page according to form type
 *
 * @author Tom Sandberg
 * @author Ken Cowles
 *
 * @version 2.0 Redesigned for responsiveness
 */
$(function () {
    // declared cookie choice:
    $('#accept').on('click', function () {
        $('#cookie_banner').hide();
        $('#usrchoice').val("accept");
    });
    $('#reject').on('click', function () {
        $('#cookie_banner').hide();
        $('#usrchoice').val("reject");
    });
    var reg = { top: 48, height: 570 };
    var log = { top: 80, height: 380 };
    var ren = { top: 80, height: 420 };
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
            $('#policylnk').on('click', function () {
                var plnk = '../php/postPDF.php?doc=../accounts/PrivacyPolicy.pdf';
                window.open(plnk, '_blank');
            });
            // NOTE: email validation is performed by HTML5, and again by server
            /**
             * For username problems, notify user immediately
             */
            var namespace = false;
            var goodname = true;
            var uniqueness = $.Deferred();
            /**
             * Ensure the user name has no embedded spaces
             *
             * @return {null}
             */
            var spacesInName_1 = function () {
                var uname = $('#uname').val();
                if (uname.indexOf(' ') !== -1) {
                    alert("No spaces in user name please");
                    $('#uname').css('color', 'red');
                    namespace = true;
                }
                else {
                    namespace = false;
                    $('#uname').css('color', 'black');
                }
                return;
            };
            /**
             * Make sure user name is unique;
             * NOTE: TypeScript won't allow a function's return value to be boolean! "you
             * must return a value": hence the return values specified below
             *
             * @return {boolean}
             */
            var uniqueuser = function () {
                var data = $('#uname').val();
                var ajaxdata = { username: data };
                var current_users = 'getUsers.php';
                $.ajax(current_users, {
                    data: ajaxdata,
                    method: 'post',
                    success: function (match) {
                        if (match === "NO") {
                            goodname = true;
                        }
                        else {
                            goodname = false;
                        }
                        uniqueness.resolve();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        uniqueness.reject();
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            };
            $('#uname').on('blur', function () {
                spacesInName_1();
                if (!namespace) {
                    uniqueuser();
                    $.when(uniqueness).then(function () {
                        if (!goodname) {
                            alert("This user name is already taken");
                            $('#uname').css('color', 'red');
                        }
                        else {
                            $('#uname').css('color', 'black');
                        }
                        uniqueness = $.Deferred(); // re-establish for next event
                    });
                }
            });
            $('#uname').on('focus', function () {
                $(this).css('color', 'black');
            });
            // input fields: no blanks; no username spaces; valid email address
            $("#form").on('submit', function (ev) {
                ev.preventDefault();
                if (!goodname || namespace) {
                    alert("Please correct item in red before submitting");
                    return false;
                }
                if ($('#cookie_banner').css('display') !== 'none') {
                    alert("Please accept or reject cookis");
                    return false;
                }
                var formdata = $('#form').serializeArray();
                $.ajax({
                    url: 'create_user.php',
                    data: formdata,
                    dataType: 'text',
                    method: 'post',
                    success: function (result) {
                        if (result !== 'OK') {
                            alert(result);
                            return false;
                        }
                        var email = $('#email').val();
                        var mail_data = { form: 'reg', email: email };
                        $.ajax({
                            url: 'resetMail.php',
                            method: 'post',
                            data: mail_data,
                            success: function (result) {
                                if (result === 'OK') {
                                    alert("An email has been sent - it may take awhile\n" +
                                        "You can continue as a guest for now");
                                    window.open('../index.html', '_self');
                                }
                                else {
                                    alert("A problem was encountered sending mail:\n" +
                                        result);
                                    location.reload();
                                }
                            },
                            error: function (jqXHR, _textStatus, _errorThrown) {
                                var newDoc = document.open();
                                newDoc.write(jqXHR.responseText);
                                newDoc.close();
                            }
                        });
                    },
                    error: function (jqXHR, _textStatus, _errorThrown) {
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            });
            break;
        case 'renew':
            // no one-time code when logged in user renews
            var login_renew = $('input[name=code]').val();
            ren.height = login_renew == '' ? 380 : ren.height;
            $container.css({
                top: ren.top,
                height: ren.height
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
            $('#form').on('submit', function (ev) {
                ev.preventDefault();
                var password = $('input[name=password]').val();
                var cookies = $('#usrchoice').val();
                if (cookies === 'nochoice') {
                    alert("Please accept or reject cookies");
                    return false;
                }
                var confirm = $('#confirm').val();
                if (confirm !== password) {
                    alert("Your passwords do not match");
                    return false;
                }
                var formdata = {
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
                    success: function (result) {
                        if (result === 'OK') {
                            alert("Your password has been updated\nAnd you are logged in");
                            window.open('../index.html');
                            // NOTE: current window cannot be closed because it was
                            // not opened by javascript
                        }
                        else {
                            alert("Your one-time code was not located\n" +
                                "Please try again by entering the code in your email\n" +
                                "into the 'One-time code' box");
                            return;
                        }
                    },
                    error: function (jqXHR, _textStatus, _errorThrown) {
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            });
            break;
        case 'log':
            // NOTE: expired or renew password scenario handled in menus.js
            $container.css({
                top: log.top,
                height: log.height
            });
            $('#cookie_banner').hide();
            $('#form').on('submit', function (ev) {
                ev.preventDefault();
                var user = $('#username').val();
                var pass = $('#password').val();
                validateUser(user, pass);
            });
            $('#send').on('click', function (ev) {
                ev.preventDefault();
                var email = $('#forgot').val();
                if (email == '') {
                    alert("You must enter a valid email address");
                    return false;
                }
                var data = { form: 'req', email: email };
                $.ajax({
                    url: '../accounts/resetMail.php',
                    data: data,
                    dataType: 'text',
                    method: 'post',
                    success: function (result) {
                        if (result === 'OK') {
                            alert("An email has been sent: these sometimes " +
                                "take awhile\nYou are logged out and can log in" +
                                " again\nwhen your email is received");
                            $.get({
                                url: '../accounts/logout.php',
                                success: function () {
                                    window.open('../index.html', '_self');
                                }
                            });
                        }
                        else {
                            alert(result);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            });
            break;
    }
});
