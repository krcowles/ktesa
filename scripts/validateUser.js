/**
 * @fileoverview This is the function which invokes an ajax call
 * to the authenticate.php script in an effort to validate login;
 * When logged in, reload to rerun getLogin.php 
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned login for security improvement
 */
function validateUser(user, password) {
    ajaxdata = {usr_name: user, usr_pass: password};
    $.ajax( {
        url: "../accounts/authenticate.php",
        method: "post",
        data: ajaxdata,
        dataType: "text",
        success: function(srchResults) {
            var status = srchResults;
            if (status.indexOf('ADMIN') !== -1) {
                loggedInItems();
                adminLoggedIn();
                alert("Admin logged in");
                window.location.reload();
            } else if (status.indexOf('LOCATED') !== -1) {
                loggedInItems();
                alert("You are logged in");
                window.location.reload();
            } else if (status.indexOf('RENEW') !== -1) {
                loggedInItems();
                // in this case, the old cookie has been set pending renewal
                var renew = confirm("Your password is about to expire\n" + 
                    "Would you like to renew?");
                if (renew) {
                    renewPassword('renew', 'valid');
                } else {
                    renewPassword('norenew', 'valid');
                }
            } else if (status.indexOf('EXPIRED') !== -1) {
                var renew = confirm("Your password has expired\n" +
                    "Would you like to renew?");
                if (renew) {
                    renewPassword('renew', 'expired');
                } else {
                    renewPassword('norenew', 'expired');
                }
            } else if (status.indexOf('BADPASSWD') !== -1) {
                var msg = "The password you entered does not match " +
                    "your registered password;\nPlease try again";
                alert(msg);
                $('#upass').val('');
            } else { // no such user in USERS table
                var msg = "Your registration info cannot be uniquely located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
                $('#usrid').val('');
                $('#upass').val('');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();         
        }
    });
}