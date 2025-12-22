/// <reference types="jquery" />
/// <reference types="jqueryui" />
/// <reference types="leaflet" />
/// <reference types="geojson" />
/**
 * @fileoverview Capturing map tiles and tracks for offline use;
 * 
 * @author Ken Cowles
 * @version 1.0 Initial release
 */
if (screen.orientation) {
    screen.orientation.addEventListener('change', (ev) => {
        const target = ev.target;
        const type = target.type; // 'portatrait-primary', 'landcape-secondary'
        console.log(type);
        map.invalidateSize();
    });
} else {
    $(window).on('resize', () => {
        map.invalidateSize();
    });
}

const MAIN_CACHE = "offline";
// hide some display options; default display is #imphike
$('#impgpx').hide();
$('#rect_btns').hide();
// Note: the name 'opener' conflicts with a DOM lib element: hence 'iopener'
var iopener  = new bootstrap.Modal(document.getElementById('intro'));
var rectinst = new bootstrap.Modal(document.getElementById('rim'));
var save_modal = new bootstrap.Modal(document.getElementById('map_save'));
var saveStat = new bootstrap.Modal(document.getElementById('stat'));
$('#stat').on('hidden.bs.modal', () => {
    show_grp(4);
});
// vanilla method (w/o jquery):
// (document.getElementById('stat')).addEventListener('hidden.bs.modal', () => {});
iopener.show();
const show_grp = (grpno) => {
    $('#map_grp1').css('display', 'none');
    $('#map_grp2').css('display', 'none');
    $('#map_grp3').css('display', 'none');
    $('#map_grp4').css('display', 'none');
    switch (grpno) {
        case 1:
            $('#map_grp1').css('display', 'block');
            break;
        case 2:
            $('#map_grp2').css('display', 'block');
            break;
        case 3:
            $('#map_grp3').css('display', 'block');
            break;
        case 4:
            $('#map_grp4').css('display', 'block');
            break;
        default:
            alert("Invalid button group number!");
    }
};
show_grp(1); // default display for 'imphike'
// DISPLAY THE MAP:
var map = L.map('map', {
    center: [35.1, -106.65],
    minZoom: 6,
    maxZoom: 17,
    zoom: 10
});

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www,openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
map.on('locationfound', function (e) {
    map.panTo(e.latlng);
});
/**
 * This layer provides a map grid of tiles with the tile id's
 * supplied in each tile. This is primarily used for debug in order
 * to identify tiles within the area selected for saving offline.
 */
L.GridLayer.GridDebug = L.GridLayer.extend({
    createTile: function (coords) {
        var tile = document.createElement("DIV");
        tile.style.outline = '1px solid azure'; //#e6e6e6
        tile.style.fontSize = '14pt';
        tile.style.color = "azure";
        tile.innerHTML = [coords.z, coords.x, coords.y].join('/');
        return tile;
    }
});
L.gridLayer.gridDebug = function (opts) {
    return new L.GridLayer.GridDebug(opts);
};
map.addLayer(L.gridLayer.gridDebug());
findMe = () => {
    map.locate({enableHighAccuracy: true, setView: false, watch: false, maxZoom: 17});
}
/**
 * The default state is to import a hike from the site;
 * The following represent checkboxes on the 'intro' modal
 */
$('body').on('click', '#rctg', function() {
    iopener.hide();
    $('#imphike').hide();
    $('#impgpx').hide();
    $('#rect_btns').show();
    show_grp(1);
    rectinst.show();
});
$('body').on('click', "#site", function() {
    iopener.hide();
    show_grp(1); 
});
$('body').on('click', '#savegpx', function() {
    iopener.hide();
    $('#imphike').hide();
    $('#impgpx').show();
    show_grp(1);
});
/**
 * Button actions
 */
var redos = $('.redos'); // all the "Start Over" buttons
redos.each( (i, btn) => {
    $(btn).on('click', () => {
        map.dragging.enable();
        window.open('../pages/saveOffline.php?logo=no', '_self');
    });
});
$('button[id^=home]').on('click', () => {
    window.open("../pages/member_landing.html", "_self");
});
var savers = $('.save_btns'); // all the "Save" buttons
savers.each( (i, btn) => {
    $(btn).on('click', () => {
        save_modal.show();
    });  
});
$('body').on('click', '#clearrect', function() {
    rect.removeFrom(map);
    maptiles = [];
    // reset bootstrap draw button:
    $('#rect').attr('disabled', false);
    $('#rect').removeClass('btn-secondary');
    $('#rect').addClass('btn-primary');
});
$('body').on('click', '#omap', () => {
    window.open('../pages/useOffline.html', '_self');
});
// modal buttons
$('body').on('click', '#begin', function() {  // rim modal
    rectinst.hide();
});
$('body').on('click', '#restart', () => {
    window.open('../pages/saveOffline.php?logo=no', '_self');
});
// Zoom 13 is minimum to store tiles (limits memory consumption)
$('body').on('click', '#setzoom', () => {
    map.setZoom(13);
    $('#setzoom').removeClass('btn-primary');
    $('#setzoom').addClass('btn-secondary');
});
$('body').on('click', '#newctr', findMe);
$('body').on('click', '#save_map', function () {
    // Run validation tasks before saving...
    save_modal.hide();
    show_grp(1);
    // parameter validation:
    zoom_level = map.getZoom();
    if (zoom_level < 13) {
        // NOTE: clicking on alerts closes the modal
        alert("Please use zoom 13 or higher to specify area");
        save_modal.show();
        return false;
    }
    mapName = $('#map_name').val();
    if (mapName === '') {
        alert("Please specify a name for the map");
        save_modal.show();
        return false;
    }
    if (saveType === "import") {
        getRectTiles(zoom_level);
    }
    if (maptiles.length === 0) {
        alert("You must select an area (or a hike/gpx)");
        save_modal.show();
        return false;
    }
    if (saveType === "import") {
        zoomOptimizer(zoom_level);
    }
    var saved_maps = localStorage.getItem('mapnames');
    var maplist = saved_maps.split(","); // array
    if (maplist.includes(mapName)) {
        alert("This name is already used - please select a new name");
        $('#map_name').val("");
        save_modal.show();
        return false;
    }
    // Enable the tile cache event listener in the service worker
    navigator.serviceWorker.controller.postMessage({
        type: "Tiles",
        action: "Enable"
    });
    saveMapTiles(mapName)
    .then( () => {
        if (typeof rect !== 'undefined') {
            rect.remove();
        }
        if (saved_maps === 'none') {
            localStorage.setItem('mapnames', mapName);
        } else {
            maplist.push(mapName);
            var newlist = maplist.join(",");
            localStorage.setItem('mapnames', newlist);
        }
         // Disable the openstream fetch event listener in the service worker
        navigator.serviceWorker.controller.postMessage({
            type: "Tiles",
            action: "Disable"
        });
    }); 
});

// globals
var mobwidth = window.screen.width;
$('#form').width(mobwidth);
$('#map').width(mobwidth);
var mobheight = $(document).height();
var barht = $('#nav').height();
var ht_avail = mobheight - barht;
$('#map').height(ht_avail);

var mapName = 'Unassigned';
var zoom_level = 10;
var idb_track;
var map_center;
//var click_cnt = 0; For testing on non-mobile platform
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
// tile positions as object {x:tilex, y:tiley}:
var tile_coords = [];
tile_coords[13] = []; 
tile_coords[14] = [];
tile_coords[15] = [];
tile_coords[16] = [];
var saveType = "unspecified";
var gpximport = document.getElementById('gpxfile');
/**
 * There are three current methods provided to save maps:
 *  1. draw a rectangle on the map representing the area desired for offline
 *  2. import a hike track from the site; track also displays on map
 *  3. import a gpx track and center the map display on it
 */

/**
 * This function is utilized by both import methods to display data
 * retrieved from the importHike.php utility. The ajax data retrieved
 * is parsed to extract the track data and then forms a boundary box
 * around it. The appropriate 'Save' buttons are displayed for next
 * steps.
 */
function displayTrack(ajax_data, source) {
    if (ajax_data === 'Upload') {
        alert("File upload error - please check the selected file");
        return false;
    } else if (ajax_data === 'Extension') {
        alert("Selected file does not have a gpx extension");
        return false;
    } else if (ajax_data.indexOf("There is an error") !== -1) {
        alert(ajax_data);
        return false;
    } else {
        var result_array = JSON.parse(ajax_data);
        var ul = result_array[0];
        var lr = result_array[1];
        var nw = ul.map(Number); // convert to number
        var se = lr.map(Number);
        /**
         * I always mess up handling negative numbers, so here I use
         * absolute values and convert back for longitudes
         * NOPTE: [0] is lat value, [1] is lng value
         */
        var latmarg = 0.10*(nw[0] - se[0])/2;
        var abslng_west = Math.abs(nw[1]);
        var abslng_east = Math.abs(se[1]);
        var absmarg = 0.20*(abslng_west - abslng_east)/2
        var lngmarg = -absmarg;
        nw = [nw[0]+latmarg, nw[1]+lngmarg];
        se = [se[0]-latmarg, se[1]-lngmarg];
        var lat = result_array[2][0];
        var lng = result_array[2][1];
        map_center = [lat, lng];
        var idb_data;
        var track_poly = result_array[3];
        for (let i=3; i<result_array.length; i++) {
            idb_data = result_array[i].toString();
            if (i === 3) {
                idb_track = idb_data;
                map.flyTo(map_center, 13);
                setTimeout( () => {
                    zoom_level = map.getZoom();
                    zoomOptimizer(zoom_level);
                }, 2500)
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
        show_grp(2);
        return;
    }
}
/**
 * IMPORT A SITE HIKE
 */
// Clear searchbar contents when user clicks on the "X"
$('#clear').on('click', function () {
    $('#search').val("");
    var searchbox = document.getElementById('search');
    searchbox.focus();
});
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
    mapName = entry;
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
 * IMPORT A GPX FILE
 */
$('body').on('submit', '#form', (ev) => {
    ev.preventDefault();
    if (gpximport.files.length === 0) {
        alert("A gpx file has not been selected");
        return false;
    }
    var src = '#impgpx';
    var file_contents = $('#gpxfile')[0].files[0];
    mapName = file_contents.name;
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
    show_grp(1);
    const zlevel = map.getZoom();
    if (zlevel < 13) {
        alert("Minimum zoom level is 13");
        return false;
    }
    $(this).removeClass('btn-primary');
    $(this).addClass('btn-secondary');
    $(this).prop("disabled", true);
    if (typeof rect !== 'undefined') {
        rect.remove();
    }

    // Setup touch event handling
    map.dragging.disable();
    var container = map.getContainer();
    L.DomEvent.on(container, 'touchstart', function(e) {
        L.DomEvent.preventDefault(e);
        saveType = "draw";
        start_rect(e);
    });
    L.DomEvent.on(container, 'touchmove', function(e) {
        L.DomEvent.preventDefault(e);
        draw_rect(e)
    });
    L.DomEvent.on(container, 'touchend', function(e) {
        L.DomEvent.preventDefault(e);
        end_rect(e)
    });
    /* comment out non-mobile when posting to site */
    /*
    click_cnt = 0;
    $('#map').on('click', function (e) {
        if (click_cnt === 0) {
            saveType = "draw";
            $('#map').on('mousemove', function (e) {
                draw_rect(e);
            });
            start_rect(e);
        }
        else {
            end_rect(e);
        }
        return;
    });
    */

    function start_rect(ev) {
        var touch = ev.touches[0];
        //var startRect = map.mouseEventToLatLng(ev.originalEvent);
        var startRect = map.mouseEventToLatLng(touch);
        startX = startRect.lat;
        startY = startRect.lng;
        var rectX = startX + 0.005;
        var rectY = startY + 0.005;
        var crnr1 = L.latLng(startX, startY);
        var crnr2 = L.latLng(rectX, rectY);
        var latlngs = L.latLngBounds(crnr1, crnr2); //[[startX, startY], [rectX, rectY]];
        var rectOpts = { color: 'Green', weight: 1 };
        rect = L.rectangle(latlngs, rectOpts);
        rect.addTo(map);
        //click_cnt = 1;
    }
    function draw_rect(ev) {
        rect.removeFrom(map);
        var touch = ev.touches[0];
        var newRect = map.mouseEventToLatLng(touch);
        //var newRect = map.mouseEventToLatLng(ev.originalEvent);
        var rectX = newRect.lat;
        var rectY = newRect.lng;
        var crnr1 = L.latLng(startX, startY);
        var crnr2 = L.latLng(rectX, rectY);
        var latlngs = L.latLngBounds(crnr1, crnr2); //[[startX, startY], [rectX, rectY]];
        var rectOpts = { color: 'Green', weight: 1 };
        rect = L.rectangle(latlngs, rectOpts);
        rect.addTo(map);
    }
    function end_rect(ev) {
        var touchlist = ev.changedTouches;
        var items = touchlist.length;
        var touch = touchlist.item(items-1);
        var endRect = map.mouseEventToLatLng(touch);
        //var endRect = map.mouseEventToLatLng(ev.originalEvent);
        endX = endRect.lat;
        endY = endRect.lng;
        var lat_ctr = startX - (startX - endX)/2;
        var lng_ctr = startY + (endY - startY)/2;
        map_center = [lat_ctr, lng_ctr];
        //map.dragging.enable();
        show_grp(3);
        getRectTiles(zlevel);
        $('#map').off();
    }
});
// track the zoom level on map
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
    if (zoom_level < 13) {
        $('#setzoom').removeClass('btn-secondary');
        $('#setzoom').addClass('btn-primary');
    } else {
        $('#setzoom').removeClass('btn-primary');
        $('#setzoom').addClass('btn-secondary');
    }
});
// Finding tile coordinates for a higher zoom
var next_zoom_tiles = function(next_level, prevx, prevy) {
    var newx_a = 2 * prevx;
    var newx_b = newx_a + 1;
    var newy_a = 2 * prevy;
    var newy_b = newy_a + 1;
    var loc1 = { x: newx_a, y: newy_a };
    var loc2 = { x: newx_b, y: newy_a };
    var loc3 = { x: newx_a, y: newy_b };
    var loc4 = { x: newx_b, y: newy_b };
    tile_coords[next_level].push(loc1);
    tile_coords[next_level].push(loc2);
    tile_coords[next_level].push(loc3);
    tile_coords[next_level].push(loc4);
    return;
}
function getTileURL(lat, lng, zoom) {
    var latrad = lat * Math.PI / 180;
    var tileX = Math.floor((lng + 180) / 360 * (1 << zoom));
    var tileY = Math.floor((1 - Math.log(Math.tan(latrad)
        + 1 / Math.cos(latrad)) / Math.PI) / 2 * (1 << zoom));
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
    var XY1_Corner = corner1.split("/");
    var corner1XY = XY1_Corner.map(Number); // array:[0]=>zoom;[1]=>row;[2]=col
    var XY2_Corner = corner2.split("/");
    var corner2XY = XY2_Corner.map(Number);
    var tileXmid = 0;
    var tileYmid = 0;
    /**
     * User may draw from any corner, so establish matrix as if it were
     * drawn from upper left to lower right to simplify processing
     */
    var tile1 = []; // tileN => [zoom, row, col]
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
    // each tile[] is an array of 2 (see global variables, above)
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
     * to invoke 'Save'. Each level's tile coords have already been established
     * and are held in 'tile_coords'.
     */
    for (var zoom = zoom_level; zoom < 16; zoom++) { // don't get 'next level' 17
        for (let k=0; k<tile_coords[zoom].length; k++) {
            var xtile = tile_coords[zoom][k].x;
            var ytile = tile_coords[zoom][k].y;
            next_zoom_tiles(zoom+1, xtile, ytile);
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

async function saveMapTiles(userMap) {
    saveStat.show();
    let bar = 0
    let tilecnt = maptiles.length;
    let incr = 100/tilecnt;
    $('#tcnt').text(tilecnt);
    // save the  map_data in the idb
    if (saveType === "import" && typeof idb_track !== 'undefined') {
        await storeMap(userMap, map_center, zoom_level, true, idb_track);
    } else {
        await storeMap(userMap, map_center, zoom_level, false);
    }
    caches.open(MAIN_CACHE)
    .then ( (cache) => {
        for (let j=0; j<tilecnt; j++) {
            bar = (j+1) * incr;
            cache.add(maptiles[j]);
            $('#bar').css('width', bar+"%");
        }
        // save maptiles in local storage for later clearing of cache
        var tileurls = maptiles.join(",");
        localStorage.setItem(userMap, tileurls);
    });
    var oldnames = localStorage.getItem('mapnames');
    var oldmaps  = oldnames.split(",");
    if (oldmaps[0] === 'none') {
        localStorage.setItem('mapnames', userMap);
    } else {
        oldmaps.push(userMap);
        newmaps = oldmaps.join(",");
        localStorage.setItem('mapnames', newmaps);
    }
}
