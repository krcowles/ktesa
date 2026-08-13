/// <reference types="jquery" />
interface indexedDBCaches {
    code: string;
    tiles: string;
}
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
const CACHE = {
    code: 'map_source',
    tiles: 'map_tiles'
} as indexedDBCaches

$(function() {

const appMode = $('#appMode').text();

/**
 * With the release of the new mobile app for members, a modal will popup on the home page only
 * announcing the app availability. A member can "Don't show again", proceed to the app 
 * acquisition modal, or simply ignore the modal in which case it will appear again next visit.
 * The value of #startup_modal is only on home.php
 */
const announce = new bootstrap.Modal(<HTMLElement>document.getElementById('announce'));
const offline_app = new bootstrap.Modal(<HTMLElement>document.getElementById('offline'));
const installer = new bootstrap.Modal(<HTMLElement>document.getElementById('install_instructions'));


if($('#active').text() === 'Landing' && $('#startup_modal').text() === 'show') {
    $('#mobile_men').show();
    $('#panel_menu').hide();
    announce.show();
}
$('#offline_app').on('click', function(ev) {
    ev.preventDefault();
    offline_app.show();
});
// Modal buttons
$('#no_show').on('click', () => {
    $.ajax({
        url: '../php/setAppField.php',
        method: 'post',
        data: {value: 'noshow'},
        success: function() {
            // 'app' field is set to 'noshow'
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = "An error has occurred: " +
                    "We apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later";
                alert(msg);
                var ajaxerr = "panelMenujs: Trying to set 'noshow' in 'app' " +
                    "field of MEMBER_PREFS\n" +
                    "Error text: " + _textStatus + "; Error: " +
                    _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
        }
    });
    announce.hide();
});
$('#yes_get').on('click', () => {
    announce.hide();
    offline_app.show();
});
$('#get_app').on('click', () => {
    let ios = document.getElementById('ios') as HTMLInputElement;
    let android = document.getElementById('android') as HTMLInputElement;
    let type = "unspecified";
    if (ios.checked) {
        type = "ios";
        $('ios_phone').css('display', 'block');
        $('#android_phone').css('display', 'none');
    }
    if (android.checked) {
        type = "android";
        $('#ios_phone').css('display', 'none');
        $('#android_phone').css('display', 'block');
    }
    if (type === 'unspecified') {
        alert("No phone type specified");
        return false;
    }
    $('#installTo').text(type);
    $('#os').text(type);
    offline_app.hide();
    installer.show();
    return;
});
let submitBtn = document.getElementById('submit_req') as HTMLButtonElement;
submitBtn.addEventListener('click', () => {
    let overlay = document.getElementById('gifOverlay') as HTMLDivElement;
    overlay.classList.remove('d-none');
    let dist_email;
    let os = $('#os').text();
    if (os === 'ios') {
        let email_addr = document.getElementById('email') as HTMLInputElement;
        if (!email_addr.validity.valid) {
            alert("Not a valid email address");
            return false;
        }
        if (email_addr.value === '') {
            alert("No email address was entered");
            return false;
        }
        dist_email = email_addr.value;
    } else {
        dist_email = 'none';
    }
    let ajaxdata = {email: dist_email, phone: os};
    $.ajax({
        url: '../php/appDistribution.php',
        method: 'post',
        data: ajaxdata,
        success: function() {
            installer.hide();
            overlay.classList.add('d-none');
            alert("Admin will process your request");
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = "An error has occurred: " +
                    "We apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later";
                alert(msg);
                var ajaxerr = "panelMenujs: Trying to send admin mail re app" +
                    "Error text: " + _textStatus + "; Error: " +
                    _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
        }

    });
    $.ajax({
        url: '../php/setAppField.php',
        method: 'post',
        data: {value: os},
        success: function() {
            // 'app' field is set to the phone type: ios or android
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = "An error has occurred: " +
                    "We apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later";
                alert(msg);
                var ajaxerr = "panelMenujs: Trying to set 'noshow' in 'app' " +
                    "field of MEMBER_PREFS\n" +
                    "Error text: " + _textStatus + "; Error: " +
                    _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
        }
    });
    return;
});

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
                url: '../accounts/logout.php?expire=N',
                method: "get",
            success: function () {
                alert("You are logged out...");
            },
            error: function () {
                alert("Something went wrong!");
            }
        });    
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

});

