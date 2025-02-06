interface LockResults {
    status: string;
    minutes: number;
}
declare var mobile: boolean;
/**
 * @fileoverview This function invokes an ajax call to the
 * authenticate.php script in an effort to validate membership.
 * NOTE: This function is only invoked via unifiedLogin.js (log),
 * and if the home page was opened first.
 * 
 * @author Ken Cowles
 * @version 3.0 Modified for stand-alone login page (previously modal)
 * @version 3.1 Typescripted
 * @version 5.0 Upgraded security with encryption and 2FA
 */
var appMode = $('#appMode').text() as string;
/**
 * This section provides functionality to manage security questions and answers.
 * If a user has not previously provided security info, he/she will be able to
 * do so when logging in.
 */
var tbl_indx: string; 
var random: number;
var sec0 = false;
// Security Question
var question = new bootstrap.Modal(<HTMLElement>document.getElementById('twofa'));
var updates  = new bootstrap.Modal(<HTMLElement>document.getElementById('security'));
var lockout  = new bootstrap.Modal(<HTMLElement>document.getElementById('lockout'));
var resetPassModal = new bootstrap.Modal(<HTMLElement>document.getElementById('cpw'));
$('#force_reset').on('click', function() { // button in lockout modal
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
                        if (mobile) {
                            window.open('../pages/landing.php', '_self');
                        } else {
                            window.open('../pages/home.php', '_self');
                        }
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
const requiredAnswers = 3;
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
        let ajaxdata = {questions: ques, an1: moda[0], an2: moda[1],
            an3: moda[2], ix: tbl_indx};
        $.post('../accounts/updateQandA.php', ajaxdata, function(result) {
            if (result === 'ok') {
                if (sec0) { // more temporary security updates...
                    let logdata = {ix: tbl_indx};
                    let msg = tbl_indx == '1' || tbl_indx == '2' || tbl_indx == '14' ?
                        'Admin logged in' : 'You are logged in';
                    $.post('../accounts/login.php', logdata, function(status) {
                        if (status === 'OK') {
                            alert(msg);
                            window.open("../index.html", "_self");
                        } else {
                            alert("Could not complete login");
                        }
                    }, "text");
                } else {
                    $('#rvw').removeClass('rvw_new');
                    $('#rvw').addClass('rvw_accpt');
                    alert("Updated Security Questions");

                }
            } else {
                alert("Error: could not update Security Questions");
            }
        }, "text");
        updates.hide();
    }
});
// To complete login, the user must answer a randomly chosen pre-registered question
$('#submit_answer').on('click', function() {
    let usubmitted:string = $('#the_answer').val() as string;
    usubmitted = usubmitted.toLowerCase();
    let postdata = {ix: tbl_indx, rx: random};
    $.post('../accounts/retrieveAnswer.php', postdata, function(ans) {
        var msg = tbl_indx == '1' || tbl_indx == '2' || tbl_indx == '14' ? "Admin logged in" :
            "You are logged in"
        if (usubmitted === ans) {
            $('#the_answer').val("");
            var ajaxdata = {ix: tbl_indx};
            $.post('../accounts/login.php', ajaxdata, function(status) {
                if (status === 'OK') {
                    alert(msg);
                    window.open("../index.html", "_self");
                } else {
                    alert("System error: login not completed");
                } 
            });
        } else {
            alert("Your security answer does not match the expected result");
        }
    }, 'text');
});
/**
 * IF a user's password is up for renewal, he/she may renew via an email
 * link. The user is logged out until the renewal process has completed.
 * If the user chooses not to renew, he/she will be logged out and 
 * the user's information will be deleted from the USERS table.
 */
 const renewPassword = () => {
    var renew = confirm("You must renew your account to continue\n" +
        "Do you wish to renew? You will be asked to change your password");
    if (renew) { // send email to reset password
        var renewp = new bootstrap.Modal(<Element>document.getElementById('cpw'), {
            keyboard: false
        });
        renewp.show();
    } else {
        // When a user does not renew membership,
        // his/her login info is removed from the USERS table 
        $.get({
            url: '../accounts/logout.php?expire=Y',
            success: function() {
                alert("You are permanently logged out\n" +
                    "To rejoin, select 'Become a member' from the menu");
                window.open("../index.html", "_self");
            }
        });
    }
    return;
};

/**
 * The authenticating function. If you are up for renewal, you will be sent
 * to the renewPassword function - this is the only 'path' to the
 * renewPassword utility. If you have a RENEW status from getLogin, you
 * are instructed to login (via Members->Login), as you are automatically
 * logged out via menuControl.ts/js. If your password has expired, you are
 * advised to re-register and your current registration and cookie (if accepted)
 * are removed from the database.
 */
function validateUser(user: string, password: string) {
    let ajaxdata = {usr_name: user, usr_pass: password};
    let validator = "../accounts/authenticate.php";
    $.ajax( {
        url: validator,
        method: "post",
        data: ajaxdata,
        dataType: "json",
        success: function(srchResults) {
            var json = srchResults;
            if (json.status === "LOCATED") {
                tbl_indx = json.ix;
                $.post('../accounts/retrieveQuestion.php', {ix: tbl_indx}, function(qdat) {
                    if (qdat.length === 0) {
                        // this branch is temporary until all users update questions
                        alert("You have not yet registered answers to security\n" +
                            "questions for logins. You will now be able to" +
                            "do so");
                        $('#uques').empty();
                        $.post('../accounts/usersQandA.php', {ix: tbl_indx}, function(body) {
                            sec0 = true;
                            $('#uques').append(body);
                            updates.show();
                        }, "html");
                    } else {
                        sec0 = false;
                        $('#the_question').text(qdat.ques);
                        random = qdat.rindx;
                        question.show();
                        $('#the_answer').trigger('focus');
                    }
                }, 'json');
            } else if (json.status === "Blank field") {
                let ans = confirm("Your registration is not complete: Re-register?\n" 
                    + "[Your currrent username and password will be deleted]");
                if (ans) {
                    $.get("../accounts/logout.php", {redo: 'Y', user: user},
                        function() {
                            window.open("../accounts/unifiedLogin.php?form=reg", "_self");
                        }
                    );
                }
            } else if (json.status === "RENEW") {
                renewPassword();
            } else if (json.status === "EXPIRED") {
                var msg = "Your password has expired\nYou must re-register " +
                    "(Members->Become a member)";
                alert(msg);
                $.get('../accounts/logout.php?expire=Y');
                window.open("../index.html", "_self");
            } else if (json.status === "FAIL") {
                if (json.fail_cnt >= 3) {
                    $('#username').val("");
                    $('#username').css('background-color', 'lightgray');
                    $('#username').prop('disabled', true);
                    $('#password').val("");
                    $('#password').css('background-color', 'lightgray');
                    $('#password').prop('disabled', true);
                    $('#formsubmit').prop('disabled', 'disabled');
                    $('#logger').html("Reset Password");
                    $('.lomin').text("60");
                    $('#lotime').css('display', 'inline');
                    localStorage.setItem('lockout', 'yes');
                    lockout.show();
                    // auto reset fields if page still active
                    var lotimeout = setInterval(function() {
                        $.get("../accounts/lockStatus.php",function(result) {
                            if (result.status === 'ok') {
                                $('#lotime').css('display', 'none');
                                $('#username').css('background-color', 'transparent');
                                $('#username').prop('disabled', false);
                                $('#password').css('background-color', 'transparent');
                                $('#password').prop('disabled', false);
                                $('#formsubmit').prop('disabled', false);
                                lockout.hide();
                                localStorage.removeItem('lockout');
                                clearInterval(lotimeout);
                                alert("You may now login");
                            } else {
                                $('.lomin').text(result.minutes);
                            }
                        }, "json"); 
                    },100000)
                } else {
                    alert("Invalid login credentials: try again");
                }
            }
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
                var ajaxerr = "Trying to access autenticate.php;\nError text: " +
                    _textStatus + "; Error: " + _errorThrown + ";\njqXHR: " +
                    _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }      
        }
    });
    return;
}
