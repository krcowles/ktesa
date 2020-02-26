<?php
/**
 * This module constructs the html for a GPSVisualizer map. It imports
 * the gpx file, extracts latitude and longitude, calculates distances
 * between points and imports all data, incluing any waypoints or
 * popup photos. This data is formatted according to the GPSVisualizer
 * map construct and stored in the output variable '$html'. 
 * Variables expected to be defined prior to invocation: 
 *    string  $gpxPath, relative url to the gpx file;
 *    integer $hikeNo, unique hike id
 * PHP Version 7.0
 * 
 * @package GPSV_Mapping
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 */
require_once "gpxFunctions.php"; // no db connections established therein
// Error messaging
$intro = '<p style="color:red;left-margin:12px;font-size:18px;">';
$close = '</p>';
$gpxmsg = $intro . 'Mdl: makeGpsv.php - Could not parse XML in gpx file: ';
/**
 * GPX FILE NOTES:
 * 1. Metadata in GPX files can vary considerably, including defined namespaces.
 *    In some cases (e.g. Garmin), the user may specify track colors. However,
 *    since these are not necessarily compatible with the GPSV map utility, all
 *    files will use the default track colors identified below.
 * 2. Any given track may have one or more track segments. Track segments
 *    are not independently processed here, but are considered inseparable 
 *    parts of a track, and hence all trkpts together (from all segments in 
 *    the track) will be blended into  the corresponding 'parent' track. 
 *    Each track, regardless of number of segments, will have a unique set of 
 *    GPSV data. Each track will be separately written out in the GPSV html 
 *    file with a corresponding track name and color.
 *    If a track name is not specified, one will be supplied by default.
 * 3. Waypoints are independent of tracks, as are photos. These two are processed
 *    independently and added to the html output file, where they exist.
 */
$gpxdat = simplexml_load_file($gpxPath);
if ($gpxdat === false) {
    if ($gpxPath == '') {
        $filemsg = "Empty GPX Path String encountered";
    } else {
        $filemsg = $gpxPath;
    }
    throw new Exception($gpxmsg . $filemsg . $close);
}
/**
 * In case the file is constructed using 'rtept' tags instead of
 * 'trkpt' tags, convert to trkpts:
 */
if ($gpxdat->rte->count() > 0) {
    $gpxdat = convertRtePts($gpxdat);
}
/**
 * $defClrs is an array which identifies, sequentially, the color to be associated
 * with each unique track. If the number of tracks exceeds the array size, the
 * colors will begin again at the first array element and repeat.
 */
$defClrs = array('red','blue','aqua','green','fuchsia','pink','orange','black');
$noOfTrks = $gpxdat->trk->count();

// assign colors:
for ($i=0; $i<$noOfTrks; $i++) {
    // use rolling indx, in case more tracks than defaults
    $circ = $i % $noOfTrks;
    $colors[$i] = $defClrs[$circ];
}
// For determining map bounds only (may include multiple tracks)
$allLats = [];
$allLngs = [];
// GPSV javascript data
$GPSV_Tracks = [];
$ticks = [];

/**
 * Set smoothing parameter values per the following hierarchy:
 *  URL param established in hikePageData.php,
 *  hike-specific value from database,
 *  default value defined here.
*/
if (isset($elevThreshParm)) { // threshold (meters) for elevation smoothing
    $elevThresh = $elevThreshParm;
} else {
    $elevThresh = isset($hikeEThresh) ? $hikeEThresh : 1;
}
if (isset($distThreshParm)) { // threshold (meters) for distance smoothing
    $distThresh = $distThreshParm;
} else {
    $distThresh = isset($hikeDThresh) ? $hikeDThresh : 1;
}
if (isset($maWindowParm)) { // moving average window size for elevation smoothing
    $maWindow = $maWindowParm;
} else {
    $maWindow = isset($hikeMaWin) ? $hikeMaWin : 1;
}

// Set debug output parameter based on the URL param established in hikePageData.php
if (isset($makeGpsvDebugParm)) {
    $makeGpsvDebug = "true" ? true : false;
} else {
    $makeGpsvDebug = false;
}

// Open debug files with headers, if requested by query string
$handleDfa = null;
$handleDfc = null;
if ($makeGpsvDebug) {
    $handleDfa = gpsvDebugFileArray($gpxPath);
    $handleDfc = gpsvDebugComputeArray($gpxPath);
}
// calculated stats for all tracks:
$pup = (float)0;
$pdwn = (float)0;
$pmax = (float)0;
$pmin = (float)50000;
$hikeLgthTot = (float)0;

// Iterate through all tracks in the gpx file
for ($k=0; $k<$noOfTrks; $k++) { // PROCESS EACH TRK
    // html track no
    $tno = $k + 1;
    /**
    * We're building a tick-delimited string to pass to GPSV, and since ticks
    * can appear in the track name from the gpx file,
    * we use the following line to escape them out.
    */
    $trkname = str_replace("'", "\'", $gpxdat->trk[$k]->name);

    // Form javascript to draw each track:
    $line = "                t = " . $tno . "; trk[t] = {info:[],segments:[]};\n";
    $line .= "                trk[t].info.name = '" . $trkname .
        "'; trk[t].info.desc = ''; trk[t].info.clickable = true;\n";
    $line .= "                trk[t].info.color = '" .
        $colors[$k] . "'; trk[t].info." .
        "width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;\n";
    $line .= "                trk[t].info.outline_color = 'black'; trk[t].info." .
        "outline_width = 0; trk[t].info.fill_color = '" . $colors[$k] .
        "'; trk[t].info.fill_opacity = 0;\n";
    $tdat = "                trk[t].segments.push({ points:[ [";

    /**
     * Get gpx data into individual arrays and do first level
     * processing. Once per track...
     */
    $calcs = getTrackDistAndElev(
        $k, $trkname, $gpxPath, $gpxdat, $makeGpsvDebug, $handleDfa,
        $handleDfc, $distThresh, $elevThresh, $maWindow, $tdat,
        $ticks
    );
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
    $GPSV_Tracks[$k] = $line;
}  // end for: PROCESS EACH TRK

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

// Calculate map bounds and center coordiantes
$north = $allLats[0];
$south = $north;
$east = $allLngs[0];
$west = $east;
for ($i=1; $i<count($allLngs)-1; $i++) {
    if ($allLats[$i] > $north) {
        $north = $allLats[$i];
    }
    if ($i === 55) { // arbitrarily chosen #miles in length as a limit
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
/*
 *   ---- ESTABLISH ANY WAYPOINTS IN gpx FILE ----
 */
$noOfWaypts = $gpxdat->wpt->count();
$waypoints = [];
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
/*
 *   ---- ESTABLISH PHOTO DATA ----
 * Form the photo links from the mysql database
 */
if ((isset($map_type) && $map_type === 'page') || !isset($map_type)) {
    $showPhotos = true;
} else {
    $showPhotos = false;
}
$plnks = [];  // array of photo links
$defIconColor = 'red';
$defIconStyle = 'googlemini';
if ($showPhotos) {
    // see GPSVisualizer for complete list of icon styles:
    $mapicon = $defIconStyle;
    $mcnt = 0;
    $picReq = "SELECT folder,title,mpg,`desc`,lat,lng,thumb,alblnk,mid,iclr FROM "
        . "{$ttable} WHERE indxNo = :hikeIndexNo;";
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
                if (strpos($colortxt, "Blue") || strpos($colortxt, "Trail Head")) {
                    $iconColor = 'Blue';
                } elseif (strpos($iconColor, "Green")) {
                    $iconColor = 'Green';
                } else {
                    $iconColor = 'Red';
                }
                $plnk = "GV_Draw_Marker({lat:" . $photos['lat']/LOC_SCALE . ",lon:" .
                    $photos['lng']/LOC_SCALE . ",name:'" . $procName . "',desc:'" .
                    $procDesc . "',color:'" . $iconColor . 
                        "',icon:'" . $iconStyle . "'});";
            } else { // photo
                $plnk = "GV_Draw_Marker({lat:" . $photos['lat']/LOC_SCALE . ",lon:" .
                    $photos['lng']/LOC_SCALE . ",name:'" . $procDesc .
                    "',desc:'',color:'" . $iconColor . "',icon:'" . $mapicon .
                    "',url:'/pictures/zsize/" . $photos['mid'] . "_" . 
                    $photos['thumb'] . "_z.jpg" . "',thumbnail:'/pictures/nsize/" . 
                    $photos['mid'] . "_" . $photos['thumb'] . "_n.jpg" .
                    "',folder:'" . $photos['folder'] . "'});";
            }
            array_push($plnks, $plnk);
            $mcnt++;
        }
    }
}
/*
 * This section picks up the GPSV map template and provides title and data options
 */
$template = "../php/GPSV_Template.html";
$gpsv = file($template, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$maphtml = '';
foreach ($gpsv as $line) {
    if (strpos($line, "<title>") > 0) {
        $maphtml .= '        <title>' . $hikeTitle . '</title>' . PHP_EOL;
    } elseif (strpos($line, '<meta name="geo.postion"')) {
        $maphtml .= '        <meta name="geo.position" content="' . $clat .
            ', ' . $clon . '" />' . PHP_EOL;
    } elseif (strpos($line, '<meta name="ICBM"')) {
        $maphtml .= '        <meta name="ICBM" content="' . $clat . ', ' .
            $clon . '" />' . PHP_EOL;
    } elseif (strpos($line, "Although GPS Visualizer didn")) {
        $maphtml .= $line . PHP_EOL;
        if ($map_opts['show_geoloc'] === 'true') {
            $maphtml .= "                <a href='javascript:GV_Geolocate(" .
                "{marker:true,info_window:true})' target='_self' " .
                "style='position:absolute;top:20px;left:42px;z-index:500;'>" .
                "<img src='../../images/geoloc.png' /></a>" . PHP_EOL;
        }
    } elseif (strpos($line, "gv_options.center =")) {
        $maphtml .= '            gv_options.center = [' . $clat . ',' . $clon .
            '];  // [latitude,longitude] - be sure to keep the square brackets' .
            PHP_EOL;
    } elseif (strpos($line, "gv_options.zoom =")) {
        $maphtml .= "            gv_options.zoom = '{$map_opts['zoom']}';  " .
            "// higher number means closer view; can also be 'auto' for automatic " .
            "zoom/center based on map elements" . PHP_EOL;
    } elseif (strpos($line, "gv_options.map_type =")) {
        $maphtml .= "            gv_options.map_type = '{$map_opts['map_type']}'; " .
            "// popular map_type choices are 'GV_STREET', 'GV_SATELLITE', " .
            "'GV_HYBRID', 'GV_TERRAIN'" . PHP_EOL;
    } elseif (strpos($line, "gv_options.street_view ")) {
        $maphtml .= "            gv_options.street_view = " .
            "{$map_opts['street_view']}; // true|false: allow Google Street " .
            "View on the map";
    } elseif (strpos($line, "gv_options.zoom_control =")) {
        $maphtml .= "            gv_options.zoom_control = " .
            "'{$map_opts['zoom_control']}'; // 'large'|'small'|'none'" . PHP_EOL;
    } elseif (strpos($line, "gv_options.map_type_control.excluded =")) {
        $maphtml .= $line . PHP_EOL;
        $maphtml .= "            gv_options.map_type_control.style = " .
            "'{$map_opts['map_type_control']}'; // 'menu'|'none'" . PHP_EOL;
    } elseif (strpos($line, "gv_options.center_coordinates =")) {
        $maphtml .= "            gv_options.center_coordinates = " .
            "{$map_opts['center_coordinates']}  // true|false: " .
            'show a "center coordinates" box and crosshair?' . PHP_EOL;
    } elseif (strpos($line, "gv_options.measurement_tools =")) {
        $maphtml .= "            gv_options.measurement_tools = " .
            "{$map_opts['measurement_tools']}" . PHP_EOL;
    } elseif (strpos($line, "gv_options.utilities_menu =")) {
        $maphtml .= "            gv_options.utilities_menu = " .
            "{$map_opts['utilities_menu']}" . PHP_EOL;
    } elseif (strpos($line, "gv_options.tracklist_options.enabled =")) {
        $maphtml .= "              gv_options.tracklist_options.enabled = " .
            "{$map_opts['tracklist_options']} // true|false: enable or " .
            "disable the tracklist altogether" . PHP_EOL;
    } elseif (strpos($line, "gv_options.marker_list_options.enabled =")) {
        $maphtml .= "              gv_options.marker_list_options.enabled = " .
            "{$map_opts['marker_list_options']}; // true|false: enable or " .
            "disable the marker list altogether" . PHP_EOL;
    } elseif (strpos($line, "GV_Setup_Map();")) {
        $maphtml .= $line . PHP_EOL . PHP_EOL;
        break; // This is the point at which unique track data is required
    } else {
        $maphtml .= $line . PHP_EOL;
    }
}
// Add end-of-file unique data
for ($i=0; $i<$noOfTrks; $i++) {
    $maphtml .= "                // Track #" . ($i+1) . PHP_EOL;
    $maphtml .= $GPSV_Tracks[$i];
}
$maphtml .= "                // List the tracks" . PHP_EOL;
for ($j=1; $j<=$noOfTrks; $j++) {
    $maphtml .= "                t = " . $j .
        "; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:" .
        "trk[t].info.desc,color:trk[t].info.color,number:t});" . PHP_EOL;
}
$maphtml .= PHP_EOL . "                // Add tick marks" . PHP_EOL;
for ($j=0; $j<count($ticks); $j++) {
    $maphtml .= '                ' . $ticks[$j] . PHP_EOL;
}
$maphtml .= PHP_EOL . "                // Add any waypoints" . PHP_EOL;
for ($n=0; $n<$noOfWaypts; $n++) {
    $maphtml .= '                ' . $waypoints[$n] . PHP_EOL;
}
$maphtml .= PHP_EOL . "                // Create photo markers\n";
for ($z=0; $z<count($plnks); $z++) {
    $maphtml .= '                ' . $plnks[$z] . PHP_EOL;
}
$maphtml .= PHP_EOL . '                GV_Finish_Map();' . PHP_EOL;
$maphtml .= '            }' . PHP_EOL;
$maphtml .= PHP_EOL . '            GV_Map(); // execute the above code' . PHP_EOL;
$maphtml .= '       // http://www.gpsvisualizer.com/map_input?allow_export=1' .
    '&form=google&google_api_key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU' .
    '&google_street_view=1&google_trk_mouseover=1&tickmark_interval=' .
    '.3%20mi&trk_stats=1&units=us&wpt_driving_directions=1&add_elevation=auto' .
    PHP_EOL;    //$maphtml .= $line . PHP_EOL;
if ($map_opts['dynamicMarker'] === 'true') {
    $maphtml .= PHP_EOL . "            var mrkrSet = false;" . PHP_EOL .
        "            var chartMrkr;" . PHP_EOL;
    $maphtml .= '            function drawMarker( mrkrLoc ) {' . PHP_EOL .
        '                chartMrkr = new google.maps.Marker({' . PHP_EOL .
        '                    position: mrkrLoc,' . PHP_EOL .
        '                    map: gmap' . PHP_EOL .
        '                });' . PHP_EOL .
        '                mrkrSet = true;' . PHP_EOL . 
        '            }' . PHP_EOL;
    $maphtml .= "            // detect map is done loading to advise " .
        "parent of iframe" . PHP_EOL . 
        "            google.maps.event.addListenerOnce(gmap, 'idle', " .
        "function(){" . PHP_EOL .
        "                parent.iframeWindow = window;" . PHP_EOL .
        "                mapdone.resolve();" . PHP_EOL .
        "            });" . PHP_EOL;
}
$maphtml .= '</script>' . PHP_EOL;
$maphtml .= '</body>' . PHP_EOL;
$maphtml .= '</html>' . PHP_EOL;  
