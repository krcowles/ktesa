"use strict";
/**
 * @fileoverview When using offline maps already created by 'Create Offline':
 * @author Ken Cowles
 * @version 1.0 First release
 * NOTE: I cannot determine how to appease typescript for LatLngExpression
 * and GridDebug
 */
/*
const platform = type ? 'mobile' : 'notmobile';
const selectedMap = $('#selectmap').text();
if (selectedMap === '') {
    var modal = document.getElementById('use_offline') as HTMLDivElement;
    var use_omap = new bootstrap.Modal(modal);
    const modal_opts = $('#gotopts').html().trim();
    if (modal_opts === 'none') {
        $('#no_maps').css('display', 'block');
        $('#use_map').removeClass('btn-success');
        $('#use_map').addClass('btn-secondary');
        $('#use_map').addClass('disabled');
        $('#off_close').removeClass('btn-secondary');
        $('#off_close').addClass('btn-primary');
    } else {
        let sel_opts = document.createElement('TEXTNODE');
        sel_opts.textContent = modal_opts;
        $('#select_map').prepend(modal_opts);
        $('#available').css('display', 'block');
    }
    use_omap.show();
    $('#use_map').on('click', function() {
        const choice = $('#select_map').val();
        const newpage = `../pages/useOffline.php?type=${platform}&map=${choice}`;
        use_omap.hide();
        window.open(newpage, "_self");
    });
} else {
    readMapData(selectedMap)
    .then((mapdat) => {
        // Map setup
        const ctr = mapdat[0] as string;
        const mapctr = ctr.split(",");
        const lat = parseFloat(mapctr[0]);
        const lng = parseFloat(mapctr[1]);
        const display_center: any = [lat, lng];
        const zoom = parseInt(mapdat[1] as string);
        const hasTrack = mapdat[2] as string;
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
            const poly = mapdat[4] as L.Polyline<GeoJSON.LineString | GeoJSON.MultiLineString, any>;
            //const pline = JSON.parse(poly);
            //const track = L.polyline(pline);
            poly.addTo(map);
        }
        // Control buttons...
        $('#zoomin').on('click', function() {
            alert("Zooming in");
        });
 */
// Establish cache for map tiles
/*
caches.open("Map1")
.then( (cache) => {
    cache.add("https://tile.openstreetmap.org/13/1674/3242.png");
} );
 */
/*
    });
}
*/
readMapKeys().then((result) => {
    alert(result);
});
