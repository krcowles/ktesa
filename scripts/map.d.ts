/// <reference types="googlemaps" />
/// <reference types="jquery" />
// variables embedded via php on home.php
declare var CL: Clusters;
declare var NM: Normals;
declare var tracks: string[];
declare var allHikes: number[];
declare var locations: HikeObjs;
declare var pages: string[];
declare var pgnames: string[];
declare type ClustererType = {
    map: google.maps.Map;
    clusterSet: google.maps.Marker[];
    options: MarkerOpts;
};
declare var newBounds: boolean;
// external lib
declare class MarkerClusterer {
    constructor(map: google.maps.Map, clusterSet: google.maps.Marker[], options: MarkerOpts);
}
interface Clusters extends Array<CL> {
    [index: number]: CL;
}
interface Normals extends Array<NM> {
    [index: number]: NM;
}
interface HikeObjs extends Array<HikeObjectLocation> {
    [index: number]: HikeObjectLocation;
}
interface HikeObjectLocation {
    type: string;
    group: number;
}
interface GPS_Coord {
    lat: number;
    lng: number;
}
interface CL {
    group: string;
    loc: GPS_Coord;
    page: number;
    hikes: NM[];
}
interface NM {
    name: string;
    indx: number;
    lgth: number;
    elev: number;
    diff: string;
    prev: string;
    loc: GPS_Coord;
    dirs: string;
}
interface geoOptions {
    enableHighAccuracy: boolean;
}
interface GeoErrorObject {
    code: number;
    message: string;
}
interface MapDoc extends Document {
    fullScreen: boolean;
    mozFullScreen: boolean;
    webkitIsFullScreen: boolean;
}
interface MarkerId {
    hikeid: string;
    clicked?: boolean;
    pin: google.maps.Marker;
}
interface MarkerIds extends Array<MarkerId> {
    //[index: number]: MarkerId;
}
interface HikeTrackObj {
    hike: number;
    track: google.maps.Polyline;
}
interface Hilite_Obj {
    obj?: Normals | NM;
    type?: string;
}
interface MarkerOpts {
    imagePath: string;
    gridSize: number;
    maxZoom: number;
    averageCenter: boolean;
    zoomOnClick: boolean;
}
interface NewTracksArray {
    hike_objs: NM[];
    single_hikes: number[];
    info_wins: string[];
    colors: string[];
}
interface AjaxData {
    no: number;
    action?: string;
}
interface HTMLPosition {
    left: number;
    top: number;
}
interface JsonElement{
	lat: number;
	lng: number;
	ele?: number;
}
interface WayptElement {
	lat: number;
	lng: number;
	name: string;
	sym: string;
}
interface JsonFile {
	wpts?: WayptElement[];
    name: string;
	trk: JsonElement[];
}
