<?php
	$lines = file($_GET[map_name]);
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
	}
?>