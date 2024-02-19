<?php
/**
 * This file is utilized when the user clicks on the hike page link to display
 * a full-page map, OR when the link is copied into the url without being on
 * the hike page. 
 * PHP Version 7.1
 * 
 * @package GPSV_Mapping
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";
$geoloc = '../../images/geoloc.png';

$hikeIndexNo = filter_input(INPUT_GET, 'hno');
$hikeTitle   = filter_input(INPUT_GET, 'hike');
$table_age   = filter_input(INPUT_GET, 'tbl');
$ttable      = $table_age === 'new' ? 'ETSV' : 'TSV';
$wtable      = $table_age === 'new' ? 'EWAYPTS' : 'WAYPTS';
$hike_list   = filter_input(INPUT_GET, 'json');
$hike_tracks = explode(",", $hike_list);

$trkno = 1;
$trk_nmes = [];
$gpsv_trk = [];
$trk_lats = [];
$trk_lngs = [];
$gpsv_tick = [];
$pageData = prepareMappingData(
    $hike_tracks, $trk_nmes, $gpsv_trk, $trk_lats, $trk_lngs, $gpsv_tick
);

/**
 * The map_opts specify the optional settings for the full-page map.
 */
$map_opts = [
    'zoom' => 17,
    'map_type' => 'GV_HYBRID',
    'street_view'=> 'true',
    'zoom_control' => 'large',
    'map_type_control' => 'menu',
    'center_coordinates' => 'true',
    'measurement_tools' => 'false',
    'utilities_menu' =>  "{ 'maptype':true, 'opacity':true, " .
        "'measure':true, 'export':true }",
    'tracklist_options' => 'true',
    'marker_list_options' => 'true',
    'dynamicMarker' => 'false'  
];
/**
 * The primary file used to create a GPSV, with optional settings
 * as determined by the above $map_opts array.
 */
require '../php/multiMap.php';
