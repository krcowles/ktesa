// modal object definition
var modal = (function() {
    // Local/private to "modal"
    var $window = $(window);
    var $modal = $('<div class="modal" style="background-color:ivory;"/>'); 
    var $content = $('<div class="modal-content"/>');
    var $close = $('<button id="canceler" style="position:relative;">Cancel</button>');

    $modal.append($content);
    $modal.append($close);
    $close.on('click', function(e) {
        e.preventDefault();
        modal.close();
    });

    // private functions: (currently only one)
    // login box:
    function loginModal(setwidth) {
        /**
         * This modal needs a high z-index; In order to set it with javascript,
         * 'position' must be either absolute, relative, or fixed
         */
        $('#enter').after($close);
        $close.css('left', '134px');
        $modal.css('z-index', '10000');
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
        $close.on('click', function() {
            modal.close();
        });
    }

    // public:
    return {   // returns object methods:
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
                padding: '8px'
            }).appendTo('body');
            if (settings.id === 'logins') {
                loginModal();
            } else {
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
}());  // modal is an IIFE