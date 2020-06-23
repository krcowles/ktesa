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
        $('#pwlnk').on('click', function(ev) {
            let email = window.prompt("Enter your account's email address");
            let data = {email: email};
            $.ajax({
                url: '../accounts/resetPassword.php',
                method: 'post',
                data: data,
                dataType: 'text',
                success: function(result) {
                    if (result === "OK") {
                        alert("You will receive an email with a link to reset");
                    } else if (result === "BADEMAIL") {
                        alert("The address is not a valid email address");
                    } else if (result === "NOTFOUND") {
                        alert("Your email does not appear in our database");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    let newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
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