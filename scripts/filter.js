"use strict";
/// <reference types="jqueryui" />
/**
 * @fileoverview This module setups & executes the table's filtering capability
 * @author Ken Cowles
 * @version 2.0 Typescripted, with some type errors corrected
 */
var appMode = $('#appMode').text();
var arealoc = {}; // coordinates of location from which to calculate radius
var mapHikes = []; // save hikes to be drawn together on a new map
var hikearea = $('#area').val(); // top value of select set as a 'primer'
$('body').on('change', '#area', function () {
    hikearea = $(this).val();
});
positionMain();
/**
 * This function will place position elements on the page on page
 * load and during window resize
 */
function positionMain() {
    // Position the options table:
    var table_pos = $('#refTbl').offset();
    $('#divopts').css('left', table_pos.left);
    // Filter options and note
    var winwidth = $(window).innerWidth();
    var tblwidth = $('.sortable').width();
    var margs = Math.floor((winwidth - tblwidth) / 2) + "px";
    $('#tblfilter').css('margin-left', margs);
    $('#tblfilter').css('margin-right', margs);
    $('#filtnote').css('margin-left', margs);
    $('#filtnote').css('margin-right', margs);
    return;
}
/**
 * After selecting an area around which to filter hikes, clicking on the button
 * will perform the filtering
 */
$('#filtpoi').on('click', function () {
    $('#sort1').val("No Sort");
    $('#sort2').val("No Sort");
    var epsilon = $('#pseudospin').val();
    var area = hikearea;
    $.ajax({
        url: '../json/areas.json',
        dataType: 'json',
        success: function (json_data) {
            var areaLocCenters = json_data.areas;
            for (var j = 0; j < areaLocCenters.length; j++) {
                if (areaLocCenters[j].loc == area) {
                    arealoc = {
                        "lat": areaLocCenters[j].lat,
                        "lng": areaLocCenters[j].lng
                    };
                    break;
                }
            }
            filterList(epsilon, arealoc);
            toggleScrollSelect(false);
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
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
                var ajaxerr = "Trying to access areas.json;\nError text: " +
                    _textStatus + "; Error: " + _errorThrown + ";\njqXHR: " +
                    _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
            return false;
        }
    });
});
$('#filthike').on('click', function () {
    $('#sort1').val("No Sort");
    $('#sort2').val("No Sort");
    var epsilon = $('#pseudospin').val();
    var hikeloc = $('#usehike').val();
    if (hikeloc !== '') {
        arealoc = getHikeCoords(hikeloc);
        filterList(epsilon, arealoc);
        toggleScrollSelect(false);
    }
    else {
        alert("You have not selected a hike");
        return;
    }
});
/**
 * This function extracts latitude/longitude form the table for the
 * target hike name.
 */
function getHikeCoords(hike) {
    var $tblrows = $('.sortable tbody tr');
    var coords = {};
    $tblrows.each(function () {
        var hikeLinkText = $(this).find('td').eq(hike_hdr).children().eq(0).text();
        if (hikeLinkText === hike) {
            var hlat = $(this).data('lat');
            var hlon = $(this).data('lon');
            coords = { lat: hlat, lng: hlon };
            return;
        }
    });
    if (Object.keys(coords).length === 0) {
        alert("Hike not found - try new link");
    }
    return coords;
}
/**
 * This function creates the rows for the results table based on the
 * filter parameters (radius from center pt, center pt). After creating
 * the table, it displays the results
 */
function filterList(radius, geo) {
    $('#ftable tbody').empty();
    var ctrlat = geo.lat;
    var ctrlng = geo.lng;
    $('#maintbl tbody tr').each(function () {
        var hikelat = $(this).data('lat');
        var hikelng = $(this).data('lon');
        var distance = radialDist(hikelat, hikelng, ctrlat, ctrlng, 'M');
        if (distance <= radius) {
            // create clone, else node is removed from big table!
            var $clone = $(this).clone();
            $('#ftable tbody').append($clone);
        }
    });
    $('#results').show();
    tableSort('#ftable');
    // data retrieved is always from the current state of #maintbl
    if (ftbl_init) {
        var ftbl_units = $('#units').text();
        if (ftbl_units.indexOf('English') !== -1) {
            curr_ftbl_state = engtxt;
            // currently, table units are Metric: this table must be converted
            var $fbody = $('#ftable').find('tbody');
            convert('#ftable', $fbody, 'Metric');
        }
    }
    else {
        ftbl_init = true;
        curr_ftbl_state = curr_main_state;
    }
    setupConverter('#ftable', false);
    $('#refTbl').hide();
    return;
}
/**
 * This function will return the radial distance between two lat/lngs
 */
function radialDist(lat1, lon1, lat2, lon2, unit) {
    if (lat1 === lat2 && lon1 === lon2) {
        return 0;
    }
    var radlat1 = Math.PI * lat1 / 180;
    var radlat2 = Math.PI * lat2 / 180;
    var theta = lon1 - lon2;
    var radtheta = Math.PI * theta / 180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist);
    dist = dist * 180 / Math.PI;
    dist = dist * 60 * 1.1515;
    if (unit === "K") {
        dist = dist * 1.609344;
    }
    if (unit === "N") {
        dist = dist * 0.8684;
    } // else result is in miles "M"
    return dist;
}
