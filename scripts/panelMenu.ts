/// <reference types="jqueryui" />
/**
 * @fileoverview This script controls actions of the bootstrap navbar
 * 
 * @author Ken Cowles
 * 
 * @version 6.0 Added filter & sort functions provided on navbar
 * @version 7.0 Added gpx file editing capability
 * @version 7.1 Added 'Membership Benefits' to navbar
 * @version 7.2 Added 'Latest Additions' to navbar
 * @version 7.3 Updated ajax error handling
 * @version 8.0 Revised login to pre-scan for lockout condition
 */
$(function() {  // document ready function

// establish the page title in the logo's 'ctr' div
function logo_title() {
    var pgtitle = $('#trail').detach();
    $('#ctr').append(pgtitle);
}
logo_title();
const requiredAnswers = 3; // number of security questions to be answered
// Modal handles for panel items:
var resetPassModal = new bootstrap.Modal(<HTMLElement>document.getElementById('cpw'));
var questions = new bootstrap.Modal(<HTMLElement>document.getElementById('security'));
var bymiles   = new bootstrap.Modal(<HTMLElement>document.getElementById('bymiles'));
var byloc     = new bootstrap.Modal(<HTMLElement>document.getElementById('byloc'));
var gpxedit   = new bootstrap.Modal(<HTMLElement>document.getElementById('ged'));
var newpgs    = new bootstrap.Modal(<HTMLElement>document.getElementById('newpgs'));
const membens = new bootstrap.Modal(<HTMLElement>document.getElementById('membennies'));
const $benmo  = $("<div id=movr style='border-style:solid;border-width:1px;border-radius:6px;" +
    "border-color:darkslategray;background-color:khaki;padding-top:2px;padding-left:4px;" +
    "color:darkslategray;'>Free Membership<br />Click for Benefits</div>");
var lockout   = new bootstrap.Modal(<HTMLElement>document.getElementById('lockout'));
/**
 * This function counts the number of security questions and returns
 * true is correct, false (with user alers) if not
 */
 const countAns = () => {
    var acnt = 0;
    $('input[id^=q]').each(function() {
        if ($(this).val() !== '') {
            acnt++
        }
    });
    if (acnt > requiredAnswers) {
        alert("You have supplied more than " + requiredAnswers + " answers");
        return false;
    } else if (acnt < requiredAnswers) {
        alert("Please supply answers to " + requiredAnswers + " questions");
        return false;
    } else {
        return true;
    }
}

// NOTE: Here, appMode is a LOCAL variable for the panel
var appMode = $('#appMode').text() as string;
// when page is called, clear any menu items that are/were active
$('.dropdown-item a').removeClass('active');
var activeItem = $('#active').text();
switch(activeItem) {
    case "Home":
        $('#homepg').addClass('active');
        $('#homepgfilt').css('display', 'inline-block');
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
 * Menu item locations are now set, so establish popup for member benefits
 */
var starpos = $('#benefits').offset() as JQuery.Coordinates;
$benmo.css({
    width: '140px',
    height: '52px',
    position: 'absolute',
    left: starpos.left,
    top: starpos.top + 40
});
$('#benefits').on('mouseover', function() {
    $('body').append($benmo);
});
$('#benefits').on('mouseout', function() {
    $benmo.remove();
});
$('#benefits').on('click', function(ev) {
    ev.preventDefault();
    membens.show();
});
/**
 * Filter and Sort (navbar) operation:
 */
$(".modalsearch").on("autocompleteselect", function(event, ui) {
    // the searchbar dropdown uses 'label', but place 'value' in box & use that
    event.preventDefault();
    var entry = ui.item.value;
    $(this).val(entry);
});
$("#startfromh").on("autocompleteselect", function(event, ui) {
    // the searchbar dropdown uses 'label', but place 'value' in box & use that
    event.preventDefault();
    var entry = ui.item.value;
    $(this).val(entry);
});
$('#fhmiles').on('click', function() {
    bymiles.show();
    return;
});
$('#fhloc').on('click', function() {
    byloc.show();
    return;
});
$('#apply_miles').on('click', function() {
    ascending = true;
    let hike = $('#startfromh').val() as string;
    if (hike === '') {
        alert("You have not selected a hike");
        return false;
    }
    let hmis = parseInt($('#misfromh').val() as string);
    miles_from_hike(hike, hmis);
    bymiles.hide();
    return;
});
$('#apply_loc').on('click', function() {
    ascending = true;
    let poi  = $('#area').val() as string;
    let lmis = parseInt(<string>$('#misfroml').val());
    miles_from_locale(poi, lmis);
    byloc.hide();
    return;
});
$('#sort_rev').on('click', function() {
    ascending = ascending ? false : true;
    if (!sort_diff && !sort_dist) {
        sortableHikes.sort(compareObj);
    } else if (sort_diff) {
        sortableHikes.sort(compareDiff);
    } else {
        sortableHikes.sort(compareDist);
    }
    formTbl(sortableHikes);
});
$('#sort_diff').on('click', function() {
    sort_diff = true;
    sort_dist = false;
    ascending = true;
    sortableHikes.sort(compareDiff);
    formTbl(sortableHikes);
});
$('#sort_dist').on('click', function() {
    sort_diff = false;
    sort_dist = true;
    ascending = true;
    sortableHikes.sort(compareDist);
    formTbl(sortableHikes);
});
/**
 * Functions which simulate the jquery ui 'spinner' widget
 */
$('.uparw').on('click', function() {
    let spinner = $('#pseudospin');
    if (activeItem === 'Home') {
        if ($('#byloc').css('display') !== 'none') {
            spinner = $('#misfroml');
        } else if ($('#bymiles').css('display') !== 'none') {
            spinner = $('#misfromh');
        }
    }
    let current = parseInt(<string>spinner.val());
    let spinup = current >= 50 ? 50 : current + 1; 
    spinner.val(spinup);
});
$('.dwnarw').on('click', function() {
    let spinner = $('#pseudospin');
    if (activeItem === 'Home') {
        if ($('#byloc').css('display') !== 'none') {
            spinner = $('#misfroml');
        } else if ($('#bymiles').css('display') !== 'none') {
            spinner = $('#misfromh');
        }
    }
    let current = parseInt(<string>spinner.val());
    let spindwn = current > 1 ? current -1 : 1;
    spinner.val(spindwn);
});

/**
 * Some menu items require a response that is not simply opening
 * a new window
 */
$('#editgpx').on('click', function() {
    gpxedit.show();
});
$('#edform').on('submit', function() {
    var ifile = $('#file2edit').val();
    if (ifile == '') {
        alert("No file has been selected");
        return false;
    }
    var back = window.location.href;
    var uricode = encodeURIComponent(back);
    $('#backurl').val(uricode);
    gpxedit.hide();
    return;
});
$('#login').on('click', function() {
    $.get('../accounts/lockStatus.php', function(lock_status: LockResults) {
        if (lock_status.status !== "ok") {
            $('.lomin').text(lock_status.minutes);
            lockout.show();
        } else {
            localStorage.removeItem('lockout');
            window.open("../accounts/unifiedLogin.php?form=log");
        }
    }, "json");
});
$('#force_reset').on('click', function() {
    //lockout.hide();
    resetPassModal.show();
    return;
});
$('#send').on('click', function(ev) {
    ev.preventDefault();
    let email = $('#rstmail').val();
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
                        window.open('../pages/home.php', '_self');
                    }
                });
                resetPassModal.hide();
            } else {
                alert(result);
            }
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
            $('#email').css('color', 'red');
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
                    var ajaxerr = "Trying to send 'Reset Password' email\n" +
                        "Error text: " + _textStatus + "; Error: " +
                        _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                    var errobj = { err: ajaxerr };
                    $.post('../php/ajaxError.php', errobj);
                }
        }
    });
});

$('#cookies').on('click', function() {
    let newchoice: string;
    let changeto = $(this).text();
    if (changeto == 'Accept Cookies') {
        newchoice = 'accept';
    } else {
        newchoice = 'reject';
    }
    let change = {choice: newchoice};
    $.ajax({
        url: '../accounts/member_cookies.php',
        method: 'post',
        dataType: 'text',
        data: change,
        success: function() {
            window.location.reload();
        },
        error: function() {
            alert("Error retrieving member cookies - admin notified");
            let err = {err: "Mobile member_cookies.php error"};
            $.post('../php/ajaxError.php', err);
        }
    });
});
$('#logout').on('click', function() {
    $.ajax({
        url: '../accounts/logout.php',
        method: 'get',
        success: function() {
            alert("You have been successfully logged out");
            window.open('../index.html', '_self');
        },
        error: function(_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = alert("Failed to execute logout;\n" +
                    "We apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later");
                alert(msg);
                var ajaxerr = "Trying to logout;\nError text: " +
                    _textStatus + "; Error: " + _errorThrown + "; jqXHR: " +
                    _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
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
        error: function(_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = "Cannot change cookie preference at this time:\n" +
                    "We apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later";
                alert(msg);
                var ajaxerr = "Trying to access [];\nError text: " +
                    _textStatus + "; Error: " + _errorThrown + "; jqXHR: " +
                    _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
        }
    });
    return;
});
$('#updte_sec').on('click', function() {
    $.post('../accounts/usersQandA.php', function(data) {
        $('#uques').empty();
        $('#uques').append(data);
        questions.show();
    }, "html");
    return;
});
$('#resetans').on('click', function() {
    $('input[id^=q]').each(function() {
        $(this).val("");
    });
});
$('#closesec').on('click', function() {
    var modq = <string[]>[];
    var moda = <string[]>[];
    if (countAns()) {
        $('input[id^=q]').each(function() {
            var answer = <string>$(this).val();
            if (answer !== '') {
                let qid = this.id;
                qid = qid.substring(1);
                modq.push(qid);
                answer = answer.toLowerCase();
                moda.push(answer);
            }
        });
        let ques = modq.join();
        let ajaxdata = {questions: ques, an1: moda[0], an2: moda[1], an3: moda[2]};
        $.post('../accounts/updateQandA.php', ajaxdata, function(result) {
            if (result === 'ok') {
                alert("Updated Security Questions");
            } else {
                alert("Error: could not update Security Questions");
            }
        }, "text");
        questions.hide();
    }
});
$()
// In order to be able to close the admintools tab, it must be opened by javascript:
$('#adminpg').on('click', function() {
    window.open("../admin/admintools.php");
});

$('#latest').on('click', function() {
    $.ajax({
        url: '../pages/newHikes.php',
        method: 'post',
        dataType: 'html',
        success: function(list) {
            $('#newest').empty();
            $('#newest').append(list);
            newpgs.show();
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
                var ajaxerr = "Trying to access Latest Hikes\nError text: " +
                    _textStatus + "; Error: " + _errorThrown + ";\njqXHR: " +
                    _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
        }
    });
});

});  // end document ready
