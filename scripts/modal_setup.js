"use strict";
/**
 * @fileoverview This module contains the modal object definition (private)
 * and private functions providing modal functionaliry for the type of
 * modal invoked
 *
 * @author Ken Cowles
 * @version 2.0 Redesigned login for security improvement
 * @version 3.0 Typescripted, with some type errors corrected
 */
var modal = (function () {
    // modal object def is local (private) to "modal"
    var $window = $(window);
    var $modal = $('<div class="modal" style="background-color:ivory;"/>');
    var $content = $('<div class="modal-content"/>');
    var $close = $('<button id="canceler" style="position:relative;">Cancel</button>');
    $modal.append($content);
    $modal.append($close);
    $('body').on('click', '#canceler', function (e) {
        e.preventDefault();
        modal.close();
    });
    // public: return object's methods
    return {
        center: function () {
            var top = Math.max($window.height() - $modal.outerHeight(), 0) / 2;
            var left = Math.max($window.width() - $modal.outerWidth(), 0) / 2;
            $modal.css({
                top: top + $window.scrollTop(),
                left: left + $window.scrollLeft()
            });
        },
        open: function (settings) {
            $content.empty().append(settings.content.html());
            var modalTop = $('#navbar').height() + 'px';
            var vpWidth = $('#navbar').width();
            var logwd = parseInt(settings.width) / 2;
            var modalLeft = (vpWidth / 2 - logwd) + 'px';
            $modal.css({
                position: 'absolute',
                top: modalTop,
                left: modalLeft,
                width: settings.width,
                height: settings.height,
                border: '2px solid',
                padding: '8px',
                textAlign: 'left',
                zIndex: '1000'
            }).appendTo('body');
            if (settings.id === 'resetpass') {
                passwd_reset('reset');
            }
            else if (settings.id === 'renewpass') {
                passwd_reset('renew');
            }
            else if (settings.id === 'contact') {
                $modal.css({
                    width: settings.width,
                    height: settings.height,
                    border: '2px solid',
                    padding: '8px'
                }).appendTo('body');
                modal.center();
                $(window).on('resize', modal.center);
            }
        },
        close: function () {
            $content.empty();
            $modal.detach();
            $(window).off('resize', modal.center);
        }
    };
    //  ------------------  private functions:  ------------------
    /**
     * This function allows a user to login or refresh password
     */
    function passwd_reset(type) {
        $('#sendmail').after($close);
        $close.css({
            'float': 'right',
            'margin-right': '6px'
        });
        $('#femail').trigger('focus');
        $('#emailform').on('submit', function (ev) {
            ev.preventDefault();
            var email = $('#femail').val();
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
                                if (type === 'reset') {
                                    window.open('../index.html', '_self');
                                }
                                else {
                                    window.open('', 'homePage', '');
                                    window.close();
                                }
                            }
                        });
                        modal.close();
                    }
                    else {
                        var errmsg = void 0;
                        if (result.indexOf('valid') !== -1) {
                            errmsg = 'The email is not valid; You cannot change ' +
                                'your password at this time\nThe admin has been ' +
                                'notified';
                        }
                        else {
                            errmsg = "Your email could not be located in  our " +
                                "database\nThe admin has been notified";
                        }
                        alert(errmsg);
                        modal.close();
                        var ajaxerr = "Forgot/change password error: " +
                            errmsg + "; " + email;
                        var errobj = { err: ajaxerr };
                        $.post('../php/ajaxError.php', errobj);
                    }
                },
                error: function () {
                    var msg = "A server error has occurred\nYou will be unable " +
                        "to change your password at this time\nThe admin has been " +
                        "notified";
                    alert(msg);
                    modal.close();
                    var ajaxerr = "resetMail.php error:  " + email + "; Database" +
                        " password may have been altered";
                    var errobj = { err: ajaxerr };
                    $.post('../php/ajaxError.php', errobj);
                }
            });
        });
    }
}()); // modal is an IIFE
