"use strict";
/// <reference path="canvas.d.ts" />
/**
* @fileoverview This module will assemble track data for all tracks, even
 * when there are multiple files to parse. Each set of track data is used
 * to draw a given track's elevation chart. Note: the var 'hikeFiles', is
 * a list of the page's gpx filenos, supplied by hikePageTemplate.php via
 * multiMap.php. Track numbers increment over multiple files, that is, the
 * track numbers go from 1..n, where each number is associated with a given
 * track in a given file.
 *
 * @author Ken Cowles
 * @version 2.0 Typescripted, with some type errors corrected
 * @version 3.0 Converted to use of GPX database instead of gpx files
 */
var hikeTrack; // hike fileno supplied to ajax call
var allTracks = $.Deferred(); // when done with all files, draw chart
var promises = []; // collection of promises (one per file)
// globals
var trkOrder = []; // The returned order of ajax calls vs "i" in for loop
var trkSequence = []; // Re-ordered seq of track indices per order called
var trkIndex = []; // The corrected order of tracks as an index into arrays
var gpsvTracks = []; // track names appearing in GPSV tracklist box
var trkLats = []; // array of each track's set of latitudes
var trkLngs = []; // array of each track's set of longitudes
var trkMaxs = []; // elevation maxes, one per track
var trkMins = []; // elevation mins, one per track
var trkRows = []; // array of each track's set of chart points:
var tbltype = $('#tbls').text();
if (typeof pseudo === 'undefined') {
    var pseudo = false;
}
// [{x:distance, y:elevation}, ...], where dist=>miles, ele=>feet
// Get charting data for each hike file specified
for (var i = 0; i < hikeFiles.length; i++) {
    var trackDef = $.Deferred();
    promises.push(trackDef);
    hikeTrack = hikeFiles[i];
    getTrackData(trackDef, i, cluster_page);
}
$.when.apply($, promises).then(function () {
    /**
     * Note: due to asynchronous loading, gpsvTracks and associated data
     * can be returned in any order. The order in which the file data is
     * actually returned is tracked by the array of objects: 'trkOrder',
     * which lists the call id ('i' in the for loop) and which tracks
     * were returned with the call.
     */
    var lim = trkOrder.length;
    var fin = 0; // no of files processed
    var tno = 0; // no of tracks accumulated
    while (fin < lim) {
        for (var k = 0; k < lim; k++) {
            // was 'parseInt': typescript wants int
            var ord = trkOrder[k].called;
            if (ord === fin) {
                for (var m = 0; m < trkOrder[k].tracks.length; m++) {
                    trkSequence[tno++] = trkOrder[k].tracks[m];
                }
                fin++;
                break;
            }
        }
    }
    for (var l = 0; l < tno; l++) {
        var indx_1 = gpsvTracks.indexOf(trkSequence[l]);
        trkIndex[l] = indx_1;
    }
    allTracks.resolve();
});
/**
 * The values for sets of lats, lngs, and plot data, along with track names and
 * maxs and mins, are retrieved by php for each track in the hikeFfile supplied.
 * The routine then adds the data to the globals such that each track, whether
 * from one or multiple files, has a corresponding set of data supplied to the
 * charting routine (dynamicChart.js).
 */
function getTrackData(promise, callorder, clusterPg) {
    var trkurl = '../php/getTrackData.php?fileno=' + hikeTrack + '&chrt=y' +
        '&tbl=' + tbltype;
    if (clusterPg) {
        var adder = pseudo ? '&clus=y&pseudo=y' : '&clus=y';
        trkurl += adder;
    }
    $.ajax({
        url: trkurl,
        method: "get",
        dataType: "json",
        success: function (chartdata) {
            var order = { called: callorder, tracks: chartdata[0] };
            trkOrder.push(order);
            gpsvTracks = gpsvTracks.concat(chartdata[0]);
            trkRows = trkRows.concat(chartdata[1]);
            trkLats = trkLats.concat(chartdata[2]);
            trkLngs = trkLngs.concat(chartdata[3]);
            trkMaxs = trkMaxs.concat(chartdata[4]);
            trkMins = trkMins.concat(chartdata[5]);
            promise.resolve();
        },
        error: function (_jqXHR, textStatus, errorThrown) {
            if (appMode === 'production') {
                var msg_1 = "Could not read " + hikeTrack + ";\nThere will " +
                    "be no chart data for it";
                alert(msg_1);
            }
            else {
                var msg = "Ajax call in prepareTracks.js failed " +
                    "with error code: " + errorThrown +
                    "Could not extract XML data from " + hikeTrack +
                    "\nSystem error message: " + textStatus;
                alert(msg);
            }
            promise.reject();
        }
    });
    return;
}
/**
 * This function determines the radial distance between lat/lng pairs
 */
function distance(lat1, lon1, lat2, lon2, unit) {
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
    dist = dist * 60 * 1.1515; // Miles
    if (unit === "K") {
        dist = dist * 1.609344;
    } // Kilometers
    return dist;
}
