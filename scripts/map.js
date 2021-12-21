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
 * @version 5.0 Reworked to synchronize with the new thumbnail loading process
 * @version 6.0 Change from old panel design to bootstrap navbar design
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
// map event handler globals for determining resulting action in handler
var panning = false;
var marker_click = false;
var zoomThresh = 13;
var panel = $('#nav').height() + $('#logo').height();
/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible.
 */
var initDivParms = function () {
    mapht = $(window).height() - panel;
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
 * This function places the geopositioning symbol in the lower right corner of the map
 */
function locateGeoSym() {
    var fromTop = panel + mapht - 84;
    var fromLft = $('#map').width() - 120;
    $('#geoCtrl').css('top', fromTop);
    $('#geoCtrl').css('left', fromLft);
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
function initMap() {
    var clustererMarkerSet = [];
    var nmCtr = { lat: 34.450, lng: -106.042 };
    var json_style_array = [
        { "featureType": "poi", "stylers": [{ "visibility": "off" }] }
    ];
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
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        styles: json_style_array
    });
    new google.maps.KmlLayer({
        url: "https://nmhikes.com/maps/NM_Borders.kml",
        map: map
    });
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
            if (loadSpreader !== undefined) {
                clearInterval(loadSpreader);
                loadSpreader = undefined;
            }
            zoom_level = map.getZoom();
            marker_click = zoom_level < zoomThresh ? true : false;
            map.setCenter(location);
            if (marker_click) {
                map.setZoom(zoomThresh);
                marker_click = false;
            }
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
            if (loadSpreader !== undefined) {
                clearInterval(loadSpreader);
                loadSpreader = undefined;
            }
            zoom_level = map.getZoom();
            marker_click = zoom_level < zoomThresh ? true : false;
            map.setCenter(hikeobj.loc);
            if (marker_click) {
                map.setZoom(zoomThresh);
                marker_click = false;
            }
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
        if (typeof loadSpreader !== undefined) {
            clearInterval(loadSpreader);
            loadSpreader = undefined;
        }
        var zoomTracks = false;
        zoomdone = $.Deferred();
        var idle = google.maps.event.addListener(map, 'idle', function () {
            var curZoom = map.getZoom();
            var perim = String(map.getBounds());
            if (curZoom >= zoomThresh) {
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
        if (loadSpreader !== undefined) {
            clearInterval(loadSpreader);
            loadSpreader = undefined;
        }
        panning = true;
    });
    map.addListener('dragend', function () {
        // no highlighting is required during pan
        var curr_zoom = map.getZoom();
        var zoomTracks = false;
        if (curr_zoom >= zoomThresh) {
            zoomTracks = true;
        }
        var newBds = String(map.getBounds());
        zoomedHikes = IdTableElements(newBds, zoomTracks);
        if (zoomTracks && zoomedHikes.length > 0) {
            zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2]);
        }
        panning = false;
    });
    map.addListener('center_changed', function () {
        if (!panning && (!marker_click && !cluster_click)) {
            if (loadSpreader !== undefined) {
                clearInterval(loadSpreader);
                loadSpreader = undefined;
            }
            var curZoom = map.getZoom();
            var zoomTracks = false;
            var perim = String(map.getBounds());
            if (curZoom >= zoomThresh) {
                zoomTracks = true;
            }
            zoomedHikes = IdTableElements(perim, zoomTracks);
            if (zoomTracks && zoomedHikes.length > 0) {
                zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2]);
                if (applyHighlighting) {
                    restoreTracks();
                    highlightTracks();
                }
            }
        }
        else {
            cluster_click = false;
        }
    });
}
// ////////////////////// END OF MAP INITIALIZATION  ///////////////////////
// ///////////////////////////  TRACK DRAWING  /////////////////////////////
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
            var usererr = "User couldn't retrieve json file: " +
                json_filename;
            var errobj = { err: usererr };
            $.post('../php/ajaxError.php', errobj);
            deferred.reject();
        }
    });
    return;
}
// /////////////////////////  END TRACK DRAWING  ///////////////////////////
// //////////////////////////  GEOLOCATION CODE ////////////////////////////
/**
 * Drop the geolocation symbol on the user's current location
 */
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
            icon: geoIcon
        });
        map.setCenter(newWPos);
        var currzoom = map.getZoom();
        if (currzoom < zoomThresh) {
            map.setZoom(zoomThresh);
        }
    }
    function error(eobj) {
        var msg = 'Error retrieving position; Code: ' + eobj.code;
        window.alert(msg);
    }
}
// //////////////////////  MAP FULL SCREEN DETECT  //////////////////////
$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange', function () {
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
    $('.like').each(function () {
        var $icon = $(this);
        var $tooldiv = $icon.parent().prev();
        positionFavToolTip($tooldiv, $icon);
    });
    google.maps.event.trigger(map, "resize");
});
// //////////////////////////////////////////////////////////////
