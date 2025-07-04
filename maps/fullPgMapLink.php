<?php
/**
 * This file is utilized when the user clicks on the hike page link to display
 * a full-page map, OR when the link is copied into the url without being on
 * the hike page. 
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";
$geoloc = '../../images/geoloc.png';

$origin      = isset($_GET['org']) ? filter_input(INPUT_GET, 'org') : 'x';
if ($origin === 'x' || ($origin !== 'd' && $origin !== 'g')) {
    echo "This link must be executed as an embedded link in a hike page";
    exit;
}
$hikeIndexNo = filter_input(INPUT_GET, 'hno');
$hikeTitle   = filter_input(INPUT_GET, 'hike');
$table_age   = filter_input(INPUT_GET, 'tbl');
$htable      = $table_age === 'new' ? 'EHIKES' : 'HIKES';
$ttable      = $table_age === 'new' ? 'ETSV' : 'TSV';
$wtable      = $table_age === 'new' ? 'EWAYPTS' : 'WAYPTS';
$hike_list   = filter_input(INPUT_GET, 'json');
$hike_tracks = explode(",", $hike_list);
if (empty($hike_tracks)) {
    throw new Exception(
        "Link from {$origin}, indx {$hikeIndexNo}, contains empty track list"
    );
}

$trkno = 1;
$trk_nmes = [];
$gpsv_trk = [];
$trk_lats = [];
$trk_lngs = [];
$gpsv_tick = [];
$pageData = prepareMappingData(
    $pdo, $hike_tracks, $trk_nmes, $gpsv_trk, 
    $trk_lats, $trk_lngs, 
    $gpsv_tick, (int) $hikeIndexNo, $htable 
); // returns miles, maxmin, asc, dsc & fills above arrays

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
