<?php
/**
 * This file is utilized when the user clicks on the hike page link to display
 * a full-page map, OR when the link is copied into the url without being on
 * the hike page. 
 * 
 * @package GPSV_Mapping
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../maps
 */
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
$map = filter_input(INPUT_GET, 'map_name');
$hikeIndexNo = filter_input(INPUT_GET, 'hno');
$hikeTitle = filter_input(INPUT_GET, 'hike');
$gpxPath = filter_input(INPUT_GET, 'gpx');
if (filter_input(INPUT_GET, 'tbl') === 'new') {
    $ttable = 'ETSV';
} else {
    $ttable = 'TSV';
}
/**
 * The map_opts specify the optional settings for the full-page map.
 */
$map_opts = [
    'show_geoloc' => 'true',
    'zoom' => 'auto',
    'map_type' => 'GV_HYBRID',
    'street_view'=> 'true',
    'zoom_control' => 'large',
    'map_type_control' => 'menu',
    'center_coordinates' => 'true',
    'measurement_tools' => 'false',
    'utilities_menu' => "{ 'maptype':true, 'opacity':true, " .
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
require '../php/makeGpsv.php';
$lines = explode("\n", $maphtml);
foreach ($lines as &$dat) {
    $dat .= "\n"; // $lines array uses 'file' which retains newline
}
for ($i=0; $i<count($lines); $i++) {
    echo $lines[$i];
}
?>