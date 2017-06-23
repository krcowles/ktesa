<?php
    /*
     * There are three cases to consider:
     *  1. Map is in iframe on hike page; temp map file is used, created by hike pg.
     *  2. Map is still in build/tmp directory during page-creation, re-define path
     *  3. Full-page map needs standalone link with no prev. stored files
     */
    $map = filter_input(INPUT_GET,'map_name');
    if ( !file_exists($map) ) {
        # Map is not an iframe map (case 1.); try case 2:
        $map = '../build/tmp/maps/' . $map;
        if ( !file_exists($map) ) { 
            # Not case 2: Map is not in page-creation tmp directory, assume full pg link:
            # Using GET params instead of calling in db and searching for hike (for now)
            $hikeTitle = filter_input(INPUT_GET,'hike');
            $gpsvFile = filter_input(INPUT_GET,'gpsv');
            $gpxfile = filter_input(INPUT_GET,'gpx');
            include '../php/makeGpsv.php';
            $lines = explode("\n",$html); # $html comes in as a string
            foreach ($lines as &$dat) {
                $dat .= "\n"; # $lines array uses 'file' which retains newline
            }
        } else {
            $lines = file($map);  # Case 2;
        }
    } else {
        $lines = file($map);  # Case 1;
    }

    # Map option-setting:
    for($i = 0; $i < count($lines); ++$i) {
        // if (strpos($lines[$i], "GV_Draw_Marker") === false || ($_GET[show_markers_url] === true)) { // suppress markers? 
        if (strpos($lines[$i], "GV_Draw_Marker") === false) {  
                echo ($lines[$i]);
        }
        elseif ($_GET[show_markers_url] == true) {
                echo ($lines[$i]);		// suppress markers per url param
        }
        if (strpos($lines[$i], "Although GPS Visualizer didn't create") !== false) {
            // insert geolocation code:
            if (isset($_GET[show_geoloc]) == true && $_GET[show_geoloc] == "true"){
                echo "<p><a href='javascript:GV_Geolocate({marker:true,info_window:true})' style='font-size:12px'>Geolocate me!</a></p>";
            }
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
