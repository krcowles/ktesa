"use strict"
/**
 * @fileoverview This module contains the modal object definition (private)
 * and private functions providing modal functionaliry for the type of 
 * modal invoked
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned login for security improvement
 */
var modal = (function() {
    // modal object def is local (private) to "modal"
    var $window = $(window);
    var $modal = $('<div class="modal" style="background-color:ivory;"/>'); 
    var $content = $('<div class="modal-content"/>');
    var $close = $('<button id="canceler" style="position:relative;">Cancel</button>');

    $modal.append($content);
    $modal.append($close);
    $('body').on('click', '#canceler', function(e) {
        e.preventDefault();
        modal.close();
    });

    // public: return object's methods
    return {
        center: function() {
            var top = Math.max($window.height() - $modal.outerHeight(), 0) / 2;
            var left = Math.max($window.width() - $modal.outerWidth(), 0) / 2;
            $modal.css({
                    top: top + $window.scrollTop(),
                    left: left + $window.scrollLeft()
            });
        },
        open: function(settings) {
            $content.empty().append(settings.content.html());
            var modalTop  = $('#navbar').height() + 'px';
            var vpWidth   = $('#navbar').width();
            var logwd = parseInt(settings.width)/2;
            var modalLeft = parseInt(vpWidth/2 - logwd) + 'px';
            $modal.css({
                position: 'absolute',
                top: modalTop,
                left: modalLeft,
                width: settings.width || auto,
                height: settings.height || auto,
                border: '2px solid',
                padding: '8px',
                textAlign: 'left',
                zIndex: '1000'
            }).appendTo('body');
            
            if (settings.id === 'resetpass') {
                passwd_reset('reset');
            } else if (settings.id === 'renewpass') {
                passwd_reset('renew');
            }
            else if (settings.id === 'contact') {
                contactAdmins();
            } else { // default (not used)
                $modal.css({
                    width: settings.width || auto,
                    height: settings.height || auto,
                    border: '2px solid',
                    padding: '8px'
                }).appendTo('body');
                modal.center();
                $(window).on('resize', modal.center);
            }
        },
        close: function() {
            $content.empty();
            $modal.detach();
            $(window).off('resize', modal.center);
        }
    };

    //  ------------------  private functions:  ------------------
    /**
     * This function allows a user to login or refresh password
     * 
     * @return {null}
     */
    function passwd_reset(type) {
        $('#sendmail').after($close);
        $close.css({
            'float': 'right',
            'margin-right': '6px'
        });
        $('#emailform').on('submit', function(ev) {
            ev.preventDefault();
            let email = $('#femail').val(); 
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
                                if (type === 'reset') {
                                    window.open('../index.html', '_self');
                                } else {
                                    window.open('', 'homePage', '');
                                    window.close();
                                }
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

    }
    /**
     * This presents a means by which a visitor can pose questions
     * of comments
     * 
     * @return {null}
     */
    function contactAdmins() {
        $('#submit').after($close);
        $close.css({
            'top': '1',
            'float': 'right',
            'margin-right': '6px'
        });
        $close.css('top', '1px');
        $('#submit').on('click', function() {
            var ta = $('#fdbk').val();
            for (let i=0; i< 2; i++) {
                let ajaxdata = {admin: i, feedback: ta};
                $.ajax({
                    url: '../admin/support.php',
                    method: 'post',
                    data: ajaxdata,
                    success: function() {
                        alert("Email sent");
                    },
                    error: function() {
                        alert("Error encountered: not sent");
                    }
                });
            }
            modal.close();
        });
    }

}());  // modal is an IIFE