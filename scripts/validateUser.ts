/**
 * @fileoverview This function invokes an ajax call to the
 * authenticate.php script in an effort to validate membership.
 * NOTE: This function is only invoked via unifiedLogin.js (log),
 * and if the home page was opened first.
 * 
 * @author Ken Cowles
 * @version 3.0 Modified for stand-alone login page (previously modal)
 * @version 3.1 Typescripted
 */
/**
 * The authenticating function: renew/expire needed in cases where either
 * cookies are off or there is no browser cookie (e.g. cookies rejected)
 */
function validateUser(user: string, password: string) {
    let ajaxdata = {usr_name: user, usr_pass: password};
    $.ajax( {
        url: "../accounts/authenticate.php",
        method: "post",
        data: ajaxdata,
        dataType: "text",
        success: function(srchResults) {
            var status = srchResults;
            if (status.indexOf('ADMIN') !== -1) {
                alert("Admin logged in");
                window.open('../index.html', '_self');
            } else if (status.indexOf('LOCATED') !== -1) {
                alert("You are now logged in");
                window.open('../index.html', '_self');
            } else if (status.indexOf('RENEW') !== -1) {
                var renew = confirm("Your password is about to expire\n" + 
                    "Would you like to renew?");
                if (renew) {
                    renewPassword('renew');
                } else {
                    renewPassword('norenew');
                }
            } else if (status.indexOf('EXPIRED') !== -1) {
                var renew = confirm("Your password has expired\n" +
                    "Would you like to renew?");
                if (renew) {
                    renewPassword('renew');
                } else {
                    renewPassword('norenew');
                }
            } else if (status.indexOf('BADPASSWD') !== -1) {
                var msg = "The password you entered does not match " +
                    "your registered password;\nPlease try again";
                alert(msg);
                $('#password').val('');
            } else { // "FAIL": no such user (or multiple) in USERS table
                var msg = "Your registration info cannot be uniquely located:\n" +
                    "Please click on the 'Sign me up!' link to register";
                alert(msg);
            }
        },
        error: function(jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();         
        }
    });
    return;
}
