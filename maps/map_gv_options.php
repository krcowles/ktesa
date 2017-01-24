gv_options.zoom = <?php if (isset($_GET[zoom])) {echo $_GET[zoom];} else {echo 5;}?>;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
gv_options.map_type = <?php if (isset($_GET[map_type_url])) {echo "'"; echo $_GET[map_type_url]; echo "'";} else {echo "'GV_STREET'";}?>; // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_TOPO_US', 'GV_TOPO_WORLD', 'GV_OSM' (http://www.gpsvisualizer.com/misc/google_map_types.html)
gv_options.zoom_control = <?php if (isset($_GET[zoom_control_url])) {echo "'"; echo $_GET[zoom_control_url]; echo "'";} else {echo "'none'";}?>;  // 'large'|'small'|'none'
gv_options.map_type_control.style = <?php if (isset($_GET[map_type_control_url])) {echo "'"; echo $_GET[map_type_control_url]; echo "'";} else {echo "'none'";}?>;  // 'menu'|'none'
gv_options.center_coordinates = <?php if (isset($_GET[center_coordinates])) {echo $_GET[center_coordinates];} else {echo "false";}?>;  // true|false: show a "center coordinates" box and crosshair?
gv_options.utilities_menu = <?php if (isset($_GET[utilities_menu])) {echo $_GET[utilities_menu];} else {echo "false";}?>;  // true|false
gv_options.measurement_tools = <?php if (isset($_GET[measurement_tools])) {echo $_GET[measurement_tools];} else {echo "false";}?>;  // true|false|'separate' ('separate' to put a ruler outside the utilities menu)
gv_options.tracklist_options.enabled = <?php if (isset($_GET[tracklist_options_enabled])) {echo $_GET[tracklist_options_enabled];} else {echo "false";}?>;  // true|false: enable or disable the tracklist altogether
gv_options.marker_list_options.enabled = <?php if (isset($_GET[marker_list_options_enabled])) {echo $_GET[marker_list_options_enabled];} else {echo "false";}?>;  // true|false: enable or disable the marker list altogether

			gv_options.zoom = 14;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements

