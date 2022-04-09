"use strict";
var kmlnode = document.getElementById('kmlfile');
var kmlurl = kmlnode.textContent;
function initKml() {
    var mapid = document.getElementById('kmap');
    var map = new google.maps.Map(mapid, {
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
    new google.maps.KmlLayer({
        url: kmlurl,
        map: map
    });
}
