/// <reference types="leaflet" />
const customIcon = L.icon({
    iconUrl: "../images/geodot.png"
});
map.locate(
    {enableHighAccuracy: true, setView: false, watch: true, maxZoom: 17});
map.on('locationfound', function(e) {
    // Create marker with custom icon at user's location
    L.marker(e.latlng, {icon: customIcon})
      .addTo(map)
});

/* Standard gps options ---
var gpsOptions = { enableHighAccuracy: true };
var Lmarker = null;
function setupLoc(): void {
    if (navigator.geolocation) {
        const watchId = navigator.geolocation.watchPosition(success, error, gpsOptions);
        function success(pos_obj) {
            var geoPos = pos_obj.coords;
            var geoLat = geoPos.latitude;
            var geoLng = geoPos.longitude;
            //var pos = { lat: geoLat, lng: geoLng };
            var mrkr_coords = [geoLat, geoLng] as latLngExpression;
            var iconOptions = {
                iconUrl: '../images/geodot.png',
                iconSize: [16, 16]
            } as IconOptions;
            var mrkr_icon = L.icon(iconOptions);
            var markerOptions = {
                icon: mrkr_icon
            };
            var Lmarker = new L.Marker(mrkr_coords, markerOptions);
            Lmarker.addTo(map);
        } 
        function error(eobj) {
            var msg = '<p>Error in get position call: code ' + eobj.code + '</p>';
            window.alert(msg);
        }
    }
    else {
        alert('Geolocation not supported on this browser');
    }
}

/**
 * FUTURE ENHANCEMENT: save gpx track
 */
/*
var geoOptions = { enableHighAccuracy: true };
var init_gps = true; // set first data points; enable locater
var gpsloc; // marks current location
var gps_tracking; // gps data acquisition timer
var gps_interval = 10000; // msec between geolocation acquisitions
var $gon = $('#gon'); // turn on tracking button
var $goff = $('#goff'); // turn off tracking button
var gps_opts = new bootstrap.Modal(document.getElementById('gtrk'));
var connection = new bootstrap.Modal(document.getElementById('offline'));
// Button styling for modal
$('#gps_modal').on('click', function () {
    if ($('#gstate').text() === 'Off') {
        $('#goff').prop('disabled', true);
        $('#gon').prop('disabled', false);
        $('#gstate').text('On');
    }
    else {
        $('#goff').prop('disabled', false);
        $('#gon').prop('disabled', true);
        $('#gstate').text('Off');
    }
    gps_opts.show();
});
// Button clicks:
$gon.on('click', function () {
    gps_location(); // get first data point for trackData
    gps_opts.hide();
    $('#closer').trigger('click');
    gps_tracking = setInterval(function () {
        gps_location();
        if (!notice && navigator.onLine) {
            connection.show();
        }
        if (trackData.length > 2) {
            if (navigator.onLine) {
                track.setMap(null); // remove previous
            }
        }
        if (trackData.length > 1) { // begin creating track
            track = new google.maps.Polyline({
                path: trackData,
                geodesic: true,
                strokeColor: '#F00',
                strokeOpacity: 1.0,
                strokeWeight: 3,
                zIndex: 100
            });
            if (navigator.onLine) {
                track.setMap(map);
            }
        }
    }, gps_interval);
});
$goff.on('click', function () {
    $('#goff').prop('disabled', true);
    $('#gon').prop('disabled', false);
    $('#gstate').text('Off');
    clearInterval(rptr);
    gpsloc.setMap(null);
    clearInterval(gps_tracking);
});
$('#gdwnld').on('click', function () {
    alert("download track");
    gps_opts.hide();
});
*/
