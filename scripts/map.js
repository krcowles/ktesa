"use strict";
/// <reference path='./map.d.ts' />
/**
 * @fileoverview This routine initializes the google map to view the state
 *		of New Mexico, places markers on hike locations, and clusters the markers
 * 		together, displaying the number of hikes in each cluster. It also draws hike
 * 		tracks when zoomed in, and afterwards when panned. The script relies on the
 * 		externally supplied lib 'markerclusterer.js'. That lib was modified slightly
 *      by adding a line specifying the state of boolean 'newBounds' to prevent
 *      duplicate calls to form a side table (see Pan and Zoom handlers below).
 * @author Ken Cowles
 *
 * @version 8.0 Major mods to improve side table formation when multiple map events occur
 */
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
var zoomThresh = 13; // Default zoom level for drawing tracks
// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
var colors = [
    'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
var geoOpts = { enableHighAccuracy: true };
// global vars:
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
var first_load = true;
/**
 *  'panning' global is used to prevent repetitive event triggers when panning
 *  'kill_table' informs any current side table formation to 'abort'
 */
var panning = false;
var kill_table = false;
/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible; 'panel' is also used
 * in locateGeoSymbol().
 */
var panel = $('#nav').height() + $('#logo').height();
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
        var utname = unTranslate(hikeobj.name);
        var marker = new google.maps.Marker({
            position: markerLoc,
            map: map,
            icon: nmicon,
            // 'title' is what is displayed on mouseover of the marker
            title: utname /// hikeobj.name
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
     * (NM Boundary on map)]; The 'center_change' occurs first. All map event trigger
     * code in this script has been arranged to call setCenter() before setZoom().
     * The 'first_load' condition invokes a simplified 'idle' listener
     */
    /**
     * PANNING: a 'center_change' event will obviously occur, so a variable called
     * 'panning' is set to prevent the 'center_change' listener from repeatedly
     * responding as the pan progresses.
     */
    map.addListener('dragstart', function () {
        kill_table = true;
        panning = true;
    });
    map.addListener('dragend', function () {
        setIdleListener('de'); // Drag End...
    });
    /**
     * The goal is to create a side table once and only once per user-initiated
     * map event. If a follow-on event occurs while the side table is still under
     * construction, it will be aborted and started anew with the new bounds.
     *                    ---- Other considerations ----
     * When there is only a center change (not resulting from a pan event,
     * which is handled separately), form the side table. This will happen, e.g., when
     * the map is already zoomed in to zoomThresh level (or greater). When a zoom is to
     * follow the center change, then let only the zoom form the side table in order
     * to reduce invocations of side table formation.
     *
     * 1. Since page load/reload triggers a center_change & zoom, the var "newBounds"
     *    is set false on initialization to prevent the load from invoking both
     *    center_change and zoom invocations of the side table.
     * 2. When completing a search in the searchbar, the "newBounds" may be set to
     *    indicate that only a center change is occurring.
     * 3. A click on any clusterer (see markerclusterer.js) will shift center via
     *    'map.fitBounds' - the bounds which were established by the clusterer and
     *    assigned during creation, and when zoomOnClick option is 'true'. This
     *    seems to register two consecutive 'center change/zoom's the first time
     *    a cluster is clicked, but only one 'center change/zoom' thereafter. The
     *    3rd party software has been modified to set the var "newBounds" false so
     *    that only the zoom event controls the side table formation.
     * 4. A click on any marker will shift center. This is determined by the order of
     *    code execution as defined in the marker listeners. [NOTE: even if the marker
     *    were already 'dead center', the click would shift it out then back again];
     *    Note that when the zoom is already at zoomThresh or greater, the marker
     *    click will not be followed by a zoom.
     *
     * Lastly, the setIdleListener function has an argument to indicate the event
     * invoking the function, but only the 'pan' event requires it. It was originally
     * used to understand event synchronization.
     */
    map.addListener('center_changed', function () {
        if (panning) { // when panning, simply wait for the dragend event
            return;
        }
        else {
            if (!first_load) {
                kill_table = true;
            }
            if (window.newBounds) {
                // if a center change only, initiate side table formation
                setIdleListener('cc'); // Center Change
                window.newBounds = false;
            }
        }
    });
    /**
     * Zoom change will always initiate the side table formation.
     */
    map.addListener('zoom_changed', function () {
        if (!first_load) {
            kill_table = true;
        }
        setIdleListener('zm'); // ZooM
    });
    /**
     * NOTE: 'idle' does not mean the map is displayed!
     *
     * The time to update the side table and tracks is when any of the events has
     * completed and the map has returned to an idle state. This idle listener
     * executes the idle ops, which include re-generating the side table for the
     * new bounds, and if the zoom threshold is active, draw any newly included tracks.
     */
    function setIdleListener(event_type) {
        if (first_load) {
            var init_idle = google.maps.event.addListener(map, 'idle', function () {
                // first load always has zoom < zoomThresh
                kill_table = false;
                first_load = false;
                var bounds = String(map.getBounds());
                var hike_result = IdTableElements(bounds, false);
                formTbl(hike_result[0]);
                google.maps.event.removeListener(init_idle);
            });
        }
        else {
            console.log('Idle');
            var idle = google.maps.event.addListener(map, 'idle', function () {
                return __awaiter(this, void 0, void 0, function () {
                    var curZoom, zoomTracks, perim;
                    return __generator(this, function (_a) {
                        switch (_a.label) {
                            case 0:
                                curZoom = map.getZoom();
                                zoomTracks = curZoom >= zoomThresh ? true : false;
                                perim = String(map.getBounds());
                                // in case of intervening map event:
                                kill_table = false;
                                zoomedHikes = IdTableElements(perim, zoomTracks);
                                return [4 /*yield*/, formTbl(zoomedHikes[0])];
                            case 1:
                                _a.sent();
                                if (zoomTracks && zoomedHikes[1].length > 0) {
                                    $.when(zoom_track(zoomedHikes[1], zoomedHikes[2], zoomedHikes[3])).then(function () {
                                        if (event_type === 'de') {
                                            panning = false;
                                        }
                                        else {
                                            if (applyHighlighting) {
                                                restoreTracks();
                                                highlightTracks();
                                            }
                                        }
                                        google.maps.event.removeListener(idle);
                                    });
                                }
                                else {
                                    if (event_type === 'de') {
                                        panning = false;
                                    }
                                    google.maps.event.removeListener(idle);
                                }
                                return [2 /*return*/];
                        }
                    });
                });
            });
        }
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
