"use strict";
/**
 * @fileoverview Check total password strength as it is entered by the registrant.
 * If the length and other criteria are not met, do not allow submission. Minimum length
 * is 10 char and each of the four conditions listed must be met.
 *
 * @author Ken Cowles
 * @version 5.0 Upgraded login security
 */
var lcalpha = /[a-z]/;
var ucalpha = /[A-Z]/;
var numchar = /[0-9]/;
var spcchar = /\W|_/;
var lc = 0;
var uc = 0;
var nm = 0;
var sp = 0;
var total = 0;
var current_password = '';
var latest_password = '';
$('.signup').val('');
var addKey = function (type, key) {
    var cnt = 0;
    switch (type) {
        case 'lc':
            lc++;
            cnt = lc;
            break;
        case 'uc':
            uc++;
            cnt = uc;
            break;
        case 'nm':
            nm++;
            cnt = nm;
            break;
        case 'sp':
            sp++;
            cnt = sp;
    }
    var id = "#" + type;
    $(id).text(cnt);
    $(id).css('color', 'darkgreen');
    total++;
    $('#total').text(total);
    current_password += key;
    latest_password = current_password;
    if (total >= 10 && lc > 0 && uc > 0 && nm > 0 && sp > 0) {
        $('#wk').hide();
        $('#st').show();
        $('#showdet').css('display', 'none');
    }
};
var deleteKey = function (keychar) {
    $('#total').text(total);
    if (total < 10) {
        $('#total').css('color', 'maroon');
    }
    if (ucalpha.test(keychar)) {
        uc -= 1;
        $('#uc').text(uc);
        if (uc === 0) {
            $('#sp').css('color', 'maroon');
        }
    }
    else if (lcalpha.test(keychar)) {
        lc -= 1;
        $('#lc').text(lc);
        if (lc === 0) {
            $('#lc').css('color', 'maroon');
        }
    }
    else if (numchar.test(keychar)) {
        nm -= 1;
        $('#nm').text(nm);
        if (nm === 0) {
            $('#nm').css('color', 'maroon');
        }
    }
    else if (spcchar.test(keychar)) {
        sp -= 1;
        $('#sp').text(sp);
        if (sp === 0) {
            $('#sp').css('color', 'maroon');
        }
    }
    if (total < 10 || uc === 0 || lc === 0 || nm === 0 || sp === 0) {
        $('#wk').show();
        $('#st').hide();
        $('#showdet').show();
    }
};
var keyChecker = function (ev) {
    var thiskey = ev.key;
    if (thiskey !== "Shift") {
        /**
         * When the user clicks on a backspace, track the changes!
         */
        if (thiskey === "Backspace") {
            var lastchar = latest_password.slice(-1);
            total -= 1;
            deleteKey(lastchar);
            current_password = current_password.slice(0, -1);
            latest_password = current_password;
        }
        else if (thiskey.length === 1) {
            if (lcalpha.test(thiskey)) {
                addKey('lc', thiskey);
            }
            else if (ucalpha.test(thiskey)) {
                addKey('uc', thiskey);
            }
            else if (numchar.test(thiskey)) {
                addKey('nm', thiskey);
            }
            else if (spcchar.test(thiskey)) {
                addKey('sp', thiskey);
            }
        }
    }
    return;
};
/**
 * When a range of text is selected and deleted, this function will adjust
 * the counts. Note: document.getSelection() does not apply to input text
 */
var rangeCheck = function (ev) {
    var thiskey = ev.key;
    if (thiskey === 'Backspace') {
        var newword = $('#password').val();
        if (newword !== current_password) {
            var lgth = newword.length;
            var deleted = current_password.substring(lgth);
            total -= deleted.length;
            for (var j = 0; j < deleted.length; j++) {
                deleteKey(deleted[j]);
            }
            current_password = newword;
            latest_password = newword;
        }
    }
};
// password input has classname 'renpass'
$('.renpass').on('focus', function () {
    document.addEventListener('keydown', keyChecker);
    document.addEventListener('keyup', rangeCheck);
    return;
});
$('.renpass').on('blur', function () {
    document.removeEventListener('keydown', keyChecker);
    document.removeEventListener('keyup', rangeCheck);
    return;
});
