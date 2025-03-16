"use strict";
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
/**
 * @fileoverview Create an editable polyline for the track of interest
 * @author Ken Cowles
 * @version 1.0 First release
 */
var DECIMALS = 7;
var SIDEBAR_WIDTH = 260;
var $DOT = $("<div class='dot'/>");
var NOMINAL_CLICK_TIME = 250;
// Global vars
var map;
var gpxtrack;
var previewed = new Array;
var gpx_pos;
var clicks = 0;
var double = false;
var triple = false;
var displayed = false;
var beginPreview;
var changeTimer;
var undos = [];
var seqix = 0;
// position editbar text 
var del_btn_pos = $('#del').offset();
$('#dproc').css({
    'left': del_btn_pos.left - 28,
    'top': 60
});
// Set dimensions per window width
function setWidths() {
    var winwidth = window.innerWidth; // innerWidth required by Safari...
    gpx_pos = winwidth - SIDEBAR_WIDTH;
    $('#map').width(gpx_pos - 6);
}
setWidths();
$(window).on('resize', function () {
    setWidths();
});
window.addEventListener('click', function (ev) {
    clicks = ev.detail;
    if (clicks === 3) {
        triple = true;
    }
    else if (clicks === 2) {
        double = true;
    }
});
document.addEventListener('mousedown', function () {
    if (displayed) {
        clearSels();
        displayed = false;
    }
});
function isTypePt(node) {
    var id = '';
    if (node.nodeType === 3) {
        var parent = node.parentElement;
        id = parent.id;
    }
    else if (node.nodeType === 1) {
        id = node.id;
    }
    var pt_node = id.substring(0, 2) === 'pt' ? true : false;
    var data = { ispt: pt_node, nid: id };
    return data;
}
function highlightSels() {
    for (var k = 0; k < previewed.length; k++) {
        var selid = '#pt' + previewed[k];
        $(selid).css({
            color: 'darkred',
            textDecoration: 'line-through'
        });
    }
}
function clearSels() {
    for (var m = 0; m < previewed.length; m++) {
        var selid = '#pt' + previewed[m];
        $(selid).css({
            color: 'black',
            textDecoration: 'none'
        });
    }
}
/**
 * There are significant browser implementation differences in the
 * Selection object. All browsers seem to implement the anchorNode
 * (snode var, below), and the focusNode (enode, below) The 'type'
 * of snode is going to be either the text node in the div, or the div
 * itself when triple-clicking - depending on  browser implementation.
 * Hence, to get the desired ranges for all cases, extract the id's from
 * these two nodes, and don't rely on other specified properties and
 * methods presented in the on-line documention for 'Selection' object.
 */
document.addEventListener('selectionchange', function () {
    // restart every time a selection change occurs:
    beginPreview = null;
    if (changeTimer !== 0) {
        clearTimeout(changeTimer);
    }
    // start the change timing process...
    beginPreview = $.Deferred();
    changeTimer = setTimeout(function () {
        if (typeof beginPreview !== null) {
            beginPreview.resolve(Previews('show'));
        }
    }, 100);
});
function Previews(action) {
    var selection = document.getSelection();
    if (!selection.isCollapsed) { // precludes action from removeAllRanges();
        var snode = selection.anchorNode;
        var snode_data = isTypePt(snode);
        var ptlgth = snode_data.ispt ? 2 : 3;
        var sid = parseInt(snode_data.nid.substring(ptlgth));
        if (triple) {
            // Otherwise, Chrome sets eid = sid + 1
            eid = sid;
            triple = false;
        }
        else {
            var enode = selection.focusNode;
            var enode_data = isTypePt(enode);
            // pt id is always 2 for non-flagged gpxpoints, 3 otherwise
            var ptlgth = enode_data.ispt ? 2 : 3;
            var eid = parseInt(enode_data.nid.substring(ptlgth));
            // get correct order
            if (eid < sid) {
                var tmp = sid;
                sid = eid;
                eid = tmp;
            }
        }
        if (action === 'show') {
            clearSels();
            previewed = [];
            var pix = 0;
            for (var j = sid; j <= eid; j++) {
                previewed[pix++] = j;
            }
            highlightSels();
            displayed = true;
        }
        else { // delete
            var del_qty = eid - sid + 1;
            var dels = trk_json.splice(sid, del_qty);
            displayJson();
            gpxtrack.setMap(null);
            setNewTrack();
            var undo_dat = { seqno: seqix, starts: sid, deletes: dels };
            undos.push(undo_dat);
            var ajaxdata = { type: 'del', trk: trackno, first: sid, last: eid };
            $.post("updateGPX.php", ajaxdata);
        }
    }
}
function initMap() {
    var mapEl = document.getElementById("map");
    map = new google.maps.Map(mapEl, {
        center: mapCtr,
        zoom: 15,
        // optional settings:
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
        streetViewControl: false,
        rotateControl: false,
        mapTypeId: google.maps.MapTypeId.TERRAIN,
    });
    var trackBounds = new google.maps.LatLngBounds(trk_sw, trk_ne);
    map.fitBounds(trackBounds);
    setNewTrack();
    google.maps.event.addListener(map, 'maptypeid_changed', function () {
        var current_id = map.getMapTypeId();
        if (current_id === 'satellite') {
            $('#dproc').removeClass('regfonts');
            $('#pttxt').removeClass('regfonts');
            $('#dproc').addClass('bgfonts');
            $('#pttxt').addClass('bgfonts');
        }
        else {
            $('#dproc').removeClass('bgfonts');
            $('#pttxt').removeClass('bgfonts');
            $('#dproc').addClass('regfonts');
            $('#pttxt').addClass('regfonts');
        }
    });
    map.addListener('idle', function () {
        displayJson();
    });
}
function setNewTrack() {
    gpxtrack = new google.maps.Polyline({
        path: trk_json,
        geodesic: true,
        strokeColor: 'green',
        strokeOpacity: .8,
        strokeWeight: 1,
        zIndex: 1,
        editable: true
    });
    google.maps.event.addListener(gpxtrack.getPath(), 'set_at', function (ev) {
        // ev is the zero-based path item number
        var newloc = ev;
        var newpos = gpxtrack.getPath();
        var movedLatLng = newpos.getAt(newloc);
        var newlat = movedLatLng.lat();
        var newlng = movedLatLng.lng();
        var mlat = newlat.toFixed(DECIMALS);
        var mlng = newlng.toFixed(DECIMALS);
        var movedPt = { lat: parseFloat(mlat), lng: parseFloat(mlng) };
        trk_json[newloc] = movedPt;
        displayJson();
        $('#gpxpts').scrollTop(0);
        var divpos = $('#pt' + ev).offset();
        var newscroll = divpos.top - 20;
        $('#gpxpts').scrollTop(newscroll);
        $('#pt' + newloc).css({
            'color': 'darkgreen',
            'fontWeight': 'bold'
        });
        var ajaxdata = { type: 'mod', trk: trackno, pt: ev, lat: newlat, lng: newlng };
        $.post("updateGPX.php", ajaxdata);
    });
    google.maps.event.addListener(gpxtrack.getPath(), 'insert_at', function (ev) {
        var newpath = gpxtrack.getPath();
        var added = newpath.getAt(ev);
        var alat = added.lat();
        var alng = added.lng();
        trk_json.splice(ev, 0, { "lat": alat, "lng": alng });
        displayJson();
        $('#gpxpts').scrollTop(0);
        var divpos = $('#pt' + ev).offset();
        var newscroll = divpos.top - 20;
        $('#gpxpts').scrollTop(newscroll);
        $('#pt' + ev).css({
            'color': 'brown',
            'fontWeight': 'bold'
        });
        var ajaxdata = { type: 'add', trk: trackno, pt: ev, lat: alat, lng: alng };
        $.post("updateGPX.php", ajaxdata);
    });
    gpxtrack.addListener('mouseover', function (ev) {
        var ptno = ev.vertex;
        $('#ptid').val(ptno);
    });
    gpxtrack.addListener('mouseout', function () {
        $('#ptid').val('');
    });
    gpxtrack.addListener('click', function (ev) {
        var idloc = ev.vertex;
        $('#pt' + idloc).css({
            'color': 'blue',
            'fontWeight': 'bold'
        });
        $('#gpxpts').scrollTop(0);
        var divpos = $('#pt' + idloc).offset();
        var newscroll = divpos.top - 20;
        $('#gpxpts').scrollTop(newscroll);
    });
    gpxtrack.setMap(map);
}
$('#del').on('click', function () {
    var _a;
    var deletions = document.getSelection();
    if (deletions.rangeCount === 0) {
        alert("Nothing has been selected");
        return false;
    }
    if (deletions.isCollapsed) {
        alert("There is no valid selection");
        return false;
    }
    var rcnt = deletions.rangeCount;
    if (rcnt > 1) {
        alert("Cannot delete multiple ranges at this time");
        deletions.removeAllRanges();
        return false;
    }
    Previews('delete');
    (_a = document.getElementById('udel')) === null || _a === void 0 ? void 0 : _a.removeAttribute('disabled');
    $('#udel').removeClass('udel_off');
    $('#udel').addClass('udel_on');
    gpxtrack.setMap(null);
    setNewTrack();
    return;
});
$('#udel').on('click', function () {
    var _a;
    // LIFO stack: undo in reverse order...
    var uitem = undos.pop();
    var insertPt = uitem.starts;
    var inserts = uitem.deletes.length;
    var restores = [];
    for (var k = 0; k < inserts; k++) {
        var ritem = { lat: uitem.deletes[k].lat, lng: uitem.deletes[k].lng };
        restores.push(ritem);
    }
    trk_json.splice.apply(trk_json, __spreadArray([insertPt, 0], restores, false));
    var serial_undos = JSON.stringify(restores);
    var ajaxdata = { type: 'undo', trk: trackno, start: insertPt, undos: serial_undos };
    $.post("updateGPX.php", ajaxdata);
    if (undos.length === 0) {
        (_a = document.getElementById('udel')) === null || _a === void 0 ? void 0 : _a.setAttribute('disabled', 'disabled');
        $('#udel').removeClass('udel_on');
        $('#udel').addClass('udel_off');
    }
    displayJson();
    gpxtrack.setMap(null);
    setNewTrack();
});
function displayJson() {
    $('#gpxpts').empty();
    trk_json.forEach(function (item, index) {
        var lat = item.lat.toFixed(DECIMALS);
        var lng = item.lng.toFixed(DECIMALS);
        var pt = "Pt " + index + ": " + lat + "," + lng;
        var ptid = "pt" + index;
        var next = "<div id='" + ptid + "' class='gpxitems'>" + pt + "</div>";
        $('#gpxpts').append(next);
    });
}
$('#back').on('click', function () {
    window.open("../pages/home.php", "_self");
});
