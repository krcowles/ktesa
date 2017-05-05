<?php
    # During build process, map won't be in this directory:
    $map = filter_input(INPUT_GET,'map_name');
    if ( !file_exists($map) ) {
        $map = '../build/tmp/maps/' . $map;
    }
    # the above should have located the map...
    if (!file_exists($map)) {
        die("Geomap could not be located");
    }
    $lines = file($map);
    for($i = 0; $i < count($lines); ++$i) {
        // if (strpos($lines[$i], "GV_Draw_Marker") === false || ($_GET[show_markers_url] === true)) { // suppress markers? 
        if (strpos($lines[$i], "GV_Draw_Marker") === false) {  
                echo ($lines[$i]);
        }
        elseif ($_GET[show_markers_url] == true) {
                echo ($lines[$i]);		// suppress markers per url param
        }
        if (strpos($lines[$i], "Although GPS Visualizer didn't create") !== false) {
                include 'map_geoloc.php';		// insert geolocation code
        }
        if (strpos($lines[$i], "this must be loaded AFTER gv_options are set") !== false) {
                include 'map_gv_options.php';	// insert gv_options code
        }
        if (strpos($lines[$i], "GV_Map();") !== false) {
                if ($_GET[dynamicMarker_url] == true) {
                        include 'dynamic_Elev.php';	// insert dynamic marker from elevation chart
                }
        }
    }
?>
