<?php
/**
 * This code is used for both makeGpsv.php (single gpx file) and
 * multiMap.php (multiple gpx files from Table Only). It fills in
 * the GPSV Template with the extracted data to create the map.
 * Expected pre-defined units:
 *  $hikeTitle Map name
 *  $clat      latitude of map center
 *  $clon      longitude of map center
 *  $map_opts  set of options for map creation
 *  $noOfTrks  total tracks to be displayed
 * 
 * PHP Version 7.3
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None at this time
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
if (isset($showPhotos) && $showPhotos) {
    $maphtml .= PHP_EOL . "                // Create photo markers\n";
    for ($z=0; $z<count($plnks); $z++) {
        $maphtml .= '                ' . $plnks[$z] . PHP_EOL;
    }
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
