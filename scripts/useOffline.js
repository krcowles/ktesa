"use strict";
/// <reference types="bootstrap" />
/**
 * @fileoverview When using offline maps already created by 'Create Offline':
 * @author Ken Cowles
 * @version 1.0 First release
 * NOTE: I cannot determine how to appease typescript for LatLngExpression
 * and GridDebug, so 'any' is utilized.
 */
const opt_start = "<option value='";
const opt_end = "</option>";
var maps_available = new bootstrap.Modal(document.getElementById('use_offline'));
readMapKeys().then((result) => {
    if (result.indexOf('Failed') !== -1) {
        alert("Failed to read existing map data: contact admin");
        return false;
    }
    if (result.length === 0) {
        $('#available').css('display', 'none');
        $('#no_maps').css('display', 'block');
        $('#use_map').removeClass('btn-success');
        $('#use_map').addClass('btn-secondary');
        $('#use_map').addClass('disabled');
        $('#off_close').removeClass('btn-secondary');
        $('#off_close').addClass('btn-primary');
    }
    else {
        let opts = '';
        let modal_opts = result;
        modal_opts.forEach((opt) => {
            opts += opt_start + opt + "'>" + opt + opt_end;
        });
        $('#select_map').append(opts);
    }
    maps_available.show();
    return;
});
$('body').on('click', '#use_map', function () {
    const choice = $('#select_map').val();
    maps_available.hide();
    //alert("You chose " + choice);
    readMapData(choice)
        .then((mapdat) => {
        // Map setup
        const ctr = mapdat[0];
        const mapctr = ctr.split(",");
        const lat = parseFloat(mapctr[0]);
        const lng = parseFloat(mapctr[1]);
        const display_center = [lat, lng];
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
        if (hasTrack !== 'n') {
            const poly = mapdat[4];
            poly.addTo(map);
        }
        // Control buttons...
        $('#zoomin').on('click', function () {
            alert("Zooming in");
        });
    });
});
$('#back').on('click', function () {
    window.open("../pages/member_landing.html", "_self");
});
