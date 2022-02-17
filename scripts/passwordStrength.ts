/**
 * @fileoverview Check total password strength as it is entered by the registrant.
 * If the length and other criteria are not met, do not allow submission. Minimum length
 * is 10 char and each of the four conditions listed must be met.
 * 
 * @author Ken Cowles
 * @version 1.0 First pass checker
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
var pfocus = false;
var current_password = '';
var latest_password = '';
$('.signup').val('');

const addKey = (type:string, key:string) => {
    let cnt = 0;
    switch(type) {
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
    let id = "#" + type;
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
const keyChecker = (ev:KeyboardEvent) => {
    let thiskey = ev.key;
    if (thiskey !== "Shift") {
        /**
         * When the user clicks on a backspace, track the changes!
         */
        if (thiskey === "Backspace") {
            let lastchar = latest_password.slice(-1);
            total -= 1;
            $('#total').text(total);
            if (total < 10) {
                $('#total').css('color', 'maroon');
            }
            if (ucalpha.test(lastchar)) {
                uc -= 1;
                $('#uc').text(uc);
                if (uc === 0) {
                    $('#sp').css('color', 'maroon');
                }
            } else if (lcalpha.test(lastchar)) {
                lc -= 1;
                $('#lc').text(lc);
                if (lc === 0) {
                    $('#lc').css('color', 'maroon');
                }
            } else if (numchar.test(lastchar)) {
                nm -= 1;
                $('#nm').text(nm);
                if (nm === 0) {
                    $('#nm').css('color', 'maroon');
                }
            } else if (spcchar.test(lastchar)) {
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
            current_password = current_password.slice(0, -1);
            latest_password = current_password;
        } else if (thiskey.length === 1) {
            if (lcalpha.test(thiskey)) {
                addKey('lc', thiskey);
            } else if (ucalpha.test(thiskey)) {
                addKey('uc', thiskey);
            } else if (numchar.test(thiskey)) {
                addKey('nm', thiskey);
            } else if (spcchar.test(thiskey)) {
                addKey('sp', thiskey);
            }
        } 
    }
    return;
};
$('.renpass').on('focus', function() {
    pfocus = true;
    document.addEventListener('keydown', keyChecker);
    return;

});
$('.renpass').on('blur', function() {
    if (pfocus) {
        document.removeEventListener('keydown', keyChecker);
    }
    pfocus = false;
    return;
});
