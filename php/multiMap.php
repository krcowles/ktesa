<?php
/**
 * This module constructs the html for a GPSVisualizer map using one or
 * more gpx files. This module can be included by any of three possible
 * sources, each of which can specify optional elements to be included:
 *      1. "Draw Map" button on Table Only page
 *      2. Hike Page via hikePageTemplate.php (hikePageData.php)
 *      3. Full page map via link on Hike Page (fullPgMapLink.php)
 * Optional elements are waypoints and photo markers.
 * Whether required by the calling program or not, latitude and longitude
 * values are extracted, and, if requested,  ascent/descent data and debug
 * summary file (query string parameters).
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 */
require_once "../php/global_boot.php";
require_once "../php/gpxFunctions.php";

// detect if script called as window (Table Only page)
$tblOnly = isset($hikeIndexNo) ? false : true;

// tblOnly files are input via query string, $map_opts are included below:
if ($tblOnly) {
    $query_files = $_GET['m'];
    $files = [];
    for ($j=0; $j<count($query_files); $j++) {
        // If > 1 file listed in a single "m[]" parameter, get extra files
        if (strpos($query_files[$j], ",") !== false) {
            $merged = explode(",", $query_files[$j]);
            foreach ($merged as $new) {
                array_push($files, $new);
            }
        } else {
            array_push($files, $query_files[$j]);
        }
    }
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
        'show_markers' => 'true',
        'dynamicMarker' => 'true'  
    ];
    $hikeTitle = "Multiple GPX File Map";
    $makeGpsvDebug = false;
    $handleDfa  = null;
    $handleDfc  = null;
    $distThresh = 1;
    $elevThresh = 1;
    $maWindow   = 1;
}
// data for hike page, if required
$hikeFiles = json_encode($files);
// data for map
$allLats     = [];
$allLngs     = [];
$defClrs     = array('red','blue','aqua','green','fuchsia','pink','orange','black');
$colorIndx   = 0;
$noOfTrks    = 0;  // sequentially increasing with each file
$GPSV_Tracks = [];
$waypoints   = [];
$trackTicks  = []; // each member is an array of ticks for a track 
foreach ($files as $gpx) {
    $gpxPath = '../gpx/' . $gpx;
    $gpxdat  = simplexml_load_file($gpxPath);
    if ($gpxdat->rte->count() > 0) {
        $gpxdat = convertRtePts($gpxdat);
    }
    $noOfFileTrks = $gpxdat->trk->count();
    
    // calculated stats for all tracks within the file
    $pup = (float)0;
    $pdwn = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    $hikeLgthTot = (float)0;
    // Iterate through all tracks in this gpx file
    for ($k=0; $k<$noOfFileTrks; $k++) { // PROCESS EACH TRK
        $ticks = []; // one set of ticks per track
        $trkNo = $noOfTrks + $k + 1;
        $trkname = str_replace("'", "\'", $gpxdat->trk[$k]->name);
        $line = "                t = " . $trkNo . "; trk[t] = " .
            "{info:[],segments:[]};\n";
        $line .= "                trk[t].info.name = '" . $trkname .
            "'; trk[t].info.desc = ''; trk[t].info.clickable = true;\n";
        $line .= "                trk[t].info.color = '" .
            $defClrs[$colorIndx] . "'; trk[t].info." .
            "width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;\n";
        $line .= "                trk[t].info.outline_color = " .
            "'black'; trk[t].info." .
            "outline_width = 0; trk[t].info.fill_color = '" . $defClrs[$colorIndx++] .
            "'; trk[t].info.fill_opacity = 0;\n";
        $tdat = "                trk[t].segments.push({ points:[ [";
        /**
         * Get gpx data into individual arrays and do first level
         * processing. $tdat will be updated with all lats/lngs
         */
        $calcs = getTrackDistAndElev(
            $trkNo, $k, $trkname, $gpxPath, $gpxdat, $makeGpsvDebug, $handleDfa,
            $handleDfc, $distThresh, $elevThresh, $maWindow, $tdat, $ticks
        );
        array_push($trackTicks, $ticks);
        $hikeLgthTot += $calcs[0];
        if ($calcs[1] > $pmax) {
            $pmax = $calcs[1];
        }
        if ($calcs[2] < $pmin) {
            $pmin = $calcs[2];
        }
        $pup  += $calcs[3];
        $pdwn += $calcs[4];
        $allLats = array_merge($allLats, $calcs[5]);
        $allLngs = array_merge($allLngs, $calcs[6]);
        // Finish javascript for this trk: remove last ",[" and end string:
        $tdat = substr($tdat, 0, strlen($tdat)-2);
        $line .= $tdat . " ] });\n";
        $line .= "                GV_Draw_Track(t);\n";
        array_push($GPSV_Tracks, $line);
    }  // end PROCESS EACH TRK

    // Do debug output (summary stats for entire hike)
    if ($makeGpsvDebug) { // only if param is set
        fputs(
            $handleDfc,
            sprintf("hikeLgthTot,%.2f mi", $hikeLgthTot / 1609) .
            sprintf(",pmax %.2fm,%.2fft", $pmax, $pmax * 3.28084) .
            sprintf(",pmin:%.2fm,%.2fft", $pmin, $pmin * 3.28084) .
            sprintf(",pup:%.2fm,%.2fft", $pup, $pup * 3.28084) .
            sprintf(",pdwn:%.2fm,%.2fft", $pdwn, $pdwn * 3.28084) .
            PHP_EOL .
            "distThresh:{$distThresh},elevThresh:{$elevThresh}" .
            ",maWindow:{$maWindow}" . PHP_EOL
        );
        fclose($handleDfa);
        fclose($handleDfc);
    }
    /**
     *   ---- ESTABLISH ANY WAYPOINTS IN GPX FILE ----
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
    /**
     *   ---- OPTIONAL PHOTOS ----
     */
    $plnks = [];  // array of photo links
    $defIconColor = 'red';
    $defIconStyle = 'googlemini';
    if (!$tblOnly) {
        // see GPSVisualizer for complete list of icon styles:
        $mapicon = $defIconStyle;
        $mcnt = 0;
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
                // If wypt in E/TSV file....
                if (empty($photos['mid'])) { // waypoint icon
                    // determine icon color and style (clumsy for now, but works)
                    if (empty($photos['iclr'])) {
                        $colortxt = $defIconColor;
                        $iconStyle = $defIconStyle;
                    } else {
                        $colortxt = $iconColor;
                        $iconStyle = $iconColor;
                    }
                    if (strpos($colortxt, "Blue")
                        || strpos($colortxt, "Trail Head")
                    ) {
                        $iconColor = 'Blue';
                    } elseif (strpos($iconColor, "Green")) {
                        $iconColor = 'Green';
                    } else {
                        $iconColor = 'Red';
                    }
                    $plnk = "GV_Draw_Marker({lat:" . $photos['lat']/LOC_SCALE .
                        ",lon:" . $photos['lng']/LOC_SCALE . ",name:'" .
                        $procName . "',desc:'" . $procDesc . "',color:'" .
                        $iconColor . "',icon:'" . $iconStyle . "'});";
                } else { // photo
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
                }
                array_push($plnks, $plnk);
                $mcnt++;
            }
        }
    }
    $noOfTrks += $noOfFileTrks;
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
