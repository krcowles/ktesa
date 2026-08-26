/// <reference types="bootstrap" />
declare var mobile: boolean;
interface LockResults {
    result: string;
    minutes: number;
}
/**
 * @fileoverview Mobile Navbar menu actions where href="#"
 * 
 * @author Ken Cowles
 * @version 1.0 First release of responsive design
 * @version 1.1 Typescripted
 * @version 1.2 Updated logout menu to reflect state of 'mobile' var
 * @version 1.3 Updated ajax error handling
 * @version 2.0 Eliminate old offline maps method, add new 'Get Mobile App' method
 */

$(function() { // document ready function
/**
 * Menu setup
 */
var appMode = $('#appMode').text() as string;
// Modals
var chg_modal = new bootstrap.Modal(<HTMLElement>document.getElementById('cpw'));
var lockout    = new bootstrap.Modal(<HTMLElement>document.getElementById('lockout'));
var ajaxerror  = new bootstrap.Modal(<HTMLElement>document.getElementById('ajaxerr'));

const offline_app = new bootstrap.Modal(<HTMLElement>document.getElementById('offline'));
const installer = new bootstrap.Modal(<HTMLElement>document.getElementById('install_instructions'));

$('#offline_app').on('click', function(ev) {
    ev.preventDefault();
    offline_app.show();
});
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
});
$('#yes_get').on('click', () => {
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

/**
 * Menu operation
 */
$('#login').on('click', function() {
    $.get('../accounts/lockStatus.php', function(lock_status: LockResults) {
        if (lock_status.result !== "ok") {
            $('.lomin').text(lock_status.minutes);
            lockout.show();
        } else {
            localStorage.removeItem('lockout');
            window.open("../accounts/unifiedLogin.php?form=log", "_self");
        }
    }, "json");
});
$('#force_reset').on('click', function() { // button in 'lockout' modal
    //lockout.hide();
    chg_modal.show();
    return;
});
$('#logout').on('click', (ev) => {
    ev.preventDefault();
    $.ajax({
        url: '../accounts/logout.php?expire=N',
        method: "get",
        success: function() {
            alert("You have been successfully logged out");
            window.open('../index.html', '_self');
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
                alert("Logout unsuccessful: Admin notified");
                let msg = "panelMenu.js:failure to logout (logout.php)";
                $.post('../php/ajaxError.php', msg);
        }
    });
});
$('#chg').on('click', (ev) => {
    ev.preventDefault();
    chg_modal.show();
});
$('#send').on('click', function(ev) { // button in chg_modal
    ev.preventDefault();
    let email = $('#cpwmail').val();
    let data = {form: 'chg', email: email};
    $.ajax({
        url: '../accounts/resetMail.php',
        data: data,
        dataType: 'text',
        method: 'post',
        success: function(result) {
            if (result === 'OK') {
                alert("An email has been sent: these sometimes " +
                    "take awhile\nYou are logged out and can log in" +
                    " again\nwhen your email is received");
                $.get({
                    url: '../accounts/logout.php',
                    success: function() {
                        window.open('../pages/landing.php', '_self');
                    }
                });
                chg_modal.hide();
            } else {
                alert(result);
            }
        },
        error: function() {
            ajaxerror.show();
            let err ={err: "Mobile - resetMail.php error"};
            $.post('../php/ajaxError.php', err);
        }
    });
});
$('#offline_app').on('click', () => {
    offline_app.show();
});
$('#app_guide').on('click', () => {
    let app_link = document.createElement('A') as HTMLAnchorElement;
    app_link.href = "../php/pdf_downloader.php?file=App_guide.pdf"
    app_link.download = "App User Guide.pdf";
    app_link.style.display = "none";
    document.body.appendChild(app_link);
    app_link.click();
    app_link.remove();
});

});