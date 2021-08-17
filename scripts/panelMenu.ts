/// <reference types="jqueryui" />
/**
 * @fileoverview This script controls actions of the bootstrap navbar
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 Introduction of bootstrap navbar for non-mobile platforms
 */
$(function() {  // document ready function

// establish the page title in the logo 'ctr' div
var logo_done = $.Deferred();
function logo_title(deferred: JQueryDeferred<any>) {
    var pgtitle = $('#trail').detach();
    $('#ctr').append(pgtitle);
    deferred.resolve();
}
logo_title(logo_done);

// Reset password modal:
var resetPassModal = new bootstrap.Modal(<HTMLElement>document.getElementById('cpw'));

// when page is called, clear any menu items that are/were active
$('.dropdown-item a').removeClass('active');
var activeItem = $('#active').text();
switch(activeItem) {
    case "Home":
        $('#homepg').addClass('active');
        break;
    case "Table":
        $('#tblpg').addClass('active');
        break;
    case "Favorites":
        $('#favpg').addClass('active');
        break;
    case "About":
        $('#aboutpg').addClass('active');
        break;
    case "Admin":
        $('#adminpg').addClass('active');
        break;
    case "Create":
        $('#createpg').addClass('active');
        break;
    case "Edit":
        $('#conteditpg').addClass('active');
        break;
    case "EditPub":
        $('#editpubpg').addClass('active');
        break;
    case "PubReq":
        $('#pubreqpg').addClass('active');
        break;
}

/**
 * Some menu items require a response that is not simply opening
 * a new window
 */
$('#logout').on('click', function() {
    $.ajax({
        url: '../accounts/logout.php',
        method: 'get',
        dataType: 'text',
        success: function(result) {
            if (result === 'Done') {
                alert("You have been successfully logged out");
                window.open('../index.html');
            } else {
                alert("Failed to execute logout; Admin notified");
                var ajxerr = {err: "Could not execute logout.php"};
                $.post('../php/ajaxError.php', ajxerr);
            }
        },
        error: function() {
            alert("Failed to execute logout; Admin notified");
            var ajxerr = {err: "Could not execute logout.php"};
            $.post('../php/ajaxError.php', ajxerr);
        }
    });
    return;
});
$('#chg').on('click', function() {
    resetPassModal.show();
    return;
});
$('#usrcookies').on('click', function() {
    var cookie_action = $(this).text();
    var action = cookie_action === 'Accept Cookies' ? 'accept' : 'reject';
    var ajaxdata = {choice: action};
    $.ajax({
        url: '../accounts/member_cookies.php',
        method: 'post',
        dataType: 'text',
        data: ajaxdata,
        success: function() {
            window.location.reload();
        },
        error: function() {
            let msg = "Cannot change cookie preference at this time:\n" +
                "The admin has been notified";
            alert(msg);
            let errobj = {err: msg};
            $.post('../php/ajaxError.php', errobj);
        }
    });
    return;
});


});  // end document ready
