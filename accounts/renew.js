$(function() {

// Start with checkbox cleared
$('#ckbox').prop('checked', false);

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
    if ($('#confirm').val() !== $('#password').val()) {
        alert("Passwords do not match");
        return false;
    }
});

});