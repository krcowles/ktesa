"use strict";
/**
 * @fileoverview A short script used by at least two different pages to send
 * a passsword reset email to the user.
 *
 * @author Ken Cowles
 *
 * @version 1.0 Instituted as a separate script when moving to main bootstrap navbar
 */
$('#send').on('click', function (ev) {
    ev.preventDefault();
    var email = $('#rstmail').val();
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
                var msg = void 0;
                if (result.indexOf('valid') !== -1) {
                    msg = "Your email is not valid. You cannot reset\n" +
                        "your password until this has been corrected";
                }
                else {
                    msg = "Your email could not be located in our database\n" +
                        "Please make sure it is the address you used when registering";
                }
                alert(msg);
            }
        },
        error: function (jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
    return true;
});
