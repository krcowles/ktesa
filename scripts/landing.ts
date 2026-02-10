/// <reference types="jquery" />
declare function deleteNamedCache(cache: string): void;
/**
 * @fileoverview This script performs basic menu operations and page setup
 * for the landing site. Due to the fact that there is no mobileNavbar.php
 * and accompanying navMenu.js, this script supplies membership selections
 * independently. This script is invoked by both memeber and nonmember
 * landing sites.
 * 
 * @author Ken Cowles
 * 
 * @version 1.0 First responsive design implementation
 * @version 1.1 Typescripted
 * @version 2.0 Rescripted due to changes in bootstrap causing menu issues
 * @version 3.0 Rescripted for offline maps presentation
 */
$(function() {

// Show benefits, depending on available space...
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
            if (member) {
                var ans = confirm("Logging out will delete any saved " +
                    "offline maps. Proceed?");
                if (ans) {
                    deleteNamedCache(CACHE_NAMES.code);
                    deleteNamedCache(CACHE_NAMES.tiles);
                    clearObjectStore();
                    localStorage.removeItem('mapnames');
                    localStorage.removeItem('current_ver');
                    var proceed = $.Deferred();
                    navigator.serviceWorker.getRegistrations()
                    .then( async function(registrations) { 
                        if (registrations.length !== 0) {
                            var i = 0;
                            for(let registration of registrations) {
                                i++;
                                if (i === 1) { // In case of multiple service_workers 
                                    var unreg = await registration.unregister();
                                    if (!unreg) {
                                        console.log("Not unregistered...");
                                    }
                                }
                            }
                        }
                        proceed.resolve()
                    });
                    $.when(proceed).then( async () => {
                        $.ajax({
                            url: '../accounts/logout.php?expire=N',
                            method: "get",
                            success: function () {
                                window.open("https://nmhikes.com/pages/nonmember_landing.html", "_self");
                            },
                            error: function () {
                                alert("Something went wrong!");
                            }
                        });    
                    });
                }
            }
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
    window.open("../pages/saveOffline.php?logo=no", "_self");
});
$('#choice4').on('click', function() {
    window.open("../pages/useOffline.html", "_self");
});
$('.blocks').on('click', function() {
    alert("Members only: sign up for a free membership!");
});

});

