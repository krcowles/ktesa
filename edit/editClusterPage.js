"use strict";
/**
 * @fileoverview Supply button functionality (as they are outside of form)
 * Basic data validation.
 *
 * @author Ken Cowles
 *
 * @version 2.0 First release with Cluster Page editing
 * @version 2.1 Typescripted
 */
$(function () {
    // initialize select box display (blank if none saved)
    var indxNo = $('input[name=indxNo]').val();
    var locale = $('#locale').text();
    $('#area').val(locale);
    // prevent enter key from submitting form:
    $('form').find('.ta').on('keydown', function (ev) {
        var retval = true;
        if (ev.key == "Enter") {
            retval = false;
        }
        return retval;
    });
    /**
     * This function will loosely validate the data in the lat/lng boxes;
     * An empty box constitutes valid data.
     */
    var validateLatLng = function () {
        var pglat = $('input[name=lat]').val();
        var pglng = $('input[name=lng]').val();
        // check for non-numerics
        var decimal = /^[-+]?[0-9]+\.[0-9]+$/;
        if (!decimal.test(pglat)) {
            alert("You must enter a decimal number for latitude");
            return false;
        }
        if (!decimal.test(pglng)) {
            alert("You must enter a decimal number for longitude");
            return false;
        }
        var lat = parseFloat(pglat);
        var lng = parseFloat(pglng);
        // valid number?
        if (isNaN(lat)) {
            alert("The latitude entry is not a valid number");
            return false;
        }
        if (isNaN(lng)) {
            alert("The longitude entry is not a valid number");
            return false;
        }
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
        if (lng < -109.25 || lng > -102.96) {
            alert("This longitude is outside of New Mexico");
            return false;
        }
        return true;
    };
    // Buttons:
    $('#preview').on('click', function (ev) {
        ev.preventDefault();
        var cpviewer = '../pages/hikePageTemplate.php?age=new' +
            '&clus=y&hikeIndx=' + indxNo;
        window.open(cpviewer, "_blank");
    });
    $('#submit').on('click', function (ev) {
        if (!validateLatLng()) {
            return false;
        }
    });
    var dirtxt = $('#dirs').val();
    if (dirtxt.indexOf("INVALID") !== -1) {
        $('#dirs').css('color', 'brown');
        $('#dirs').on('focus', function () {
            $(this).css('color', 'black');
        });
    }
});
