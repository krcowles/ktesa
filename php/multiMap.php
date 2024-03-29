<?php
/**
 * This module constructs the html for a GPSVisualizer map using one or
 * more track files [hence the script name 'multiMap']. This module can
 * be included by any of three possible sources, each of which can specify
 * optional elements to be included:
 *      1. "Draw Map" button on Table Only page (tableOnly.php)
 *      2. Hike Page via hikePageTemplate.php   (hikePageData.php)
 *      3. Full page map via link on Hike Page  (fullPgMapLink.php)
 * Optional elements are waypoints and photo markers.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 */
require_once "../php/global_boot.php";

$tblOnly = isset($hikeIndexNo) ? false : true;
// tblOnly files are input via query string as an array of track names
if ($tblOnly) {
    $hike_tracks = $_GET['m'];
    $wtable = 'WAYPTS'; // only published hikes will use this routine
    $gpsv_trk = [];
    $trk_nmes = [];
    $trk_lats = [];
    $trk_lngs = [];
    $gpsv_tick = [];
    prepareMappingData(
        $hike_tracks, $trk_nmes, $gpsv_trk, $trk_lats, $trk_lngs, $gpsv_tick
    ); // returns miles, maxmin, asc, dsc & fills above array
    $map_opts = [
        'zoom' => 17,
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
        'dynamicMarker' => 'false'  
    ];
    $hikeTitle = "Multiple GPX File Map";
}

/**
 * '$hike_tracks' is an array of 1 or more json files to be applied to the map.
 * The following variables must be defined in the caller if not above:
 *  - $gpsv_trk
 *  - $trk_nmes
 *  - $trk_lats
 *  - $trk_lngs
 *  - $gpsv_tick
 */
$allLats     = []; // for calculating map bounds
$allLngs     = []; // for calculating map bounds
$defClrs     = array('red','blue','aqua','green','fuchsia','pink','orange','black');
$colorIndx   = 0;
$gpsv_trkno  = 1;  // sequentially increasing with each file
$GPSV_Tracks = [];
$waypoints   = [];
$noOfWaypts  = 0;
$geoloc = "../../images/geoloc.png"; 
$noOfTracks  = count($hike_tracks);
$trkno       = 1;

foreach ($hike_tracks as $json) {
    $line = '';
    // Iterate through all tracks
    for ($k=0; $k<$noOfTracks; $k++) { // PROCESS EACH TRK 
        // Accumulate data for finding map bounds:
        $allLats = array_merge($allLats, $trk_lats[$k]);
        $allLngs = array_merge($allLngs, $trk_lngs[$k]); 
        /**
         * Prepare track data for map setup (GV_Map_Setup)
         */
        if ($colorIndx === 8) { // colors should rollover to the beginning now
            $colorIndx = 0;
        }
        $trkNo = $gpsv_trkno++;
        $line  = "                t = " . $trkNo .
                                        "; trk[t] = {info:[],segments:[]};\n";
        $line .= "                trk[t].info.name = '" . $trk_nmes[$k] .
                "'; trk[t].info.desc = ''; trk[t].info.clickable = true;\n";
        $line .= "                trk[t].info.color = '" .
            $defClrs[$colorIndx] . "'; trk[t].info." .
            "width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;\n";
        $line .= "                trk[t].info.outline_color = " .
            "'black'; trk[t].info." .
            "outline_width = 0; trk[t].info.fill_color = '" .
            $defClrs[$colorIndx++] . "'; trk[t].info.fill_opacity = 0;\n";

        $line .= "                trk[t].segments.push({ points:" . 
                    $gpsv_trk[$k] ." });\n";
        $line .= "                GV_Draw_Track(t);\n";
        array_push($GPSV_Tracks, $line);
    }  // end PROCESS EACH TRK

    /**
     *   ---- ESTABLISH ANY WAYPOINTS ----
     */
    if (isset($hikeIndexNo)) {
        $getAllWptsReq
            = "SELECT * FROM {$wtable} WHERE `indxNo`={$hikeIndexNo}";
        $allWpts = $pdo->query($getAllWptsReq)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allWpts as $wpt) {
            $lat = $wpt['lat']/LOC_SCALE;
            $lng = $wpt['lng']/LOC_SCALE;
            $wpt['sym'] = empty($wpt['sym']) ? "Triangle, Yellow" : $wpt['sym'];
            $wlnk = "GV_Draw_Marker({lat:" . $lat . ",lon:" . $lng .
                ",name:'" . $wpt['name'] . "',desc:'',color:'blue'," .
                "icon:'" . $wpt['sym'] . "'});\n";
            array_push($waypoints, $wlnk);
        }
        $noOfWaypts = count($allWpts);
    }

    /**
     *   ---- OPTIONAL PHOTOS ----
     */
    $plnks = [];  // array of photo links
    $defIconColor = 'red';
    $defIconStyle = 'googlemini';
    if (!$tblOnly) {
        // see GPSVisualizer for complete list of icon styles:
        $mapicon = $defIconStyle;
        $picReq = "SELECT * FROM {$ttable} WHERE indxNo = :hikeIndexNo;";
        $dbdat = $pdo->prepare($picReq);
        $dbdat->bindValue(":hikeIndexNo", $hikeIndexNo);
        $dbdat->execute();
        $photoDat = $dbdat->fetchAll(PDO::FETCH_ASSOC);
        foreach ($photoDat as $photos) {
            if ($photos['mpg'] === 'Y') {
                $procName = preg_replace("/'/", "\'", $photos['title']);
                $procName = preg_replace('/"/', '\"', $procName);
                $procDesc = preg_replace("/'/", "\'", $photos['desc']);
                $procDesc = preg_replace('/"/', '\"', $procDesc);
                if (empty($photos['iclr'])) {
                    $iconColor = $defIconColor;
                } else {
                    $iconColor = $photos['iclr'];
                }
                $aspect = $photos['imgWd']/$photos['imgHt'];
                $thumb_nom = 300;
                $thumb_width = $aspect < 1 ? $aspect * $thumb_nom : $thumb_nom;
                $plnk = "GV_Draw_Marker({lat:" . $photos['lat']/LOC_SCALE .
                    ",lon:" . $photos['lng']/LOC_SCALE . ",name:'" . $procDesc .
                    "',desc:'',color:'" . $iconColor . "',icon:'" . $mapicon .
                    "',url:'/pictures/zsize/" . $photos['mid'] . "_" . 
                    $photos['thumb'] .
                    "_z.jpg" . "',thumbnail:'/pictures/zsize/" . 
                    $photos['mid'] . "_" . $photos['thumb'] . "_z.jpg" .
                    "',thumbnail_width:'" . $thumb_width . 
                    "',folder:'" . $photos['folder'] . "'});";
                array_push($plnks, $plnk);
            }
        }
    }
}
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

if (!isset($hikepage)) {
    $lines = explode("\n", $maphtml);
    foreach ($lines as &$dat) {
        $dat .= "\n"; // $lines array uses 'file' which retains newline
    }
    for ($i=0; $i<count($lines); $i++) {
        echo $lines[$i];
    }
}
