<?php
    /*
     * There are two cases to consider:
     *  1. Map is in an iframe: on hike page or during page creation; 
     *      a tmp map file is created by either hikePageTemplate.php or by
     *      displayHikePg.php and stored in maps/tmp/;
     *  2. Full-page map needs standalone link and assumes no prev. stored files:
     *      for this case: map_name="MapLink";
     */
    $dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
    $map = filter_input(INPUT_GET,'map_name');
    if ($map == 'MapLink') {
        /* 
         * This is a full-page map link: the following parameters need to be
         * established prior to 'including' the makeGpsv.php script:
         *    - $hikeIndexNo (index no for SELECT in mysql database)
         *    - $hikeTitle  (hike name, placed in map)
         *    - $gpxPath    (gpx file path to create the map track)
         */
        $hikeIndexNo = filter_input(INPUT_GET,'hno');
        $hikeTitle = filter_input(INPUT_GET,'hike');
        $gpxPath = filter_input(INPUT_GET,'gpx');
		if ($ehikes) {
		    $ttable = 'ETSV';
		} else {
		    $ttable = 'TSV';
		}
        include '../php/makeGpsv.php';
        $lines = explode("\n",$html); # $html comes in as a string
        foreach ($lines as &$dat) {
            $dat .= "\n"; # $lines array uses 'file' which retains newline
        }
    } else {
        if ( !file_exists($map) ) {
            $msgout = '<p style="color:red;font-size:18px;margin-left:12px;margin-top:10px;>'
                    . 'MAP FAILURE: Could not locate the indicated map: ' . $map . "</p>";
            echo $msgout;
        } else {
            $lines = file($map);
        }
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
