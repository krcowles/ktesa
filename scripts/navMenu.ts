/// <reference types="bootstrap" />
declare var mobile: boolean;
/**
 * @fileoverview Navbar menu actions where href="#"
 * 
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 * @version 1.1 Typescripted
 * @version 1.2 Updated logout menu to reflect state of 'mobile' var
 * @version 1.3 Updated ajax error handling
 */

$(function() { // document ready function
/**
 * Menu setup
 */
var appMode = $('#appMode').text() as string;  // LOCAL navbar var
var choice = $('#cookies_choice').text();
if (choice === 'accept') {
    $('#cookies').text('Reject Cookies');
} else {
    $('#cookies').text('Accept Cookies');
}
var chg_modal = new bootstrap.Modal(<HTMLElement>document.getElementById('cpw'), {
    keyboard: false
});
var ajaxerror = new bootstrap.Modal(<HTMLElement>document.getElementById('ajaxerr'));

// Setup modal as a user presentation for any ajax errors.
var ajaxerror = new bootstrap.Modal(<HTMLElement>document.getElementById('ajaxerr'), {
    keyboard: false
});

/**
 * Menu operation
 */
$('#logout').on('click', function() {
    let ajax = {expire: 'N'};
    $.ajax({
        url: '../accounts/logout.php',
        data: ajax,
        method: "get",
        success: function() {
            if (mobile) {
                window.open('../pages/landing.php', '_self');
            } else {
                window.open('../pages/home.php', '_self');
            }
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
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

$('#chg').on('click', function() {
    chg_modal.show();
});
$('#send').on('click', function(ev) {
    ev.preventDefault();
    let email = $('#cpwmail').val();
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
                        window.open('../pages/landing.php', '_self');
                    }
                });
                chg_modal.hide();
            } else {
                alert(result);
            }
        },
        error: function() {
            ajaxerror.show();
            let err ={err: "Mobile - resetMail.php error"};
            $.post('../php/ajaxError.php', err);
        }
    });
});

$('#cookies').on('click', function() {
    let newchoice: string;
    let changeto = $(this).text();
    if (changeto == 'Accept Cookies') {
        newchoice = 'accept';
    } else {
        newchoice = 'reject';
    }
    let change = {choice: newchoice};
    $.ajax({
        url: '../accounts/member_cookies.php',
        method: 'post',
        dataType: 'text',
        data: change,
        success: function() {
            window.location.reload();
        },
        error: function() {
            ajaxerror.show();
            let err = {err: "Mobile member_cookies.php error"};
            $.post('../php/ajaxError.php', err);
        }
    });
});

});