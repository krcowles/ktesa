/// <reference path="canvas.d.ts" />
declare var hikeFiles: string[]; // on hikePageTemplate.php
declare var appMode: string;
/**
 * @fileoverview This file will assemble track data for all tracks. The
 * track data is used to draw a given track's elevation chart.
 * Note: the var 'hikeFiles', a list of the page's gpx files, is supplied
 * via php in hikePageTemplate.php
 * @author Ken Cowles
 * @version 2.0 Typescripted, with some type errors corrected
 * @version 3.0 Modified getTrackData() to highlight steep inclines on chart
 */
const grade_threshold = 20;
const min_run = 4;
var hikeTrack: string; // variable used in getTrackData() ajax
var allTracks: JQueryDeferred<void>  = $.Deferred(); // when done, draw chart
var promises: JQueryDeferred<void>[]  = []; // collection of promises
// The following have a one-to-one correspondence for track drawing:
var gpsvTracks: string[] = []; // track names appearing in GPSV tracklist box
var trkLats: number[][] = []; // array of track's latitudes
var trkLngs: number[][] = [];
var trkEles: number[][] = [];
var trkMaxs: number[] = []; // elevation max 
var trkMins: number[] = []; // elevation min
var trkRows: Chartrow[][] = []; // track data points {x, y}

for (let i=0; i<hikeFiles.length; i++) {
    let trackDef: JQueryDeferred<void> = $.Deferred();
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
function getTrackData(promise: JQueryDeferred<void>): void {
    $.ajax({
        dataType: "xml",
        url: hikeTrack,
        success: function(gpsdata:XMLDocument) {
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
            // process by track/route (may be multiple per file)
            $(gpsdata).find(gpxtype).each(function(indx) {
                let trackName: string;
                let child = $(this).find('name');
                if (child.length == 0) {
                    // GPSVisualizer supplies a default name if none in gpx file
                    trackName = '[track ' + (indx + 1) + ']';
                } else {
                    trackName = child.text();
                }
                let lats: number[] = [];
                let lngs: number[] = [];
                let elevs: number[] = [];
                let rows: Chartrow[] = [];
                gpsvTracks.push(trackName);
                var hikelgth = 0;
                $(this).find(pts).each(function() {
                    let tag = parseFloat(<string>$(this).attr('lat'));
                    lats.push(tag);
                    tag =parseFloat(<string>$(this).attr('lon'));
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
                trkEles.push(elevs);
                // form the array of datapoint objects for this track:
                rows[0] = { x: 0, y: elevs[0], g: 0 };
                let emax = 0;
                let emin = 20000;
                let dist: number[] = [];
                let start = false;
                let consec = -0;
                let steeps: number[] = [];
                let runs: number[] = [];
                let runindx = 0;
                for (let i=0; i<lats.length-1; i++) {
                    dist = distInMiles(lats[i],lngs[i],lats[i+1],lngs[i+1],
                        elevs[i], elevs[i+1]);
                    hikelgth += dist[0];
                    // check for consecutive 'steep' grades
                    let degrees = Math.abs(dist[1]);
                    if (degrees > grade_threshold) {
                        start = true;
                        steeps.push(i);
                        consec++;
                    // once start is true, keep tracking until below threshhold
                    } else if (start && degrees >= grade_threshold - 1) {
                        steeps.push(i);
                        consec++;
                    }
                    if (start && degrees < grade_threshold -1) {
                        start = false;
                        if (consec >= min_run) {
                            for (let j=0; j<steeps.length; j++) {
                                runs[runindx++] = steeps[j];
                            }
                        }
                        consec = 0;
                        steeps = [];
                    }
                    if (elevs[i+1] > emax) { emax = elevs[i+1]; }
                    if (elevs[i+1] < emin) { emin = elevs[i+1]; }
                    let dataPtObj = { x: hikelgth, y: elevs[i+1], g: 0};
                    rows.push(dataPtObj);
                }
                let rindx = 0;
                for (let k=0; k<rows.length; k++) {
                    if (k === runs[rindx]) {
                        rows[k].g = 1;
                        rindx++;
                    }
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
        error: function(_jqXHR, _textStatus, _errorThrown) {
            if (appMode === 'development') {
                var newDoc = document.open();
                newDoc.write(_jqXHR.responseText);
                newDoc.close();
            }
            else { // production
                var msg = "Could not read " + hikeTrack + 
                    "\nWe apologize for any inconvenience\n" +
                    "The webmaster has been notified; please try again later";
                alert(msg);
                var ajaxerr = "Trying to access gpx file: " + hikeTrack +
                    ";\nError text: " +  _textStatus + "; Error: " +
                    _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
                var errobj = { err: ajaxerr };
                $.post('../php/ajaxError.php', errobj);
            }
            promise.reject();
        }
    });
    return;
}
/**
 * This function determines the radial distance between lat/lng pairs, and calculates
 * the grade (slope) from the elevation change.
 */
function distInMiles(lat1: number, lon1: number, lat2: number, lon2: number, 
    el1: number, el2: number) {

    var rads = Math.PI/180;
    var R = 6371; // Radius of the earth in km
    var dLat = (lat2-lat1) * rads;  // convert to radians
    var dLon = (lon2-lon1) * rads;
    var rlat1 = lat1 * rads;
    var rlat2 = lat2 * rads;
    var a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(rlat1) * Math.cos(rlat2) * 
        Math.sin(dLon/2) * Math.sin(dLon/2)
        ; 
    var b = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var kilos = R * b; 
    var meters =kilos * 1000; // Distance in meters
    var miles = kilos / 1.609344
    var grade = (el2 - el1) / meters;
    var slope = Math.atan(grade);
    slope *= 180/Math.PI
    return [miles, slope];
}
