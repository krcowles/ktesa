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
var gpsvTracks = []; // track names appearing in GPSV tracklist box
var trkLats = []; // array of each track's set of latitudes
var trkLngs = []; // array of each track's set of longitudes
var trkMaxs = []; // elevation maxes, one per track
var trkMins = []; // elevation mins, one per track
var trkRows = []; // array of each track's set of chart points:
// [{x:distance, y:elevation}, ...], where dist=>miles, ele=>feet
for (var i = 0; i < hikeFiles.length; i++) {
    var trackDef = $.Deferred();
    promises.push(trackDef);
    hikeTrack = hikeFiles[i];
    getTrackData(trackDef);
}
$.when.apply($, promises).then(function () {
    // Note: due to asynchronous loading, gpsvtracks and associated data
    // may not be in the same order as the gpsv map's tracklist box
    allTracks.resolve();
});
/**
 * The values for sets of lats/lngs/eles + track names and maxs and mins are
 * retrieved (for all tracks in the hikeFfile supplied) by php and delivered
 * back to the success routine. From the sets of lats/lngs/eles, the chart
 * row data is created. The routine then adds the data to the globals listed
 * above such that each track, whether from one or multiple files, has a
 * corresponding set of data supplied to the charting routine (dynamicChart.js).
 */
function getTrackData(promise) {
    $.ajax({
        url: '../php/getTrackData.php?fileno=' + hikeTrack + '&chrt=y',
        method: "get",
        dataType: "json",
        success: function (chartdata) {
            gpsvTracks = gpsvTracks.concat(chartdata[0]);
            var tlats = chartdata[1];
            var tlngs = chartdata[2];
            var trkEles = chartdata[3];
            trkMaxs = trkMaxs.concat(chartdata[4]);
            trkMins = trkMins.concat(chartdata[5]);
            // create row objects for chart
            var trkcnt = tlats.length;
            for (var j = 0; j < trkcnt; j++) {
                var chartrow = [];
                var startEle = parseFloat(trkEles[j][0]) * 3.2808;
                chartrow[0] = { x: 0, y: startEle };
                var datcnt = tlats[j].length - 1;
                var hikelgth = 0;
                for (var k = 0; k < datcnt; k++) {
                    hikelgth += distance(tlats[j][k], tlngs[j][k], tlats[j][k + 1], tlngs[j][k + 1], "M");
                    var ele = trkEles[j][k] * 3.2808;
                    var dataPtObj = { x: hikelgth, y: ele };
                    chartrow.push(dataPtObj);
                }
                // the following pushes establish the track's indices
                trkRows.push(chartrow); // pushes an array of objects
                trkLats = trkLats.concat(tlats); // pushes an array of lats
                trkLngs = trkLngs.concat(tlngs); // pushes an array of lngs
            }
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
