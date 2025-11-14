/// <reference types="jquery" />
/**
 * @fileoverview This script performs basic menu operations and page setup
 * for the landing site. Due to the fact that there is no mobileNavbar.php
 * and accompanying navMenu.js, this script supplies member activities
 * separately.
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First responsive design implementation
 * @version 1.1 Typescripted
 * @version 2.0 Rescripted due to changes in bootstrap causing menu issues
 * @version 3.0 Rescripted for offline maps presentation
 */
$(function() {

// Show benefits or not...
const logo = document.getElementById('logo') as HTMLDivElement;
const logo_ht = logo.clientHeight as number;
const welcome = document.getElementById('welcome') as HTMLHeadElement;
const welcome_ht = welcome.clientHeight;
const opts = document.getElementById('opts') as HTMLParagraphElement;
const opts_ht = opts.clientHeight;
const user_ht = $('.usr_choices').height() as number;
const consumed_space = logo_ht + welcome_ht + opts_ht + user_ht;
var view_space = window.innerHeight;
var bene_space = $('#bennies').height() as number;
var bene_alloc = view_space - consumed_space;
if (bene_alloc <= bene_space) {
    $('#bennies').hide();
}

const member = $('#cookie_state').text() === 'OK' ? true : false;
if (!member) {
    // disable choices 3 & 4
    const c3pos = $('#choice3').offset() as JQuery.Coordinates;
    const c4pos = $('#choice4').offset() as JQuery.Coordinates;
    const saver = document.getElementById('choice3') as HTMLDivElement;
    const useit = document.getElementById('choice4') as HTMLDivElement;
    const svwidth = saver.offsetWidth;
    const uswidth = useit.offsetWidth;
    const svheight = saver.clientHeight;
    const usheight = useit.clientHeight;
    const block3 = "<div class='blocks' style='position:absolute;background-color:gainsboro;" +
        "opacity:0.7;z-index:100;width:" + svwidth + "px;height:" + svheight + "px;top:" +
        Math.round(c3pos.top) + "px;left:" + Math.round(c3pos.left) + "px;'><div>";
    const block4 = "<div class='blocks' style='position:absolute;background-color:gainsboro;" +
        "opacity:0.7;z-index:100;width:" + uswidth + "px;height:" + usheight + "px;top:" +
        Math.round(c4pos.top) + "px;left:" + Math.round(c4pos.left) + "px;'><div>";
    $('body').append(block3);
    $('body').append(block4);
} else {
    // establish a localStorage item to save the list of browser maps
    if (localStorage.getItem('mapnames') === null) {
            localStorage.setItem('mapnames', 'none');
    }
}

$('#membership').on('change', function() {
    var id = $(this).find("option:selected").attr("id");
    var newloc: string;
    switch(id) {
        case 'bam':
            newloc = "../accounts/unifiedLogin.php?form=reg";
            window.open(newloc, "_self");
            break;
        case 'login':
            newloc = "../accounts/unifiedLogin.php?form=log";
            window.open(newloc, "_self");
            break;
        case 'logout':
            $.ajax({
                url: '../accounts/logout.php?expire=N&mobile=T',
                method: "get",
                success: function () {
                    window.open("../index.html", "_self");
                },
                error: function () {
                    alert("Something went wrong!");
                }
            });
            break;
        default:
            alert("This should never happen!");
    }
});
$(window).on('resize', function () {
    window.open("../index.html", "_self");
});
/**
 * Page links
 */
$('#choice1').on('click', function () {
    window.open("../pages/responsiveTable.php", "_self");
});
$('#choice2').on('click', function () {
    window.open("../pages/mapOnly.php", "_self");
});
$('#choice3').on('click', function() {
    window.open("../pages/saveOffline.php?logo=no");
});
$('#choice4').on('click', function() {
    window.open("../pages/useOffline.html");
});
$('.blocks').on('click', function() {
    alert("Members only: sign up for a free membership!");
});

});

