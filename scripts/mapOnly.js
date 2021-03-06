"use strict";
/// <reference path="./map.d.ts" />
/**
 * @fileoverview This routine initializes the google map to view the state
 * of New Mexico, places markers on hike locations, and clusters the markers
 * together displaying the number of markers in the group. It also draws hike
 * tracks when zoomed in, and then also when panned.
 *
 * @author Ken Cowles
 *
 * @version 1.0 Responsive design intro (new menu, etc.)
 * @version 1.1 Typescripted
 */
/**
 * INITIALIZATION OF PAGE & GLOBAL DEFINITIONS
 */
// Hike Track Colors: red, blue, orange, purple, black
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000'];
var geoOptions = { enableHighAccuracy: true };
var map;
var $fullScreenDiv; // Google's hidden inner div when clicking on full screen mode
var $map = $('#map');
var mapht;
var drawnHikes = []; // hike numbers which have had tracks created
var drawnTracks = []; // array of objects: {hike:hikeno , track:polyline}
var zoomedHikes;
// globals to register when a zoom needs to call highlightTrack
var applyHighlighting = false;
var hilite_obj = {}; // global object holding hike object & marker type
var hilited = [];
var zoomLevel;
var zoomdone;
var panning = false;
// Custom tick mark for map tracks
var mapTick = {
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};
// position text in logo
var title = $('#trail').text();
$('#ctr').text(title);
// position searchbar
var navheight = $('nav').height();
var logoheight = $('#logo').height();
var srchtop = navheight + 16 + logoheight + 14; // 16px padding on navbar
$('#searchbar').css({
    top: srchtop,
    left: '100px'
});
/**
 * This function positions the geosymbol in the bottom right corner of the map,
 * left of the google map zoom control
 */
function locateGeoSym() {
    var winht = window.innerHeight - 64;
    var mapwd = $('#map').width() - 80;
    $('#geoCtrl').css({
        top: winht,
        left: mapwd
    });
    return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);
/**
 * Use the arrays passed in to the home page by php: one for each type
 * of marker to be displayed (Clustered, Normal):
 * 		CL Array: Clustered hike pages
 * 		NM Array: Normal hike pages
 * And one for creating tracks:
 * 		tracks Array: ordered list of json file names
 */
var locaters = []; // global used to popup info window on map when hike is searched
/**
 * A simple function which correlates the number of hikes in a group to its icon
 */
var getIcon = function (no_of_hikes) {
    var icon = "../images/pins/hike" + no_of_hikes + ".png";
    return icon;
};
// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
function initMap() {
    google.maps.Marker.prototype.clicked = false; // used in sideTables.js
    var clustererMarkerSet = [];
    var nmCtr = { lat: 34.450, lng: -106.042 };
    map = new google.maps.Map($map.get(0), {
        center: nmCtr,
        zoom: 7,
        // optional settings:
        zoomControl: true,
        scaleControl: true,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
            mapTypeIds: [
                // only two of these show, don't know why...
                google.maps.MapTypeId.ROADMAP,
                google.maps.MapTypeId.TERRAIN,
                google.maps.MapTypeId.SATELLITE,
                google.maps.MapTypeId.HYBRID
            ]
        },
        fullscreenControl: true,
        streetViewControl: false,
        rotateControl: false,
        mapTypeId: google.maps.MapTypeId.TERRAIN
    });
    // ///////////////////////////   MARKER CREATION   ////////////////////////////
    CL.forEach(function (clobj) {
        AddClusterMarker(clobj.loc, clobj.group, clobj.hikes, clobj.page);
    });
    NM.forEach(function (nmobj) {
        AddHikeMarker(nmobj);
    });
    // Cluster Markers:
    function AddClusterMarker(location, group, clhikes, page) {
        var hikecnt = clhikes.length;
        var clicon = getIcon(hikecnt);
        var marker = new google.maps.Marker({
            position: location,
            map: map,
            icon: clicon,
            title: group
        });
        marker.clicked = false;
        var srchmrkr = { hikeid: group, pin: marker };
        locaters.push(srchmrkr);
        clustererMarkerSet.push(marker);
        var iwContent = '<div id="iwCH">';
        if (page > 0) {
            var link_1 = "hikePageTemplate.php?clus=y&hikeIndx=";
            iwContent += '<br /><a href="' + link_1 + page + '">' + group + '</a>';
        }
        else {
            iwContent += '<br />' + group;
        }
        var link = "responsivePage.php?hikeIndx=";
        clhikes.forEach(function (clobj) {
            iwContent += '<br /><a href="' + link + clobj.indx + '">' +
                clobj.name + '</a>';
            iwContent += ' Lgth: ' + clobj.lgth + ' miles; Elev Chg: ' +
                clobj.elev + ' ft; Diff: ' + clobj.diff;
        });
        var iw = new google.maps.InfoWindow({
            content: iwContent,
            maxWidth: 600
        });
        iw.addListener('closeclick', function () {
            marker.clicked = false;
        });
        marker.addListener('click', function () {
            if (zoomLevel < 13) {
                map.setZoom(13);
            }
            map.setCenter(location);
            iw.open(map, this);
            marker.clicked = true; // marker prototype property
        });
    }
    // Normal Hike Markers
    function AddHikeMarker(hikeobj) {
        var nmicon = getIcon(1);
        var marker = new google.maps.Marker({
            position: hikeobj.loc,
            map: map,
            icon: nmicon,
            title: hikeobj.name
        });
        marker.clicked = false;
        var srchmrkr = { hikeid: hikeobj.name, pin: marker };
        locaters.push(srchmrkr);
        clustererMarkerSet.push(marker);
        // infoWin content: add data for this hike
        var iwContent = '<div id="iwNH"><a href="responsivePage.php?hikeIndx='
            + hikeobj.indx + '">' + hikeobj.name + '</a><br />';
        iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
        iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
        iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
        iwContent += '<a href="' + hikeobj.dirs + '">Directions</a></div>';
        var iw = new google.maps.InfoWindow({
            content: iwContent,
            maxWidth: 400
        });
        iw.addListener('closeclick', function () {
            marker.clicked = false;
        });
        marker.addListener('click', function () {
            if (zoomLevel < 13) {
                map.setZoom(13);
            }
            map.setCenter(hikeobj.loc);
            iw.open(map, this);
            marker.clicked = true; // marker prototype property
        });
    }
    // /////////////////////// Marker Grouping /////////////////////////
    new MarkerClusterer(map, clustererMarkerSet, {
        imagePath: '../images/markerclusters/m',
        gridSize: 50,
        maxZoom: 12,
        averageCenter: true,
        zoomOnClick: true
    });
    // //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
    map.addListener('zoom_changed', function () {
        zoomdone = $.Deferred();
        var idle = google.maps.event.addListener(map, 'idle', function () {
            var curZoom = map.getZoom();
            var perim = String(map.getBounds());
            if (curZoom >= 13) {
                zoomedHikes = tracksInBounds(perim);
            }
            if (zoomedHikes.length > 0) {
                $.when(zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])).then(function () {
                    google.maps.event.removeListener(idle);
                    zoomdone.resolve();
                });
            }
            else {
                google.maps.event.removeListener(idle);
            }
        });
    });
    map.addListener('dragstart', function () {
        panning = true;
    });
    map.addListener('dragend', function () {
        // no highlighting is required during pan
        var curr_zoom = map.getZoom();
        var newBds = String(map.getBounds());
        if (curr_zoom >= 13) {
            zoomedHikes = tracksInBounds(newBds);
        }
        if (zoomedHikes.length > 0) {
            zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2]);
        }
        panning = false;
    });
    map.addListener('center_changed', function () {
        if (!panning) {
            $.when(zoomdone).then(function () {
                if (applyHighlighting) {
                    restoreTracks();
                    highlightTracks();
                }
            });
        }
        else {
            panning = false;
        }
    });
}
// ////////////////////// END OF MAP INITIALIZATION  ///////////////////////
// ///////////////////////////  TRACK DRAWING  /////////////////////////////
/**
 * When there is a pan or zoom, identify tracks that should be displayed
 */
function tracksInBounds(boundsStr) {
    var singles = []; // individual hike nos
    var hikeInfoWins = []; // info window content for each hikeno in singles
    var trackColors = []; // for clusters, tracks get unique colors
    var max_color = colors.length - 1; // cycle through colors in var
    // Define north, south, east,west
    var beginA = boundsStr.indexOf('((') + 2;
    var leftParm = boundsStr.substring(beginA, boundsStr.length);
    var beginB = leftParm.indexOf('(') + 1;
    var rightParm = leftParm.substring(beginB, leftParm.length);
    var south = parseFloat(leftParm);
    var north = parseFloat(rightParm);
    var westIndx = leftParm.indexOf(',') + 1;
    var westStr = leftParm.substring(westIndx, leftParm.length);
    var west = parseFloat(westStr);
    var eastIndx = rightParm.indexOf(',') + 1;
    var eastStr = rightParm.substring(eastIndx, rightParm.length);
    var east = parseFloat(eastStr);
    CL.forEach(function (clus) {
        var color = 0;
        var link = "responsivePage.php?hikeIndx=";
        if (clus.page > 0) { // then this is a 'Cluster Page'
            link = "responsivePage.php?clus=y&hikeIndx=";
        }
        clus.hikes.forEach(function (hike) {
            var lat = hike.loc.lat;
            var lng = hike.loc.lng;
            if (lng <= east && lng >= west && lat <= north && lat >= south) {
                var cliw = '<div id="iwCH"><a href="' + link + hike.indx +
                    '" target="_blank">' + hike.name + '</a><br />Length: ' +
                    hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                    '<br />Difficulty: ' + hike.diff + '</div>';
                singles.push(hike.indx);
                hikeInfoWins.push(cliw);
                trackColors.push(colors[color++]);
                if (color > max_color) { // rotate through colors
                    color = 0;
                }
            }
        });
    });
    NM.forEach(function (hike) {
        var lat = hike.loc.lat;
        var lng = hike.loc.lng;
        if (lng <= east && lng >= west && lat <= north && lat >= south) {
            var nmiw = '<div id="iwNH"><a href="responsivePage.php?hikeIndx=' +
                hike.indx + '" target="_blank">' + hike.name + '</a><br />Length: ' +
                hike.lgth + ' miles<br />Elev Chg: ' + hike.elev +
                '<br />Difficulty: ' + hike.diff + '</div>';
            singles.push(hike.indx);
            hikeInfoWins.push(nmiw);
            trackColors.push(colors[0]);
        }
    });
    return [singles, hikeInfoWins, trackColors];
}
/**
 * This file will create tracks for the input arrays of hike objects and clusters.
 * If a track has already been created, it will not be created again.
 */
function zoom_track(hikenos, infoWins, trackcolors) {
    var promises = [];
    for (var i = 0, j = 0; i < hikenos.length; i++, j++) {
        if (!drawnHikes.includes(hikenos[i])) {
            if (tracks[hikenos[i]] !== '') {
                var sgldef = $.Deferred();
                promises.push(sgldef);
                var trackfile = '../json/' + tracks[hikenos[i]];
                drawnHikes.push(hikenos[i]);
                if (j === trackcolors.length) {
                    j = 0; // rollover colors when # of tracks > # of colors
                }
                drawTrack(trackfile, infoWins[i], trackcolors[j], hikenos[i], sgldef);
            }
        }
    }
    return $.when.apply($, promises);
}
/**
 * This function draws the track for the hike object
 */
function drawTrack(json_filename, info_win, color, hikeno, deferred) {
    var sgltrack;
    $.ajax({
        dataType: "json",
        url: json_filename,
        success: function (trackDat) {
            sgltrack = new google.maps.Polyline({
                icons: [{
                        icon: mapTick,
                        offset: '0%',
                        repeat: '15%'
                    }],
                path: trackDat,
                geodesic: true,
                strokeColor: color,
                strokeOpacity: .6,
                strokeWeight: 3,
                zIndex: 1
            });
            sgltrack.setMap(map);
            // create the mouseover text:
            var iw = new google.maps.InfoWindow({
                content: info_win
            });
            sgltrack.addListener('mouseover', function (mo) {
                var trkPtr = mo.latLng;
                iw.setPosition(trkPtr);
                iw.open(map);
            });
            sgltrack.addListener('mouseout', function () {
                iw.close();
            });
            var newtrack = { hike: hikeno, track: sgltrack };
            drawnTracks.push(newtrack);
            deferred.resolve();
        },
        error: function () {
            var msg = 'Did not succeed in getting track data: ' +
                json_filename;
            alert(msg);
            deferred.reject();
        }
    });
    return;
}
/**
 * This function emphasizes the hike track(s) when the user searches for
 * a hike with the search bar, or zooms to it via the zoom icon in the
 * side table. If the track has not been drawn yet, it is drawn.
 * NOTE: A javascript anomaly - passing in a single object in an array
 * results in the function receiving the object, but not as an array.
 * Hence a 'type' identifier is used here
 */
function highlightTracks() {
    if (!$.isEmptyObject(hilite_obj)) {
        if (hilite_obj.type === 'cl') { // object is an array of objects
            // wait for tracks to be drawn, if not already...
            var cluster = hilite_obj.obj;
            cluster.forEach(function (track) {
                var polyno = track.indx;
                for (var k = 0; k < drawnTracks.length; k++) {
                    if (drawnTracks[k].hike == polyno) {
                        var polyline = drawnTracks[k].track;
                        polyline.setOptions({
                            strokeWeight: 4,
                            strokeColor: '#FFFF00',
                            strokeOpacity: 1,
                            zIndex: 10
                        });
                        hilited.push(polyline);
                        break;
                    }
                }
            });
        }
        else { // mrkr === 'nm'; object is a single object
            // wait for tracks to be drawn, if not already...
            var nmobj = hilite_obj.obj;
            var polyno = nmobj.indx;
            for (var k = 0; k < drawnTracks.length; k++) {
                if (drawnTracks[k].hike == polyno) {
                    var polyline = drawnTracks[k].track;
                    polyline.setOptions({
                        strokeWeight: 4,
                        strokeColor: '#FFFF00',
                        strokeOpacity: 1,
                        zIndex: 10
                    });
                    hilited.push(polyline);
                    break;
                }
            }
        }
        hilite_obj = {};
    }
}
/**
 * Undo any previous track highlighting
 */
function restoreTracks() {
    for (var n = 0; n < hilited.length; n++) {
        hilited[n].setOptions({
            strokeOpacity: 0.60,
            strokeWeight: 3,
            zIndex: 1
        });
    }
    return;
}
// /////////////////////////  END TRACK DRAWING  ///////////////////////////
// //////////////////////////  GEOLOCATION CODE ////////////////////////////
function setupLoc() {
    navigator.geolocation.getCurrentPosition(success, error, geoOptions);
    function success(pos) {
        var geoPos = pos.coords;
        var geoLat = geoPos.latitude;
        var geoLng = geoPos.longitude;
        var newWPos = { lat: geoLat, lng: geoLng };
        new google.maps.Marker({
            position: newWPos,
            map: map,
            icon: "../images/currentLoc.png"
        });
        map.setCenter(newWPos);
        var currzoom = map.getZoom();
        if (currzoom < 13) {
            map.setZoom(13);
        }
    } // end of watchSuccess function
    function error(eobj) {
        var msg = 'Error retrieving position; Code: ' + eobj.code;
        window.alert(msg);
    }
}
// //////////////////////  MAP FULL SCREEN DETECT  //////////////////////
$(document).bind('webkitfullscreenchange mozfullscreenchange fullscreenchange', function () {
    var thisMapDoc = document;
    var isFullScreen = thisMapDoc.fullScreen ||
        thisMapDoc.mozFullScreen ||
        thisMapDoc.webkitIsFullScreen;
    if (isFullScreen) {
        console.log('fullScreen!');
        var $gicon = $('#geoCtrl').detach();
        var $nhbox = $('#newHikeBox').detach();
        $gicon.appendTo($fullScreenDiv);
        $nhbox.appendTo($fullScreenDiv);
    }
    else {
        console.log('NO fullScreen!');
    }
});
// //////////////////////  WINDOW RESIZE EVENT  //////////////////////
$(window).on('resize', function () {
    locateGeoSym();
});
// //////////////////////////////////////////////////////////////
