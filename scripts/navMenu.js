"use strict";
/// <reference types="bootstrap" />
/**
 * @fileoverview Navbar menu actions where href="#"
 *
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 * @version 1.1 Typescripted
 * @version 1.2 Updated logout menu to reflect state of 'mobile' var
 * @version 1.3 Updated ajax error handling
 */
$(function () {
    /**
     * Menu setup
     */
    var appMode = $('#appMode').text(); // LOCAL navbar var
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
    var lockout = new bootstrap.Modal(document.getElementById('lockout'));
    var ajaxerror = new bootstrap.Modal(document.getElementById('ajaxerr'));
    // Setup modal as a user presentation for any ajax errors.
    var ajaxerror = new bootstrap.Modal(document.getElementById('ajaxerr'), {
        keyboard: false
    });
    /**
     * Menu operation
     */
    $('#login').on('click', function () {
        $.get('../accounts/lockStatus.php', function (lock_status) {
            if (lock_status.result !== "ok") {
                $('.lomin').text(lock_status.minutes);
                lockout.show();
            }
            else {
                localStorage.removeItem('lockout');
                window.open("../accounts/unifiedLogin.php?form=log");
            }
        }, "json");
    });
    $('#force_reset').on('click', function () {
        //lockout.hide();
        chg_modal.show();
        return;
    });
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
            error: function (_jqXHR, _textStatus, _errorThrown) {
                if (appMode === 'development') {
                    var newDoc = document.open();
                    newDoc.write(_jqXHR.responseText);
                    newDoc.close();
                }
                else { // production
                    var ajaxerr = "Trying to access mobile logout;\nError text: " +
                        _textStatus + "; Error: " + _errorThrown + "; jqXHR: " +
                        _jqXHR.responseText;
                    var errobj = { err: ajaxerr };
                    $.post('../php/ajaxError.php', errobj);
                    ajaxerror.show();
                }
            }
        });
    });
    $('#chg').on('click', function () {
        chg_modal.show();
    });
    $('#send').on('click', function (ev) {
        ev.preventDefault();
        var email = $('#cpwmail').val();
        var data = { form: 'chg', email: email };
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
});
