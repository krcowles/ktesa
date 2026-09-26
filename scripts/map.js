"use strict";
/// <reference path='./map.d.ts' />
/**
 * @fileoverview This routine initializes the google map to view the state
 *		of New Mexico, places markers on hike locations, and clusters the markers
 * 		together, displaying the number of hikes in each cluster. It also draws hike
 * 		tracks when zoomed in, and afterwards when panned. The clusterer is now
 *      supported by the google maps javascript API. A window property (boolean
 *      'newBounds') is used to prevent duplicate calls to form a side table
 *      (see Pan and Zoom handlers below).
 * @author Ken Cowles
 *
 * @version 8.0 Major mods to improve side table formation when multiple map events occur
 * @version 9.0 Modified to support new Google maps marker type (AdvancedMarkerElement)
 * @version 9.1 Added margins to map when cluster is clicked
 * @version 10.0 Replaced the deprecated kmlLayer call in Google Maps with a google.Polygon.
 */
const hike_mrkr_icon = "../images/blue_nobg.png";
// <a href="https://www.flaticon.com/free-icons/marker" title="marker icons">Marker icons created by Vector Stall - Flaticon</a>
const clus_mrkr_icon = "../images/star8.png";
const initialValue = 0;
const zoomThresh = 13; // Default zoom level for drawing tracks
// Hike Track Colors on Map: [NOTE: Yellow is reserved for highlighting]
const colors = [
    'Red', 'Blue', 'DarkGreen', 'HotPink', 'DarkBlue', 'Chocolate', 'DarkViolet', 'Black'
];
var hikearr; // array of hike objects used to form side table
const geoOpts = { enableHighAccuracy: true };
var markers;
var appMode = $('#appMode').text();
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
 * Because the map, adjustWidth, and sideTable divs are floats, height
 * needs to be specified for the divs to be visible; 'panel' is also used
 * in locateGeoSymbol().
 * NOTE: The sizes of the <img> icons in ktesaPanel.php must now be specified
 * explicitly in order to eliminate a timing problem rendering the side table.
 * The new method of using a polygon instead of a kml layer call (which is now
 * deprecated), resolves much faster, introducing a timing change. It is worth
 * noting that Firefox may report numerous info messages regarding the new
 * method, while Chrome does not. These can be safely ignored.
 */
var panel = $('#nav').height() + $('#logo').height();
const initDivParms = () => {
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
var locaters = []; // global used to popup info window on map when hike is searched
/**
 * Collect the number of hikes associated with a clusterer for labelling purposes
 */
const makeClusterLabel = (markers) => {
    var total = [];
    markers.forEach(function (mrkr) {
        total.push(Number(mrkr.hikes));
    });
    var hike_total = total.reduce((accumulator, currentValue) => accumulator + currentValue, initialValue);
    return hike_total;
};
/**
 * Create a DOM element containing a marker or clusterer icon with a mrkr_cnt div showing
 * the number of hikes associated with it
 */
const build_content = (glyph, count) => {
    var gtop;
    var glft;
    var gsize;
    var gpadding = "0 3px 0 3px";
    if (glyph === hike_mrkr_icon) { // single marker or cluster marker
        gtop = "10px";
        glft = "13px";
        gsize = "11px";
    }
    else {
        gtop = "10px";
        if (count < 10) {
            glft = "12px";
            gsize = "11px;";
        }
        else if (count < 100) {
            glft = "10px";
            gsize = "10px;";
            gpadding = "0 2px 0 2px";
        }
        else {
            gtop = "11px";
            glft = "9px";
            gsize = "9px";
            gpadding = "0 2px 0 2px";
        }
    }
    var content = document.createElement("div");
    var icon = document.createElement("img");
    var mrkr_cnt = document.createElement("div");
    var mrkr_txt = document.createTextNode(String(count));
    mrkr_cnt.style.background = "white";
    mrkr_cnt.style.position = "absolute";
    mrkr_cnt.style.top = gtop;
    mrkr_cnt.style.left = glft;
    mrkr_cnt.style.fontSize = gsize;
    mrkr_cnt.style.padding = gpadding;
    mrkr_cnt.style.borderRadius = "6px";
    mrkr_cnt.appendChild(mrkr_txt);
    icon.style.zIndex = "900";
    icon.src = glyph;
    content.appendChild(icon);
    content.appendChild(mrkr_cnt);
    return content;
};
/**
 * Use the arrays passed in to the home page by php: one for each type
 * of marker to be displayed (Clustered, Normal):
 *      NM Array: Objects describing 'normal' hikes (single tracks)
 * 		CL Array: Objects describinb 'clustered' hikes (more than 1 track)
 * And an array for creating tracks: (see track drawing near end of script)
 * 		tracks Array: ordered list of json file names
 */
var nm_marker_data = [];
NM.forEach(function (hikeobj) {
    var mrkr_loc = hikeobj.loc;
    var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
        + hikeobj.indx + '" target="_blank">' + hikeobj.name + '</a><br />';
    iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
    iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
    iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
    iwContent += '<a href="' + hikeobj.dirs + '">Directions</a></div>';
    var nm_title = hikeobj.name;
    var nm_marker = { position: mrkr_loc, iw_content: iwContent, title: nm_title };
    nm_marker_data.push(nm_marker);
});
const cl_marker_data = [];
CL.forEach(function (clobj) {
    const mrkr_loc = clobj.loc;
    const hikecnt = clobj.hikes.length;
    let iwContent = '<div id="iwCH">';
    var link;
    if (clobj.page > 0) {
        link = "hikePageTemplate.php?clus=y&hikeIndx=";
        iwContent += '<a href="' + link + clobj.page + '">' +
            clobj.group + '</a>';
    }
    else {
        iwContent += clobj.group + "<br/>";
    }
    link = "hikePageTemplate.php?hikeIndx=";
    clobj.hikes.forEach(function (clobj) {
        iwContent += '<br/><a href="' + link + clobj.indx + '" target="_blank">' +
            clobj.name + '</a>';
        iwContent += ' Lgth: ' + clobj.lgth + ' miles; Elev Chg: ' +
            clobj.elev + ' ft; Diff: ' + clobj.diff;
    });
    var cl_marker = { position: mrkr_loc, iw_content: iwContent,
        title: clobj.group, hikecnt: hikecnt };
    cl_marker_data.push(cl_marker);
});
// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
function initMap() {
    const nmCtr = { lat: 34.450, lng: -106.042 };
    var options = {
        center: nmCtr,
        zoom: 7,
        mapId: "39681f98dcd429f8", // vector map; all styling
        // optional settings:
        isFractionalZoomEnabled: true,
        zoomControl: true,
        scaleControl: true,
        fullscreenControl: true,
        streetViewControl: false,
        rotateControl: false,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
            mapTypeIds: [
                google.maps.MapTypeId.TERRAIN,
                google.maps.MapTypeId.SATELLITE
            ]
        },
        mapTypeId: 'terrain',
    };
    map = new google.maps.Map(mapEl, options);
    /**
     * In order to reduce the somewhat extensive comments explaining map
     * listeners, use the ordinal to refer to the list of comments at the
     * end of the file.
     */
    // 1. [References at end of file]
    google.maps.event.addListenerOnce(map, 'idle', function () {
        kill_table = false;
        first_load = false;
        var bounds = String(map.getBounds());
        var hike_result = IdTableElements(bounds, false, 7);
        formTbl(hike_result[0]);
    });
    // 2. [References at end of file]
    map.addListener('dragstart', function () {
        kill_table = true;
        panning = true;
    });
    map.addListener('dragend', function () {
        setIdleListener('de'); // Drag End...
    });
    // 3. [References at end of file]
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
    // 4. [References at end of file]
    map.addListener('zoom_changed', function () {
        if (!first_load) {
            kill_table = true;
        }
        setIdleListener('zm'); // ZooM
    });
    // New method to replace deprecated kml layer	
    const nmBorderPath = [
        { lat: 32.4420444958635, lng: -109.049495308693 },
        { lat: 31.3434530504803, lng: -109.045615049533 },
        { lat: 31.3438536159949, lng: -108.210647795015 },
        { lat: 31.7869032382901, lng: -108.203254915468 },
        { lat: 31.7850830856735, lng: -107.283567177026 },
        { lat: 31.7863052774039, lng: -106.539514775671 },
        { lat: 31.8178343839605, lng: -106.614986549809 },
        { lat: 31.8447405331725, lng: -106.61612370633 },
        { lat: 31.8952054369513, lng: -106.64407909206 },
        { lat: 31.9141010099202, lng: -106.633748923526 },
        { lat: 31.9722199884253, lng: -106.632605287073 },
        { lat: 31.9803297275266, lng: -106.650061890884 },
        { lat: 32.0010887853062, lng: -106.623625658904 },
        { lat: 32.0007470652221, lng: -106.378387283311 },
        { lat: 32.0016580242852, lng: -106.00324037618 },
        { lat: 32.004382108736, lng: -104.922304814538 },
        { lat: 32.0032650272727, lng: -104.85106805229 },
        { lat: 32.0074034895819, lng: -104.019296949948 },
        { lat: 32.0060152222294, lng: -103.981377077849 },
        { lat: 32.0062289025373, lng: -103.729444279739 },
        { lat: 32.0042814747499, lng: -103.332549418381 },
        { lat: 32.0020227787787, lng: -103.058413767661 },
        { lat: 32.0851168230641, lng: -103.055640531826 },
        { lat: 32.5155455179839, lng: -103.060018185604 },
        { lat: 32.9536389141087, lng: -103.049330863501 },
        { lat: 33.3778314740626, lng: -103.043100992793 },
        { lat: 33.5658431867775, lng: -103.038736452727 },
        { lat: 33.8261815911813, lng: -103.033258497866 },
        { lat: 34.3078204763021, lng: -103.029645833697 },
        { lat: 34.7453327558501, lng: -103.022657024631 },
        { lat: 34.9647798759331, lng: -103.025251273923 },
        { lat: 35.1772655150643, lng: -103.026151164684 },
        { lat: 35.6236480179456, lng: -103.02229404801 },
        { lat: 35.742327299615, lng: -103.022612263713 },
        { lat: 36.0560618512093, lng: -103.024047954518 },
        { lat: 36.4915918464103, lng: -103.027286789536 },
        { lat: 36.4923701848871, lng: -102.997400999016 },
        { lat: 36.9985238353847, lng: -102.997709442614 },
        { lat: 36.9997601837273, lng: -103.07786588474 },
        { lat: 36.9944690622369, lng: -103.993635035945 },
        { lat: 36.9932073726899, lng: -105.146172547082 },
        { lat: 36.992604521715, lng: -105.213091465415 },
        { lat: 36.9945603614965, lng: -105.713459997846 },
        { lat: 36.992289650437, lng: -105.992000086492 },
        { lat: 36.9915042439681, lng: -106.472176939021 },
        { lat: 36.9895015941857, lng: -106.86124887722 },
        { lat: 36.9990837907051, lng: -106.89037023567 },
        { lat: 36.9975257849804, lng: -107.410820543541 },
        { lat: 36.9987767566937, lng: -107.472460293817 },
        { lat: 36.999471575633, lng: -108.372472924296 },
        { lat: 36.9966409005893, lng: -109.048480115363 },
        { lat: 35.9966639816639, lng: -109.047846506598 },
        { lat: 34.9546462439613, lng: -109.046640810431 },
        { lat: 34.5917805775226, lng: -109.048652751175 },
        { lat: 33.7833019238717, lng: -109.050349253456 },
        { lat: 33.205164822801, lng: -109.050525833602 },
        { lat: 32.7795505537932, lng: -109.051346155985 },
        { lat: 32.4420444958635, lng: -109.049495308693 }
    ];
    new google.maps.Polygon({
        paths: nmBorderPath,
        strokeColor: '#00cc00',
        strokeOpacity: 0.4,
        strokeWeight: 2,
        fillColor: '#002200',
        fillOpacity: 0,
        map: map
    });
    // end new method
    const infoWindow = new google.maps.InfoWindow({
        content: "",
        disableAutoPan: true,
        maxWidth: 400
    });
    // ///////////////////////////   MARKER CREATION   ////////////////////////////
    const nm_markers = nm_marker_data.map((mrkr_data) => {
        const position = mrkr_data.position;
        const nm_title = mrkr_data.title;
        // THE MARKER:
        const marker = new google.maps.marker.AdvancedMarkerElement({
            position: position,
            map: map,
            content: build_content(hike_mrkr_icon, 1),
            title: nm_title
        });
        marker.hikes = 1;
        // MARKER SEARCH:
        const srchmrkr = {
            hikeid: mrkr_data.title,
            clicked: false,
            pin: marker
        };
        locaters.push(srchmrkr);
        const itemno = locaters.length - 1;
        // CLICK ON MARKER:
        marker.addListener("click", () => {
            zoom_level = map.getZoom();
            // newBounds is true if only a center change with no follow-on zoom
            // this statement must precede the setCenter cmd.
            window.newBounds = zoom_level >= zoomThresh ? true : false;
            map.setCenter(mrkr_data.position);
            if (!window.newBounds) {
                map.setZoom(zoomThresh);
            }
            let this_mrkr = locaters[itemno];
            this_mrkr.clicked = true;
            infoWindow.setContent(mrkr_data.iw_content);
            infoWindow.open(map, marker);
        });
        // INFO WINDOW CLOSE:
        infoWindow.addListener('closeclick', function () {
            locaters[itemno].clicked = false;
        });
        return marker;
    });
    const cl_markers = cl_marker_data.map((mrkr_data) => {
        const position = mrkr_data.position;
        const cl_title = mrkr_data.title;
        const hike_count = mrkr_data.hikecnt;
        // THE MARKER:
        const marker = new google.maps.marker.AdvancedMarkerElement({
            position: position,
            map: map,
            content: build_content(hike_mrkr_icon, hike_count),
            title: cl_title,
            gmpClickable: true
        });
        marker.hikes = hike_count;
        // MARKER SEARCH:
        const srchmrkr = {
            hikeid: mrkr_data.title,
            clicked: false,
            pin: marker
        };
        locaters.push(srchmrkr);
        const itemno = locaters.length - 1;
        // CLICK ON MARKER:
        marker.addListener("click", () => {
            zoom_level = map.getZoom();
            // newBounds is true if only a center change and no follow-on zoom
            window.newBounds = zoom_level >= zoomThresh ? true : false;
            map.setCenter(position);
            if (!window.newBounds) {
                map.setZoom(zoomThresh);
            }
            locaters[itemno].clicked = true;
            infoWindow.setContent(mrkr_data.iw_content);
            infoWindow.open(map, marker);
        });
        // INFO WINDOW CLOSE:
        infoWindow.addListener('closeclick', function () {
            locaters[itemno].clicked = false;
        });
        return marker;
    });
    markers = [...nm_markers, ...cl_markers];
    const renderer = {
        /**
         * render( CLUSTER, stats, map) where CLUSTER 'Accessors' are bounds, count, position
         * and 'cluster' contains various properties, including _position, and markers[]
         */
        render: function (cluster) {
            var marker_label = makeClusterLabel(cluster.markers);
            return new google.maps.marker.AdvancedMarkerElement({
                position: cluster._position,
                map: map,
                content: build_content(clus_mrkr_icon, marker_label),
                title: "Cluster"
            });
        }
    };
    // /////////////////////// Marker Grouping in Clusterer /////////////////////////
    new markerClusterer.MarkerClusterer({
        map: map,
        markers: markers,
        // typescript typing is problematic for onClusterClick, so cluster type is 'any'
        onClusterClick: (_event, cluster, map) => {
            const bounds = new google.maps.LatLngBounds();
            cluster.markers.forEach((marker) => {
                if (marker.position) {
                    bounds.extend(marker.position);
                }
            });
            const pixelPadding = {
                top: 50,
                right: 50,
                bottom: 50,
                left: 50
            };
            map.fitBounds(bounds, pixelPadding);
        },
        algorithmOptions: { maxZoom: 12 }, // no apparent effect...
        renderer: renderer
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
                var hike_result = IdTableElements(bounds, false, 7);
                formTbl(hike_result[0]);
                google.maps.event.removeListener(init_idle);
            });
        }
        else {
            var idle = google.maps.event.addListener(map, 'idle', async function () {
                var curZoom = map.getZoom();
                var zoomTracks = curZoom >= zoomThresh ? true : false;
                var perim = String(map.getBounds());
                // in case of intervening map event:
                kill_table = false;
                zoomedHikes = IdTableElements(perim, zoomTracks, curZoom);
                await formTbl(zoomedHikes[0]);
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
    for (let i = 0, j = 0; i < hikenos.length; i++, j++) {
        if (!drawnHikes.includes(hikenos[i])) {
            // All hikes should have a json file
            let sgldef = $.Deferred();
            promises.push(sgldef);
            let trackfile = "../json/pmn" + hikenos[i] + "_1.json";
            drawnHikes.push(hikenos[i]);
            if (j === trackcolors.length) {
                j = 0; // rollover colors when # of tracks > # of colors
            }
            drawTrack(trackfile, infoWins[i], trackcolors[j], hikenos[i], sgldef);
        }
    }
    return $.when.apply($, promises);
}
/**
 * This function draws the track for the hike object
 */
function drawTrack(json_filename, info_win, color, hikeno, deferred) {
    let sgltrack;
    mapTick.fillcolor = color;
    $.ajax({
        dataType: "json",
        url: json_filename,
        success: function (trackDat) {
            let track_data = trackDat["trk"];
            for (let j = 0; j < track_data.length; j++) {
                let org_json = track_data[j];
                delete org_json["ele"];
            }
            sgltrack = new google.maps.Polyline({
                icons: [{
                        icon: mapTick,
                        offset: '0%',
                        repeat: '15%'
                    }],
                path: track_data,
                geodesic: true,
                strokeColor: color,
                strokeOpacity: .6,
                strokeWeight: 3,
                zIndex: 1
            });
            sgltrack.setMap(map);
            // create the mouseover text:
            let iw = new google.maps.InfoWindow({
                content: info_win
            });
            sgltrack.addListener('mouseover', function (mo) {
                let trkPtr = mo.latLng;
                iw.setPosition(trkPtr);
                iw.open(map);
            });
            sgltrack.addListener('mouseout', function () {
                iw.close();
            });
            let newtrack = { hike: hikeno, track: sgltrack };
            drawnTracks.push(newtrack);
            deferred.resolve();
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            let msg = "map.js: Trying to access" + json_filename +
                " in drawTrack()";
            ajaxError(appMode, _jqXHR, _textStatus, msg);
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
        new google.maps.marker.AdvancedMarkerElement({
            position: newWPos,
            map: map
        });
        var currzoom = map.getZoom();
        window.newBounds = currzoom >= zoomThresh ? true : false;
        map.setCenter(newWPos);
        if (!window.newBounds) {
            map.setZoom(zoomThresh);
        }
    }
    function error(eobj) {
        let msg = 'Error retrieving position; Code: ' + eobj.code;
        window.alert(msg);
    }
}
// //////////////////////  MAP FULL SCREEN DETECT  //////////////////////
$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange', function () {
    let thisMapDoc = document;
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
    let newWinWidth = window.innerWidth;
    let mapWidth = Math.round(0.72 * newWinWidth);
    let tblWidth = newWinWidth - (mapWidth + 3); // 3px = adjustWidth
    initDivParms();
    $map.css('width', mapWidth + 'px');
    $('#sideTable').css('width', tblWidth + 'px');
    locateGeoSym();
    $('.like').each(function () {
        let $icon = $(this);
        let $tooldiv = $icon.parent().prev();
        positionFavToolTip($tooldiv, $icon);
    });
    google.maps.event.trigger(map, "resize");
});
// //////////////////////////////////////////////////////////////
/**
 * List of referenced comments from initMap()
 */
// 1.
/**
 * Due to a recent change in the google maps api, the occasions when
 * the 'idle' event fires, the following function was added, as the
 * new api resulted in the side tables not being displayed.
 */
// 2.
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
// 3.
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
// 4.
/**
 * Zoom change will always initiate the side table formation.
 */
