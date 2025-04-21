$(function() {

$('#owner').hide();
var appMode = $('#appMode').text();
$('form').on('submit', function(ev) {
    ev.preventDefault();
    let sender = $('#sender').val();
    let msg    = $('#message').val();
    let ajaxdata = {form: 'own', email: sender, message: msg};
    $.ajax({
        url: "../accounts/resetMail.php",
        method: "post",
        data: ajaxdata,
        dataType: "text",
        success: function(result) {
            if (result !== "OK") {
                alert(result);
            } else {
                alert("Email sent");
            }
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
            let msg = "ownership.js: attempting to send admin mail via " +
                "resetMail.php";
            ajaxError(appMode, _jqXHR, _textStatus, msg);
        }
    });
});

});