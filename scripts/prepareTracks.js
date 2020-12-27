"use strict"
/**
 * @fileoverview This file will assemble track data for all tracks. The
 * track data is used to draw a given track's elevation chart.
 * Note: the var 'hikeFiles', a list of the page's gpx files, is supplied
 * via php in hikePageTemplate.php
 * 
 * @author Tom Sandberg
 * @author Ken Cowles
 */
var hikeTrack; // variable used in getTrackData() ajax
var allTracks  = $.Deferred(); // when done, draw chart
var promises   = []; // collection of promises
// The following have a one-to-one correspondence for track drawing:
var gpsvTracks = []; // track names appearing in GPSV tracklist box
var trkLats = []; // array of track's latitudes
var trkLngs = [];
var trkMaxs = []; // elevation max 
var trkMins = []; // elevation min
var trkRows = []; // track data points {x, y}

for (let i=0; i<hikeFiles.length; i++) {
    let trackDef = $.Deferred();
    promises.push(trackDef);
    hikeTrack = "../gpx/" + hikeFiles[i];
    getTrackData(trackDef);
}
$.when.apply($, promises).then(function() {
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
        dataType: "xml",
        url: hikeTrack,
        success: function(gpsdata) {
            var gpxtype = 'trk';
            var pts = 'trkpt';
            if ($(gpsdata).find('trk').length === 0) {
                if ($(gpsdata).find('rte').length ===0) {
                    alert("No, or unrecognizable, track data in gpx");
                    return;
                }
                gpxtype = 'rte';
                pts = 'rtept';
            }
            // process track/route by track/route (may be multiple tracks)
            $(gpsdata).find(gpxtype).each(function() {
                let lats = [];
                let lngs = [];
                let elevs = [];
                let rows = [];
                gpsvTracks.push($(this).find('name').text());
                var hikelgth = 0;
                $(this).find(pts).each(function() {
                    let tag = parseFloat($(this).attr('lat'));
                    lats.push(tag);
                    tag =parseFloat( $(this).attr('lon'));
                    lngs.push(tag);
                    let $ele = $(this).find('ele').text();
                    if ( $ele.length ) { 
                        tag = parseFloat($ele) * 3.2808;
                        elevs.push(tag);
                    } else {   // some GPX files contain trkpts w/no ele tag
                        // remove entries for trkpts that have no elevation:
                        lats.pop();
                        lngs.pop();
                    }
                });
                trkLats.push(lats);
                trkLngs.push(lngs);
                // form the array of datapoint objects for this track:
                rows[0] = { x: 0, y: elevs[0] };
                let emax = 0;
                let emin = 20000;
                for (let i=0; i<lats.length-1; i++) {
                    hikelgth += distance(lats[i],lngs[i],lats[i+1],lngs[i+1],"M");
                    if (elevs[i+1] > emax) { emax = elevs[i+1]; }
                    if (elevs[i+1] < emin) { emin = elevs[i+1]; }
                    let dataPtObj = { x: hikelgth, y: elevs[i+1] };
                    rows.push(dataPtObj);
                }
                trkRows.push(rows);
                // set y axis range values:
                // NOTE: this algorithm works for elevs above 1,000ft (untested below that)
                let Cmin = Math.floor(emin/100);
                let Cmax = Math.ceil(emax/100);
                if ( (emin - 100 * Cmin) < 40 ) {
                    emin = Cmin - 0.5;
                } else {
                    emin = Cmin;
                }
                if ( (100 * Cmax - emax) < 40 ) {
                    emax = Cmax + 0.5;
                } else {
                    emax = Cmax;
                }
                emax *= 100;
                emin *= 100;
                trkMaxs.push(emax);
                trkMins.push(emin);
            });
            promise.resolve();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var msg = "Ajax call in prepareTracks.js failed " +
                "with error code: " + errorThrown + 
                "Could not extract XML data from " + hikeTrack + 
                "\nSystem error message: " + textStatus;
            promise.reject();
           alert(msg);
        }
    });
    return;
}
/**
 * This function determines the radial distance between lat/lng pairs
 * @param {number} lat1 
 * @param {number} lon1 
 * @param {number} lat2 
 * @param {number} lon2 
 * @param {string} unit can be either 'K' [kilometers] or 'M' [miles]
 * @return {number}
 */
function distance(lat1, lon1, lat2, lon2, unit) {
    if (lat1 === lat2 && lon1 === lon2) { return 0; }
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1-lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist);
    dist = dist * 180/Math.PI;
    dist = dist * 60 * 1.1515; // Miles
    if (unit === "K") { dist = dist * 1.609344; } // Kilometers
    return dist;
}