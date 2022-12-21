/// <reference path='./map.d.ts' />
// Overload & redeclare block warnings do not show up during compile
/**
 * @fileoverview Set up a full page map showing the Favorites selected
 * by the user
 *
 * @author Ken Cowles
 * @version  2.0 Typescripted, some type errors corrected
 * @version  3.0 Updated for compatibility with side table that shows previews
 * @version  3.1 Changed <a> links to open new tab
 */
var map;
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000', '#FFFF00'];
var $fullScreenDiv; // for google maps full screen mode
var $map = $('#map');
var mapht;
var maxlat = 0; // north
var maxlng = -180; // east
var minlat = 90; // south
var minlng = 0; // west
var nht = $('#nav').height();
var lht = $('#logo').height();
var navHt = nht + lht;
/**
 * This function is called initially, and again when resizing the window;
 * Because the map, adjustWidth and sideTable divs are floats, height
 * needs to be specified for the divs to be visible.
 */
var initDivParms = function () {
    var wht = $(window).height();
    mapht = wht - navHt;
    $map.css('height', mapht + 'px');
    $('#adjustWidth').css('height', mapht + 'px');
    $('#sideTable').css('height', mapht + 'px');
};
initDivParms();
var mapTick = {
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};
/**
 * This function simply locates the geolocation symbol on the page
 */
function locateGeoSym() {
    var winht = navHt + mapht - 80;
    var mapwd = $('#map').width() - 120;
    $('#geoCtrl').css('top', winht);
    $('#geoCtrl').css('left', mapwd);
    return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);
// icons for geolocation:
var smallGeo = '../images/starget.png';
var medGeo = '../images/purpleTarget.png';
var lgGeo = '../images/ltarget.png';
var locaters = []; // global used to popup info window on map when hike is searched
formTbl(NM);
/**
 * This function returns the correct icon for the map based on no. of hikes
 */
var getIcon = function (no_of_hikes) {
    var icon = "../images/pins/hike" + no_of_hikes + ".png";
    return icon;
};
// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
var mapdone = $.Deferred();
/**
 * The google maps callback function to initialize the map
 *
 * @return {null}
 */
function initMap() {
    google.maps.Marker.prototype.clicked = false; // used in favSideTable.ts
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
    NM.forEach(function (nmobj) {
        AddHikeMarker(nmobj);
    });
    /**
     * The only hikes on the favorites pages are 'normal' hikes, i.e.
     * not clusters.
     *
     * @param {object} hikeobj The hike object from mapJsData.php
     *
     * @return {null}
     */
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
        var iwContent = '<div id="iwNH"><a href="hikePageTemplate.php?hikeIndx='
            + hikeobj.indx + '" target="_blank">' + hikeobj.name + '</a><br />';
        iwContent += 'Length: ' + hikeobj.lgth + ' miles<br />';
        iwContent += 'Elevation Change: ' + hikeobj.elev + ' ft<br />';
        iwContent += 'Difficulty: ' + hikeobj.diff + '<br />';
        iwContent += '<a href="' + hikeobj.dir + '">Directions</a></div>';
        var iw = new google.maps.InfoWindow({
            content: iwContent,
            maxWidth: 400
        });
        iw.addListener('closeclick', function () {
            marker.clicked = false;
        });
        marker.addListener('click', function () {
            map.setCenter(hikeobj.loc);
            var curr = map.getZoom();
            if (curr <= 12) {
                map.setZoom(13);
            }
            iw.open(map, this);
            marker.clicked = true;
        });
        // IdTableElements must be invoked in order to create side table:
        var idle = google.maps.event.addListener(map, 'idle', function () {
            var perim = String(map.getBounds());
            IdTableElements(perim, true);
        });
        map.addListener('bounds_changed', function () {
            google.maps.event.trigger(map, 'resize');
        });
        return;
    }
    // /////////////////////// Marker Grouping /////////////////////////
    var markerCluster = new MarkerClusterer(map, clustererMarkerSet, {
        imagePath: '../images/markerclusters/m',
        gridSize: 50,
        maxZoom: 12,
        averageCenter: true,
        zoomOnClick: true
    });
    // IdTableElements must be called in order to initiate the side table creation
    var idle = google.maps.event.addListener(map, 'idle', function () {
        var perim = String(map.getBounds());
        IdTableElements(perim, true); // kicks off 'formTbl'
    });
    map.addListener('bounds_changed', function () {
        google.maps.event.trigger(map, 'resize');
    });
    return;
} // end of initMap()
// ////////////////////// END OF MAP INITIALIZATION  /////////////////////////////
// collect mouseover data for tracks; initialize arrow holding info
var trackdat = [];
for (var i = 0; i < tracks.length; i++) {
    trackdat[i] = '';
}
NM.forEach(function (hobj, indx) {
    trackdat[indx] = '<div id="iwNH">' + hobj.name + '<br />Length: ' +
        hobj.lgth + ' miles<br />Elev Chg: ' + hobj.elev +
        '<br />Difficulty: ' + hobj.diff + '</div>';
});
// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
var geoOptions = { enableHighAccuracy: true };
// deferred wait for map to get initialized
$.when(mapdone).then(drawTracks).then(function () {
    $fullScreenDiv = $map.children('div:first');
});
/**
 * Draw tracks for each of the favorites
 */
function drawTracks() {
    var trkcolor = 0;
    var promises = [];
    tracks.forEach(function (fname, indx) {
        if (fname !== '') {
            var trackdef = $.Deferred();
            promises.push(trackdef);
            var trkfile = '../json/' + fname;
            drawTrack(trkfile, colors[trkcolor++], indx, trackdef);
            if (trkcolor >= colors.length) {
                trkcolor = 0; // rollover colors when tracks exceeds colors size
            }
        }
    });
    $.when.apply($, promises).then(function () {
        if (allHikes.length === 1) {
            map.setCenter(NM[0].loc);
            map.setZoom(13);
        }
        else if (allHikes.length > 1) {
            var bounds = { north: maxlat, south: minlat, east: maxlng, west: minlng };
            map.fitBounds(bounds);
        }
    });
}
/**
 * This function draws one track
 */
function drawTrack(jsonfile, color, ptr, def) {
    $.ajax({
        dataType: "json",
        url: jsonfile,
        success: function (trackDat) {
            var track = new google.maps.Polyline({
                icons: [{
                        icon: mapTick,
                        offset: '0%',
                        repeat: '15%'
                    }],
                path: trackDat,
                geodesic: true,
                strokeColor: color,
                strokeOpacity: 1.0,
                strokeWeight: 3
            });
            track.setMap(map);
            // create the mouseover text:
            var iw = new google.maps.InfoWindow({
                content: trackdat[ptr]
            });
            track.addListener('mouseover', function (mo) {
                var trkPtr = mo.latLng;
                iw.setPosition(trkPtr);
                iw.open(map);
            });
            track.addListener('mouseout', function () {
                iw.close();
            });
            // establish map boundaries
            trackDat.forEach(function (latlngpair) {
                if (latlngpair.lat > maxlat) {
                    maxlat = latlngpair.lat;
                }
                if (latlngpair.lat < minlat) {
                    minlat = latlngpair.lat;
                }
                if (latlngpair.lng < minlng) {
                    minlng = latlngpair.lng;
                }
                if (latlngpair.lng > maxlng) {
                    maxlng = latlngpair.lng;
                }
            });
            def.resolve();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            var msg = 'Did not succeed in getting JSON data: ' + jsonfile +
                "\nError: " + textStatus;
            alert(msg);
            def.reject();
        }
    });
    return;
} // end drawTrack
// /////////////////////// END OF HIKE TRACK DRAWING /////////////////////
// ////////////////////////////  GEOLOCATION CODE //////////////////////////
/**
 * Locate the user on the map
 */
function setupLoc() {
    if (navigator.geolocation) {
        var obj = navigator;
        var myGeoLoc = navigator.geolocation.getCurrentPosition(success, error, geoOptions);
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
            var msg = '<p>Error in get position call: code ' + eobj.code + '</p>';
            window.alert(msg);
        }
    }
    else {
        window.alert('Geolocation not supported on this browser');
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
        // apparently don't need positionFavToolTip for fav page
    });
    google.maps.event.trigger(map, "resize");
});
// //////////////////////////////////////////////////////////////
