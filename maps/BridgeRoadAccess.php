<?php
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html xmlns="https://www.w3.org/1999/xhtml">
	<head>
		<title>BridgeRoad</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<meta name="geo.position" content="35.7507275; -106.6393075" />
		<meta name="ICBM" content="35.7507275, -106.6393075" />
	</head>
	<body style="margin:0px;">
		
		<script type="text/javascript">
			API = 'google'; // can be either 'leaflet' or 'google'
			if (self.API && API.match(/^g/i)) {
				google_api_key = '<?=API_KEY;?>'; // Your project's Google Maps API key goes here (https://code.google.com/apis/console)
				language_code = '';
				document.writeln('<script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=geometry&amp;language='+(self.language_code?self.language_code:'')+'&amp;key='+(self.google_api_key?self.google_api_key:'')+'" type="text/javascript"><'+'/script>');
			} else {
				document.writeln('<link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet" />');
				document.writeln('<script src="https://unpkg.com/leaflet/dist/leaflet.js" type="text/javascript"><'+'/script>');
			}
			thunderforest_api_key = ''; // To display OpenStreetMap tiles from ThunderForest, you need a key (https://www.thunderforest.com/docs/apikeys/)
			ign_api_key = ''; // To display topo tiles from IGN.fr, you need a key (https://api.ign.fr/)
		</script>

		<!--
			If you want to transplant this map into another Web page, by far the best method is to
			simply include it in a IFRAME tag (see https://www.gpsvisualizer.com/faq.html#google_html).
			But, if you must paste the code into another page, be sure to include all of these parts:
			   1. The "div" tags that contain the map and its widgets, below
			   2. Three sections of JavaScript code:
			      a. The API code (from googleapis.com or unpkg.com/leaflet), above
			      b. "gv_options" and the code that calls a .js file on gpsvisualizer.com
			      c. The "GV_Map" function, which contains all the geographic info for the map
		-->
		<div style="margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px;">
			<div id="gmap_div" style="width:100%; height:100%; margin:0px; margin-right:12px; background-color:#f0f0f0; float:left; overflow:hidden;">
				<p style="text-align:center; font:10px Arial;">This map was created using <a target="_blank" href="https://www.gpsvisualizer.com/">GPS Visualizer</a>'s do-it-yourself geographic utilities.<br /><br />Please wait while the map data loads...</p>
			</div>
				
			<div id="gv_infobox" class="gv_infobox" style="font:11px Arial; border:solid #666666 1px; background-color:#ffffff; padding:4px; overflow:auto; display:none; max-width:400px;">
				<!-- Although GPS Visualizer didn't create an legend/info box with your map, you can use this space for something else if you'd like; enable it by setting gv_options.infobox_options.enabled to true -->
			</div>


			<div id="gv_tracklist" class="gv_tracklist" style="font:11px Arial; line-height:11px; background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_marker_list" class="gv_marker_list" style="background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_clear_margins" style="height:0px; clear:both;"><!-- clear the "float" --></div>
		</div>

		
		<!-- begin GPS Visualizer setup script (must come after loading of API code) -->
		<script type="text/javascript">
			/* Global variables used by the GPS Visualizer functions (20230531093258): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [35.7507275,-106.63930755];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 12;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
			gv_options.map_type = 'GV_OSM_RELIEF';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_OSM', 'GV_TOPO_US', 'GV_TOPO_WORLD' (https://www.gpsvisualizer.com/misc/google_map_types.html)
			gv_options.map_opacity = 1.00;  // number from 0 to 1
			gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?
			gv_options.width = 700;  // width of the map, in pixels
			gv_options.height = 700;  // height of the map, in pixels
			
			gv_options.map_div = 'gmap_div';  // the name of the HTML "div" tag containing the map itself; usually 'gmap_div'
			gv_options.doubleclick_zoom = true;  // true|false: zoom in when mouse is double-clicked?
			gv_options.doubleclick_center = true;  // true|false: re-center the map on the point that was double-clicked?
			gv_options.scroll_zoom = true; // true|false; or 'reverse' for down=in and up=out
			gv_options.page_scrolling = true; // true|false; does the map relenquish control of the scroll wheel when embedded in scrollable pages?
			gv_options.autozoom_adjustment = 0; gv_options.autozoom_default = 11;
			gv_options.centering_options = { 'open_info_window':true, 'partial_match':true, 'center_key':'center', 'default_zoom':null } // URL-based centering (e.g., ?center=name_of_marker&zoom=14)
			gv_options.street_view = true; // true|false: allow Google Street View on the map (Google Maps only)
			gv_options.tilt = false; // true|false: allow Google Maps to show 45-degree tilted aerial imagery?
			gv_options.disable_google_pois = false;  // true|false: if you disable clickable POIs on Google Maps, you also lose the labels on parks, airports, etc.
			gv_options.animated_zoom = true; // true|false: only affects Leaflet maps
			
			// widgets on the map:
			gv_options.zoom_control = 'auto'; // 'auto'|'large'|'small'|'none'
			gv_options.recenter_button = true; // true|false: is there a 'click to recenter' button above the zoom control?
			gv_options.geolocation_control = false; // true|false; only works on secure servers
			gv_options.geolocation_options = { center:true, zoom:null, marker:true, info_window:true };
			gv_options.scale_control = true; // true|false
			gv_options.map_opacity_control = false;  // true|false
			gv_options.map_type_control = {};  // widget to change the background map
			  gv_options.map_type_control.visible = 'auto'; // true|false|'auto': is a map type control placed on the map itself?
			  gv_options.map_type_control.filter = false;  // true|false: when map loads, are irrelevant maps ignored?
			  gv_options.map_type_control.excluded = [];  // comma-separated list of quoted map IDs that will never show in the list ('included' also works)
			gv_options.center_coordinates = true;  // true|false: show a "center coordinates" box and crosshair?
			gv_options.measurement_tools = true; // true|false: put a measurement ruler on the map?
			gv_options.measurement_options = { visible:false, distance_color:'', area_color:'' };
			gv_options.crosshair_hidden = true;  // true|false: hide the crosshair initially?
			gv_options.mouse_coordinates = false;  // true|false: show a "mouse coordinates" box?
			gv_options.utilities_menu = { 'maptype':true, 'opacity':true, 'measure':true, 'geolocate':true, 'profile':true, 'export':true };
			gv_options.allow_export = true;  // true|false
			
			gv_options.infobox_options = {}; // options for a floating info box (id="gv_infobox"), which can contain anything
			  gv_options.infobox_options.enabled = true;  // true|false: enable or disable the info box altogether
			  gv_options.infobox_options.position = ['LEFT_TOP',52,4];  // [Google anchor name, relative x, relative y]
			  gv_options.infobox_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.infobox_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			
			// track-related options:
			gv_options.track_optimization = 1; // sets Leaflet's smoothFactor parameter
			gv_options.track_tooltips = true; // true|false: should the name of a track appear on the map when you mouse over the track itself?
			gv_options.tracklist_options = {}; // options for a floating list of the tracks visible on the map
			  gv_options.tracklist_options.enabled = true;  // true|false: enable or disable the tracklist altogether
			  gv_options.tracklist_options.position = ['RIGHT_TOP',4,32];  // [Google anchor name, relative x, relative y]
			  gv_options.tracklist_options.min_width = 100; // minimum width of the tracklist, in pixels
			  gv_options.tracklist_options.max_width = 180; // maximum width of the tracklist, in pixels
			  gv_options.tracklist_options.min_height = 0; // minimum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.max_height = 310; // maximum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.desc = true;  // true|false: should tracks' descriptions be shown in the list
			  gv_options.tracklist_options.toggle = false;  // true|false: should clicking on a track's name turn it on or off?
			  gv_options.tracklist_options.checkboxes = true;  // true|false: should there be a separate icon/checkbox for toggling visibility?
			  gv_options.tracklist_options.zoom_links = true;  // true|false: should each item include a small icon that will zoom to that track?
			  gv_options.tracklist_options.highlighting = true;  // true|false: should the track be highlighted when you mouse over the name in the list?
			  gv_options.tracklist_options.tooltips = false;  // true|false: should the name of the track appear on the map when you mouse over the name in the list?
			  gv_options.tracklist_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.tracklist_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			  gv_options.tracklist_options.header = 'Tracks:'; // HTML code; be sure to put backslashes in front of any single quotes, and don't include any line breaks
			  gv_options.tracklist_options.footer = ''; // HTML code
			gv_options.profile_options = { visible:false, icon:true, units:'us', filled:true, waypoints:true, height:120, width:'100%', y_min:null, y_max:null, gap_between_tracks:false }; // see https://www.gpsvisualizer.com/tutorials/profiles_in_maps.html


			// marker-related options:
			gv_options.default_marker = { color:'red',icon:'googlemini',scale:1 }; // icon can be a URL, but be sure to also include size:[w,h] and optionally anchor:[x,y]
			gv_options.vector_markers = true; // are the icons on the map in embedded SVG format?
			gv_options.marker_tooltips = true; // do the names of the markers show up when you mouse-over them?
			gv_options.marker_shadows = true; // true|false: do the standard markers have "shadows" behind them?
			gv_options.marker_link_target = '_blank'; // the name of the window or frame into which markers' URLs will load
			gv_options.info_window_width = 0;  // in pixels, the width of the markers' pop-up info "bubbles" (can be overridden by 'window_width' in individual markers)
			gv_options.thumbnail_width = 0;  // in pixels, the width of the markers' thumbnails (can be overridden by 'thumbnail_width' in individual markers)
			gv_options.photo_size = [0,0];  // in pixels, the size of the photos in info windows (can be overridden by 'photo_width' or 'photo_size' in individual markers)
			gv_options.hide_labels = false;  // true|false: hide labels when map first loads?
			gv_options.labels_behind_markers = false; // true|false: are the labels behind other markers (true) or in front of them (false)?
			gv_options.label_offset = [0,0];  // [x,y]: shift all markers' labels (positive numbers are right and down)
			gv_options.label_centered = false;  // true|false: center labels with respect to their markers?  (label_left is also a valid option.)
			gv_options.driving_directions = true;  // put a small "driving directions" form in each marker's pop-up window? (override with dd:true or dd:false in a marker's options)
			gv_options.garmin_icon_set = 'gpsmap'; // 'gpsmap' are the small 16x16 icons; change it to '24x24' for larger icons
			gv_options.marker_list_options = {};  // options for a dynamically-created list of markers
			  gv_options.marker_list_options.enabled = false;  // true|false: enable or disable the marker list altogether
			  gv_options.marker_list_options.floating = true;  // is the list a floating box inside the map itself?
			  gv_options.marker_list_options.position = ['RIGHT_BOTTOM',6,38];  // floating list only: position within map
			  gv_options.marker_list_options.min_width = 160; // minimum width, in pixels, of the floating list
			  gv_options.marker_list_options.max_width = 160;  // maximum width
			  gv_options.marker_list_options.min_height = 0;  // minimum height, in pixels, of the floating list
			  gv_options.marker_list_options.max_height = 310;  // maximum height
			  gv_options.marker_list_options.draggable = true;  // true|false, floating list only: can it be moved around the screen?
			  gv_options.marker_list_options.collapsible = true;  // true|false, floating list only: can it be collapsed by double-clicking its top bar?
			  gv_options.marker_list_options.include_tickmarks = false;  // true|false: are distance/time tickmarks included in the list?
			  gv_options.marker_list_options.include_trackpoints = false;  // true|false: are "trackpoint" markers included in the list?
			  gv_options.marker_list_options.dividers = false;  // true|false: will a thin line be drawn between each item in the list?
			  gv_options.marker_list_options.desc = false;  // true|false: will the markers' descriptions be shown below their names in the list?
			  gv_options.marker_list_options.icons = true;  // true|false: should the markers' icons appear to the left of their names in the list?
			  gv_options.marker_list_options.thumbnails = false;  // true|false: should markers' thumbnails be shown in the list?
			  gv_options.marker_list_options.folders_collapsed = false;  // true|false: do folders in the list start out in a collapsed state?
			  gv_options.marker_list_options.folders_hidden = false;  // true|false: do folders in the list start out in a hidden state?
			  gv_options.marker_list_options.collapsed_folders = []; // an array of folder names
			  gv_options.marker_list_options.hidden_folders = []; // an array of folder names
			  gv_options.marker_list_options.count_folder_items = false;  // true|false: list the number of items in each folder?
			  gv_options.marker_list_options.folder_zoom = true;  // true|false: is there a zoom link next to each folder name?
			  gv_options.marker_list_options.wrap_names = true;  // true|false: should marker's names be allowed to wrap onto more than one line?
			  gv_options.marker_list_options.unnamed = '[unnamed]';  // what 'name' should be assigned to  unnamed markers in the list?
			  gv_options.marker_list_options.colors = false;  // true|false: should the names/descs of the points in the list be colorized the same as their markers?
			  gv_options.marker_list_options.default_color = '';  // default HTML color code for the names/descs in the list
			  gv_options.marker_list_options.limit = 0;  // how many markers to show in the list; 0 for no limit
			  gv_options.marker_list_options.center = false;  // true|false: does the map center upon a marker when you click its name in the list?
			  gv_options.marker_list_options.zoom = false;  // true|false: does the map zoom to a certain level when you click on a marker's name in the list?
			  gv_options.marker_list_options.zoom_level = 15;  // if 'zoom' is true, what level should the map zoom to?
			  gv_options.marker_list_options.info_window = true;  // true|false: do info windows pop up when the markers' names are clicked in the list?
			  gv_options.marker_list_options.url_links = false;  // true|false: do the names in the list become instant links to the markers' URLs?
			  gv_options.marker_list_options.toggle = false;  // true|false: does a marker disappear if you click on its name in the list?
			  gv_options.marker_list_options.help_tooltips = false;  // true|false: do "tooltips" appear on marker names that tell you what happens when you click?
			  gv_options.marker_list_options.id = 'gv_marker_list';  // id of a DIV tag that holds the list
			  gv_options.marker_list_options.header = ''; // HTML code; be sure to put backslashes in front of any single quotes, and don't include any line breaks
			  gv_options.marker_list_options.footer = ''; // HTML code
			gv_options.marker_filter_options = {};  // options for removing waypoints that are out of the current view
			  gv_options.marker_filter_options.enabled = false;  // true|false: should out-of-range markers be removed?
			  gv_options.marker_filter_options.movement_threshold = 8;  // in pixels, how far the map has to move to trigger filtering
			  gv_options.marker_filter_options.limit = 0;  // maximum number of markers to display on the map; 0 for no limit
			  gv_options.marker_filter_options.update_list = true;  // true|false: should the marker list be updated with only the filtered markers?
			  gv_options.marker_filter_options.sort_list_by_distance = false;  // true|false: should the marker list be sorted by distance from the center of the map?
			  gv_options.marker_filter_options.min_zoom = 0;  // below this zoom level, don't show any markers at all
			  gv_options.marker_filter_options.zoom_message = '';  // message to put in the marker list if the map is below the min_zoom threshold
			gv_options.synthesize_fields = {}; // for example: {label:'{name}'} would cause all markers' names to become visible labels
				

			
			// Load GPS Visualizer's mapping functions (this must be loaded AFTER gv_options are set):
			var script_file = (self.API && API.match(/^g/i)) ? 'google_maps/functions3.js' : 'leaflet/functions.js';
			if (document.location.protocol == 'https:') { // secure pages require secure scripts
				document.writeln('<script src="https://www.gpsvisualizer.com/'+script_file+'" type="text/javascript"><'+'/script>');
			} else {
				document.writeln('<script src="http://maps.gpsvisualizer.com/'+script_file+'" type="text/javascript"><'+'/script>');
			}
		</script>
		<style type="text/css">
			/* Put any custom style definitions here (e.g., .gv_marker_info_window, .gv_marker_info_window_name, .gv_marker_list_item, .gv_tooltip, .gv_label, etc.) */
			#gmap_div .gv_marker_info_window {
				font-size:11px !important;
			}
			#gmap_div .gv_label {
				opacity:0.90; filter:alpha(opacity=90);
				color:white; background:#333333; border:1px solid black; padding:1px;
				font-family:Verdana !important; font-size:10px;
				font-weight:normal !important;
			}
			.legend_block {
				display:inline-block; border:solid 1px black; width:9px; height:9px; margin:0px 2px 0px 0px;
			}
			
		</style>
		
		<!-- end GPSV setup script and styles; begin map-drawing script (they must be separate) -->
		<script type="text/javascript">
			function GV_Map() {
				GV_Setup_Map();
				
				// Track #1
				t = 1; trk[t] = {info:[],segments:[]};
				trk[t].info.name = 'FRR10toHike'; trk[t].info.desc = 'Length: 18.15 km (11.28 mi)'; trk[t].info.clickable = true;
				trk[t].info.color = '#e60000'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false; trk[t].info.z_index = null;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#e60000'; trk[t].info.fill_opacity = 0;
				trk[t].info.elevation = true;
				trk[t].segments.push({ points:[ [35.7951121,-106.6029918,2596.242],[35.7952252,-106.6035712,2592.951],[35.7960606,-106.6058028,2593.236],[35.7960867,-106.6058779,2592.849],[35.7967742,-106.6066074,2582.571],[35.7968786,-106.6066718,2581.531],[35.7969918,-106.6069722,2581.699],[35.7970092,-106.6070688,2582.209],[35.796896,-106.6078627,2582.764],[35.7966785,-106.6092145,2583.006],[35.7966176,-106.609354,2582.664],[35.7966176,-106.6095579,2582.377],[35.7955037,-106.6118646,2577.777],[35.7954689,-106.6120577,2578.064],[35.7954776,-106.6122293,2578.5],[35.7956429,-106.6123796,2578.565],[35.7959214,-106.6124225,2577.513],[35.7961564,-106.6124439,2576.753],[35.7965131,-106.6123259,2574.565],[35.7967481,-106.6121757,2572.813],[35.7972702,-106.612004,2566.667],[35.7976792,-106.6122186,2565.807],[35.7983754,-106.6126585,2575.698],[35.7990019,-106.6127336,2574.219],[35.7993674,-106.6127336,2572.006],[35.7995501,-106.6127872,2570.235],[35.7996894,-106.6129375,2568.645],[35.799759,-106.6131735,2568.037],[35.799846,-106.6138494,2570.355],[35.7998199,-106.6142786,2573.996],[35.7999417,-106.614418,2574.563],[35.800342,-106.6155553,2570.062],[35.8004943,-106.6158825,2570.074],[35.8007597,-106.6160917,2569.183],[35.8009555,-106.6162902,2568.67],[35.801508,-106.616419,2570.695],[35.8017386,-106.6164351,2572.45],[35.8026827,-106.6168535,2575.408],[35.8036138,-106.6172504,2569.139],[35.8037878,-106.6175455,2569.864],[35.8040924,-106.6178566,2572.627],[35.804262,-106.6181785,2574.803],[35.8044187,-106.6184253,2574.503],[35.8050321,-106.6190422,2570.583],[35.8055368,-106.6192889,2568.448],[35.8055803,-106.620158,2563.95],[35.8056847,-106.6208231,2558.411],[35.805689,-106.6209894,2556.608],[35.8058021,-106.6210914,2554.436],[35.8060327,-106.621027,2551.883],[35.806272,-106.6209626,2546.042],[35.8069811,-106.6205496,2536.829],[35.8073379,-106.6203189,2538.317],[35.8075076,-106.6203135,2538.168],[35.8076772,-106.6204369,2535.961],[35.8077512,-106.62063,2532.882],[35.8077729,-106.6209304,2533.677],[35.8077077,-106.6212094,2536.961],[35.8077468,-106.6213918,2540.138],[35.8078164,-106.6216117,2542.463],[35.8079905,-106.621837,2539.831],[35.8082906,-106.6218907,2544.503],[35.8085734,-106.6218853,2547.046],[35.8093043,-106.6218638,2550.39],[35.8096088,-106.6219604,2557.67],[35.8096871,-106.6220677,2560.04],[35.8097393,-106.6222179,2561.665],[35.8097132,-106.6224432,2564.175],[35.8093304,-106.6227973,2565.515],[35.8092608,-106.622926,2567.032],[35.8091999,-106.6230655,2570.314],[35.8090346,-106.6232049,2571.692],[35.8086952,-106.6232264,2570.372],[35.8083559,-106.6233873,2571.412],[35.8080905,-106.6235644,2573.054],[35.8078338,-106.6236877,2572.041],[35.8077164,-106.6240311,2576.051],[35.8079078,-106.6247821,2583.621],[35.8081558,-106.6255653,2584.577],[35.8082297,-106.6258925,2585.107],[35.8080949,-106.6262627,2585.324],[35.8077468,-106.6268259,2581.706],[35.8071204,-106.6273355,2581.464],[35.8068506,-106.6275126,2581.079],[35.8064939,-106.6279203,2579.302],[35.806259,-106.6282958,2577.004],[35.8061241,-106.628505,2574.847],[35.8058239,-106.6287303,2571.727],[35.8056194,-106.6290092,2569.11],[35.804845,-106.6297871,2567.425],[35.8041707,-106.6304308,2565.183],[35.803579,-106.6311336,2560.785],[35.8030351,-106.6318309,2557.927],[35.8028829,-106.6319865,2557.755],[35.8023521,-106.6324371,2557.218],[35.8016995,-106.6327268,2555.488],[35.8011121,-106.6329253,2556.02],[35.8005552,-106.6332632,2552.421],[35.8000157,-106.6337192,2549.523],[35.7997155,-106.6338587,2549.569],[35.7995371,-106.6339821,2549.754],[35.7993456,-106.6341537,2549.812],[35.799189,-106.63432,2549.323],[35.7989193,-106.6345614,2547.683],[35.7986538,-106.6348886,2545.58],[35.7983319,-106.6359454,2539.723],[35.7981317,-106.6365194,2536.035],[35.797862,-106.6371095,2532.218],[35.7975443,-106.6375816,2530.762],[35.7974182,-106.6377854,2530.583],[35.797131,-106.6381609,2529.686],[35.7968003,-106.6387939,2525.652],[35.7965784,-106.6391158,2523.827],[35.7964957,-106.6393626,2522.726],[35.7963489,-106.6395155,2522.092],[35.7961672,-106.6396375,2521.246],[35.7959856,-106.6397998,2520.125],[35.7958866,-106.6399634,2519.133],[35.7957669,-106.6400331,2518.249],[35.7956407,-106.6400331,2517.253],[35.7952513,-106.6403228,2514.346],[35.7950055,-106.6407251,2511.908],[35.7947314,-106.6409612,2507.726],[35.7945443,-106.6411918,2504.998],[35.7943767,-106.6416022,2503.442],[35.7943093,-106.6418517,2502.023],[35.7942941,-106.6420662,2500.888],[35.7941548,-106.6424498,2497.676],[35.7940156,-106.6427529,2494.009],[35.793872,-106.6427448,2493.721],[35.7937523,-106.6426054,2494.324],[35.7936544,-106.6425866,2493.592],[35.7935021,-106.6426161,2492.479],[35.7931192,-106.6429245,2490.866],[35.7929104,-106.6431499,2489.845],[35.7927711,-106.6433752,2488.536],[35.7925797,-106.6437614,2486.002],[35.7923447,-106.6439867,2483.627],[35.7922729,-106.6441771,2482.056],[35.7921707,-106.6450113,2478.089],[35.7920445,-106.6452527,2477.146],[35.791903,-106.6454726,2476.869],[35.7915528,-106.6461754,2478.369],[35.7911677,-106.6466635,2477.849],[35.7909566,-106.6469049,2477.274],[35.790602,-106.647267,2476.873],[35.7898557,-106.6481388,2475.539],[35.7897078,-106.6484231,2473.336],[35.7895838,-106.6486216,2471.946],[35.7893792,-106.6487825,2470.591],[35.7892378,-106.6489434,2469.588],[35.7890638,-106.6491553,2468.906],[35.7888592,-106.6492948,2468.606],[35.788646,-106.6495013,2467.834],[35.7884807,-106.6496006,2467.682],[35.7881891,-106.6497105,2467.258],[35.7879715,-106.6499546,2465.989],[35.787445,-106.6506118,2461.415],[35.7871578,-106.650998,2459.129],[35.7867618,-106.6513628,2457.329],[35.7863484,-106.6516283,2455.959],[35.7861003,-106.6518804,2454.537],[35.7857783,-106.6522935,2452.148],[35.7853062,-106.6528112,2449.105],[35.7852648,-106.6529936,2448.171],[35.7852344,-106.6532484,2446.373],[35.7851495,-106.6533262,2446.02],[35.784995,-106.6533825,2445.977],[35.784871,-106.6534173,2446.055],[35.7847992,-106.6536695,2444.77],[35.7847404,-106.6538036,2444.124],[35.7846251,-106.6539431,2443.587],[35.7842944,-106.6541684,2443.142],[35.7838483,-106.6543481,2441.897],[35.7836416,-106.654619,2440.138],[35.7835785,-106.6547826,2438.986],[35.7833218,-106.6549328,2437.67],[35.7830759,-106.6552037,2436.176],[35.782682,-106.6555926,2434.61],[35.7823948,-106.6557294,2433.617],[35.782286,-106.6557938,2433.182],[35.7820663,-106.6558528,2432.697],[35.7819945,-106.6559467,2432.229],[35.781927,-106.6561317,2431.15],[35.7817682,-106.6562229,2430.269],[35.7816463,-106.656518,2428.285],[35.7814526,-106.6568211,2426.019],[35.7813003,-106.6571483,2424.125],[35.7811981,-106.6572851,2423.337],[35.7810958,-106.6573414,2423.199],[35.7810022,-106.657438,2422.876],[35.7808738,-106.6575882,2422.348],[35.7807324,-106.6576526,2422.335],[35.7805975,-106.6577598,2421.91],[35.7803864,-106.6578564,2421.867],[35.7803124,-106.6579422,2421.75],[35.7801057,-106.6582024,2421.227],[35.7800252,-106.6583151,2420.955],[35.7797902,-106.6584492,2421.064],[35.7796683,-106.6586101,2420.72],[35.7795117,-106.6588837,2419.871],[35.7792244,-106.6591439,2419.437],[35.7786021,-106.6596454,2417.879],[35.7784976,-106.6597635,2417.4],[35.7782213,-106.6599458,2415.682],[35.778069,-106.6600317,2414.707],[35.7778557,-106.6600934,2413.819],[35.7770876,-106.6605654,2410.326],[35.7769287,-106.6606888,2410.021],[35.776796,-106.6608819,2409.217],[35.7766632,-106.6610482,2408.355],[35.7765849,-106.6613084,2407.14],[35.7765566,-106.6615713,2405.59],[35.7765827,-106.6617832,2403.822],[35.7766567,-106.661979,2400.389],[35.7768156,-106.6621238,2397.457],[35.7769831,-106.6622257,2395.292],[35.7775902,-106.6625959,2389.247],[35.7776555,-106.6626978,2388.274],[35.7776751,-106.6628668,2386.945],[35.777662,-106.6631484,2384.957],[35.7775924,-106.6633388,2383.487],[35.7772116,-106.6638511,2380.52],[35.7768547,-106.664173,2378.15],[35.7765283,-106.6644734,2372.384],[35.7762998,-106.6646263,2369.96],[35.7762345,-106.664696,2369.318],[35.7760844,-106.6647443,2368.878],[35.7759799,-106.6647443,2368.858],[35.7758885,-106.6647953,2368.455],[35.7755621,-106.6649964,2366.686],[35.7751441,-106.6650233,2366.596],[35.7744545,-106.6653398,2364.982],[35.7739474,-106.6656268,2366.144],[35.7737864,-106.6657689,2366.548],[35.7734186,-106.6659513,2367.021],[35.7731684,-106.6662115,2367.378],[35.7730661,-106.6664341,2366.967],[35.7729268,-106.6666675,2367.642],[35.772744,-106.6668659,2368.591],[35.7720389,-106.6673139,2371.575],[35.7716189,-106.6681051,2376.503],[35.7714557,-106.6683117,2378.393],[35.7712207,-106.6684914,2379.648],[35.7709965,-106.668478,2379.692],[35.7692533,-106.6689634,2375.999],[35.7688921,-106.66917,2373.864],[35.7686614,-106.669355,2373.702],[35.7683066,-106.6695642,2375.37],[35.7679998,-106.6696098,2376.545],[35.7673904,-106.6695133,2375.119],[35.7666853,-106.6695347,2378.035],[35.7663741,-106.669414,2378.969],[35.766139,-106.6694248,2378.994],[35.7658844,-106.6694623,2378.897],[35.7656189,-106.6696313,2379.122],[35.765312,-106.6701597,2379.511],[35.7649006,-106.6707015,2380.341],[35.7646699,-106.6710904,2380.014],[35.7645067,-106.6716725,2378.247],[35.7642586,-106.6719916,2376.466],[35.7641171,-106.6722223,2374.708],[35.7640192,-106.6723484,2373.565],[35.7639974,-106.6725603,2371.433],[35.7638821,-106.6729304,2367.399],[35.7638734,-106.6732764,2366.409],[35.7637341,-106.6735178,2365.875],[35.7633684,-106.6739416,2365.947],[35.7632683,-106.6742125,2366.478],[35.7630028,-106.6744995,2367.681],[35.7627873,-106.6747141,2368.12],[35.7624783,-106.6748455,2368.338],[35.7621148,-106.6749796,2368.269],[35.7617209,-106.6752613,2367.891],[35.7613617,-106.6755429,2366.224],[35.7609177,-106.675964,2361.338],[35.7607262,-106.6761678,2359.02],[35.7605151,-106.6762805,2357.401],[35.7604062,-106.6764146,2355.872],[35.7603105,-106.6767338,2353.077],[35.7602887,-106.6771737,2350.971],[35.7601472,-106.6776082,2348.815],[35.7599862,-106.6777021,2348.04],[35.7597859,-106.6778255,2347.148],[35.7595813,-106.6779113,2346.014],[35.7593049,-106.6778737,2345.612],[35.7588043,-106.677981,2343.795],[35.7586062,-106.6779622,2343.492],[35.7580316,-106.6779998,2342.575],[35.7577204,-106.6780588,2342.17],[35.7574026,-106.6781446,2342.192],[35.7568584,-106.6784585,2342.611],[35.7567039,-106.6784826,2342.711],[35.7566081,-106.6785765,2342.231],[35.7563839,-106.678665,2341.582],[35.7562816,-106.6787937,2340.622],[35.7560727,-106.6788983,2339.556],[35.7557962,-106.6789466,2338.837],[35.7554327,-106.6789573,2336.647],[35.7551171,-106.6788983,2333.87],[35.7548668,-106.678775,2332.158],[35.7545621,-106.6785952,2329.485],[35.7542421,-106.6783619,2325.864],[35.754007,-106.6782171,2323.078],[35.7537763,-106.6781205,2322.068],[35.753613,-106.6781124,2321.517],[35.7530928,-106.6782707,2319.819],[35.7519217,-106.6783914,2317.852],[35.7514341,-106.6785094,2316.459],[35.7510466,-106.6785282,2313.212],[35.7507615,-106.6785899,2311.529],[35.75001,-106.6788259,2309.041],[35.7495425,-106.6790378,2307.139],[35.7490766,-106.6792685,2307.267],[35.748811,-106.6793275,2307.675],[35.7486543,-106.679424,2307.29],[35.7484497,-106.6795179,2306.942],[35.7478489,-106.6797003,2307.409],[35.747555,-106.6798452,2308.285],[35.7472655,-106.6799954,2307.722],[35.7470978,-106.6800544,2307.184],[35.7469259,-106.6801992,2306.658],[35.7467278,-106.6803655,2305.811],[35.746484,-106.6805533,2304.593],[35.7463163,-106.6806418,2303.279],[35.746164,-106.680623,2301.726],[35.7459071,-106.6804808,2297.958],[35.7457852,-106.6803333,2296.195],[35.7456589,-106.6800195,2295.753],[35.7454782,-106.6797566,2294.19],[35.7453737,-106.6797057,2293.797],[35.744799,-106.6796574,2293.308],[35.7444202,-106.6794965,2293.679],[35.7439631,-106.6792926,2294.168],[35.7438063,-106.6792658,2293.689],[35.7433143,-106.6791022,2293.245],[35.7429899,-106.679011,2291.974],[35.742339,-106.6790056,2285.427],[35.7416837,-106.6790485,2283.235],[35.7415161,-106.6791317,2283.042],[35.7409196,-106.6796869,2281.383],[35.7403296,-106.6802689,2278.1],[35.7398311,-106.6808188,2274.315],[35.7393586,-106.6813713,2272.479],[35.738577,-106.6823933,2270.685],[35.7383005,-106.682758,2270.702],[35.7376779,-106.6837907,2270.476],[35.7374493,-106.6842788,2270.107],[35.7371989,-106.6850352,2269.075],[35.73704,-106.6855207,2268.024],[35.7368985,-106.6857889,2266.62],[35.7365436,-106.6862798,2263.802],[35.736082,-106.6868377,2260.773],[35.7357358,-106.6871783,2259.512],[35.7353461,-106.6875216,2258.306],[35.7349325,-106.6877791,2257.795],[35.7344948,-106.6879749,2257.339],[35.7341682,-106.6881064,2256.865],[35.7339483,-106.6880769,2256.307],[35.733613,-106.6878676,2253.976],[35.7333779,-106.6876933,2251.98],[35.7331667,-106.6874599,2249.727],[35.7328227,-106.6870308,2245.738],[35.7326202,-106.6868216,2243.97],[35.7323938,-106.6866526,2242.744],[35.7322827,-106.6864997,2242.37],[35.7308958,-106.686202,2238.775],[35.7301947,-106.6859794,2236.821],[35.7294696,-106.6858345,2234.834],[35.7285551,-106.6863012,2232.165],[35.7278975,-106.6863817,2231.83],[35.7275295,-106.6863951,2232.49],[35.7266324,-106.6866311,2229.273],[35.7260184,-106.6866982,2228.328],[35.725498,-106.6868994,2226.358],[35.7253586,-106.6869798,2225.571],[35.725032,-106.6871756,2224.426],[35.724725,-106.6873473,2224.674],[35.7243134,-106.6874814,2224.847],[35.7240391,-106.6875163,2223.991],[35.7230613,-106.6876397,2224.038],[35.7225562,-106.6876987,2224.387],[35.7220183,-106.6878247,2224.006],[35.7217069,-106.6879186,2223.491],[35.7213019,-106.6881064,2223.176],[35.7209121,-106.6883183,2222.604],[35.7206529,-106.6884363,2222.164],[35.7203176,-106.6886321,2221.502],[35.7199474,-106.6888735,2220.876],[35.7197187,-106.6890746,2220.3],[35.7195184,-106.689316,2219.345],[35.7192418,-106.689662,2218.167],[35.7189566,-106.6899785,2217.019],[35.7184753,-106.6912875,2213.546],[35.7182009,-106.6916603,2212.764],[35.7180332,-106.6919231,2212.156],[35.7176804,-106.6926366,2210.817],[35.7172797,-106.6932964,2207.564],[35.7168878,-106.6938195,2205.377],[35.7161735,-106.6946965,2202.462],[35.7155615,-106.6952062,2199.873],[35.7150127,-106.6958311,2196.463],[35.7145859,-106.6961852,2193.847],[35.71422,-106.6964078,2192.228],[35.7138803,-106.696558,2191.059],[35.7136473,-106.6966626,2190.207],[35.7133053,-106.6966653,2189.339],[35.7129242,-106.696794,2188.626],[35.7114302,-106.6968986,2181.443],[35.7106418,-106.696845,2178.427],[35.7104698,-106.6967833,2177.59],[35.7103064,-106.6967055,2177.058],[35.7101649,-106.6966572,2176.719],[35.7087928,-106.6965312,2174.488],[35.708392,-106.6964024,2173.683],[35.7079129,-106.6961771,2171.87],[35.7075644,-106.6960001,2169.266],[35.7074381,-106.6958767,2167.936],[35.7072965,-106.6956192,2165.438] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:35.7969363,lon:-106.6075799,alt:2582.56629246931,name:'0.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:260.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7956511,lon:-106.6123809,alt:2578.53388987391,name:'0.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:352.9,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7997140,lon:-106.6130209,alt:2568.43002956603,name:'0.9 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:290.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8021765,lon:-106.6166292,alt:2573.82197980827,name:'1.2 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:340.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8055510,lon:-106.6195721,alt:2566.98249296898,name:'1.5 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:273.5,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8078214,lon:-106.6216181,alt:2542.38765556995,name:'1.8 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:313.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8079993,lon:-106.6236082,alt:2572.69426168947,name:'2.1 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:201.3,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8066277,lon:-106.6277674,alt:2579.96864229734,name:'2.4 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:222.8,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8034201,lon:-106.6313373,alt:2559.94990266845,name:'2.7 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:226.1,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7996712,lon:-106.6338893,alt:2549.61489404972,name:'3.0 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:209.3,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7971740,lon:-106.6381047,alt:2529.82033435745,name:'3.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:226.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7943082,lon:-106.6418673,alt:2501.94062412686,name:'3.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:265.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7918781,lon:-106.6455226,alt:2476.97581294075,name:'3.9 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:238.4,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7888587,lon:-106.6492953,alt:2468.60418706823,name:'4.2 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:218.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7855025,lon:-106.6525960,alt:2450.37014482699,name:'4.5 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:221.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7823048,lon:-106.6557827,alt:2433.25711849598,name:'4.8 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:205.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7791558,lon:-106.6591992,alt:2419.26534653139,name:'5.1 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:213.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7771857,lon:-106.6623492,alt:2393.27514318169,name:'5.4 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:333.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7748179,lon:-106.6651730,alt:2365.83248855866,name:'5.7 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:200.4,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7714586,lon:-106.6683081,alt:2378.35975795336,name:'6.0 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:225.8,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7673233,lon:-106.6695153,alt:2375.39638569867,name:'6.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:181.4,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7641321,lon:-106.6721979,alt:2374.89387867686,name:'6.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:232.9,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7612045,lon:-106.6756920,alt:2364.49438204947,name:'6.9 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:217.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7578794,lon:-106.6780287,alt:2342.37692269214,name:'7.2 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:188.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7538438,lon:-106.6781488,alt:2322.3636951302,name:'7.5 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:161.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7495829,lon:-106.6790195,alt:2307.30330337428,name:'7.8 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:200.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7457000,lon:-106.6801215,alt:2295.89667253556,name:'8.1 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:116.4,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7415961,lon:-106.6790920,alt:2283.13409081527,name:'8.4 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:201.9,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7383771,lon:-106.6826569,alt:2270.69728815633,name:'8.7 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:227.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7359282,lon:-106.6869890,alt:2260.21275643186,name:'9.0 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:218.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7322753,lon:-106.6864981,alt:2242.35069762926,name:'9.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:170.1,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7280560,lon:-106.6863623,alt:2231.91075110742,name:'9.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:185.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7238524,lon:-106.6875399,alt:2223.99997368001,name:'9.9 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:185.8,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7197411,lon:-106.6890549,alt:2220.35634800817,name:'10.2 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:215.5,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7172396,lon:-106.6933499,alt:2207.34032968942,name:'10.5 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:227.3,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7138363,lon:-106.6965778,alt:2190.89798305279,name:'10.8 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:200.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7095412,lon:-106.6965999,alt:2175.7048887268,name:'11.1 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:175.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7072965,lon:-106.6956192,alt:2165.438,name:'11.27 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'FRR10toHike [tickmarks]',rotation:124.1,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.8140895,lon:-106.5817165,alt:2484.55,name:'North entrance to FR10',desc:'',color:'#ffff00',icon:''});
				GV_Draw_Marker({lat:35.6873655,lon:-106.6524625,alt:1937.392,name:'South entrance to FR10',desc:'',color:'#ffff00',icon:''});
				GV_Draw_Marker({lat:35.7073118,lon:-106.6955951,alt:2165.296,name:'Park',desc:'',color:'#ffff00',icon:''});
				
				GV_Finish_Map();
					
			}
			GV_Map(); // execute the above code
			// https://www.gpsvisualizer.com/map_input?add_elevation=auto&allow_export=1&form=google&format=google&google_street_view=1&google_trk_mouseover=1&tickmark_interval=.3%20mi&trk_stats=1&units=us&wpt_driving_directions=1
		</script>
		
		
		
	</body>

</html>
