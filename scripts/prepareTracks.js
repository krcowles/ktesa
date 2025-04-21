"use strict";
/// <reference path="canvas.d.ts" />
/**
 * @fileoverview This file will assemble track data for all tracks. The
 * track data is used to draw a given track's elevation chart.
 *
 * @version 2.0 Typescripted, with some type errors corrected
 * @version 3.0 Modified getTrackData() to highlight steep inclines on chart
 * @version 4.0 Eliminated gpx files - using only json files
 */
var grade_threshold = 20;
var min_run = 4;
var hikeTrack; // variable used in getTrackData() ajax
var allTracks = $.Deferred(); // when done, draw chart
var promises = []; // collection of promises
// The following have a one-to-one correspondence for track drawing:
var gpsvTracks = []; // track names appearing in GPSV tracklist box
var trkLats = []; // array of track's latitudes
var trkLngs = [];
var trkEles = [];
var trkMaxs = []; // elevation max 
var trkMins = []; // elevation min
var trkRows = []; // track data points {x, y}
for (var i = 0; i < hike_file_list.length; i++) {
    var trackDef = $.Deferred();
    promises.push(trackDef);
    hikeTrack = "../json/" + hike_file_list[i];
    // each file is a single track:
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
        url: hikeTrack,
        success: function (json_file) {
            gpsvTracks.push(json_file['name']);
            var gpsdata = json_file['trk'];
            var lats = [];
            var lngs = [];
            var elevs = [];
            var rows = [];
            var hikelgth = 0;
            for (var k = 0; k < gpsdata.length; k++) {
                lats.push(gpsdata[k]['lat']);
                lngs.push(gpsdata[k]['lng']);
                elevs.push(gpsdata[k]['ele']);
            }
            trkLats.push(lats);
            trkLngs.push(lngs);
            trkEles.push(elevs);
            var emax = 0;
            var emin = 20000;
            var dist = [];
            var start = false;
            var consec = -0;
            var steeps = [];
            var runs = [];
            var runindx = 0;
            for (var i = 0; i < lats.length - 1; i++) {
                if (elevs[i] !== 0) {
                    dist = distInMiles(lats[i], lngs[i], lats[i + 1], lngs[i + 1], elevs[i], elevs[i + 1]);
                    hikelgth += dist[0];
                    // check for consecutive 'steep' grades
                    var degrees = Math.abs(dist[1]);
                    if (degrees > grade_threshold) {
                        start = true;
                        steeps.push(i);
                        consec++;
                        // once start is true, keep tracking until below threshhold
                    }
                    else if (start && degrees >= grade_threshold - 1) {
                        steeps.push(i);
                        consec++;
                    }
                    if (start && degrees < grade_threshold - 1) {
                        start = false;
                        if (consec >= min_run) {
                            for (var j = 0; j < steeps.length; j++) {
                                runs[runindx++] = steeps[j];
                            }
                        }
                        consec = 0;
                        steeps = [];
                    }
                    if (elevs[i + 1] !== 0) {
                        if (elevs[i + 1] > emax) {
                            emax = elevs[i + 1];
                        }
                        if (elevs[i + 1] < emin) {
                            emin = elevs[i + 1];
                        }
                        var dataPtObj = { x: hikelgth, y: elevs[i + 1], g: 0 };
                        rows.push(dataPtObj);
                    }
                }
            }
            var rindx = 0;
            for (var k = 0; k < rows.length; k++) {
                if (k === runs[rindx]) {
                    rows[k].g = 1;
                    rindx++;
                }
            }
            trkRows.push(rows);
            // set y axis range values:
            // NOTE: this algorithm works for elevs above 1,000ft (untested below that)
            var Cmin = Math.floor(emin / 100);
            var Cmax = Math.ceil(emax / 100);
            if ((emin - 100 * Cmin) < 40) {
                emin = Cmin - 0.5;
            }
            else {
                emin = Cmin;
            }
            if ((100 * Cmax - emax) < 40) {
                emax = Cmax + 0.5;
            }
            else {
                emax = Cmax;
            }
            emax *= 100;
            emin *= 100;
            trkMaxs.push(emax);
            trkMins.push(emin);
            promise.resolve();
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            var msg = "prepareTracks.js: attempting to access file " +
                hikeTrack + " [json]";
            ajaxError(appMode, _jqXHR, _textStatus, msg);
            promise.reject();
        }
    });
    return;
}
/**
 * This function determines the radial distance between lat/lng pairs, and calculates
 * the grade (slope) from the elevation change.
 */
function distInMiles(lat1, lon1, lat2, lon2, el1, el2) {
    var rads = Math.PI / 180;
    var R = 6371; // Radius of the earth in km
    var dLat = (lat2 - lat1) * rads; // convert to radians
    var dLon = (lon2 - lon1) * rads;
    var rlat1 = lat1 * rads;
    var rlat2 = lat2 * rads;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(rlat1) * Math.cos(rlat2) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var b = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var kilos = R * b;
    var meters = kilos * 1000; // Distance in meters
    var miles = kilos / 1.609344;
    var grade = (el2 - el1) / meters;
    var slope = Math.atan(grade);
    slope *= 180 / Math.PI;
    return [miles, slope];
}
