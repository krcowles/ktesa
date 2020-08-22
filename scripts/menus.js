/**
 * @fileoverview This script controls actions of the jQuery-UI-based menu
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 Redesigned login for security improvement
 */
$(function() {  // document ready function

var menuWidth = ['140', '180', '140', '140']; // calculate these later...
var subWidth = ['140']; // ditto: unused as there is only one sub-menu
var $mainMenus = $('.menu-main');
var navPos = $('#navbar').offset();
var navBottom = navPos.top + $('#navbar').height() + 5 + 'px';
// usr_login div hidden during page load to prevent display when alerts appear
$('#usr_login').css('display', 'block');
var login_content = $('#usr_login').detach();
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
        $('#newpg').prepend(icon);
        break;
    case "Edit":
        $('#editor').prepend(icon);
        break;
    case "EditPub":
        $('#pubtbl').prepend(icon);
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
        case 'Create New Hike':
            page = 'new';
            break;
        case 'Continue Editing Your Hikes':
            page = 'existing';
            break;
        case 'Edit Your Published Hike':
            page = 'published';
            break;
        case 'Submit for Publication':
            page = 'Under Construction';
            // no script yet
            break;
        case 'Log in':
            modal.open(
                {content: login_content, height: '156px', width: '316px',
                    id: 'logins'}
            );
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
        case 'Become a Member':
            page = 'register';
            break;
        case 'About this site':
            window.open('../pages/about.php', '_self');
            break;
        case 'Contact us':
            // code ready, but holding off for now
            /*
            modal.open(
                {content: support, height: '150px', width: '340px',
                    id: 'contact'}
            );
            */
            break;
        case 'Accept Cookies':
            $.ajax({
                url: '../accounts/member_cookies.php?choice=accept',
                method: 'get',
                dataType: 'html',
                success: function() {

                },
                error: function() {

                }
            });
            break;
        case 'Reject Cookies':
            $.ajax({
                url: '../accounts/member_cookies.php?choice=reject',
                method: 'get',
                dataType: 'html',
                success: function() {

                },
                error: function() {

                }
            });
            break;
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
                var msg = 'Error encountered: ' + textStatus + 
                    '; Error code: ' + errorThrown;
                alert(msg);
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
