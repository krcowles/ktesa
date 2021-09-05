"use strict";
/**
 * @fileoverview This file manages display options for selecting a time frame
 * for viewing visitor data
 *
 * @author Ken Cowles
 * @version 1.0
 */
$('#begin').datepicker({
    dateFormat: "yy-mm-dd"
});
$('#end').datepicker({
    dateFormat: "yy-mm-dd"
});
var thisyr = $('#curr_yr').text();
var selected = '';
var mosel;
var daysel;
var start;
var end;
var ajaxdata;
// display the options when a year is selected
$('#strt_yr').on('change', function () {
    selected = $('#strt_yr option:selected').text();
    if (selected !== 'Select Year') {
        if (selected !== thisyr) {
            $('#sglmo').append(longmos);
        }
        else {
            $('#sglmo').append(shortmos);
        }
        $('#sglmo').append(onemnth);
        // on first display, monthsel will be January:
        $('#sgldays').append(addmax);
        $('#opts').show();
    }
    else {
        $('#sglmo').empty();
        $('#opts').hide();
    }
});
$('body').on('change', '#yr1', function () {
    $('#sglday').children().eq(0).replaceWith(mindays);
    mosel = parseInt($('#yr1 option:selected').val());
    var intsel = parseInt(selected);
    var days = daysInMonth(mosel, intsel);
    var adder = days - 28;
    if (adder === 1) {
        $('#sgldays').append(addone);
    }
    else if (adder === 2) {
        $('#sgldays').append(addtwo);
    }
    else if (adder === 3) {
        $('#sgldays').append(addmax);
    }
});
// When 'Display Month' is clicked
$('body').on('click', '#onemo', function () {
    clearData();
    mosel = parseInt($('#yr1 option:selected').val());
    // 'Display Month' is only visible when a valid year has been selected
    var datemo = mosel.toString();
    if (mosel < 10) {
        datemo = '0' + datemo;
    }
    var intsel = parseInt(selected);
    start = selected + '-' + datemo + '-01';
    var noOfDays = daysInMonth(mosel, intsel);
    var days = noOfDays.toString();
    if (noOfDays < 10) {
        days = '0' + days;
    }
    end = selected + '-' + datemo + '-' + days;
    ajaxdata = { start: start, end: end };
    getVisitorData(ajaxdata);
});
// When 'Display Day' is clicked
$('body').on('click', '#oneday', function () {
    clearData();
    var month = $('#yr1 option:selected').val();
    if (parseInt(month) < 10) {
        month = '0' + month;
    }
    daysel = $('#sgldays option:selected').text();
    if (parseInt(daysel) < 10) {
        daysel = '0' + daysel;
    }
    start = selected + '-' + month + '-' + daysel;
    end = selected + '-' + month + '-' + daysel;
    ajaxdata = { start: start, end: end };
    getVisitorData(ajaxdata);
});
// When 'Display Range' is clicked (always displayed)
$('#range').on('click', function () {
    clearData();
    start = $('#begin').val();
    end = $('#end').val();
    ajaxdata = { start: start, end: end };
    getVisitorData(ajaxdata);
});
/**
 * This function returns the number of days in a month
 */
var daysInMonth = function (month, year) {
    return new Date(year, month, 0).getDate();
};
/**
 * Clear out any existing results
 *
 */
var clearData = function () {
    if ($('#vdat').length > 0) {
        $('#vdat').remove();
    }
    if ($('#nodat').length > 0) {
        $('#nodat').remove();
    }
};
/**
 * This is the function that extracts visitor data for display on the page
 */
var getVisitorData = function (postdata) {
    $('#loading').show();
    $.ajax({
        url: 'getVisitorData.php',
        method: 'post',
        data: postdata,
        dataType: 'html',
        success: function (results) {
            $('#loading').hide();
            $('body').append(results);
        },
        error: function (jqXHR) {
            var newDoc = document.open();
            newDoc.write(jqXHR.responseText);
            newDoc.close();
        }
    });
};
