/// <reference types="bootstrap" />
/// <reference types="jquery" />
/// <reference types="leaflet" />
interface CacheMapOptions extends L.MapOptions {
    maxNativeZoom: number;
}
/**
 * @fileoverview User selects offline map already created by 'Create Offline':
 * @author Ken Cowles
 * @version 1.0 First release of offline maps
 * @version 2.0 Added gps tracking capabiity
 * 
 * Note: this version of the typescript compiler config is not yet supporting
 * ES2025, so that the 'Set.difference' method is not known and results in
 * a transpiler error. Typescript also complains that invalidateSize doesn't
 * exist...
 */
// Globals
const MAIN_CACHE = 'offline';
var leaflet_map: L.Map;
var polydat: L.LatLngLiteral[] = [];
var track_poly: L.Polyline;
var zooming = false;
var moving_zoom: number;
const maps_available = new bootstrap.Modal(document.getElementById('use_offline') as HTMLDivElement);

const redraw = () => {
    leaflet_map.invalidateSize({
        animate: true,
        pan: true
    });
};
if (screen.orientation) {
    screen.orientation.addEventListener('change', () => {
        redraw();
    });
} else {
    $(window).on('resize', () => {
        redraw();
    });
}

// Back to landing site
$('#back').on('click', function () {
    window.open("../pages/member_landing.html", "_self");
});
const displayMap = (map_name:string) => {
    readMapData(map_name)
        .then((mapdata) => {
        const mapdat = mapdata as string[];
        // Map setup
        const ctr = mapdat[0];
        const mapctr = ctr.split(",");
        const lat = parseFloat(mapctr[0]);
        const lng = parseFloat(mapctr[1]);
        const cctr = [lat, lng] as unknown;
        const display_center = cctr as L.LatLngLiteral;
        const zoom = parseInt(mapdat[1]);
        const hasTrack = mapdat[2];
        const timeStamp = mapdat[3];
        console.log(timeStamp);
        const mapopts = {
            center: display_center,
            minZoom: 8,
            maxZoom: 18,
            zoom: zoom
        } as CacheMapOptions
        leaflet_map = L.map('map', mapopts);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            maxNativeZoom: 16,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(leaflet_map);
        // point to the starting zoom level
        const zctrl = document.createElement("DIV");
        const zsym  = document.createTextNode("Z: ");
        zctrl.style.marginLeft = "8px";
        zctrl.style.fontSize = "14px";
        zctrl.style.color = "brown";
        zctrl.style.fontWeight = "bold";
        const zval  = document.createElement("SPAN");
        zval.id = "zval";
        zval.textContent = zoom.toString();
        zctrl.append(zsym, zval);
        $('.leaflet-top.leaflet-left').append(zctrl);
        // debounce zoom: zoomend doesn't work in this case
        leaflet_map.addEventListener("zoom", () => {
            if (!zooming) {
                zooming = true;
                setTimeout( () => {
                    var moving_zoom = leaflet_map.getZoom();
                    $('#zval').text(" " + moving_zoom);
                    zooming = false;
                }, 100);
            } 
        });
        if (hasTrack !== 'n') {
            const idb_trk = mapdat[4] as unknown;
            const saved_trk = idb_trk as L.Polyline;
            saved_trk.addTo(leaflet_map);
        }
        var marker: any = null;
        const customIcon = L.icon({
            iconUrl: "../images/geodot.png",
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        leaflet_map.locate({ enableHighAccuracy: true, setView: false, watch: true, maxZoom: 17 });
        leaflet_map.on('locationfound', function (event:L.LocationEvent) {
            if (marker !== null) {
                marker.remove();
            }
            // Create marker with custom icon at user's location
            marker = L.marker(event.latlng, { icon: customIcon }).addTo(leaflet_map);
        });
    });
    $('#select_map').append($('<option>', {
        value: leaflet_map,
        text: leaflet_map
    }));
};
/**
 * For the page load, id the maps available to be displayed;
 * When a map is selected and #use_map is clicked, display the map;
 */
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
    const map_choice = $('#select_map').val() as string;
    maps_available.hide();
    displayMap(map_choice);
    return;
});

/**
 * When a user wishes, he may delete a saved map:
 */
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
    // remaining items...
    usermaps = map_list.length === 0 ? "none" : map_list.join(",");
    localStorage.setItem('mapnames', usermaps);
    if (map_list.length === 0) {
        // last map - delete all urls
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
