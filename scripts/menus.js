"use strict"
/**
 * @fileoverview This script controls actions of the jQuery-UI-based menu
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned login for security improvement
 */
$(function() {  // document ready function

var menuWidth = ['140', '200', '150', '140']; // calculate these later...
var subWidth = ['140']; // ditto: unused as there is only one sub-menu
var $mainMenus = $('.menu-main');
var navPos = $('#navbar').offset();
var navBottom = navPos.top + $('#navbar').height() + 5 + 'px';
// usr_login div hidden during page load to prevent display when alerts appear
$('#usr_login').css('display', 'block');
var support = $('#feedback').detach();
// page_type allows setting of icon in the menu
var page_type = $('#page_id').text();
var icon = '<span class="ui-icon ui-icon-circle-arrow-e"></span>';
switch(page_type.trim()) { // only on actual pages...
    case "Home":
        $('#home').prepend(icon);
        break;
    case "Table":
        $('#table').prepend(icon);
        break;
    case "Admin":
        $('#atools').prepend(icon);
        break;
    case "Favorites":
        $('#yours').prepend(icon);
        break;
    case "Create":
        $('#newPg').prepend(icon);
        break;
    case "Edit":
        $('#edits').prepend(icon);
        break;
    case "EditPub":
        $('#epubs').prepend(icon);
        break;
    case "About":
        $('#about').prepend(icon);
        break;
    default:
}
function gotoPage(content) {
    // match up text with page
    var page;
    switch (content.trim()) {
        case 'Home':
            window.open('../index.html', '_self');
            break;
        case 'Table Only':
            window.open('../pages/tableOnly.php', '_self');
            break;
        case 'Admintools':
            page = 'admin';
            break;
        case 'View Published Hikes':
            page = 'viewPubs';
            // no script yet
            break;
        case 'Show Favorites':
            let favpg = '../pages/favTable.php';
            window.open(favpg, '_self');
            break;
        case 'Create New Page':
            page = 'new';
            break;
        case 'Continue Editing Your Pages':
            page = 'existing';
            break;
        case 'Edit Your Published Page':
            page = 'published';
            break;
        case 'Submit for Publication':
            page = 'Under Construction';
            // no script yet
            break;
        case 'Log in':
            if (user_cookie_state === 'EXPIRED') {
                var renew = confirm("Your password has expired\n" +
                    "Would you like to renew?");
                if (renew) {
                    renewPassword('renew');
                } else {
                    renewPassword('norenew');
                }
            } else if (user_cookie_state === 'RENEW') {
                var renew = confirm("Your password is about to expire\n" + 
                    "Would you like to renew?");
                if (renew) {
                    renewPassword('renew');
                } else {
                    renewPassword('norenew');
                }
            } else {
                // state = OK || NOLOGIN || NONE || MULTIPLE
                if (user_cookie_state === 'NONE' || user_cookie_state === 'MULTIPLE') {
                    alert("You cannot be logged in at this time");
                    return;
                } else { // NOLOGIN => cookies off, or no cookie (e.g. rejected)
                    window.open('../accounts/unifiedLogin.php?form=log', '_blank');
                }
            }
            return; 
        case 'Log out':
            $.get({
                url: '../accounts/logout.php',
                success: function() {
                    alert("You are logged out");
                    notLoggedInItems();
                    $('#ifadmin').css('display', 'none');
                    window.open('../index.html', '_self');
                }
            });
            break;
        case 'Change Password':   
        case 'Forgot Password':
            modal.open(
                {content: lost_password, height: '140px', width: '240px',
                id: 'resetpass'}
            );
            return;
        case 'Become a Member':
            window.open('../accounts/unifiedLogin.php?form=reg', '_blank');
            return;
        case 'About this site':
            window.open('../pages/about.php', '_blank');
            break;
        case 'Contact Us':
            document.getElementById("support").click(); 
            break;
        case 'Accept Cookies':
            let accept = {choice: 'accept'};
            $.ajax({
                url: '../accounts/member_cookies.php',
                method: 'post',
                dataType: 'text',
                data: accept,
                success: function() {
                    window.location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
            return;
        case 'Reject Cookies':
            let reject = {choice: 'reject'};
            $.ajax({
                url: '../accounts/member_cookies.php',
                method: 'post',
                dataType: 'text',
                data: reject,
                success: function() {
                    window.location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    var newDoc = document.open();
                    newDoc.write(jqXHR.responseText);
                    newDoc.close();
                }
            });
            return;
        case 'Privacy Policy':
            let policy = '../php/postPDF.php?doc=../accounts/PrivacyPolicy.pdf';
            window.open(policy, '_blank');
            return;
        default:
            alert(content);
    }
    if (typeof page !== 'undefined') {
        $.get({
            url: '../php/opener.php?page=' + page,
            dataType: "html",
            success: function(redir) {
                $('body').after(redir);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var newDoc = document.open();
                newDoc.write(jqXHR.responseText);
                newDoc.close();
            }
        });
    }
}
$(".menus").menu({
    select: function(evt, ui) {  // ui is object whose [item]is a jQuery obj
        var itemText = ui.item.text();
        var $itemDiv = ui.item.children().eq(0);
        if (!$itemDiv.hasClass('ui-state-disabled')) {
            gotoPage(itemText);
        }
    }
});
$(".ui-menu").css('background-color', 'honeydew');
$mainMenus.each(function(indx) {
    var pos = $(this).offset();
    var left = pos.left;
    var menuId = '#menu-' + this.id;
    $(menuId).css('left', left);
    $(menuId).css('top', navBottom);
    $(menuId).width(menuWidth[indx]);
    $(this).on('mouseover', function() {
        $(this).find('.menu-item').find('.menuIcons').removeClass('menu-open');
        $(this).find('.menu-item').find('.menuIcons').addClass('menu-close');
        $(menuId).removeClass('menu-default');
        $(menuId).addClass('menu-active');
        $(menuId).show();
    });
    $(this).on('mouseout', function() {
        $(this).find('.menu-item').find('.menuIcons').removeClass('menu-close');
        $(this).find('.menu-item').find('.menuIcons').addClass('menu-open');
        $(menuId).removeClass('menu-default');
        $(menuId).addClass('menu-default');
        $(menuId).hide();
    });
});

});  // end document ready
