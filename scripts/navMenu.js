"use strict"
/**
 * @fileoverview Navbar menu actions where href="#"
 * 
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 */

/**
 * Menu setup
 */
var choice = $('#cookies_choice').text();
if (choice === 'accept') {
    $('#cookies').text('Reject Cookies');
} else {
    $('#cookies').text('Accept Cookies');
}
var chg_modal = new bootstrap.Modal(document.getElementById('cpw'), {
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
            window.open('../pages/landing.php')
        },
        error: function(jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
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
                modal.close();
            } else {
                alert(result);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
});

$('#cookies').on('click', function() {
    let newchoice;
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
        error: function(jqXHR, textStatus, errorThrown) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
});
