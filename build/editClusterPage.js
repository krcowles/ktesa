"use strict"
/**
 * @fileoverview Supply button functionality (as they are outside of form)
 * Basic data validation. NOTE: The variables 'authors', 'titles', and
 * 'state' are embedded in the page.
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 * 
 * @version 2.0 First release with Cluster Page editing
 */
$( function () { // when page is loaded...

// initialize select box display (blank if none saved)
var indxNo = $('input[name=indxNo]').val();
var locale = $('#locale').text();
$('#area').val(locale);

// prevent enter key from submitting form:
$('form').find('.ta').on('keydown', function(ev) {
    if (ev.key == "Enter") {
        return false;
    }
});

/**
 * This function will loosely validate the data in the lat/lng boxes;
 * An empty box constitutes valid data.
 * 
 * @return {boolean}
 */
const validateLatLng = () => {
    let lat = $('input[name=lat]').val(); // currently a string value
    let lng = $('input[name=lng]').val();
    // valid data?
    if (isNaN(lat)) {
        alert("The latitude entry is not a valid number");
        return false;
    }
    if (isNaN(lng)) {
        alert("The longitude entry is not a valid number");
        return false;
    }
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    if (lng > 0) {
        alert("Longitude must be a negative decimal number");
        return false;
    }
    if (lat % 1 === 0) {
        alert("For latitude, Please enter a decimal number");
        return false;
    }
    if (lng % 1 === 0) {
        alert("For longitude, Please enter a decimal number");
        return false;
    }
    if (lat > 37.0 || lat < 31.316) {
        alert("This latitude is outside of New Mexico");
        return false;
    }
    if (colngord < -109.25 || lng > -102.96) {
        alert("This longitude is outside of New Mexico");
        return false;
    }
    return true;
}

// Buttons:
$('#preview').on('click', function(ev) {
    ev.preventDefault();
    let cpviewer = '../pages/hikePageTemplate.php?age=new' +
        '&clus=y&hikeIndx=' + indxNo;
    window.open(cpviewer, "_blank");
});

$('#submit').on('click', function() {
    if (!validateLatLng()) {
        return false;
    }
    $('form').trigger('submit');
});

let dirtxt = $('#dirs').val();
if (dirtxt.indexOf("INVALID") !== -1) {
    $('#dirs').css('color', 'brown');
    $('#dirs').on('focus', function() {
        $(this).css('color', 'black');
    });
}

});