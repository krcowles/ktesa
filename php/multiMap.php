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
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 */
require_once "../php/global_boot.php";
//require_once "../php/gpxFunctions.php";

// detect if script called as window (Table Only page)
$tblOnly = isset($hikeIndexNo) ? false : true;

// tblOnly files are input via query string, $map_opts are included below:
if ($tblOnly) {
    $geoloc = "../../images/geoloc.png";
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
$hikeFiles = $files;
// data for map
$allLats     = [];
$allLngs     = [];
$defClrs     = array('red','blue','green','fuchsia','pink','orange','black');
$colorIndx   = 0;
$noOfTrks    = 0;  // sequentially increasing with each file
$GPSV_Tracks = [];
$waypoints   = [];
$trackTicks  = []; // each member is an array of ticks for a track

// Create GPSV <script> to append to HTML of map
$mapdata = true;
foreach ($files as $fileno) {
    $tracksReq = "SELECT MAX(trackno) FROM `GPX` WHERE `fileno` = ?;";
    $tracks = $gdb->prepare($tracksReq);
    $tracks->execute([$fileno]);
    $trackmax = $tracks->fetch(PDO::FETCH_NUM);
    $trackcnt = $trackmax[0];
    for ($k=1; $k<=$trackcnt; $k++) { // PROCESS EACH TRK
        $ticks = []; // one set of ticks per track
        $trkNo = $noOfTrks + $k;
        $gpsvTrack = "SELECT `trkname` FROM `META` WHERE `fileno`=? AND " .
            "`trkno`=?;";
        $gpsvtrk = $gdb->prepare($gpsvTrack);
        $gpsvtrk->execute([$fileno, $k]);
        $gpsvname = $gpsvtrk->fetch(PDO::FETCH_NUM);
        $trkname = $gpsvname[0];
        $line = "                t = " . $trkNo . "; trk[t] = " .
            "{info:[],segments:[]};\n";
        $line .= "                trk[t].info.name = '" . $trkname .
            "'; trk[t].info.desc = ''; trk[t].info.clickable = true;\n";
        $line .= "                trk[t].info.color = '" .
            $defClrs[$colorIndx] . "'; trk[t].info." .
            "width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;\n";
        $line .= "                trk[t].info.outline_color = " .
            "'black'; trk[t].info." .
            "outline_width = 0; trk[t].info.fill_color = '" .
            $defClrs[$colorIndx++] . "'; trk[t].info.fill_opacity = 0;\n";
        $tdat = "                trk[t].segments.push({ points:[ [";
        include "../php/getTrackData.php";  // extract from gpx database 
        $allLats = array_merge($allLats, $latdat);
        $allLngs = array_merge($allLngs, $lngdat);
        $tickMrk = 0.3 * 1609;
        for ($m=1; $m<count($latdat); $m++) {
            $hikeLgth = (float) 0;
            $d_and_r = distance(
                floatval($latdat[$m-1]), floatval($lngdat[$m-1]), 
                floatval($latdat[$m]), floatval($lngdat[$m])
            );
            $hikeLgth += ($d_and_r[0] * 3.2808)/5280;
            $rotation = $d_and_r[1];
            // Form GPSV javascript track and tickmark data for this trkpt
            $tdat .= $latdat[$m] . "," . $lngdat[$m] . "],[";
            if ($hikeLgth > $tickMrk) {
                $tick
                    = "GV_Draw_Marker({lat:" . $gpxlats[$m] . ",lon:" . $gpxlons[$m]
                        . ",alt:" . $gpxeles[$m] . ",name:'" . $tickMrk/1609 . " mi'"
                        . ",desc:trk[" . $seqTrkNo . "].info.name,color:trk["
                        . $seqTrkNo . "]"
                        . ".info.color,icon:'tickmark',type:'tickmark',folder:'"
                        . $trkname . " [tickmarks]',rotation:" . $rotation
                        . ",track_number:" . $seqTrkNo . ",dd:false});";
                array_push($ticks, $tick);
                $tickMrk += 0.30 * 1609; // interval in miles converted to meters
            }
        }
        $tdat = substr($tdat, 0, strlen($tdat)-2);
        $line .= $tdat . " ] });\n";
        $line .= "                GV_Draw_Track(t);\n";
        array_push($GPSV_Tracks, $line);
    }
    $noOfTrks = $trkNo;
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
    $getWaypts = "SELECT * FROM `WAYPTS` WHERE `fileno`=?;";
    $wpts = $gdb->prepare($getWaypts);
    $wpts->execute([$fileno]);
    $gpsvWaypts = $wpts->fetchAll(PDO::FETCH_ASSOC);
    $noOfWaypts = count($gpsvWaypts);
    if ($noOfWaypts > 0) {
        foreach ($gpsvWaypts as $waypt) {
            $wlat = $waypt['lat'];
            $wlng = $waypt['lon'];
            $sym  = $waypt['sym'];
            $text = str_replace("'", "\'", $waypt['name']);
            //$desc = str_replace("'", "\'", $waypt->desc);
            $desc = "";
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
