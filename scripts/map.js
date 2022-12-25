"use strict";
/// <reference path='./map.d.ts' />
/**
 * @fileoverview This routine initializes the google map to view the state
 *		of New Mexico, places markers on hike locations, and clusters the markers
 * 		together displaying the number of hikes in the group. It also draws hike
 * 		tracks when zoomed in, and afterwards when panned. The script relies on the
 * 		externally supplied lib 'markerclusterer.js'. That lib was modified slightly
 *      by adding a line specifying the state of boolean 'newBounds' to prevent
 *      duplicate calls to form a side table (see Pan and Zoom handlers below).
 * @author Ken Cowles
 * @version 3.0 Added Cluster Page compatibility (removes indexPageTemplate links)
 * @version 4.0 Typescripted, with some type errors corrected
 * @version 5.0 Reworked to synchronize with the new thumbnail loading process
 * @version 6.0 Change from old panel design to bootstrap navbar design
 * @version 7.0 Rework asynchronous map handlers to better control behavior; increase
 * 				number of track colors available.
 * @version 7.1 Eliminated remnants of old side table asynch loading; simplified handlers
 *              to reduce no. of events that triggered side tble creation.
 * @version 7.2 Minor 'fileoverview' edit
 * @version 7.3 Switched from google raster map to vector map (Note new mapId)
 * NOTE: 7.3 fixed an unknown problem happening only on the home machine, regardless of
 * browser used. The side tables would not initially display until a manual zoom occurred.
 * No other machine (or tablet) seemed to display this anomaly.
 * @version 7.4 Changed <a> links to open new tab
 */
var zoomThresh = 13; // Default zoom level for drawing tracks
// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
var colors = [
    'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
var geoOpts = { enableHighAccuracy: true };
// globals:
var map;
var $fullScreenDiv; // Google's hidden inner div when clicking on full screen mode
var $map = $('#map');
var mapEl = $map.get(0);
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
// map event handler global used to prevent repeatitive event triggers when panning
var panning = false;
// setting space for ktesaPanel
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
    strokeColor: 'Black',
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
    /* For reasons unknown, placing the options object directly within the
     * google.maps.Map argument seems to ignore all mapTypeControl settings
     */
    var options = {
        center: nmCtr,
        zoom: 7,
        mapId: "39681f98dcd429f8",
        // optional settings:
        isFractionalZoomEnabled: true,
        zoomControl: true,
        scaleControl: true,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
            mapTypeIds: [
                google.maps.MapTypeId.TERRAIN,
                google.maps.MapTypeId.SATELLITE
            ]
        },
        fullscreenControl: true,
        streetViewControl: false,
        rotateControl: false,
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        styles: json_style_array
    };
    map = new google.maps.Map(mapEl, options);
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
            iwContent += '<br /><a href="' + link + clobj.indx + '" target="_blank">' +
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
            zoom_level = map.getZoom();
            // newBounds is true if only a center change and no follow-on zoom
            window.newBounds = zoom_level >= zoomThresh ? true : false;
            map.setCenter(location);
            if (!window.newBounds) {
                map.setZoom(zoomThresh);
            }
            iw.open(map, this);
            locaters[itemno].clicked = true;
        });
    }
    // Normal Hike Markers
    function AddHikeMarker(hikeobj) {
        var markerLoc = hikeobj.loc;
        var nmicon = getIcon(1);
        var marker = new google.maps.Marker({
            position: markerLoc,
            map: map,
            icon: nmicon,
            // 'title' is what is displayed on mouseover of the marker
            title: hikeobj.name
        });
        var srchmrkr = { hikeid: hikeobj.name, clicked: false, pin: marker };
        locaters.push(srchmrkr);
        var itemno = locaters.length - 1;
        clustererMarkerSet.push(marker);
        // infoWin content: add data for this hike
        var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
            + hikeobj.indx + '" target="_blank">' + hikeobj.name + '</a><br />';
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
            zoom_level = map.getZoom();
            // newBounds is true if only a center change with no follow-on zoom
            // this statement must precede the setCenter cmd.
            window.newBounds = zoom_level >= zoomThresh ? true : false;
            map.setCenter(markerLoc);
            if (!window.newBounds) {
                map.setZoom(zoomThresh);
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
    /**
     * NOTE: Loading the map on page load/reload causes an initial center_change AND
     * zoom_change event [with or without the markerclusterer.js and/or kml overlay
     * (NM Boundary on map)]; The 'center_change' occurs first. Map event trigger code
     * has been arranged to call setCenter before setZoom in each case.
     */
    /**
     * PANNING: a 'center_change' event will obviously occur, so a variable called
     * 'panning' is set to prevent the 'center_change' listener from acting. The 'center_change'
     *  event will be triggered repeatedly but will not affect the pan.
     */
    map.addListener('dragstart', function () {
        panning = true;
    });
    map.addListener('dragend', function () {
        // this should be done by setIdleListener.... always set panning false;
        var curr_zoom = map.getZoom();
        var zoomTracks = curr_zoom >= zoomThresh ? true : false;
        var newBds = String(map.getBounds());
        zoomedHikes = IdTableElements(newBds, zoomTracks);
        if (zoomTracks && zoomedHikes[0].length > 0) {
            $.when(zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])).then(function () {
                panning = false;
            });
        }
        else {
            panning = false;
        }
    });
    /**
     * The goal is to create a side table once and only once per user-initiated
     * map event. When there is only a center change (not resulting from a pan event,
     * which is handled separately), form the side table. This will happen, e.g., when
     * the map is already zoomed in to zoomThresh level (or greater). When a zoom is to
     * follow the center change, then let only the zoom form the side table, as the side
     * table would otherwise need to be re-formed immediately after having been formed
     * by the re-centering, resulting in potential asynch operational conflicts
     * 1. The var "newBounds" is set false on initialization, so the home page load
     *    will only form the table after the zoom event is complete.
     * 2. When completing a search in the searchbar,
     * 3. A click on any clusterer (see markerclusterer.js) will shift center via
     *    'map.fitBounds' - the bounds which were established by the clusterer and
     *    assigned during creation, and when zoomOnClick option is 'true'. This
     *    seems to register two consecutive 'center change/zoom's the first time
     *    a cluster is clicked, but only one 'center change/zoom' thereafter. The
     *    3rd party software has been modified to set var "newBounds" false so that
     *    the zoom event controls the map formation.
     * 4. A click on any marker will shift center. This is determined by the order of
     *    code execution as defined in the marker listeners. [NOTE: even if the marker
     *    were already 'dead center', the click would shift it out then back again];
     *    Note that when the zoom is already at zoomThresh or greater, the marker
     *    click will not be followed by a zoom.
     */
    map.addListener('center_changed', function () {
        if (panning) { // when panning, simply wait for the dragend event
            return;
        }
        else {
            if (window.newBounds) { // if a center change only, initiate side table formation
                setIdleListener();
                window.newBounds = false;
            }
        }
    });
    /**
     * Zoom change will always initiate the side table formation.
     */
    map.addListener('zoom_changed', function () {
        setIdleListener();
    });
    /**
     * The time to update the side table and tracks is when any of the events has completed
     * and the map has returned to an idle state. This function performs the idle ops,
     * which include re-generating the side table for the new bounds, and if the zoom
     * threshold is active, then draw any newly included tracks. Note that when done,
     * the listener is removed.
     */
    function setIdleListener() {
        var idle = google.maps.event.addListener(map, 'idle', function () {
            // wait for markerclusterer.js to draw clusters
            var curZoom = map.getZoom();
            var zoomTracks = curZoom >= zoomThresh ? true : false;
            var perim = String(map.getBounds());
            zoomedHikes = IdTableElements(perim, zoomTracks);
            if (zoomTracks && zoomedHikes[0].length > 0) {
                $.when(zoom_track(zoomedHikes[0], zoomedHikes[1], zoomedHikes[2])).then(function () {
                    if (applyHighlighting) {
                        restoreTracks();
                        highlightTracks();
                    }
                    google.maps.event.removeListener(idle);
                });
            }
            else {
                google.maps.event.removeListener(idle);
            }
        });
    }
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
    mapTick.fillcolor = color;
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
        var currzoom = map.getZoom();
        window.newBounds = currzoom >= zoomThresh ? true : false;
        map.setCenter(newWPos);
        if (!window.newBounds) {
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
