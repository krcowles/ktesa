"use strict"
/**
 * @fileoverview Visually, this script positions the login boxes on the page
 * and toggles password visibility. Functionally, it verifies that all
 * fields in the form have been entered, and registers the member's cookie choice.
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 */
$(function() {   // document ready function

/**
 * position the registration box on the page
 * 
 * @return {null}
 */ 
function setbox() {
    let regbox_center = Math.floor($('#registration').width()/2);
    let regbox_left = window.innerWidth/2 - regbox_center; // 280 = regbox/2 width
    $('#registration').offset({
        top: 200,
        left: regbox_left
    });
}
setbox();
$(window).resize(setbox);

// toggle visibility of password:
var cbox = document.getElementsByName('password');
$('#cb').on('click', function() {
    if ($(this).is(':checked')) {
        cbox[0].type = "text";
    } else {
        cbox[0].type = "password";
    }
});

// registrant's cookie choice:
$('#accept').on('click', function() {
    $('#cookie_banner').hide(); 
    $('#usrchoice').val("accept");
});
$('#reject').on('click', function() {
    $('#cookie_banner').hide();
    $('#usrchoice').val("reject");
});

// NOTE: email validation performed by HTML5, and again by server
    /**
     * For username problems, notify user immediately
     */
    var outstanding_issue = false;
    // no spaces in user name:
    var nonamespaces = true;
    /**
     * Ensure the user name has no embedded spaces
     * @return {null}
     */
    var spacesInName = function () {
        var uname = $('#uname').val();
        if (uname.indexOf(' ') !== -1) {
            alert("No spaces in user name please");
            $('#uname').focus();
            $('#uname').css('color', 'red');
            nonamespaces = false;
            outstanding_issue = true;
        }
        else {
            if (goodname) {
                outstanding_issue = false;
            }
        }
        return;
    };
    // unique user name:
    var goodname = true;
    var uniqueness = $.Deferred();
    /**
     * Make sure user name is unique;
     * NOTE: TypeScript won't allow a function's return value to be boolean! "you
     * must return a value": hence the return values specified below
     *
     * @return {boolean}
     */
    var uniqueuser = function () {
        var data = $('#uname').val();
        var ajaxdata = { username: data };
        var current_users = '../accounts/getUsers.php';
        $.ajax(current_users, {
            data: ajaxdata,
            method: 'post',
            success: function (match) {
                if (match === "NO") {
                    goodname = true;
                }
                else {
                    goodname = false;
                }
                uniqueness.resolve();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                uniqueness.reject();
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
                newDoc.close();
            }
        });
    };
    $('#uname').on('change', function () {
        spacesInName();
        if (nonamespaces) {
            uniqueuser();
            $.when(uniqueness).then(function () {
                if (!goodname) {
                    alert("This user name is already taken");
                    $('#uname').css('color', 'red');
                    outstanding_issue = true;
                }
                else {
                    if (nonamespaces) {
                        outstanding_issue = false;
                    }
                }
                uniqueness = $.Deferred(); // re-establish for next event
            });
        }
    });
    $('#uname').on('focus', function () {
        $(this).css('color', 'black');
    });
    // input fields: no blanks; no username spaces; valid email address
    $("form").submit(function () {
        if (outstanding_issue) {
            alert("Please correct item(s) in red before submitting");
            return false;
        }
        var allinputs = document.getElementsByClassName('signup');
        for (var i = 0; i < allinputs.length; i++) {
            var inputbox = allinputs[i];
            if (inputbox.value == '') {
                alert("Please complete all entries");
                return false;
            }
        }
        if ($('#cookie_banner').css('display') !== 'none') {
            alert("Please accept or reject cookis");
            return false;
        }
        ;
    });

});