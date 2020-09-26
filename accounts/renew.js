/**
 * @fileoverview Allow user to renew account with new password
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * @version 2.0 Redesigned login for security improvement
 */
$(function() {

// Start with checkbox cleared
$('#ckbox').prop('checked', false);

/**
 * position the registration box on the page
 * 
 * @return {null}
 */ 
function setbox() {
    let regbox_center = Math.floor($('#container').width()/2);
    let regbox_left = window.innerWidth/2 - regbox_center; // 280 = regbox/2 width
    $('#container').offset({
        top: 160,
        left: regbox_left
    });
}
setbox();
$(window).resize(setbox);

$('#accept').on('click', function() {
    $('#cookie_banner').slideToggle(); 
    $('#usrchoice').val("accept");
});
$('#reject').on('click', function() {
    $('#cookie_banner').slideToggle();
    $('#usrchoice').val("reject");
});

// toggle visibility of password:
var pword = document.getElementsByName('password');
$('#ckbox').on('click', function() {
    if ($(this).is(':checked')) {
        pword[0].type = "text";
        pword[0].style.position = "relative";
        //pword[0].style.left = "-20px";
    } else {
        pword[0].type = "password";
    }
});

// make sure passwords match
$('#formsubmit').on('click', function(ev) {
    if ($('#password') == '') {
        alert("You must fill in a password");
        return false;
    }
    if ($('#confirm').val() !== $('#password').val()) {
        alert("Passwords do not match");
        return false;
    }
    if ($('#usrchoice').val() == 'nochoice') {
        alert("You must designate your cookie choice below");
        return false;
    }
});

});