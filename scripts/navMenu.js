"use strict";
/// <reference types="bootstrap" />
/**
 * @fileoverview Navbar menu actions where href="#"
 *
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 * @version 1.1 Typescripted
 * @version 1.2 Updated logout menu to reflect state of 'mobile' var
 */
/**
 * Menu setup
 */
var choice = $('#cookies_choice').text();
if (choice === 'accept') {
    $('#cookies').text('Reject Cookies');
}
else {
    $('#cookies').text('Accept Cookies');
}
var chg_modal = new bootstrap.Modal(document.getElementById('cpw'), {
    keyboard: false
});
// Setup modal as a user presentation for any ajax errors.
var ajaxerror = new bootstrap.Modal(document.getElementById('ajaxerr'), {
    keyboard: false
});
/**
 * Menu operation
 */
$('#logout').on('click', function () {
    var ajax = { expire: 'N' };
    $.ajax({
        url: '../accounts/logout.php',
        data: ajax,
        method: "get",
        success: function () {
            if (mobile) {
                window.open('../pages/landing.php', '_self');
            }
            else {
                window.open('../pages/home.php', '_self');
            }
        },
        error: function () {
            ajaxerror.show();
            var err = { err: "Mobile logout error" };
            $.post('../php/ajaxError.php', err);
        }
    });
});
$('#chg').on('click', function () {
    chg_modal.show();
});
$('#send').on('click', function (ev) {
    ev.preventDefault();
    var email = $('#cpwmail').val();
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
                        window.open('../pages/landing.php', '_self');
                    }
                });
                chg_modal.hide();
            }
            else {
                alert(result);
            }
        },
        error: function () {
            ajaxerror.show();
            var err = { err: "Mobile - resetMail.php error" };
            $.post('../php/ajaxError.php', err);
        }
    });
});
$('#cookies').on('click', function () {
    var newchoice;
    var changeto = $(this).text();
    if (changeto == 'Accept Cookies') {
        newchoice = 'accept';
    }
    else {
        newchoice = 'reject';
    }
    var change = { choice: newchoice };
    $.ajax({
        url: '../accounts/member_cookies.php',
        method: 'post',
        dataType: 'text',
        data: change,
        success: function () {
            window.location.reload();
        },
        error: function () {
            ajaxerror.show();
            var err = { err: "Mobile member_cookies.php error" };
            $.post('../php/ajaxError.php', err);
        }
    });
});
