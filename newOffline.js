/// <reference types="jquery" />
/// <reference types="jqueryui" />
/// <reference types="leaflet" />
/// <reference types="geojson" />
/**
 * @fileoverview Capturing map tiles and tracks for offline use
 * @author Ken Cowles
 * @version 1.0 Initial release
 */
// Opening page modal for user to select save option:
var draw_inst = $('#drawer').detach();
$('#impgpx').hide();
$('#rect_btns').hide();
$('#clearrect').hide();
var recenter = new bootstrap.Modal(document.getElementById('rctr'));
var opener = new bootstrap.Modal(document.getElementById('intro'));
var restart = new bootstrap.Modal(document.getElementById('redo'));
opener.show();
/**
 * The default state is to import a hike from the site;
 */
$('body').on('click', '#rctg', function() {
    $('#imphike').hide();
    $('#rect_btns').show();
    $('#options').hide();
    $('#saveHike').hide();
    $('#saveGpx').hide();
    $('#allmaps').hide();
    $('#saveRect').after(draw_inst);
});
$('body').on('click', "#site", function() {
    opener.hide();
});
$('body').on('click', '#savegpx', function() {
    $('#imphike').hide();
    $('#impgpx').show();
    opener.hide();
});
$('body').on('click', '#begin', function() {
    opener.hide();
});
// globals
var mobwidth = $('document').width();
$('#form').width(mobwidth);
$('#map').width(mobwidth);
var mobheight = $('document').height();
var barht = $('#nav').height();
$('#map').height(mobheight - barht);

var mapName = 'Unassigned';
var zoom_level = 10;
var idb_track;
var map_center;
var click_cnt = 0;
var rect;
var startX;
var startY;
var endX;
var endY;
var tile = new Array(10);
for (var k = 0; k < 10; k++) {
    tile[k] = new Array(2);
}
var tile_str = "https://tile.openstreetmap.org/";
var maptiles = [];
var tile_cnt;
var tile_coords = [];
tile_coords[13] = []; // tile positions as object {x:tilex, y:tiley}
tile_coords[14] = [];
tile_coords[15] = [];
tile_coords[16] = [];
/*
// northwest corners of NM at zoom levels: (initial values for NM)
var corner13 = { '13x': 1614, '13y': 3188 };
var corner14 = { '14x': 3228, '14y': 6376 };
var corner15 = { '15x': 6458, '15y': 12754 };
var corner16 = { '16x': 12916, '16y': 25508 };
*/
// expanded NW corners for North America
var corner13 = { '13x': 313, '13y': 1905 };
var corner14 = { '14x': 626, '14y': 3811 };
var corner15 = { '15x': 1263, '15y': 7623 };
var corner16 = { '16x': 2507, '16y': 16246 };
var nw_corners = [corner13, corner14, corner15, corner16];
var noOfCorners = nw_corners.length;
var saveType = "unspecified";
var gpximport = document.getElementById('gpxfile');
// modal to show status of saving tiles:
var saveStat = new bootstrap.Modal(document.getElementById('stat'));
// Zoom 13 is minimum to store tiles (limits memory consumption)
$('#setzoom').on('click', function () {
     map.setZoom(13);
});

/**
 * There are three current methods provided to save maps:
 *  1. import a hike track from the site; track also displays on map
 *  2. draw a rectangle on the map representing the area desired for offline
 *  3. import a gpx track and center the map display on it
 */

/**
 * IMPORTING A SITE HIKE
 */
// Clear searchbar contents when user clicks on the "X"
$('#clear').on('click', function () {
    $('#search').val("");
    var searchbox = document.getElementById('search');
    searchbox.focus();
});
/**
 * This function converts json data to data suitable to be
 * stored in the indexedDB and also to be displayed on the map.
 */
function displayTrack(ajax_data, source) {
    if (ajax_data === 'Upload') {
        alert("File upload error - please check the selected file");
        return;
    } else if (ajax_data === 'Extension') {
        alert("Selected file does not have a gpx extension");
        return;
    } else if (ajax_data.indexOf("There is an error") !== -1) {
        alert(ajax_data);
    } else {
        var result_array = JSON.parse(ajax_data);
        var ul = result_array[0];
        var lr = result_array[1];
        var nw = ul.map(Number); // convert to number
        var se = lr.map(Number);
        // add margin to boundary
        var latmarg = 0.10*(nw[0] - se[0])/2;
        var lngmarg = 0.20*(nw[1] - se[1])/2;
        nw = [nw[0]+latmarg, nw[1]+lngmarg];
        se = [se[0]-latmarg, se[1]-lngmarg];
        var lat = result_array[2][0];
        var lng = result_array[2][1];
        map_center = [lat, lng];
        var track_poly = result_array[3];
        for (let i=3; i<result_array.length; i++) {
            //var trk_pt = [result_array[i][0]];
            idb_data = result_array[i].toString();
            if (i === 3) {
                idb_track = idb_data;
                map.flyTo(map_center, 13);
            } else {
                idb_track += "," + idb_data;
            }
        }
        var hike = L.polyline(track_poly);
        hike.addTo(map);
        var trkbounds = [nw, se];
        L.rectangle(trkbounds, {color:'darkgreen', fill: false, weight: 2}).addTo(map);
        // establish points on map representing area to be saved
        maptiles = [];
        startX = nw[0];
        startY = nw[1];
        endX   = se[0];
        endY   = se[1];
        saveType = "import";
        $(source).hide();
        $('#newrect').hide();
        $('#rctg').prop('checked', false);
        $('#site').prop('checked', false);
        $('#saveGpx').prop('checked', false);
        $('#search').val("");
        restart.show();
    }
}
// Establish searchbar as jQueryUI widget
$(".search").autocomplete({
    source: hikeSources,
    minLength: 2
});
// When user selects item from dropdown:
$("#search").on("autocompleteselect", function (event, ui) {
    // the searchbar dropdown uses 'label', but place 'value' in box & use that
    event.preventDefault();
    var entry = ui.item.value;
    $(this).val(entry);
    var src = '#imphike';
    $.ajax({
        url: "../php/importHike.php",
        data: { hike: entry },
        dataType: "text",
        method: "post",
        success: function (result) {
            displayTrack(result, src);
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            alert("Problem: " + _textStatus + "; Error: " + _errorThrown);
        }
    });
});

/**
 * IMPORTING A GPX FILE
 */
$('body').on('submit', '#form', (ev) => {
    ev.preventDefault();
    if (gpximport.files.length === 0) {
        alert("A gpx file has not been selected");
        return false;
    }
    var src = '#impgpx';
    const formData = new FormData($('#form')[0])
    var url = "../php/importGpx.php";
    $.ajax({
        url: url,
        method: "post",
        data: formData,
        dataType: "text",
        contentType: false,
        processData: false,
        success: function(result) {
            displayTrack(result, src);
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            alert("Problem: " + _textStatus + "; Error: " + _errorThrown);
        }
    });
});

/**
 * DRAWING A RECTANGLE DEFINING AREA TO BE SAVED
 */
$('body').on('click', '#rect', function () {
    const zlevel = map.getZoom();
    if (zlevel < 13) {
        alert("Minimum zoom level is 13");
        return false;
    }
    $('#draw_inst').show();
    $(this).removeClass('btn-primary');
    $(this).addClass('btn-secondary');
    $(this).attr("disabled", true);
    click_cnt = 0;
    if (typeof rect !== 'undefined') {
        rect.remove();
    }
    $('#map').on('ontouchstart', 'click', function (e) {
        if (click_cnt === 0) {
            saveType = "draw";
            $('#map').on('ontouchmove', 'mousemove', function (e) {
                draw_rect(e);
            });
            start_rect(e);
        }
        else {
            var endRect = map.mouseEventToLatLng(e.originalEvent);
            endX = endRect.lat;
            endY = endRect.lng;
            var lat_ctr = startX - (startX - endX)/2;
            var lng_ctr = startY + (endY - startY)/2;
            map_center = [lat_ctr, lng_ctr];
            $('#draw_inst').hide();
            $('#map').off();
            $('#rect').removeClass('btn-secondary');
            $('#rect').addClass('btn-primary');
            $('#rect').attr('disabled', false);
            getRectTiles(zlevel);
            //$('#rect').text("Clear Rect");
            $('#rect_btns').hide();
            $('#clearrect').show();
            restart.show();
        }
        return;
    });
    $('#map').on('touchend', function(e)  {
        var endRect = map.mouseEventToLatLng(e.originalEvent);
        endX = endRect.lat;
        endY = endRect.lng;
        var lat_ctr = startX - (startX - endX)/2;
        var lng_ctr = startY + (endY - startY)/2;
        map_center = [lat_ctr, lng_ctr];
        $('#draw_inst').hide();
        $('#map').off();
        $('#rect').removeClass('btn-secondary');
        $('#rect').addClass('btn-primary');
        $('#rect').attr('disabled', false);
        getRectTiles(zlevel);
        //$('#rect').text("Clear Rect");
        $('#rect_btns').hide();
        $('#clearrect').show();
        restart.show();
    }
    
    function start_rect(ev) {
        var startRect = map.mouseEventToLatLng(ev.originalEvent);
        startX = startRect.lat;
        startY = startRect.lng;
        var rectX = startX + 0.005;
        var rectY = startY + 0.005;
        var latlngs = [[startX, startY], [rectX, rectY]];
        var rectOpts = { color: 'Green', weight: 1 };
        rect = L.rectangle(latlngs, rectOpts);
        rect.addTo(map);
        click_cnt = 1;
    }
    function draw_rect(ev) {
        rect.removeFrom(map);
        var newRect = map.mouseEventToLatLng(ev.originalEvent);
        var rectX = newRect.lat;
        var rectY = newRect.lng;
        var latlngs = [[startX, startY], [rectX, rectY]];
        var rectOpts = { color: 'Green', weight: 1 };
        rect = L.rectangle(latlngs, rectOpts);
        rect.addTo(map);
    }
    function end_rect(ev) {

    }
});
$('body').on('click', '#clearrect', function() {
    rect.removeFrom(map);
    maptiles = [];
    restart.hide();$('#rect_btns').show();
});
$('body').on('click', '#restart', function() {
    location.reload();
});

/**
 * DISPLAY THE MAP
 */

var map = L.map('map', {
    center: [35.1, -106.65],
    minZoom: 6,
    maxZoom: 17,
    zoom: 10
});
// track zoom level on map
const zctrl = document.createElement("DIV");
const zsym  = document.createTextNode("Z: ");
zctrl.style.marginLeft = "8px";
zctrl.style.fontSize = "18px";
zctrl.style.color = "brown";
zctrl.style.fontWeight = "bold";
const zval  = document.createElement("SPAN");
zval.id = "zval";
zval.textContent = "10";
zctrl.append(zsym, zval);
$('.leaflet-top.leaflet-left').append(zctrl);
map.addEventListener("zoomend", () => {
    zoom_level = map.getZoom();
    $('#zval').text(" " + zoom_level);
});

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www,openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

/**
 * This layer provides a map grid of tiles with the tile id's
 * supplied in each tile. This is primarily used for debug in order
 * to identify tiles within the area selected for saving offline.
 */
L.GridLayer.GridDebug = L.GridLayer.extend({
    createTile: function (coords) {
        var tile = document.createElement("DIV");
        tile.style.outline = '1px solid black';
        tile.style.fontSize = '14pt';
        tile.style.color = "darkgray";
        tile.innerHTML = [coords.z, coords.x, coords.y].join('/');
        return tile;
    }
});
L.gridLayer.gridDebug = function (opts) {
    return new L.GridLayer.GridDebug(opts);
};
map.addLayer(L.gridLayer.gridDebug());

$('body').on('click', '#newctr', function() {
    recenter.show();
});
$('body').on('click', '#movectr', function() {
    var newlat = $('#newlat').val();
    var newlng = $('#newlng').val();
    if (newlat == '' || newlng == '') {
        alert("One or more values is missing");
        $('#newctr').prop('checked', false);
        return false;
    }
    let lat = parseFloat(newlat);
    let lng = parseFloat(newlng);
    if (!(typeof lat === 'number') || isNaN(lat)
            ||!(typeof lng === 'number') || isNaN(lng)) {
        alert("One or more values is not numeric");
        $('#newctr').prop('checked', false);
        return false;
    }
    if (lat < 20 || lat > 70) {
        alert("Latitude is out of range: must be between 20 & 70");
        $('#newctr').prop('checked', false);
        return false;
    }
    if (lng < -170 || lng > -60) {
        alert("Longitude must be a number between -60 and -170");
        $('#newctr').prop('checked', false);
        return false;
    }
    let ctr = L.latLng(lat, lng);
    let mag = map.getZoom();
    map.setView(ctr, mag);
    recenter.hide();
    $('#newctr').prop('checked', false);
});

/**
 * Define tile urls within the rectangle for saving later;
 * All tile urls are store in maptiles
 */
var get_nw_corner = function (zoom_level) {
    var zx = zoom_level + 'x';
    var zy = zoom_level + 'y';
    var xmin = 0;
    var ymin = 0;
    for (var i = 0; i < noOfCorners; i++) {
        var pos = Object.keys(nw_corners[i]); // returns array
        if ($.inArray(zx, pos) !== -1) {
            xmin = parseInt(nw_corners[i][zx]); // min tile no. at zoom
            ymin = parseInt(nw_corners[i][zy]);
            break;
        }
    }
    return { nwx: xmin, nwy: ymin };
};
var next_zoom_tiles = function (next_level, delta_x, delta_y) {
    var new_corner = get_nw_corner(next_level);
    var x_a = new_corner.nwx + 2 * delta_x;
    var x_b = x_a + 1;
    var y_a = new_corner.nwy + 2 * delta_y;
    var y_b = y_a + 1;
    var loc1 = { x: x_a, y: y_a };
    var loc2 = { x: x_b, y: y_a };
    var loc3 = { x: x_a, y: y_b };
    var loc4 = { x: x_b, y: y_b };
    tile_coords[next_level].push(loc1);
    tile_coords[next_level].push(loc2);
    tile_coords[next_level].push(loc3);
    tile_coords[next_level].push(loc4);
    return;
};
function getTileURL(lat, lng, zoom) {
    var latrad = lat * Math.PI / 180;
    var tileX = parseInt(Math.floor((lng + 180) / 360 * (1 << zoom)));
    var tileY = parseInt(Math.floor((1 - Math.log(Math.tan(latrad)
        + 1 / Math.cos(latrad)) / Math.PI) / 2 * (1 << zoom)));
    return zoom + "/" + tileX + "/" + tileY;
}
/**
 * This function identifies tile addresses for the user-selected region
 * and propagates them out to zoom level 16. The addresses are stored
 * in the global 'maptiles'. Note that the maximum number of tiles allowed
 * for capture is a 3 x 3 matrix. Otherwise memory storage gets too large.
 */
function getRectTiles(zoom_level) {
    maptiles = []; // clear for each invocation...
    var corner1 = getTileURL(startX, startY, zoom_level); // user start
    var corner2 = getTileURL(endX, endY, zoom_level);     // user end
    var corner1XY = corner1.split("/"); // array:[0]=>zoom;[1]=>row;[2]=col
    var corner2XY = corner2.split("/");
    var tileXmid = 0;
    var tileYmid = 0;
    /**
     * User may draw from any corner, so establish matrix as if it were
     * drawn from upper left to lower right to simplify processing
     */
    var tile1 = []; // tileN => [row, col]
    var tile2 = [];
    if (corner1XY[1] < corner2XY[1]) { // row check
        tile1[0] = corner1XY[1];
        tile2[0] = corner2XY[1];
    }
    else { 
        tile1[0] = corner2XY[1];
        tile2[0] = corner1XY[1];
    }
    if (corner1XY[2] < corner2XY[2]) { // col check
        tile1[1] = corner1XY[2];
        tile2[1] = corner2XY[2];
    }
    else {
        tile1[1] = corner2XY[2];
        tile2[1] = corner1XY[2];
    }
    tile1.forEach(function (element, i) {
        tile1[i] = parseInt(element);
    });
    tile2.forEach(function (element, i) {
        tile2[i] = parseInt(element);
    });
    // begin with array where 0,1 is range of upper left; 2,0 => lower right
    tile[0] = tile1.slice(); // tile1 row, col
    tile[1] = tile1.slice();
    tile[2] = tile2.slice(); // tile2 row, col
    tile[3] = tile2.slice();
    var rangeX = tile2[0] - tile1[0]; // no. of map rows
    var rangeY = tile2[1] - tile1[1]; // no. of map cols
    // determine intermdiate tile values if required
    if (rangeX > 1 || rangeY > 1) {
        // required whether 6 or 9 tiles captured
        tile[4] = tile2.slice();
        tile[5] = tile2.slice();
        if (rangeX > 1) {
            // 3 tiles across
            if (rangeX > 2) {
                alert("Too big: Please select a smaller area");
                return false;
            }
            else {
                tileXmid = tile1[0] + 1;
            }
        }
        if (rangeY > 1) {
            // 3 tiles vertical
            if (rangeY > 2) {
                alert("Too big: Please select a smaller area");
                return false;
            }
            else {
                tileYmid = tile1[1] + 1;
            }
        }
        /**
         * For rangeX or rangeY = 2 [max], there will be 3 across and will
         * include a 'mid' tile, resulting in either a 6x6, 6x9, 9x6 or 9x9
         * matrix.
         */
        if (tileXmid > 0) {
            if (tileYmid > 0) {
                // captured 9 tiles
                tile_cnt = 9;
                tile[6] = tile2.slice();
                tile[7] = tile2.slice();
                tile[8] = tile2.slice();
                // tile[0] is ok as is
                var midx = tile1[0] + 1;
                var midy = tile1[1] + 1;
                tile[1][0] = midx;
                tile[1][1] = tile1[1];
                tile[2][0] = tile1[0] + 2;
                tile[2][1] = tile1[1];
                tile[3][0] = tile1[0];
                tile[3][1] = midy;
                tile[4][0] = midx;
                tile[4][1] = midy;
                tile[5][1] = midy;
                tile[6][0] = tile1[0];
                tile[7][1] = midy;
                tile[8][0] = tile1[0];
                tile[8][1] = tile2[1];
                tile[7][0] = midx;
                tile[7][1] = tile2[1];
                tile[8][0] = tile2[0];
                tile[8][1] = tile2[1];
            } else {
                tile_cnt = 6;
                tile[1][0] = tileXmid;
                tile[1][1] = tile[0][1];
                tile[2][0] = tile2[0];
                tile[2][1] = tile[0][1];
                tile[3][0] = tile1[0];
                tile[3][1] = tile2[1];
                tile[4][0] = tileXmid;
                tile[4][1] = tile2[1];
                // tile[5] is already established above
            }
        }
        else if (tileYmid > 0) { // tileXmid already checked above
            tile_cnt = 6;
            tile[1][0] = tile2[0];
            tile[1][1] = tile1[1];
            tile[2][0] = tile1[0];
            tile[2][1] = tileYmid;
            tile[3][0] = tile2[0];
            tile[3][1] = tileYmid;
            tile[4][0] = tile1[0];
            tile[4][1] = tile2[1];
            tile[5][0] = tile2[0];
            tile[5][1] = tile2[1];
        }
    }
    else {
        // quad
        tile_cnt = 4;
        tile[0] = tile1.slice();
        tile[1] = tile1.slice();
        tile[1][0] = tile2[0];
        tile[2] = tile2.slice();
        tile[2][0] = tile1[0];
        tile[3] = tile2.slice();
        // quad may reside in 1 or 2 tiles
        if ((tile[3][0] - tile[0][0]) === 0 && (tile[3][1] - tile[0][1]) === 0) {
            tile_cnt = 1;
        }
        else if (tile[3][0] - tile[0][0] === 0) {
            tile_cnt = 2;
        }
        else if (tile[3][1] - tile[0][1] === 0) {
            tile_cnt = 2;
        }
    }
    for (var j = 0; j < tile_cnt; j++) {
        var loc = { x: tile[j][0], y: tile[j][1] };
        switch (zoom_level) {
            case 13:
                tile_coords[13].push(loc);
                break;
            case 14:
                tile_coords[14].push(loc);
                break;
            case 15:
                tile_coords[15].push(loc);
                break;
            case 16:
                tile_coords[16].push(loc);
        }
        
    }
    /**
     * Find each tile's deeper zoom level tiles. Each tile produces 4 tiles
     * at the next zoom level; therefore there will be 85 tiles per zoom level
     * 13 tiles input to the routine. Get this done in the background while waiting
     * to invoke 'Save'...
     */
    for (var zoom = zoom_level; zoom < 16; zoom++) { // don't get 'next level' 17
        var corner = get_nw_corner(zoom);
        // tile_coords obtained earlier in routine...
        for (var j = 0; j < tile_coords[zoom].length; j++) {
            var delx = tile_coords[zoom][j].x - corner.nwx;
            var dely = tile_coords[zoom][j].y - corner.nwy;
            next_zoom_tiles(zoom + 1, delx, dely);
        }
    }
    var makeTile = function (level) {
        tile_coords[level].forEach(function (tcoord) {
            var zoomdir = level + "/";
            var url = tile_str + zoomdir + tcoord.x + "/" + tcoord.y + ".png";
            maptiles.push(url);
        });
    };
    // Convert tile_coords to maptiles:
    for (var level = zoom_level; level < 17; level++) {
        makeTile(level);
    }
    return;
}

/**
 * This function will acquire and return the map bounds for the zlevel+1
 * zoom.
 */
function getNextMapZoom(zlevel) {
    const ctr = map.getCenter();
    const next_zoom = zlevel + 1;
    const newbounds = map.getPixelBounds(ctr, next_zoom);
    const sw = map.unproject(newbounds.getBottomLeft(), next_zoom);
    const ne = map.unproject(newbounds.getTopRight(), next_zoom);
    return L.latLngBounds(sw, ne);
}

/**
 * After a hike is created, there may actually be sufficient space to
 * zoom in, which reduces memory load.
 */
function zoomOptimizer(zoom) {
    var repeat = false;
    const next_zoom_bounds = getNextMapZoom(zoom);
    if (next_zoom_bounds.getNorth() > startX 
        && next_zoom_bounds.getSouth() < endX
        && next_zoom_bounds.getWest() < startY
        && next_zoom_bounds.getEast() > endY
    ) {
        map.setZoom(zoom+1);
        repeat = true;
    }
    if (repeat) {
        zoomOptimizer(zoom+1);
    }
    return;
}

$('body').on('click', '#save_map', function () {
    restart.hide();
    // parameter validation:
    zoom_level = map.getZoom();
    if (zoom_level < 13) {
        alert("Please use zoom 13 or higher to specify area");
        return false;
    }
    zoomOptimizer(zoom_level);
    zoom_level = map.getZoom(); // zoom may have changed by above call
    mapName = $('#map_name').val();
    if (mapName === '') {
        alert("Please specify a name for the map");
        return false;
    }
    if (saveType === "import") {
        getRectTiles(zoom_level);
    }
    if (maptiles.length === 0) {
        alert("You must select an area (or a hike/gpx)");
        return false;
    }
    // everything looks good, proceed to save...
    var ajaxdata = {map: mapName, act: "add"};
    $.ajax({
        url: "../php/manageMapNames.php", 
        method: "GET",
        data: ajaxdata,
        dataType: "text",
        success: function(result) {
            if (result === "DUP") {
                alert("This name is already used - please select a new name");
                $('#map_name').val("");
            } else if (result === "UPDATED") {
                $('#delchoice').append($('<option>', {
                    value: mapName,
                    text: mapName
                }));
                saveMapTiles(mapName);
                if (typeof rect !== 'undefined') {
                    rect.remove();
                }
            } else {
                alert(result);
                }
        }, 
        error: function(_jqXHR, _textStatus, _error) {
            alert("Error encountered: " + _textStatus + "; error " + _error);
        }
    });
});

function saveMapTiles(userMap) {

    saveStat.show();
    let bar = 0
    let tilecnt = maptiles.length;
    let incr = 100/tilecnt;
    $('#tcnt').text(tilecnt);
    // save the  map_data in the idb
    if (saveType === "import" && typeof idb_track !== 'undefined') {
        storeMap(userMap, map_center, zoom_level, true, idb_track);
    } else {
        storeMap(userMap, map_center, zoom_level, false);
    }
    caches.open(userMap)
    .then ( (cache) => {
        for (let j=0; j<tilecnt; j++) {
            bar = (j+1) * incr;
            cache.add(maptiles[j]);
            $('#bar').css('width', bar+"%");
        }
    });
}
$('body').on('click', '#delmap', function () {
    var choice = $('#delchoice').val();
    var choice_opt = "option[value=" + choice + "]";
    $.get("../php/manageMapNames.php", {act: "delete", map: choice}, function(result) {
        if (result === "UPDATED") {
            removeMap(choice);  // IndexedDB
            caches.delete(choice).then(function(deleted) {
                if (deleted) {
                  console.log(`Cache ${choice} was successfully deleted`);
                } else {
                  console.log(`Cache ${choice} was not found or could not be deleted`);
                }
            });
            $("#delchoice " + choice_opt).remove();
        } else {
            alert(result);
        }
        return;
    });
    return;
});

// Used in certain test cases only
function clearCache(deletedMap) {
    caches.open(deletedMap) 
    .then((cache) => cache.keys())
    .then((keys) => {
        keys.forEach((request, index, array) => {
            /*
            cache.delete(deletedMap)
            .then(function(status) {
                if (status) {
                console.log(`Cache ${deletedMap} was successfully deleted`);
                } else {
                console.log(`Cache ${deletedMap} was not found or could not be deleted`);
                }
            });
            */
            var r = request
        });
    });
}
