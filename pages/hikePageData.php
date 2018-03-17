<?php
/**
 * This script provides the data required by hikePageTemplate.php in order
 * to display an individual hike page.
 * 
 * @package Display_Page
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
$tbl = filter_input(INPUT_GET, 'age');
/**
 * The variable $hikeIndexNo is established below and is used throughout
 * to locate data corresponding to this unique hike identifier.
 */
$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx');
$ehikes = (isset($tbl) && $tbl === 'new') ? true : false;
/**
 * The following variables are used to define the tables to be used
 * in the MySQL queries, based on whether or not in-edit hike data
 * is required.
 */
if ($ehikes) {
    $htable = 'EHIKES';
    $rtable = 'EREFS';
    $gtable = 'EGPSDAT';
    $ttable = 'ETSV';
    $tbl = 'new';
} else {
    $htable = 'HIKES';
    $rtable = 'REFS';
    $gtable = 'GPSDAT';
    $ttable = 'TSV';
    $tbl = 'old';
}
/**
 * The get_HIKES_row.php utility extracts data from the $htable
 * based on the $hikeIndexNo established above.
 */
require "../mysql/get_HIKES_row.php";
if ($gpxfile == '') {
    $newstyle = false;
    $gpxPath = '';
} else {
    $newstyle = true;
    $gpxPath = '../gpx/' . $gpxfile;
}
/**
 * The get_TSV_row.php utility extracts data about the photos to
 * be displayed on the page.
 */
require "../mysql/get_TSV_row.php";
$capCnt = count($descs);
// if there are additional images (non-captioned), process them here:
if (is_array($hikeAddonImg1)) {
    $aoimg1 = '../images/' . $hikeAddonImg1[0];
    array_push($descs, $hikeAddonImg1[0]);
    array_push($alblnks, '');
    array_push($piclnks, $aoimg1);
    array_push($captions, '');
    $ht = $hikeAddonImg1[1];
    $wd = $hikeAddonImg1[2];
    array_push($widths, $wd);
    $imgRatio = $wd/$ht;
    array_push($aspects, $imgRatio);
}
if (is_array($hikeAddonImg2)) {
    $aoimg2 = '../images/' . $hikeAddonImg2[0];
    array_push($descs, $hikeAddonImg2[0]);
    array_push($alblnks, '');
    array_push($piclnks, $aoimg2);
    array_push($captions, '');
    $ht = $hikeAddonImg2[1];
    $wd = $hikeAddonImg2[2];
    array_push($widths, $wd);
    $imgRatio = $wd/$ht;
    array_push($aspects, $imgRatio);
}
/**
 * There are two possible types of hike page displays. If the hike page
 * has a map and elevation chart to display, the variable $newstyle is
 * true, and these items are displayed.  Otherwise, a page with a hike
 * summary table is presented with photos and information, but no map or
 * elevation chart ($newstyle is false).
 */
if ($newstyle) {
    /**
     * In the case of hike map and elevation chart, in order for the map to be
     * displayed in an iframe, a file is created and stored in the maps/tmp
     * sub-directory. The file is deleted upon exiting the page.
     */
    $extLoc = strrpos($gpxfile, '.');
    $gpsvMap = substr($gpxfile, 0, $extLoc); // strip file extension
    $tmpMap = "../maps/tmp/{$gpsvMap}.html";
    if (($mapHandle = fopen($tmpMap, "w")) === false) {
        $mapmsg = "Contact Site Master: could not open tmp map file: " .
            $tmpMap . ", for writing";
        die($mapmsg);
    }
    $fpLnk = "../maps/fullPgMapLink.php?maptype=page&hike={$hikeTitle}" .
        "&gpx={$gpxPath}&hno={$hikeIndexNo}&tbl={$tbl}";
    $map_opts = [
        'show_geoloc' => 'true',
        'zoom' => 'auto',
        'map_type' => 'ARCGIS_TOPO_WORLD',
        'street_view'=> 'false',
        'zoom_control' => 'large',
        'map_type_control' => 'menu',
        'center_coordinates' => 'true',
        'measurement_tools' => 'false',
        'utilities_menu' => "{ 'maptype':true, 'opacity':true, " .
            "'measure':true, 'export':true }",
        'tracklist_options' => 'false',
        'marker_list_options' => 'false',
        'show_markers' => 'true',
        'dynamicMarker' => 'true'  
    ];
    include "../php/makeGpsv.php";
    fputs($mapHandle, $maphtml);
    fclose($mapHandle);
}
