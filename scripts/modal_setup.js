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
    var $tdclose = $('<td><button id="tdcancel" style="display:block;margin:auto;">Cancel</button></td>');

    $modal.append($content);
    $modal.append($close);
    $('body').on('click', '.canceler', function(e) {
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
            
            if (settings.id === 'logins') {
                $modal.children().eq(1).remove('#canceler');
                loginModal();
            } else if (settings.id === 'contact') {
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
    function loginModal() {
        /**
         * This modal needs a high z-index; In order to set it with javascript,
         * 'position' must be either absolute, relative, or fixed
         */
        $('#loginTbl tr').eq(0).css('height', '36px');
        $('#loginTbl tr').eq(1).css('height', '36px');
        $('#loginTbl tr').eq(2).css('height', '36px');
        $('#loginTbl tr').eq(3).css('height', '8px');
        $('#replace').replaceWith($tdclose);
        $('#sendemail').on('click', function() {
            let email = $('#resetpass').val();
            if (email == '') {
                alert("No email address has been entered");
                return;
            }
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                let data = {email: email};
                $.ajax({
                    url: '../accounts/resetMail.php',
                    data: data,
                    dataType: 'text',
                    method: "post",
                    success: function(result) {
                        if (result === 'OK') {
                            alert("An email has been sent - these sometimes " +
                                "take awhile");
                            modal.close();
                        } else {
                            alert("The following error was received:\n" +
                                result);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        var newDoc = document.open();
                        newDoc.write(jqXHR.responseText);
                        newDoc.close();
                    }
                });
            } else {
                alert("Not a valid email address");
                return;
            }

        });
        $('#enter').on('click', function() {
            var pwd = $('#upass').val();
            var uid = $('#usrid').val();
            if (uid == '' && pwd == '') {
                alert("You must supply a registered user name and password");
                return;
            }
            if (uid == '') {
                alert("You must supply a registered user name");
                return;
            }
            if (pwd == '') {
                alert("You must supply a valid password");
                return;
            } 
            validateUser(uid, pwd);
            modal.close();
        });
        $tdclose.on('click', function() {
            modal.close();
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