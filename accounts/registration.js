/**
 * @fileoverview Visually, this script positions the login boxes on the page
 * and toggles password visibility. Functionally, it verifies that all
 * fields in the form have been entered
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 */
$(function() {   // document ready function
// position the registration box on the page
let regbox_left = window.innerWidth/2 - 200;
$('#registration').offset({
    top: 200,
    left: regbox_left
});

// toggle visibility of password:
var cbox = document.getElementsByName('password');
$('#cb').on('click', function() {
    if ($(this).is(':checked')) {
        cbox[0].type = "text";
        cbox[0].style.position = "relative";
        cbox[0].style.left = "-20px";
    } else {
        cbox[0].type = "password";
    }
});

// validation
var proceed = true;
/**
 * Ensure the user name has no embedded spaces
 * @return {boolean}
 */
const nospaces = () => {
    if ($('#uname').val().indexOf(' ') === -1) {
        return true;
    } else {
        alert("No spaces in user name please");
        $('#uname').focus();
        return false;
    }
};
/**
 * Make sure email is valid (otherwise blank in USERS table)
 * @return {boolean}
 */
const validemail = () => {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test($('#email').val())) {
        return true;
    } else {
        alert("You have entered an invalid email address");
        $('#email').focus();
        return false;
    }
};
/**
 * Make sure user name is unique
 * @return {boolean}
 */
const uniqueuser = (deferred) => {
    let data = $('#uname').val();
    let ajaxdata = {username: data};
    $.ajax({
        url: 'getUsers.php',
        data: ajaxdata,
        method: 'post',
        success: function(unique) {
            if (unique === "NO") {
                proceed = true;
            } else {
                proceed = false;
                alert("This user name is already taken");
            }
            deferred.resolve();
        },
        
        error: function (jqXHR, textStatus, errorThrown) {
            deferred.reject();
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
};
// input fields: no blanks; no username spaces; valid email address
$('#submit').on('click', function() {
    let msgs = 0;
    $('.signup').each(function() {
        if ($(this).val() == '' && msgs === 0) {
            proceed = false;
            alert("Please complete all entries");
            msgs++;
        }
    });
    if (proceed) {
        proceed = nospaces();
    }
    if (proceed) {
        proceed = validemail();
    }
    if (proceed) {
        let asynch = $.Deferred();
        uniqueuser(asynch);
        $.when(asynch).then(function() {
            if (proceed) {
                $('#form').submit();
            } else {
                proceed = true;
            }
        });
    } else {
        proceed = true;
    }
    return;
});

});