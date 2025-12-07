/// <reference types="bootstrap" />
/// <reference types="jquery" />
/// <reference types="leaflet" />
/**
 * @fileoverview When using offline maps already created by 'Create Offline':
 * @author Ken Cowles
 * @version 1.0 First release
 * 
 * Note: this version of the typescript compiler config is not yet supporting
 * ES2025, so that the 'Set.difference' method is not known and results in
 * a compiler error.
 */
const MAIN_CACHE = 'offline';
var maps_available = new bootstrap.Modal(document.getElementById('use_offline') as HTMLDivElement);
readMapKeys().then((result) => {
    const keyvals = result as string[];
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
    const choice = $('#select_map').val() as string;
    maps_available.hide();
    readMapData(choice)
        .then((mapdata) => {
        const mapdat = mapdata as string[];
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
        if (hasTrack !== 'n') {
            const poly = mapdat[4];
            poly.addTo(map);
        }
        var marker: any = null;
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
            marker = L.marker(e.latlng, { icon: customIcon })
                .addTo(map);
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
    var choice = $('#select_map').val() as string;
    var choice_opt = "option[value=" + choice + "]";
    $("#select_map " + choice_opt).remove();
    var choice_dat = localStorage.getItem(choice) as string;
    var choice_urls = choice_dat.split(","); // array
    localStorage.removeItem(choice);
    var urls2delete = [] as string[];
    var usermaps = localStorage.getItem('mapnames') as string;
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
            var cmpmap = localStorage.getItem(map_list[i]) as string;
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
