"use strict"
/**
 * @fileoverview Adjust page according to form type
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 1.0 First release
 */
$(function() {

// For these pages, don't show the menu
$('#navbar').hide();
$('#trail').css('top', '2px');

// declared cookie choice:
$('#accept').on('click', function() {
    $('#cookie_banner').hide(); 
    $('#usrchoice').val("accept");
});
$('#reject').on('click', function() {
    $('#cookie_banner').hide();
    $('#usrchoice').val("reject");
});


var reg = {top: 48, height: 580, width: 460};
var log = {top: 80, height: 300, width: 500};
var ren = {top: 80, height: 420, width: 560};

var formtype = $('#formtype').text();
var $container = $('#container');

/**
 * The code executed depends on which formtype is in play
 */
switch (formtype) {
    case 'reg':
        $container.css({
            top: reg.top,
            height: reg.height,
            width: reg.width
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
        const spacesInName =  () => {
            var uname = $('#uname').val();
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
         * NOTE: TypeScript won't allow a function's return value to be boolean! "you
         * must return a value": hence the return values specified below
         *
         * @return {boolean}
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
                    }
                    uniqueness.resolve();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    uniqueness.reject();
                    let newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
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
            let formdata = $('#form').serializeArray();
            $.ajax({
                url: 'create_user.php',
                data: formdata,
                dataType: 'text',
                method: 'post',
                success: function(result) {
                    if (result !== 'OK') {
                        alert("Your user data could not be stored:\n" +
                            "Please contact the system administrator");
                        return;
                    }
                    let email = $('#email').val();
                    let mail_data = {form: 'reg', email: email};
                    $.ajax({
                        url: 'resetMail.php',
                        method: 'post',
                        data: mail_data,
                        success: function(result) {
                            if (result === 'OK') {
                                alert("An email has been sent - it may take awhile\n" +
                                    "You can continue as a guest for now");
                                window.open('', 'homePage', '');
                                window.close();
                            } else {
                                alert("A problem was encountered sending mail:\n" +
                                    result);
                            }
                        },
                        error: function(jqXHR, _textStatus, _errorThrown) {
                            let newDoc = document.open();
                            newDoc.write(jqXHR.responseText);
                            newDoc.close();
                        }     
                    });
                },
                error: function(jqXHR, _textStatus, _errorThrown) {
                    let newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
        });
        break;
    case 'renew':
        // no one-time code when logged in user renews
        var login_renew = $('input[name=code]').val();
        ren.height = login_renew == '' ? 380 : ren.height
        $container.css({
            top: ren.top,
            height: ren.height,
            width: ren.width
        });
        // toggle visibility of password:
        var cbox = document.getElementsByName('password');
        $('#ckbox').on('click', function() {
            if ($(this).is(':checked')) {
                cbox[0].type = "text";
            } else {
                cbox[0].type = "password";
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
                error: function(jqXHR, _textStatus, _errorThrown) {
                    let newDoc = document.open();
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
            height: log.height,
            width: log.width
        });
        $('#cookie_banner').hide();
        $('#form').on('submit', function(ev) {
            ev.preventDefault();
            let user = $('#username').val();
            let pass = $('#password').val();
            validateUser(user, pass);
        });
        break;
}
 
});