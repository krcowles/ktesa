"use strict";
/**
 * @fileoverview Provide sideTable filtering capability
 * @author Ken Cowles
 *
 * @version 1.0 Initial release
 */
// ----- GLOBALS -----
// margin on map bounds in px;
var mapMarg = 200;
// values for sorting by difficulty
var diffs = ['Easy', 'Easy-Moderate', 'Moderate', 'Med-Difficult', 'Difficult'];
var diff_val = [1, 10, 100, 1000, 10000];
// Filter by distance from hike
var miles_from_hike = function (hike, epsilon) {
    // get coordinates of hike & container object type
    var hikeloc = getHikeGPS(hike);
    if (Object.keys(hikeloc).length === 0) {
        alert("This is not a single hike");
        return;
    }
    filterByMiles(epsilon, hikeloc);
    return;
};
/**
 * This function extracts latitude/longitude form the table for the
 * target hike name.
 */
function getHikeGPS(hike) {
    var found = false;
    var coords = {};
    if (pgnames.includes(hike)) { // These are 'Cluster Pages', not hikes
        found = true; // coords will be empty
    }
    else {
        for (var i = 0; i < CL.length; i++) {
            for (var j = 0; j < CL[i].hikes.length; j++) {
                if (CL[i].hikes[j].name == hike) {
                    var clus = CL[i].hikes[j];
                    coords = clus.loc;
                    found = true;
                    break;
                }
            }
        }
    }
    if (!found) {
        for (var k = 0; k < NM.length; k++) {
            if (NM[k].name == hike) {
                coords = NM[k].loc;
                found = true;
                break;
            }
        }
    }
    if (!found) {
        alert("This hike cannot be located in the list of hikes");
    }
    return coords;
}
/**
 * This is invoked when selecting the filter for miles from locale
 */
function miles_from_locale(locale, miles) {
    var arealoc = {};
    $.ajax({
        url: '../json/areas.json',
        dataType: 'json',
        success: function (json_data) {
            var areaCenters = json_data.areas;
            for (var j = 0; j < areaCenters.length; j++) {
                if (areaCenters[j].loc == locale) {
                    arealoc = {
                        "lat": areaCenters[j].lat,
                        "lng": areaCenters[j].lng
                    };
                    break;
                }
            }
            if (Object.keys(arealoc).length === 0) {
                alert("This locale has no location data");
                return false;
            }
            filterByMiles(miles, arealoc);
            return;
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
    return;
}
/**
 * This function will determine which hikes are within the epsilon radius;
 */
function filterByMiles(radius, gps_coords) {
    var starray = [];
    var ctrlat = gps_coords.lat;
    var ctrlng = gps_coords.lng;
    CL.forEach(function (clobj) {
        var clhikes = clobj.hikes;
        clhikes.forEach(function (hobj) {
            var hlat = hobj.loc.lat;
            var hlng = hobj.loc.lng;
            var distance = distFromCtr(hlat, hlng, ctrlat, ctrlng, 'M');
            if (distance <= radius) {
                starray.push(hobj);
            }
        });
    });
    NM.forEach(function (nmobj) {
        var hikeset = nmobj.loc;
        var hikelat = hikeset.lat;
        var hikelng = hikeset.lng;
        var distance = distFromCtr(hikelat, hikelng, ctrlat, ctrlng, 'M');
        if (distance <= radius) {
            starray.push(nmobj);
        }
    });
    if (starray.length > 0) {
        starray.sort(compareObj);
        formTbl(starray);
        var map_bounds = arrayBounds(starray);
        map.fitBounds(map_bounds, mapMarg);
    }
    else {
        alert("No hikes within specified range");
    }
}
/**
 * This function will return the radial distance between two lat/lngs
 */
function distFromCtr(lat1, lon1, lat2, lon2, unit) {
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
/**
 * Examine the gps coordinates of filtered hikes and determine the
 * map bounds for the set
 */
function arrayBounds(array) {
    var lats = [];
    var lngs = [];
    array.forEach(function (hobj) {
        lats.push(hobj.loc.lat);
        lngs.push(hobj.loc.lng);
    });
    var north = Math.max.apply(Math, lats);
    var south = Math.min.apply(Math, lats);
    var east = Math.max.apply(Math, lngs);
    var west = Math.min.apply(Math, lngs);
    var sw = { lat: south, lng: west };
    var ne = { lat: north, lng: east };
    var bounds = new google.maps.LatLngBounds(sw, ne);
    return bounds;
}
/**
 * Used when comparing objects to be sorted by difficulty
 */
function compareDiff(a, b) {
    var hikea = a.diff;
    var hikeb = b.diff;
    // rank the difficulties for the comparison
    var comparison;
    var adiff = diffs.indexOf(hikea);
    var bdiff = diffs.indexOf(hikeb);
    var arank = diff_val[adiff];
    var brank = diff_val[bdiff];
    if (ascending) {
        if (arank > brank) {
            comparison = 1;
        }
        else {
            comparison = -1;
        }
    }
    else {
        if (arank < brank) {
            comparison = 1;
        }
        else {
            comparison = -1;
        }
    }
    return comparison;
}
/**
 * Used when comparing objects to be sorted by hike distance
 */
function compareDist(a, b) {
    var hikea = a.lgth;
    var hikeb = b.lgth;
    var comparison;
    if (ascending) {
        if (hikea > hikeb) {
            comparison = 1;
        }
        else {
            comparison = -1;
        }
    }
    else {
        if (hikea < hikeb) {
            comparison = 1;
        }
        else {
            comparison = -1;
        }
    }
    return comparison;
}
