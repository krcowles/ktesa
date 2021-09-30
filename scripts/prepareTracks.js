"use strict";
/// <reference path="canvas.d.ts" />
/**
 * @fileoverview This file will assemble track data for all tracks. The
 * track data is used to draw a given track's elevation chart.
 * Note: the var 'hikeFiles', a list of the page's gpx files, is supplied
 * via php in hikePageTemplate.php
 * @author Ken Cowles
 * @version 2.0 Typescripted, with some type errors corrected
 * @version 3.0 Converted to use of database GPX files
 */
var hikeTrack; // variable used in getTrackData() ajax
var allTracks = $.Deferred(); // when done, draw chart
var promises = []; // collection of promises
// The following have a one-to-one correspondence for track drawing:
var gpsvTracks = []; // track names appearing in GPSV tracklist box
var trkLats = []; // array of track's latitudes
var trkLngs = [];
var trkMaxs = []; // elevation max 
var trkMins = []; // elevation min
var trkRows = []; // track data points {x, y}
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
 * This function retrieves the gps data from 'hikeTrack' and stores key
 * data for chart-drawing. Data is stored in the above global arrays.
 * @return {null}
 */
function getTrackData(promise) {
    $.ajax({
        dataType: "json",
        url: '../php/getTrackData.php?fileno=' + hikeTrack + '&chrt=y&wpts=n',
        method: "get",
        success: function(chartdata) { 
            gpsvTracks = chartdata[0];
            trkLats = chartdata[1];
            trkLngs = chartdata[2];
            let trkEles = chartdata[3];
            trkMaxs = chartdata[4];
            trkMins = chartdata[5];
            // create row objects for chart
            let trkcnt = trkLats.length;
            for (let j=0; j<trkcnt; j++) {
                let chartrow = [];
                chartrow[0] = {x:0, y:parseFloat(trkEles[0][0])};
                let datcnt = trkLats[j].length - 1;
                let hikelgth = 0;
                for (let k=0; k<datcnt; k++) {
                    hikelgth += distance(trkLats[j][k], trkLngs[j][k],
                        trkLats[j][k+1], trkLngs[j][k+1], "M");
                    let ele = trkEles[j][k] * 3.2808;
                    let dataPtObj = {x:hikelgth,y:ele};
                    chartrow.push(dataPtObj);
                }
                trkRows.push(chartrow);
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
