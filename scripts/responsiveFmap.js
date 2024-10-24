/// <reference path='./map.d.ts' />
/**
 * @fileoverview Set up a full page map showing the user's selected  Favorites
 *
 * @author Ken Cowles
 *
 * @version 1.0 Responsive design intro (new menu, etc.)
 * @version 2.0 Switch to new Google map markers
 */
// Globals
var select_favorites = allHikes.length > 1 && $('#favmode').text() === 'no' ? true : false;
var zoomThresh = 13;
// Positioning of elements on page
var title = $('#trail').text();
$('#ctr').text(title);
/**
 * Position the links button
 */
var links_btn = function () {
    var navht = $('#nav').height() + 16; // padding
    var logoht = $('#logo').height();
    var favtop = navht + logoht + 8 + 'px';
    var favlft = $('#map').width() - 240 + 'px';
    $('#favlist').css('top', favtop);
    $('#favlist').css('left', favlft);
};
links_btn();
// Create links to favs: appear in same order as listed on responsiveFavs.php
var link_base = '<a href="../pages/hikePageTemplate.php?hikeIndx=';
for (var j = 0; j < allHikes.length; j++) {
    var link = link_base + allHikes[j] + '">' + hikeNames[j] + '</a><br />';
    $('#favlinks').append(link);
}
// display 'no favs' modal when there are no favorites
var nofavs = new bootstrap.Modal(document.getElementById('nofavs'), {
    keyboard: false
});
if (tracks.length === 0) {
    nofavs.show();
}
// Allow user to select which favorites to display
var subset_modal = new bootstrap.Modal(document.getElementById('favlimit'));
// If user-select modal has not yet been displayed and there are multiple favorites...
if (select_favorites) {
    // create list
    var modalHikes = '<li><input id="0" class="mod_chk" type="checkbox" />&nbsp;&nbsp;' +
        'Keep All Hikes</li>';
    for (var i = 0; i < allHikes.length; i++) {
        modalHikes += '<li><input id="' + allHikes[i] + '" type="checkbox" class="mod_chk"' +
            '<scan>&nbsp;&nbsp;' + hikeNames[i] + '</scan></li>';
    }
    $('#show_only').append(modalHikes);
    subset_modal.show();
}
$('body').on('click', '#show_limited', function () {
    var keepAll = false;
    var items = $('input.mod_chk');
    if ($(items[0]).prop("checked")) {
        keepAll = true;
    }
    var showHikes = [];
    items.each(function (indx, hike) {
        if (keepAll && indx !== 0) {
            showHikes.push(hike.id);
        }
        else if (!keepAll) {
            if ($(hike).prop("checked")) {
                showHikes.push(hike.id);
            }
        }
    });
    if (showHikes.length === 0) {
        alert("You have not checked any boxes...");
        return false;
    }
    else {
        var qstring = [];
        var query;
        showHikes.forEach(function (hike) {
            qstring.push("modal_hikes[]=" + hike);
        });
        if (qstring.length > 1) {
            query = qstring.join("&");
        }
        else {
            query = qstring[0];
        }
        subset_modal.hide();
        var redo = "../pages/responsiveFavs.php?" + query;
        window.open(redo, "_self");
    }
});
var map;
var colors = ['#FF0000', '#0000FF', '#F88C00', '#9400D3', '#000000', '#FFFF00'];
var mapTick = {
    path: 'M 0,0 -5,11 0,8 5,11 Z',
    fillcolor: 'Red',
    fillOpacity: 0.8,
    scale: 1,
    strokeColor: 'Red',
    strokeWeight: 2
};
/**
 * This function positions the geosymbol in the bottom right corner of the map,
 * left of the google map zoom control
 */
function locateGeoSym() {
    var winht = window.innerHeight - 86;
    var mapwd = $('#map').width() - 124;
    $('#geoCtrl').css({
        top: winht,
        left: mapwd
    });
    return;
}
locateGeoSym();
$('#geoCtrl').on('click', setupLoc);
// //////////////////////////  INITIALIZE THE MAP /////////////////////////////
var mapdone = $.Deferred();
function initMap() {
    var nmCtr = { lat: 34.450, lng: -106.042 };
    var mapEl = document.getElementById('map');
    map = new google.maps.Map(mapEl, {
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
    });
    mapdone.resolve();
    var _loop_1 = function (j) {
        var position = marker_pos[j];
        var page = hikeNames[j];
        var trailhead = document.createElement("IMG");
        trailhead.src = "../images/trailhead.png";
        // THE MARKER:
        var marker = new google.maps.marker.AdvancedMarkerElement({
            map: map,
            position: position,
            content: trailhead,
            title: page
        });
        // CLICK ON MARKER:
        marker.addListener("click", function () {
            var zoom_level = map.getZoom();
            // newBounds is true if only a center change with no follow-on zoom
            // this statement must precede the setCenter cmd.
            window.newBounds = zoom_level >= zoomThresh ? true : false;
            map.setCenter(position);
            if (!window.newBounds) {
                map.setZoom(zoomThresh);
            }
        });
    };
    // ///////////////////////////   MARKER CREATION   ////////////////////////////
    for (var j = 0; j < allHikes.length; j++) {
        _loop_1(j);
    }
}
// ////////////////////// END OF MAP INITIALIZATION  /////////////////////////////
// ////////////////////////////  DRAW HIKING TRACKS  //////////////////////////
var trackFile; // name of the JSON file to be read in
// deferred wait for map to get initialized
$.when(mapdone).then(drawTracks);
/**
 * Draw tracks for each of the favorites
 */
function drawTracks() {
    var trkcolor = 0;
    var promises = [];
    for (var k = 0; k < tracks.length; k++) {
        if (tracks[k] !== '') {
            var trackdef = $.Deferred();
            promises.push(trackdef);
            var trkfile = '../json/' + tracks[k];
            drawTrack(trkfile, colors[trkcolor++], k, trackdef);
            if (trkcolor >= colors.length) {
                trkcolor = 0; // rollover colors when tracks exceeds colors size
            }
        }
    }
    $.when.apply($, promises).then(function () {
        if (allHikes.length === 1) {
            map.setCenter(marker_pos[0]);
            map.setZoom(13);
        }
        map.fitBounds(google_bounds);
    });
}
/**
 * This function draws one track
 */
function drawTrack(jsonfile, color, lindx, def) {
    $.ajax({
        dataType: "json",
        url: jsonfile,
        success: function (trackDat) {
            var json_track = trackDat['trk'];
            var track = new google.maps.Polyline({
                icons: [{
                        icon: mapTick,
                        offset: '0%',
                        repeat: '15%'
                    }],
                path: json_track,
                geodesic: true,
                strokeColor: color,
                strokeOpacity: 1.0,
                strokeWeight: 3
            });
            track.setMap(map);
            var $links = $('#favlinks').find('a');
            $($links[lindx]).css('color', color);
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
        var geoOptions = { enableHighAccuracy: true };
        var myGeoLoc = navigator.geolocation.getCurrentPosition(success, error, geoOptions);
        function success(pos) {
            var geoPos = pos.coords;
            var geoLat = geoPos.latitude;
            var geoLng = geoPos.longitude;
            var newWPos = { lat: geoLat, lng: geoLng };
            var geopin = new google.maps.marker.PinElement({
                scale: 1.2,
                glyph: "X",
                background: "FireBrick",
                glyphColor: "white"
            });
            new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: newWPos,
                content: geopin.element
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
// //////////////////////  WINDOW RESIZE EVENT  //////////////////////
$(window).on('resize', function () {
    locateGeoSym();
    links_btn();
});
// //////////////////////////////////////////////////////////////
