"use strict";
/// <reference path='./map.d.ts' />
/**
 * @fileoverview This routine initializes the google map to view the state
 *		of New Mexico, places markers on hike locations, and clusters the markers
 * 		together displaying the number of markers in the group. It also draws hike
 * 		tracks when zoomed in, and then also when panned. The script relies on the
 * 		externally supplied lib 'markerclusterer.js'
 * @author Ken Cowles
 * @version 3.0 Added Cluster Page compatibility (removes indexPageTemplate links)
 * @version 4.0 Typescripted, with some type errors corrected
 */
// Hike Track Colors: red, blue, orange, purple, black
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000'];
var geoOpts = { enableHighAccuracy: true };
// need to be global:
var map;
var $fullScreenDiv; // Google's hidden inner div when clicking on full screen mode
var $map = $('#map');
var mapht;
// track vars
var drawnHikes = []; // hike numbers which have had tracks created
var drawnTracks = []; // array of objects: {hike:hikeno , track:polyline}
var zoomedHikes;
// globals to register when a zoom needs to call highlightTrack
var applyHighlighting = false;
var hiliteObj = {}; // global object holding hike object & marker type
var hilited = [];
var zoom_level;
var zoomdone;
var panning = false;
/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible.
 */
var initDivParms = function () {
    mapht = $(window).height() - $('#panel').height();
    $map.css('height', mapht + 'px');
    $('#adjustWidth').css('height', mapht + 'px');
    $('#sideTable').css('height', mapht + 'px');
    return;
};
initDivParms();
// Custom tick mark for map tracks
var mapTick = {
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};
/**
 * This function places the geopoitioning symbol in the lower right corner of the map
 */
function locateGeoSym() {
    var winht = $('#panel').height() + mapht - 100;
    var mapwd = $('#map').width() - 120;
    $('#geoCtrl').css('top', winht);
    $('#geoCtrl').css('left', mapwd);
    return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);
var geoIcon = "../images/currentLoc.png";
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
var mapdone = $.Deferred();
function initMap() {
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
    mapdone.resolve();
    // ///////////////////////////   MARKER CREATION   ////////////////////////////
    CL.forEach(function (clobj) {
        AddClusterMarker(clobj.loc, clobj.group, clobj.hikes, clobj.page);
    });
    NM.forEach(function (nmobj) {
        AddHikeMarker(nmobj);
    });
    var iwContent;
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
        var srchmrkr = { hikeid: group, clicked: false, pin: marker };
        locaters.push(srchmrkr);
        var itemno = locaters.length - 1;
        clustererMarkerSet.push(marker);
        iwContent = '<div id="iwCH">';
        var link;
        if (page > 0) {
            link = "hikePageTemplate.php?clus=y&hikeIndx=";
            iwContent += '<br /><a href="' + link + page + '">' + group + '</a>';
        }
        else {
            iwContent += '<br />' + group;
        }
        link = "hikePageTemplate.php?hikeIndx=";
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
            locaters[itemno].clicked = false;
        });
        marker.addListener('click', function () {
            if (zoom_level < 13) {
                map.setZoom(13);
            }
            map.setCenter(location);
            iw.open(map, this);
            locaters[itemno].clicked = true;
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
        var srchmrkr = { hikeid: hikeobj.name, clicked: false, pin: marker };
        locaters.push(srchmrkr);
        var itemno = locaters.length - 1;
        clustererMarkerSet.push(marker);
        // infoWin content: add data for this hike
        var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
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
            locaters[itemno].clicked = false;
        });
        marker.addListener('click', function () {
            if (zoom_level < 13) {
                map.setZoom(13);
            }
            map.setCenter(hikeobj.loc);
            iw.open(map, this);
            locaters[itemno].clicked = true;
        });
    }
    // /////////////////////// Marker Grouping /////////////////////////
    var clusterer_opts = {
        imagePath: '../images/markerclusters/m',
        gridSize: 50,
        maxZoom: 12,
        averageCenter: true,
        zoomOnClick: true
    };
    new MarkerClusterer(map, clustererMarkerSet, clusterer_opts);
    // //////////////////////// PAN AND ZOOM HANDLERS ///////////////////////////////
    map.addListener('zoom_changed', function () {
        var zoomTracks = false;
        zoomdone = $.Deferred();
        var idle = google.maps.event.addListener(map, 'idle', function () {
            var curZoom = map.getZoom();
            var perim = String(map.getBounds());
            if (curZoom > 12) {
                zoomTracks = true;
            }
            zoomedHikes = IdTableElements(perim, zoomTracks);
            if (zoomTracks && zoomedHikes.length > 0) {
                $.when(zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])).then(function () {
                    zoomdone.resolve();
                    google.maps.event.removeListener(idle);
                });
            }
            else {
                zoomdone.resolve();
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
        var zoomTracks = true;
        if (curr_zoom < 13) {
            zoomTracks = false;
        }
        var newBds = String(map.getBounds());
        zoomedHikes = IdTableElements(newBds, zoomTracks);
        if (zoomTracks && zoomedHikes.length > 0) {
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
 * This file will create tracks for the input arrays of hike objects and clusters.
 * If a track has already been created, it will not be created again.
 *
 * @param {array} hikenos The array of hike numbers within zoomed map bounds
 * @param {array} infoWins The array of infoWins corresponding to hikenos
 * @param {array} trackcolors Colors assigned to a track
 *
 * @return {array} promises (deferred objects)
 */
function zoom_track(hikenos, infoWins, trackcolors) {
    var promises = [];
    for (var i_1 = 0, j = 0; i_1 < hikenos.length; i_1++, j++) {
        if (!drawnHikes.includes(hikenos[i_1])) {
            if (tracks[hikenos[i_1]] !== '') {
                var sgldef = $.Deferred();
                promises.push(sgldef);
                var trackfile = '../../json/' + tracks[hikenos[i_1]];
                drawnHikes.push(hikenos[i_1]);
                if (j === trackcolors.length) {
                    j = 0; // rollover colors when # of tracks > # of colors
                }
                drawTrack(trackfile, infoWins[i_1], trackcolors[j], hikenos[i_1], sgldef);
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
        error: function (_jqXHR, _textStatus, _errorThrown) {
            var msg = 'Did not succeed in getting JSON data: ' +
                json_filename;
            alert(msg);
            deferred.reject();
        }
    });
    return;
}
// /////////////////////////  END TRACK DRAWING  ///////////////////////////
// //////////////////////////  GEOLOCATION CODE ////////////////////////////
// THIS ISN'T WORKING AND I DON'T KNOW WHY - IT USED TO....
function setupLoc() {
    navigator.geolocation.getCurrentPosition(success, error, geoOpts);
    function success(_pos) {
        var geoPos = _pos.coords;
        var geoLat = geoPos.latitude;
        var geoLng = geoPos.longitude;
        var newWPos = { lat: geoLat, lng: geoLng };
        new google.maps.Marker({
            position: newWPos,
            map: map,
            icon: geoIcon,
        });
        map.setCenter(newWPos);
        var currzoom = map.getZoom();
        if (currzoom < 13) {
            map.setZoom(13);
        }
    }
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
    var newWinWidth = window.innerWidth;
    var mapWidth = Math.round(0.72 * newWinWidth);
    var tblWidth = newWinWidth - (mapWidth + 3); // 3px = adjustWidth
    initDivParms();
    $map.css('width', mapWidth + 'px');
    $('#sideTable').css('width', tblWidth + 'px');
    locateGeoSym();
    positionFavTooltips();
});
// //////////////////////////////////////////////////////////////
