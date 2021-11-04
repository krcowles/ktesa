<?php
/**
 * This file is utilized when the user clicks on the hike page link to display
 * a full-page map, OR when the link is copied into the url without being on
 * the hike page. 
 * PHP Version 7.1
 * 
 * @package GPSV_Mapping
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";
$geoloc = '../../images/geoloc.png';

$hikeIndexNo = filter_input(INPUT_GET, 'hno');
$hikeTitle   = filter_input(INPUT_GET, 'hike');
$tbl         = filter_input(INPUT_GET, 'tbl');
$clusterPage = false;  // default
if ($tbl === 'new') {
    $mtable = 'EMETA';
    $xtable = 'EGPX';
    $ttable = 'ETSV';
} else {
    $mtable = 'META';
    $xtable = 'GPX';
    $ttable = 'TSV';
}
if (isset($_GET['clus']) && $_GET['clus'] === 'y') {
    $files = $_GET['gpx'];
    $clusterPage = true;
} else {
    $gpx = filter_input(INPUT_GET, 'gpx');
    $files = explode(",", $gpx);
}

// required by multiMap.php
$makeGpsvDebug = false;
$handleDfa  = null;
$handleDfc  = null;
$distThresh = 1;
$elevThresh = 1;
$maWindow   = 1;
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
    'show_markers' => 'true',
    'dynamicMarker' => 'false'  
];
/**
 * The primary file used to create a GPSV, with optional settings
 * as determined by the above $map_opts array.
 */
require '../php/multiMap.php';
