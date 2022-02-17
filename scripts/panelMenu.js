"use strict";
/// <reference types="jqueryui" />
/**
 * @fileoverview This script controls actions of the bootstrap navbar
 *
 * @author Ken Cowles
 *
 * @version 1.0 Introduction of bootstrap navbar for non-mobile platforms
 * @version 5.0 Upgraded security with encryption and 2FA
 */
$(function () {
    // establish the page title in the logo's 'ctr' div
    function logo_title() {
        var pgtitle = $('#trail').detach();
        $('#ctr').append(pgtitle);
    }
    logo_title();
    var requiredAnswers = 3; // number of security questions to be answered
    // Modal handles for panel items:
    var resetPassModal = new bootstrap.Modal(document.getElementById('cpw'));
    var questions = new bootstrap.Modal(document.getElementById('security'));
    /**
     * This function counts the number of security questions and returns
     * true is correct, false (with user alers) if not
     */
    var countAns = function () {
        var acnt = 0;
        $('input[id^=q]').each(function () {
            if ($(this).val() !== '') {
                acnt++;
            }
        });
        if (acnt > requiredAnswers) {
            alert("You have supplied more than " + requiredAnswers + " answers");
            return false;
        }
        else if (acnt < requiredAnswers) {
            alert("Please supply answers to " + requiredAnswers + " questions");
            return false;
        }
        else {
            return true;
        }
    };
    // when page is called, clear any menu items that are/were active
    $('.dropdown-item a').removeClass('active');
    var activeItem = $('#active').text();
    switch (activeItem) {
        case "Home":
            $('#homepg').addClass('active');
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
     * Some menu items require a response that is not simply opening
     * a new window
     */
    $('#logout').on('click', function () {
        $.ajax({
            url: '../accounts/logout.php',
            method: 'get',
            success: function () {
                alert("You have been successfully logged out");
                window.open('../index.html');
            },
            error: function () {
                alert("Failed to execute logout; Admin notified");
                var ajxerr = { err: "Could not execute logout.php" };
                $.post('../php/ajaxError.php', ajxerr);
            }
        });
        return;
    });
    $('#chg').on('click', function () {
        resetPassModal.show();
        return;
    });
    $('#send').on('click', function () {
        var usr_mail = $('#rstmail').val();
        var postdata = { form: "req", email: usr_mail };
        $.post('../accounts/resetMail.php', postdata);
    });
    $('#usrcookies').on('click', function () {
        var cookie_action = $(this).text();
        var action = cookie_action === 'Accept Cookies' ? 'accept' : 'reject';
        var ajaxdata = { choice: action };
        $.ajax({
            url: '../accounts/member_cookies.php',
            method: 'post',
            dataType: 'text',
            data: ajaxdata,
            success: function () {
                window.location.reload();
            },
            error: function () {
                var msg = "Cannot change cookie preference at this time:\n" +
                    "The admin has been notified";
                alert(msg);
                var errobj = { err: msg };
                $.post('../php/ajaxError.php', errobj);
            }
        });
        return;
    });
    $('#updte_sec').on('click', function () {
        $.post('../accounts/usersQandA.php', function (data) {
            $('#uques').append(data);
            questions.show();
        }, "html");
        return;
    });
    $('#resetans').on('click', function () {
        $('input[id^=q]').each(function () {
            $(this).val("");
        });
    });
    $('#closesec').on('click', function () {
        var modq = [];
        var moda = [];
        if (countAns()) {
            $('input[id^=q]').each(function () {
                var answer = $(this).val();
                if (answer !== '') {
                    var qid = this.id;
                    qid = qid.substring(1);
                    modq.push(qid);
                    answer = answer.toLowerCase();
                    moda.push(answer);
                }
            });
            var ques = modq.join();
            var ajaxdata = { questions: ques, an1: moda[0], an2: moda[1], an3: moda[2] };
            $.post('../accounts/updateQandA.php', ajaxdata, function (result) {
                if (result === 'ok') {
                    alert("Updated Security Questions");
                }
                else {
                    alert("Error: could not update Security Questions");
                }
            }, "text");
            questions.hide();
        }
    });
}); // end document ready
