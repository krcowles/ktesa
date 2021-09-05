declare var longmos: string; // embedded on visitor_data.php page
declare var shortmos: string;
declare var onemnth: string;
declare var mindays: string;
declare var addone: string;
declare var addtwo: string;
declare var addmax: string;
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
var mosel: number;
var daysel: string;
var start: string;
var end: string;
var ajaxdata: object;
// display the options when a year is selected
$('#strt_yr').on('change', function() {
    selected = $('#strt_yr option:selected').text();
    if (selected !== 'Select Year') {
        if (selected !== thisyr) {
            $('#sglmo').append(longmos);
        } else {
            $('#sglmo').append(shortmos);
        }
        $('#sglmo').append(onemnth);
        // on first display, monthsel will be January:
        $('#sgldays').append(addmax);
        $('#opts').show();
    } else {
        $('#sglmo').empty();
        $('#opts').hide();
    }
});
$('body').on('change', '#yr1', function() {
    $('#sglday').children().eq(0).replaceWith(mindays);
    mosel = parseInt(<string>$('#yr1 option:selected').val());
    let intsel = parseInt(selected);
    let days = daysInMonth(mosel, intsel);
    let adder = days - 28;
    if (adder === 1) {
        $('#sgldays').append(addone);
    } else if (adder === 2) {
        $('#sgldays').append(addtwo);
    } else if (adder === 3) {
        $('#sgldays').append(addmax);
    }
});
// When 'Display Month' is clicked
$('body').on('click', '#onemo', function() {
    clearData();
    mosel = parseInt(<string>$('#yr1 option:selected').val());
    // 'Display Month' is only visible when a valid year has been selected
    let datemo = mosel.toString();
    if (mosel < 10) {
        datemo = '0' + datemo;
    }
    let intsel = parseInt(selected);
    start = selected + '-' + datemo + '-01';
    let noOfDays = daysInMonth(mosel, intsel);
    let days = noOfDays.toString();
    if (noOfDays < 10) {
        days = '0' + days;
    }
    end = selected + '-' + datemo + '-' + days;
    ajaxdata = {start: start, end: end};
    getVisitorData(ajaxdata);
});
// When 'Display Day' is clicked
$('body').on('click', '#oneday', function() {
    clearData();
    let month = <string>$('#yr1 option:selected').val();
    if (parseInt(month) < 10) {
        month = '0' + month;
    }
    daysel = $('#sgldays option:selected').text();
    if (parseInt(daysel) < 10) {
        daysel = '0' + daysel;
    }
    start = selected + '-' + month + '-' + daysel;
    end   = selected + '-' + month + '-' + daysel;
    ajaxdata = {start: start, end: end};
    getVisitorData(ajaxdata);
});
// When 'Display Range' is clicked (always displayed)
$('#range').on('click', function() {
    clearData();
    start = <string>$('#begin').val();
    end = <string>$('#end').val();
    ajaxdata = {start: start, end: end};
    getVisitorData(ajaxdata);
});
/**
 * This function returns the number of days in a month
 */
const daysInMonth = (month: number, year: number): number => {
    return new Date(year, month, 0).getDate();
}
/**
 * Clear out any existing results
 *
 */
const clearData = (): void => {
    if ($('#vdat').length > 0) {
        $('#vdat').remove();
    }
    if ($('#nodat').length > 0) {
        $('#nodat').remove();
    }
}
/**
 * This is the function that extracts visitor data for display on the page
 */
const getVisitorData = (postdata: object): void => {
    $('#loading').show();
    $.ajax({
        url: 'getVisitorData.php',
        method: 'post',
        data: postdata,
        dataType: 'html',
        success: function(results) {
            $('#loading').hide();
            $('body').append(results);
        },
        error: function(jqXHR) {
            var newDoc = document.open();
		    newDoc.write(jqXHR.responseText);
		    newDoc.close();
        }
    });
}