/// <reference path="canvas.d.ts" />
declare var hikeFiles: string[]; // on hikePageTemplate.php
declare var appMode: string;
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
var hikeTrack: string; // hike fileno supplied to ajax call
var allTracks: JQueryDeferred<void>  = $.Deferred(); // when done with all files, draw chart
var promises: JQueryDeferred<void>[]  = []; // collection of promises (one per file)
// globals
var gpsvTracks: string[] = []; // track names appearing in GPSV tracklist box
var trkLats: string[][] = []; // array of each track's set of latitudes
var trkLngs: string[][] = []; // array of each track's set of longitudes
var trkMaxs: number[] = []; // elevation maxes, one per track
var trkMins: number[] = []; // elevation mins, one per track
var trkRows: Coords[][] = []; // array of each track's set of chart points:
                  // [{x:distance, y:elevation}, ...], where dist=>miles, ele=>feet

for (let i=0; i<hikeFiles.length; i++) {
    let trackDef: JQueryDeferred<void> = $.Deferred();
    promises.push(trackDef);
    hikeTrack = hikeFiles[i];
    getTrackData(trackDef);
}
$.when.apply($, promises).then(function() {
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
function getTrackData(promise: JQueryDeferred<void>): void {
    $.ajax({
        url:  '../php/getTrackData.php?fileno=' + hikeTrack + '&chrt=y',
        method: "get",
        dataType: "json",
        success: function(chartdata) { 
            gpsvTracks = gpsvTracks.concat(chartdata[0]);
            let tlats   = chartdata[1];
            let tlngs   = chartdata[2];
            let trkEles = chartdata[3];
            trkMaxs = trkMaxs.concat(chartdata[4]);
            trkMins = trkMins.concat(chartdata[5]);
            // create row objects for chart
            let trkcnt = tlats.length;
            for (let j=0; j<trkcnt; j++) {
                let chartrow = [];
                let startEle = parseFloat(trkEles[j][0]) * 3.2808;
                chartrow[0] = {x:0, y:startEle};
                let datcnt = tlats[j].length - 1;
                let hikelgth = 0;
                for (let k=0; k<datcnt; k++) {
                    hikelgth += distance(tlats[j][k], tlngs[j][k],
                        tlats[j][k+1], tlngs[j][k+1], "M");
                    let ele = trkEles[j][k] * 3.2808;
                    let dataPtObj = {x:hikelgth,y:ele};
                    chartrow.push(dataPtObj);
                }
                // the following pushes establish the track's indices
                trkRows.push(chartrow); // pushes an array of objects
                trkLats = trkLats.concat(tlats);    // pushes an array of lats
                trkLngs = trkLngs.concat(tlngs);    // pushes an array of lngs
            }
            promise.resolve();
        },
        error: function(_jqXHR, textStatus, errorThrown) {
            if (appMode === 'production') {
                let msg = "Could not read " + hikeTrack + ";\nThere will " +
                    "be no chart data for it";
                alert(msg);
            } else {
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
function distance(lat1: number, lon1: number, lat2: number, lon2: number, unit: string) {
    if (lat1 === lat2 && lon1 === lon2) {
        return 0;
    }
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1 - lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist);
    dist = dist * 180 / Math.PI;
    dist = dist * 60 * 1.1515; // Miles
    if (unit === "K") {
        dist = dist * 1.609344;
    } // Kilometers
    return dist;
}
