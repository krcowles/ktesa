function initMap() {
    var nmCtr = { lat: 34.450, lng: -106.042 };
    map = new google.maps.Map(document.getElementById('kmap'), {
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
    new google.maps.KmlLayer({
        url: "https://nmhikes.com/gpx/Picacho.kml",
        map: map
    });
}
