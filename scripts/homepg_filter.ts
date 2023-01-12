interface GPSData {
    lat?: number;
    lng?: number;
}
interface FilterData {
    type: string;
    coords: GPSData;
}
/**
 * @fileoverview Provide sideTable filtering capability
 * @author Ken Cowles
 * 
 * @version 1.0 Initial release
 */
// ----- GLOBALS -----
// margin on map bounds in px;
const mapMarg  = 200;
// values for sorting by difficulty
const diffs    = ['Easy', 'Easy-Moderate', 'Moderate', 'Med-Difficult', 'Difficult'];
const diff_val = [1, 10, 100, 1000, 10000];

// Filter by distance from hike
const miles_from_hike = (hike: string, epsilon: number) => {
    // get coordinates of hike & container object type
    let hikeloc = getHikeGPS(hike);
    if (Object.keys(hikeloc).length === 0) {
        alert("This is not a single hike");
        return;
    }
    filterByMiles(epsilon, hikeloc);
    return;
};
/**
 * This function extracts latitude/longitude form the table for the
 * target hike name.
 */
function getHikeGPS(hike: string): GPSData {
    var found = false;
    var coords: GPSData = {};
    if (pgnames.includes(hike)) { // These are 'Cluster Pages', not hikes
            found = true; // coords will be empty
    } else {
        for (let i=0; i<CL.length; i++) {
            for (let j=0; j<CL[i].hikes.length; j++) {
                if (CL[i].hikes[j].name == hike) {
                    let clus = CL[i].hikes[j];
                    coords = clus.loc;
                    found = true;
                    break;
                }
            }
        }          
    }
    if (!found) {
        for (let k=0; k<NM.length; k++) {
            if (NM[k].name == hike) {
                coords = NM[k].loc;
                found = true;
                break;
            }
        }
    }
    if (!found) {
        alert("This hike cannot be located in the list of hikes");
    }
    return coords;
}
/**
 * This is invoked when selecting the filter for miles from locale
 */
function miles_from_locale(locale: string, miles: number) {
    var arealoc: GPSData = {};
    $.ajax({ // returns array of location centers on success
        url:      '../json/areas.json',
        dataType: 'json',
        success: function(json_data) {
            var areaCenters: AreaData[] = json_data.areas;
            for (var j=0; j<areaCenters.length; j++) {
                if (areaCenters[j].loc == locale) {
                    arealoc = {
                        "lat": areaCenters[j].lat, 
                        "lng": areaCenters[j].lng
                    };
                    break;
                }
            }
            if (Object.keys(arealoc).length === 0) {
                alert("This locale has no location data");
                return false;
            }
            filterByMiles(miles, arealoc);
            return;
        },
        error: function() {
            alert("Unable to retrieve area data\nAdmin has been notified");
            let errobj = {err: "No areas.json file"};
            $.post('../php/ajaxError.php', errobj);
            return false;
        }
    });
    return;
}
/**
 * This function will determine which hikes are within the epsilon radius;
 */
function filterByMiles(radius: number, gps_coords: GPSData): void {
    var starray: NM[] = [];
    let ctrlat = <number>gps_coords.lat;
    let ctrlng = <number>gps_coords.lng;
    CL.forEach(function(clobj: CL) {
        let clhikes = clobj.hikes;
        clhikes.forEach(function(hobj) {
            let hlat = hobj.loc.lat;
            let hlng = hobj.loc.lng;
            let distance = distFromCtr(hlat, hlng, ctrlat, ctrlng, 'M');
            if (distance <= radius) {
                starray.push(hobj);
            }
        }); 
    });
    NM.forEach(function(nmobj: NM) {
        let hikeset = nmobj.loc;
        let hikelat = hikeset.lat;
        let hikelng = hikeset.lng;
        let distance = distFromCtr(hikelat, hikelng, ctrlat, ctrlng, 'M');
        if (distance <= radius) {
            starray.push(nmobj);
        }
    });
    if (starray.length > 0) {
        starray.sort(compareObj);
        formTbl(starray);
        let map_bounds: google.maps.LatLngBounds = arrayBounds(starray);
        map.fitBounds(map_bounds, mapMarg);
    } else {
        alert("No hikes within specified range");
    }
}
/**
 * This function will return the radial distance between two lat/lngs
 */
function distFromCtr(lat1: number, lon1: number, lat2: number, lon2: number, unit: string) {
    if (lat1 === lat2 && lon1 === lon2) { return 0; }
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1-lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist);
    dist = dist * 180/Math.PI;
    dist = dist * 60 * 1.1515;
    if (unit === "K") { dist = dist * 1.609344; }
    if (unit === "N") { dist = dist * 0.8684; }  // else result is in miles "M"
    return dist;
}
/**
 * Examine the gps coordinates of filtered hikes and determine the
 * map bounds for the set
 */
function arrayBounds(array: NM[]) {
    let lats: number[] = [];
    let lngs: number[] = [];
    array.forEach(function(hobj) { 
        lats.push(hobj.loc.lat);
        lngs.push(hobj.loc.lng); 
    });
    let north = Math.max(...lats);
    let south = Math.min(...lats);
    let east  = Math.max(...lngs);
    let west  = Math.min(...lngs);
    let sw = {lat: south, lng: west} as google.maps.LatLngLiteral;
    let ne = {lat: north, lng: east} as google.maps.LatLngLiteral;
    let bounds = new google.maps.LatLngBounds(sw, ne);
    return bounds;
}
/** 
 * Used when comparing objects to be sorted by difficulty
 */
function compareDiff(a: NM, b: NM) {
    var hikea: string = a.diff;
    var hikeb: string = b.diff;
    // rank the difficulties for the comparison
    var comparison: number;
    var adiff = diffs.indexOf(hikea);
    var bdiff = diffs.indexOf(hikeb);
    var arank = diff_val[adiff];
    var brank = diff_val[bdiff];
    if (ascending) {
        if (arank > brank) {
            comparison = 1;
        } else {
            comparison = -1;
        }
    } else {
        if (arank < brank) {
            comparison = 1;
        } else {
            comparison = -1;
        }
    }
    return comparison;
}
/** 
 * Used when comparing objects to be sorted by hike distance
 */
function compareDist(a: NM, b: NM) {
    var hikea: number = a.lgth;
    var hikeb: number = b.lgth;
    var comparison: number;
    if (ascending) {
        if (hikea > hikeb) {
            comparison = 1;
        } else {
            comparison = -1;
        }
    } else {
        if (hikea < hikeb) {
            comparison = 1;
        } else {
            comparison = -1;
        }
    }
    return comparison;
}
