"use strict";
/// <reference types="bootstrap" />
/// <reference types="jquery" />
/// <reference types="leaflet" />
/**
 * @fileoverview When using offline maps already created by 'Create Offline':
 * @author Ken Cowles
 * @version 1.0 First release of offline maps
 * @version 2.0 Added gps tracking capabiity
 *
 * Note: this version of the typescript compiler config is not yet supporting
 * ES2025, so that the 'Set.difference' method is not known and results in
 * a transpiler error.
 */
const MAIN_CACHE = 'offline';
const $fname = $('#gpxname');
$fname.val("");
var tracking = false;
var gpx_text = '';
var dwnld_name = '';
var polydat = [];
var maps_available = new bootstrap.Modal(document.getElementById('use_offline'));
var track_saver = new bootstrap.Modal(document.getElementById('gpx_track'));
$('#clear').hide();
$('#save').hide();
$('#track').on('click', () => {
    const $tracker = $('#track');
    if ($tracker.text().includes("Off")) {
        $tracker.text("Track (On)");
        $('#clear').show();
        $('#save').show();
        $('#back').hide();
        tracking = true;
    }
    else {
        $tracker.text("Track (Off)");
        $('#clear').hide();
        $('#save').hide();
        $('#back').show();
        tracking = false;
    }
});
$('#save').on('click', () => {
    if (!navigator.onLine) {
        alert("You must have an internet connection to save the track");
        return false;
    }
    track_saver.show();
    return;
});
$('#clear').on('click', () => {
    polydat = [];
    const ans = confirm("Stop tracking after clear?");
    if (ans) {
        const trkbtn = document.getElementById('track');
        trkbtn.click();
    }
});
const downloadGpx = (gpxstring, fname) => {
    const blob = new Blob([gpxstring], { type: 'text/plain' });
    const blobUrl = URL.createObjectURL(blob);
    const anchor = document.createElement("A");
    anchor.href = blobUrl;
    anchor.download = fname;
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
    URL.revokeObjectURL(blobUrl);
};
$('body').on('click', '#dwnld_track', () => {
    // convert to gpx and download...
    const track_name = $fname.val();
    if (track_name === '') {
        alert("You must supply a track name");
        return false;
    }
    track_saver.hide();
    dwnld_name = track_name + ".gpx";
    const ajax_string = JSON.stringify(polydat);
    //
    $.ajax({
        url: "../php/dwnld_gpx.php",
        method: "post",
        data: { name: track_name, linedata: ajax_string },
        dataType: "text",
        success: function (gpx_contents) {
            downloadGpx(gpx_contents, dwnld_name);
        },
        error: function (_jqXHR, _textStatus, _errorThrown) {
            var msg = "An error has occurred: " +
                "Report this message to the admin\n";
            "Error text: " + _textStatus + "; Error: " +
                _errorThrown + ";\njqXHR: " + _jqXHR.responseText;
            alert(msg);
        }
    });
    return;
});
readMapKeys().then((result) => {
    const keyvals = result;
    if (keyvals.length === 0) {
        $('#available').css('display', 'none');
        $('#no_maps').css('display', 'block');
        $('#use_map').removeClass('btn-success');
        $('#use_map').addClass('btn-secondary');
        $('#use_map').addClass('disabled');
        $('#off_close').removeClass('btn-secondary');
        $('#off_close').addClass('btn-primary');
    }
    else {
        if (keyvals[0].indexOf('Failed') !== -1) {
            alert("Failed to read existing map data: contact admin");
            return false;
        }
        let opts = '';
        let modal_opts = keyvals;
        modal_opts.forEach((opt) => {
            opts += "<option value='" + opt + "'>" + opt + "</option>";
        });
        $('#select_map').append(opts);
    }
    maps_available.show();
    return;
});
$('body').on('click', '#use_map', function () {
    const choice = $('#select_map').val();
    maps_available.hide();
    readMapData(choice)
        .then((mapdata) => {
        const mapdat = mapdata;
        // Map setup
        const ctr = mapdat[0];
        const mapctr = ctr.split(",");
        const lat = parseFloat(mapctr[0]);
        const lng = parseFloat(mapctr[1]);
        const cctr = [lat, lng];
        const display_center = cctr;
        const zoom = parseInt(mapdat[1]);
        const hasTrack = mapdat[2];
        const timeStamp = mapdat[3];
        console.log(timeStamp);
        var map = L.map('map', {
            center: display_center,
            minZoom: 8,
            maxZoom: 17,
            zoom: zoom
        });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        /*
        L.GridLayer.GridDebug = L.GridLayer.extend({
            createTile: function (coords) {
                var tile = document.createElement("DIV");
                tile.style.outline = '1px solid azure';
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
        */
        if (hasTrack !== 'n') {
            const idb_trk = mapdat[4];
            const saved_trk = idb_trk;
            saved_trk.addTo(map);
        }
        var marker = null;
        const customIcon = L.icon({
            iconUrl: "../images/geodot.png",
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        map.locate({ enableHighAccuracy: true, setView: false, watch: true, maxZoom: 17 });
        map.on('locationfound', function (e) {
            if (marker !== null) {
                marker.remove();
            }
            // Create marker with custom icon at user's location
            marker = L.marker(e.latlng, { icon: customIcon }).addTo(map);
            if (tracking) {
                map.eachLayer((layer) => {
                    if (layer instanceof L.Polyline) {
                        // remove previous polyline before extending new one
                        map.removeLayer(layer);
                    }
                });
                polydat.push(e.latlng);
                if (polydat.length > 1) {
                    L.polyline(polydat).addTo(map);
                }
            }
        });
    });
    $('#select_map').append($('<option>', {
        value: choice,
        text: choice
    }));
});
$('#back').on('click', function () {
    window.open("../pages/member_landing.html", "_self");
});
$('body').on('click', '#delmap', function () {
    /**
     * NOTE: to get here, there had to be at least one stored map!
     * ----------------------------------------------------------------
     * There is a possibility that two (or more) maps share tile urls,
     * so when deleting tile urls in cache, care must be taken to avoid
     * elimination of urls needed by other maps. All url strings are
     * stored in local storage with the item name equal to the mapname
     * used to save. Most items will be in the range of 10-20K, so impact
      * is low, especiall since a low number of maps will be saved.
     */
    var choice = $('#select_map').val();
    var choice_opt = "option[value=" + choice + "]";
    $("#select_map " + choice_opt).remove();
    var choice_dat = localStorage.getItem(choice);
    var choice_urls = choice_dat.split(","); // array
    localStorage.removeItem(choice);
    var urls2delete = [];
    var usermaps = localStorage.getItem('mapnames');
    var map_list = usermaps.split(","); // array
    var indx = map_list.indexOf(choice);
    if (indx !== -1) {
        map_list.splice(indx, 1);
    }
    usermaps = map_list.length === 0 ? "none" : map_list.join(",");
    localStorage.setItem('mapnames', usermaps);
    if (map_list.length === 0) {
        urls2delete = [...choice_urls];
    }
    else {
        var baseSet = new Set(choice_urls);
        for (let i = 0; i < map_list.length; i++) {
            var cmpmap = localStorage.getItem(map_list[i]);
            var cmpSet = new Set(cmpmap.split(","));
            baseSet = baseSet.difference(cmpSet);
        }
        // baseSet should now contain only no overlapping urls
        urls2delete = Array.from(baseSet);
    }
    caches.open(MAIN_CACHE)
        .then((cache) => {
        let cnt = 0;
        urls2delete.forEach((url) => {
            cache.delete(url);
            cnt++;
        });
        alert(choice + " deleted");
        console.log("Deleted ", cnt, " items");
    });
});
