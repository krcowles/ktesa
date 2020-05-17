<?php
/**
 * This module constructs the html for a GPSVisualizer map using multiple
 * gpx files. The tracks are passed via query string from the Table Only
 * page. For each track, latitude and longitude values are extracted.
 * For now, ascent/descent & debug summary are not included, as well as
 * not including photos. As the gpx files are obtained from the site, 
 * less file checking is required.
 * PHP Version 7.3
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 */
require "../php/global_boot.php";
require "../php/gpxFunctions.php";

$files = $_GET['m'];

// establish map display options:
$map_opts = [
    'show_geoloc' => 'false',
    'zoom' => 'auto',
    'map_type' => 'ARCGIS_TOPO_WORLD',
    'street_view'=> 'false',
    'zoom_control' => 'large',
    'map_type_control' => 'menu',
    'center_coordinates' => 'true',
    'measurement_tools' => 'false',
    'utilities_menu' => "{ 'maptype':true, 'opacity':true, " .
        "'measure':true, 'export':true }",
    'tracklist_options' => 'true',
    'marker_list_options' => 'false',
    'show_markers' => 'true',
    'dynamicMarker' => 'true'  
];
$allLats     = [];
$allLngs     = [];
$defClrs     = array('red','blue','aqua','green','fuchsia','pink','orange','black');
$noOfTrks    = 0;
$GPSV_Tracks = [];
$ticks = [];
$waypoints   = [];
// Note: $fileno starts at one (GPSV convention for track id)
$fileno = 1;
foreach ($files as $gpx) {
    $gpxPath = '../gpx/' . $gpx;
    $gpxdat  = simplexml_load_file($gpxPath);
    $noOfFileTrks = $gpxdat->trk->count();
    $noOfTrks += $noOfFileTrks;
    // assign colors:
    for ($i=0; $i<$noOfFileTrks; $i++) {
        // use rolling indx, in case more tracks than defaults
        $circ = $i % $noOfFileTrks;
        $colors[$i] = $defClrs[$circ];
    }
    // Iterate through all tracks in this gpx file
    for ($k=0; $k<$noOfFileTrks; $k++) { // PROCESS EACH TRK
        $trkname = str_replace("'", "\'", $gpxdat->trk[$k]->name);
        $line = "                t = " . $fileno . "; trk[t] = " .
            "{info:[],segments:[]};\n";
        $line .= "                trk[t].info.name = '" . $trkname .
            "'; trk[t].info.desc = ''; trk[t].info.clickable = true;\n";
        $line .= "                trk[t].info.color = '" .
            $colors[$k] . "'; trk[t].info." .
            "width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;\n";
        $line .= "                trk[t].info.outline_color = " .
            "'black'; trk[t].info." .
            "outline_width = 0; trk[t].info.fill_color = '" . $colors[$k] .
            "'; trk[t].info.fill_opacity = 0;\n";
        $tdat = "                trk[t].segments.push({ points:[ [";
        /**
         * Get gpx data into individual arrays and do first level
         * processing. $tdat will be updated with all lats/lngs
         */
        $makeGpsvDebug = false;
        $handleDfa  = null;
        $handleDfc  = null;
        $distThresh = 1;
        $elevThresh = 1;
        $maWindow   = 1;
        $calcs = getTrackDistAndElev(
            $fileno, $k, $trkname, $gpxPath, $gpxdat, $makeGpsvDebug, $handleDfa,
            $handleDfc, $distThresh, $elevThresh, $maWindow, $tdat,
            $ticks
        );
        $allLats = array_merge($allLats, $calcs[5]);
        $allLngs = array_merge($allLngs, $calcs[6]);

        // Finish javascript for this trk: remove last ",[" and end string:
        $tdat = substr($tdat, 0, strlen($tdat)-2);
        $line .= $tdat . " ] });\n";
        $line .= "                GV_Draw_Track(t);\n";
        array_push($GPSV_Tracks, $line);
    }  // end PROCESS EACH TRK
    /**
     *   ---- ESTABLISH ANY WAYPOINTS IN gpx FILE ----
     */
    $noOfWaypts = $gpxdat->wpt->count();
    if ($noOfWaypts > 0) {
        foreach ($gpxdat->wpt as $waypt) {
            $wlat = $waypt['lat'];
            $wlng = $waypt['lon'];
            $sym = $waypt->sym;
            //$text = preg_replace("/'/", "\'", $waypt->name);
            $text = str_replace("'", "\'", $waypt->name);
            $desc = str_replace("'", "\'", $waypt->desc);
            $wlnk = "GV_Draw_Marker({lat:" . $wlat . ",lon:" . $wlng .
                ",name:'" . $text . "',desc:'" . $desc . "',color:'" . "blue" .
                "',icon:'" . $sym . "'});\n";
            array_push($waypoints, $wlnk);
        }
    }
    $fileno++;
}
$hikeTitle = "Multiple File Map";
// Calculate map bounds and center coordiantes
$north = $allLats[0];
$south = $north;
$east = $allLngs[0];
$west = $east;
for ($i=1; $i<count($allLngs)-1; $i++) {
    if ($allLats[$i] > $north) {
        $north = $allLats[$i];
    }
    if ($i === 50) { // arbitrarily chosen #miles in length as a limit
        $msg = "lat: " . $allLats[$i] . ', north: ' . $north;
    }
    if ($allLats[$i] < $south) {
        $south = $allLats[$i];
    }
    if ($allLngs[$i] < $west) {
        $west = $allLngs[$i];
    }
    if ($allLngs[$i] > $east) {
        $east = $allLngs[$i];
    }
}
$clat = $south + ($north - $south)/2;
$clon = $west + ($east - $west)/2;
require "fillGpsvTemplate.php";
$lines = explode("\n", $maphtml);
foreach ($lines as &$dat) {
    $dat .= "\n"; // $lines array uses 'file' which retains newline
}
for ($i=0; $i<count($lines); $i++) {
    echo $lines[$i];
}
