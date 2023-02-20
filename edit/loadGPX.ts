/**
 * @fileoverview This script takes the specified gpx file and sets the lat/lng
 * parameters for google maps.
 * 
 * @author Ken Cowles
 * @version 1.0 First release
 */

/**
 * First, get the boundary box for the file for establishing mapBounds
 */
var north:number, south:number, east:number, west:number;  // derived track boundaries
north = trk_json[0].lat;
south = trk_json[0].lat;
east  = trk_json[0].lng;
west  = trk_json[0].lng;

trk_json.forEach(function(latlng){
    if (latlng.lat > north) {
        north = latlng.lat
    }
    if (latlng.lat < south) {
        south = latlng.lat
    }
    if (latlng.lng > east) {
        east = latlng.lng
    }
    if (latlng.lng < west) {
        west = latlng.lng
    }
});

var trk_sw = {lat: south, lng: west};
var trk_ne = {lat: north, lng: east};
// get the 'center' of the rectangle for centering the map
var htCtr = south + (north - south)/2;
var wdCtr = west + (east - west)/2;
var mapCtr = {lat: htCtr, lng: wdCtr};
