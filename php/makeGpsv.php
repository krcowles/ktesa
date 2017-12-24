<?php
/**
 * This module constructs the html for a GPSVisualizer map. It imports
 * the gpx file, extracts latitude and longitude, calculates distances
 * between points and imports all data, incluing any waypoints or
 * popup photos. This data is formatted according to the GPSVisualizer
 * map construct and stored in the output variable '$html'. 
 * Variables expected to be defined prior to invocation: 
 * string: $gpxPath, relative url to the gpx file;
 * integer: $hikeNo, unique hike id
 * 
 * @package GPSV_Mapping
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
 * @link    ../php
 */
/**
 * The connectToDb function determines which host is active and establishes
 * a connection to the MySQL database for each case. In additional, some
 * general purpose global functions provide insert/update functionality for
 * the database so as to preserve null fields.
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
/**
 * Function to calculate the distance between two lat/lng coordinates.
 * In addition, the 'rotation' angle is calculated which provides the correct
 * oritentation on the page for key elements, like tick marks on the track.
 * 
 * @param float $lat1 starting latitude
 * @param float $lon1 starting longitude
 * @param float $lat2 ending latitude
 * @param float $lon2 ending longitude
 * 
 * @return array
 */
function distance($lat1, $lon1, $lat2, $lon2)
{
    if ($lat1 === $lat2 && $lon1 === $lon2) {
        return array (0,0);
    }
    $radlat1 = deg2rad($lat1);
    $radlat2 = deg2rad($lat2);
    $theta = $lon1 - $lon2;
    $dist = sin($radlat1) * sin($radlat2) +  cos($radlat1) *
        cos($radlat2) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    if (is_nan($miles)) {
        $err = $lat1 . ',' . $lon1 . '; ' . $lat2 . ',' . $lon2;
        echo $GLOBALS['intro'] .
            "Mdl: makeGpsv.php/function distance() - Not a number: " . $err . "</p>";
    }
    // angles using planar coords: ASSUME a minute/seconds in lat/lng spec
    $dely = $lat2 - $lat1;
    $delx = $lon2 - $lon1;
    $radang = atan2($dely, $delx);
    $angle = rad2deg($radang);
    // Convert Euclid Angle to GPSV Rotation
    if ($dely >= 0) {
        if ($delx >= 0) {
            $rotation = 90.0 - $angle;  // Northeast
        } else {
            $rotation = 450.0 - $angle; // Northwest
        }
    } else {
        $rotation = 90.0 + -$angle;     // South
    }
    $rotation = round($rotation);
    return array ($miles,$rotation);
}
// Error message data
$intro = '<p style="color:red;left-margin:12px;font-size:18px;">';
$close = '</p>';
$gpxmsg = $intro . 'Mdl: makeGpsv.php - Could not parse XML in gpx file: ';
/**
 * GPX FILE NOTES:
 * 1. Metadata in GPX files can vary considerably, including defined namespaces.
 *    In some cases (e.g. Garmin), the user may specify track colors. However,
 *    since these are not necessarily compatible with the GPSV map utility, all
 *    files will use the default track colors identified below. Other items,
 *    such as <author> may or may not exist, so this script will attempt to 
 *    minimize the use of supplied metadata.
 * 2. Any given track may have one or more track segments. Track segments
 *    are not independently processed here, but are considered inseparable 
 *    parts of a track, and hence all trkpts together (from all segments in 
 *    the track) will be blended into  the corresponding 'parent' track. 
 *    However, there certainly can be more than 1 track per file. 
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
    die($gpxmsg . $filemsg . $close);
}
/**
 * $defClrs is an array which identifies, sequentially, the color to be associated
 * with each unique track. If the number of tracks exceeds the array size, the
 * colors will begin again at the first array element and repeat.
 */
$defClrs = array('red','blue','fuchsia','yellow','green','black','aqua','pink');
$noOfTrks = $gpxdat->trk->count();
// assign colors:
for ($i=0; $i<$noOfTrks; $i++) {
    // use rolling indx, in case more tracks than defaults
    $circ = $i % $noOfTrks;
    $colors[$i] = $defClrs[$circ];
}
$GPSV_Tracks = [];
$ticks = [];
// PROCESS EACH TRACK:
for ($k=0; $k<$noOfTrks; $k++) {
    $gpxlats = [];
    $gpxlons = [];
    $plat = 0;
    $plng = 0;
    $tno = $k + 1;
    // Form javascript to draw each track:
    $line = "                t = " . $tno . "; trk[t] = {info:[],segments:[]};\n";
    $line .= "                trk[t].info.name = '" . $gpxdat->trk[$k]->name .
        "'; trk[t].info.desc = ''; trk[t].info.clickable = true;\n";
    $line .= "                trk[t].info.color = '" .
        $colors[$k] . "'; trk[t].info." .
        "width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;\n";
    $line .= "                trk[t].info.outline_color = 'black'; trk[t].info." .
        "outline_width = 0; trk[t].info.fill_color = '" . $colors[$k] .
        "'; trk[t].info.fill_opacity = 0;\n";
    $tdat = "                trk[t].segments.push({ points:[ [";
    // Each track will have separate tick mark sets
    $hikeLgth = 0;
    $tickMrk = 0.30;
    $indx = 0;
    $noOfSegs = $gpxdat->trk[$k]->trkseg->count();
    for ($j=0; $j<$noOfSegs; $j++) {
        foreach ($gpxdat->trk[$k]->trkseg[$j]->trkpt as $datum) {
            if (!($datum['lat'] == $plat && $datum['lon'] == $plng)) {
                $plat = (float)$datum['lat'];
                $plng = (float)$datum['lon'];
                array_push($gpxlats, $plat);
                array_push($gpxlons, $plng);
                $tdat .= $plat . "," . $plng . "],[";
                if ($indx > 0) {
                    $parms = distance(
                        $gpxlats[$indx-1], $gpxlons[$indx-1], $gpxlats[$indx], 
                        $gpxlons[$indx]
                    );
                    $hikeLgth += $parms[0];
                    if ($hikeLgth > $tickMrk) {
                        $tick = "GV_Draw_Marker({lat:" . $plat . ",lon:" . $plng .
                            ",name:'" . $tickMrk . " mi',desc:'',color:trk[" . $tno .
                            "].info.color,icon:'tickmark',type:'tickmark',folder:'" .
                            $track->name . " [tickmarks]',rotation:" . $parms[1] .
                            ",track_number:" . $tno . ",dd:false});";
                        array_push($ticks, $tick);
                        $tickMrk += 0.30;
                    }
                    
                } else {
                    $gpxlats[0] = $plat;
                    $gpxlngs[0] = $plng;
                }
                $indx++;
            }
        }  // end of processing trkpts in a segment
    } // end for each segment: next esgment...
    // remove last ",[" and end string:
    $tdat = substr($tdat, 0, strlen($tdat)-2);
    $line .= $tdat . " ] });\n";
    $line .= "                GV_Draw_Track(t);\n";
    $GPSV_Tracks[$k] = $line;
}  // end of for each track
// Calculate map bounds and center coordiantes
$north = $gpxlats[0];
$south = $north;
$east = $gpxlons[0];
$west = $east;
for ($i=1; $i<count($gpxlons)-1; $i++) {
    if ($gpxlats[$i] > $north) {
        $north = $gpxlats[$i];
    }
    if ($i === 55) { // arbitrarily chosen #miles in length, limit
        $msg = "lat: " . $gpxlats[$i] . ', north: ' . $north;
    }
    if ($gpxlats[$i] < $south) {
        $south = $gpxlats[$i];
    }
    if ($gpxlons[$i] < $west) {
        $west = $gpxlons[$i];
    }
    if ($gpxlons[$i] > $east) {
        $east = $gpxlons[$i];
    }
}
$clat = $south + ($north - $south)/2;
$clon = $west + ($east - $west)/2;
/*
 *   ---- ESTABLISH ANY WAYPOINTS ----
 */
$noOfWaypts = $gpxdat->wpt->count();
$waypoints = [];
if ($noOfWaypts > 0) {
    foreach ($gpxdat->wpt as $waypt) {
        $wlat = $waypt['lat'];
        $wlng = $waypt['lon'];
        $sym = $waypt->sym;
        $text = $waypt->name;
        $wlnk = "GV_Draw_Marker({lat:" . $wlat . ",lon:" . $wlng .
            ",name:'" . $text . "',desc:'',color:'" . "blue" .
            "',icon:'" . $sym . "'});\n";
        array_push($waypoints, $wlnk);
    }
}
/*
 *   ---- ESTABLISH PHOTO DATA ----
 * Form the photo links from the mysql database
 */
$plnks = [];  // array of photo links
$defIconColor = 'red';
$mcnt = 0;
$picReq = "SELECT folder,title,mpg,`desc`,lat,lng,alblnk,mid,iclr FROM {$ttable} " .
        "WHERE indxNo = {$hikeIndexNo};";
$pic = mysqli_query($link, $picReq);
if (!$pic) {
    die(
        "<p>makeGpsv.php: Failed to extract photo data for hike {$hikeIndexNo}: " .
        mysqli_error($link)
    );
}
while (($photos = mysqli_fetch_assoc($pic))) {
    if ($photos['mpg'] === 'Y') {
        $procName = preg_replace("/'/", "\'", $photos['title']);
        $procName = preg_replace('/"/', '\"', $procName);
        $procDesc = preg_replace("/'/", "\'", $photos['desc']);
        $procDesc = preg_replace('/"/', '\"', $procDesc);
        // If wypt in ETSV file....
        if ($photos['alblnk'] == '') { // waypoint icon
            $plnk = "GV_Draw_Marker({lat:" . $photos['lat'] . ",lon:" .
                $photos['lng']. ",name:'" . $procName . "',desc:'" .
                $procDesc . "',color:'" . $photos['iclr'] . "',icon:''});";
        } else { // photo
            $plnk = "GV_Draw_Marker({lat:" . $photos['lat'] . ",lon:" .
                $photos['lng'] . ",name:'" . $procDesc .
                "',desc:'',color:'" . $photos['iclr'] . "',icon:'" .
                $mapicon . "',url:'" . $photos['alblnk'] . "',thumbnail:'" .
                $photos['mid'] . "',folder:'" . $photos['folder'] . "'});";
        }
        array_push($plnks, $plnk);
        $mcnt++;
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
            $maphtml .= "                <p><a href='javascript:GV_Geolocate(" .
                "{marker:true,info_window:true})' style='font-size:12px'>" .
                "Geolocate me!</a></p>" . PHP_EOL;
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
        "            var chartMrkr;" . PHP_EOL .
        "            var imageLoc = '../images/azureMrkr.ico';" . PHP_EOL;
    $maphtml .= '            function drawMarker( mrkrLoc ) {' . PHP_EOL .
        '                chartMrkr = new google.maps.Marker({' . PHP_EOL .
        '                    position: mrkrLoc,' . PHP_EOL .
        '                    map: gmap' . PHP_EOL .
        '                });' . PHP_EOL .
        '                mrkrSet = true;' . PHP_EOL . 
        '            }' . PHP_EOL;
    $maphtml .= "            // create context for passing " .
        "iframe variables to parent" . PHP_EOL . 
        "            setTimeout( function() {" . PHP_EOL .
        "                parent.iframeWindow = window;" . PHP_EOL .
        "             }, 2000 );" . PHP_EOL;
}
$maphtml .= '</script>' . PHP_EOL;
$maphtml .= '</body>' . PHP_EOL;
$maphtml .= '</html>' . PHP_EOL;  
?>
