"use strict";
$(function () {
    $('#owner').hide();
    var appMode = $('#appMode').text();
    $('form').on('submit', function (ev) {
        ev.preventDefault();
        var sender = $('#sender').val();
        var msg = $('#message').val();
        var ajaxdata = { form: 'own', email: sender, message: msg };
        $.ajax({
            url: "../accounts/resetMail.php",
            method: "post",
            data: ajaxdata,
            dataType: "text",
            success: function (result) {
                if (result !== "OK") {
                    alert(result);
                }
                else {
                    alert("Email sent");
                }
            },
            error: function (_jqXHR) {
                if (appMode === 'development') {
                    var newDoc = document.open();
                    newDoc.write(_jqXHR.responseText);
                    newDoc.close();
                }
                else {
                    var errmsg = _jqXHR.responseText;
                    var errobj = { err: errmsg };
                    $.post('../php/ajaxError.php', errobj);
                    alert("Something has gone wrong... admin notified");
                }
            }
        });
    });
});
