<?php
require "../php/global_boot.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Alamo Aspen VIsta + AlamoAspenVistaLoopProposed</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="35.7800885; -105.8008665" />
		<meta name="ICBM" content="35.7800885, -105.8008665" />
	</head>
	<body style="margin:0px;">
		<script type="text/javascript">
			google_api_key = '<?=API_KEY;?>'; // Your project's Google Maps API key goes here (https://code.google.com/apis/console)
			if (document.location.toString().indexOf('http://www.gpsvisualizer.com') > -1) { google_api_key = ''; }
			document.writeln('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=geometry&amp;key='+self.google_api_key+'"><'+'/script>');
		</script>
		
		<!--
			If you want to transplant this map into another Web page, by far the best method is to
			simply include it in a IFRAME tag (see http://www.gpsvisualizer.com/faq.html#google_html).
			But, if you must paste the code into another page, be sure to include all of these parts:
			   1. The "div" tags that contain the map and its widgets, below
			   2. Three sections of JavaScript code:
			      a. The Google code (maps.google.com or googleapis.com) code, above
			      b. "gv_options" and the code that calls a .js file on maps.gpsvisualizer.com
			      c. The "GV_Map" function, which contains all the geographic info for the map
		-->
		<div style="margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px;">
			<div id="gmap_div" style="width:700px; height:537px; margin:0px; margin-right:12px; background-color:#f0f0f0; float:left; overflow:hidden;">
				<p align="center" style="font:10px Arial;">This map was created using <a target="_blank" href="http://www.gpsvisualizer.com/">GPS Visualizer</a>'s do-it-yourself geographic utilities.<br /><br />Please wait while the map data loads...</p>
			</div>
				
			<div id="gv_infobox" class="gv_infobox" style="font:11px Arial; border:solid #666666 1px; background-color:#ffffff; padding:4px; overflow:auto; display:none; ">
				<div id="gv_legend_header" style="padding-bottom:2px;"><b>Slope [est.] (%)</b></div>
				<div class="gv_legend_item"><span style="color:#e600e6;">&#9608; 35.4</span></div>
				<div class="gv_legend_item"><span style="color:#003ae6;">&#9608; 13.1</span></div>
				<div class="gv_legend_item"><span style="color:#00e674;">&#9608; -9.1</span></div>
				<div class="gv_legend_item"><span style="color:#ace600;">&#9608; -31.4</span></div>
				<div class="gv_legend_item"><span style="color:#e60000;">&#9608; -53.6</span></div>
			</div>


			<div id="gv_tracklist" class="gv_tracklist" style="font:11px Arial; line-height:11px; background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_marker_list" class="gv_marker_list" style="background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_clear_margins" style="height:0px; clear:both;"><!-- clear the "float" --></div>
		</div>

		
		<!-- begin GPS Visualizer setup script (must come after maps.google.com code) -->
		<script type="text/javascript">
			/* Global variables used by the GPS Visualizer functions (20160922164320): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [35.7800885,-105.8008665];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 15;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
			gv_options.map_type = 'GV_HYBRID';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_TOPO_US', 'GV_TOPO_WORLD', 'GV_OSM' (http://www.gpsvisualizer.com/misc/google_map_types.html)
			gv_options.map_opacity = 1.00;  // number from 0 to 1
			gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?
			gv_options.width = 700;  // width of the map, in pixels
			gv_options.height = 537;  // height of the map, in pixels
			
			gv_options.map_div = 'gmap_div';  // the name of the HTML "div" tag containing the map itself; usually 'gmap_div'
			gv_options.doubleclick_zoom = true;  // true|false: zoom in when mouse is double-clicked?
			gv_options.doubleclick_center = true;  // true|false: re-center the map on the point that was double-clicked?
			gv_options.scroll_zoom = true; // true|false; or 'reverse' for down=in and up=out
			gv_options.autozoom_adjustment = 0;
			gv_options.centering_options = { 'open_info_window':true, 'partial_match':true, 'center_key':'center', 'default_zoom':null } // URL-based centering (e.g., ?center=name_of_marker&zoom=14)
			gv_options.tilt = false; // true|false: allow Google to show 45-degree tilted aerial imagery?
			gv_options.street_view = true; // true|false: allow Google Street View on the map
			gv_options.animated_zoom = false; // true|false: may or may not work properly
			gv_options.disable_google_pois = false;  // true|false: if you disable clickable POIs, you also lose the labels on parks, airports, etc.
				
			// widgets on the map:
			gv_options.zoom_control = 'large'; // 'large'|'small'|'none'
			gv_options.recenter_button = true; // true|false: is there a 'click to recenter' option in the zoom control?
			gv_options.scale_control = true; // true|false
			gv_options.center_coordinates = true;  // true|false: show a "center coordinates" box and crosshair?
			gv_options.mouse_coordinates = false;  // true|false: show a "mouse coordinates" box?
			gv_options.measurement_tools = 'separate';  // true|false|'separate' ('separate' to put a ruler outside the utilities menu)
			gv_options.measurement_options = { visible:false, distance_color:'', area_color:'' };
			gv_options.crosshair_hidden = true;  // true|false: hide the crosshair initially?
			gv_options.map_opacity_control = true;  // true|false|'separate' ('separate' to put a control outside the utilities menu)
			gv_options.map_type_control = {};  // widget to change the background map
			  gv_options.map_type_control.style = 'menu';  // 'menu'|'none'
			  gv_options.map_type_control.filter = false;  // true|false: when map loads, are irrelevant maps ignored?
			  gv_options.map_type_control.excluded = [];  // comma-separated list of quoted map IDs that will never show in the list ('included' also works)
			gv_options.infobox_options = {}; // options for a floating info box (id="gv_infobox"), which can contain anything
			  gv_options.infobox_options.enabled = true;  // true|false: enable or disable the info box altogether
			  gv_options.infobox_options.position = ['LEFT_BOTTOM',3,50];  // [Google anchor name, relative x, relative y]
			  gv_options.infobox_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.infobox_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			gv_options.utilities_menu = true;  // true|false
			gv_options.allow_export = true;  // true|false

			// track-related options:
			gv_options.track_tooltips = true; // true|false: should the name of a track appear on the map when you mouse over the track itself?
			gv_options.tracklist_options = {}; // options for a floating list of the tracks visible on the map
			  gv_options.tracklist_options.enabled = true;  // true|false: enable or disable the tracklist altogether
			  gv_options.tracklist_options.position = ['RIGHT_TOP',4,32];  // [Google anchor name, relative x, relative y]
			  gv_options.tracklist_options.min_width = 100; // minimum width of the tracklist, in pixels
			  gv_options.tracklist_options.max_width = 180; // maximum width of the tracklist, in pixels
			  gv_options.tracklist_options.min_height = 0; // minimum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.max_height = 228; // maximum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.desc = true;  // true|false: should tracks' descriptions be shown in the list
			  gv_options.tracklist_options.toggle = true;  // true|false: should clicking on a track's name turn it on or off?
			  gv_options.tracklist_options.checkboxes = false;  // true|false: should there be a separate icon/checkbox for toggling visibility?
			  gv_options.tracklist_options.zoom_links = true;  // true|false: should each item include a small icon that will zoom to that track?
			  gv_options.tracklist_options.highlighting = true;  // true|false: should the track be highlighted when you mouse over the name in the list?
			  gv_options.tracklist_options.tooltips = false;  // true|false: should the name of the track appear on the map when you mouse over the name in the list?
			  gv_options.tracklist_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.tracklist_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			  gv_options.tracklist_options.header = 'Tracks:'; // HTML code; be sure to put backslashes in front of any single quotes, and don't include any line breaks
			  gv_options.tracklist_options.footer = ''; // HTML code

			// marker-related options:
			gv_options.default_marker = { color:'red',icon:'googlemini',scale:1 }; // icon can be a URL, but be sure to also include size:[w,h] and optionally anchor:[x,y]
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
			  gv_options.marker_list_options.max_height = 228;  // maximum height
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
			  gv_options.marker_list_options.wrap_names = true;  // true|false: should marker's names be allowed to wrap onto more than one line?
			  gv_options.marker_list_options.unnamed = '[unnamed]';  // what 'name' should be assigned to  unnamed markers in the list?
			  gv_options.marker_list_options.colors = false;  // true|false: should the names/descs of the points in the list be colorized the same as their markers?
			  gv_options.marker_list_options.default_color = '';  // default HTML color code for the names/descs in the list
			  gv_options.marker_list_options.limit = 0;  // how many markers to show in the list; 0 for no limit
			  gv_options.marker_list_options.center = false;  // true|false: does the map center upon a marker when you click its name in the list?
			  gv_options.marker_list_options.zoom = false;  // true|false: does the map zoom to a certain level when you click on a marker's name in the list?
			  gv_options.marker_list_options.zoom_level = 17;  // if 'zoom' is true, what level should the map zoom to?
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
				
			
			// Load GPS Visualizer's Google Maps functions (this must be loaded AFTER gv_options are set):
			if (window.location.toString().indexOf('https://') == 0) { // secure pages require secure scripts
				document.writeln('<script src="https://gpsvisualizer.github.io/google_maps/functions3.js" type="text/javascript"><'+'/script>');
			} else {
				document.writeln('<script src="http://maps.gpsvisualizer.com/google_maps/functions3.js" type="text/javascript"><'+'/script>');
			}
			
		</script>
		
		<style type="text/css">
			/* Put any custom style definitions here (e.g., .gv_marker_info_window, .gv_marker_info_window_name, .gv_marker_list_item, .gv_tooltip, .gv_label, etc.) */
			#gmap_div .gv_marker_info_window {
				font-size:11px !important;
			}
			#gmap_div .gv_label {
				opacity:0.80; filter:alpha(opacity=80);
				color:white; background-color:#333333; border:1px solid black; padding:1px;
				font:9px Verdana !important;
				font-weight:normal !important;
			}
			
		</style>
		
		<!-- end GPSV setup script and styles; begin map-drawing script (they must be separate) -->
		<script type="text/javascript">
			function GV_Map() {
			  
				GV_Setup_Map();
				
				// Track #1
				t = 1; trk[t] = {info:[],segments:[]};
				trk[t].info.name = 'Alamo Aspen VIsta'; trk[t].info.desc = '<br />[<a target="_blank" href="https://www.endomondo.com/workouts/810418447/7544338">Alamo Aspen VIsta</a>]'; trk[t].info.clickable = true;
				trk[t].info.color = '#e60000'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#e6e600'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ color:'#00a6e6', points:[ [35.777363,-105.810613], [35.777188,-105.810382] ] });
				trk[t].segments.push({ color:'#00cbe6', points:[ [35.777188,-105.810382], [35.777067,-105.810278] ] });
				trk[t].segments.push({ color:'#000ee6', points:[ [35.777067,-105.810278], [35.777029,-105.809933] ] });
				trk[t].segments.push({ color:'#9e00e6', points:[ [35.777029,-105.809933], [35.776938,-105.809606] ] });
				trk[t].segments.push({ color:'#7500e6', points:[ [35.776938,-105.809606], [35.77693,-105.809283] ] });
				trk[t].segments.push({ color:'#a300e6', points:[ [35.77693,-105.809283], [35.776835,-105.808926] ] });
				trk[t].segments.push({ color:'#4700e6', points:[ [35.776835,-105.808926], [35.776847,-105.808707] ] });
				trk[t].segments.push({ color:'#3700e6', points:[ [35.776847,-105.808707], [35.776853,-105.808674] ] });
				trk[t].segments.push({ color:'#4200e6', points:[ [35.776853,-105.808674], [35.776858,-105.808643] ] });
				trk[t].segments.push({ color:'#7900e6', points:[ [35.776858,-105.808643], [35.776903,-105.808335] ] });
				trk[t].segments.push({ color:'#c300e6', points:[ [35.776903,-105.808335], [35.776872,-105.808013] ] });
				trk[t].segments.push({ color:'#cc00e6', points:[ [35.776872,-105.808013], [35.776839,-105.807859] ] });
				trk[t].segments.push({ color:'#e600e6', points:[ [35.776839,-105.807859], [35.776836,-105.807682] ] });
				trk[t].segments.push({ color:'#ae00e6', points:[ [35.776836,-105.807682], [35.776889,-105.8075] ] });
				trk[t].segments.push({ color:'#9c00e6', points:[ [35.776889,-105.8075], [35.776983,-105.80716] ] });
				trk[t].segments.push({ color:'#9a00e6', points:[ [35.776983,-105.80716], [35.776857,-105.806852] ] });
				trk[t].segments.push({ color:'#aa00e6', points:[ [35.776857,-105.806852], [35.776844,-105.806539] ] });
				trk[t].segments.push({ color:'#0053e6', points:[ [35.776844,-105.806539], [35.77709,-105.806336] ] });
				trk[t].segments.push({ color:'#0088e6', points:[ [35.77709,-105.806336], [35.777314,-105.806102] ] });
				trk[t].segments.push({ color:'#0035e6', points:[ [35.777314,-105.806102], [35.777382,-105.80597] ] });
				trk[t].segments.push({ color:'#0028e6', points:[ [35.777382,-105.80597], [35.777594,-105.805701] ] });
				trk[t].segments.push({ color:'#00b4e6', points:[ [35.777594,-105.805701], [35.777689,-105.805588] ] });
				trk[t].segments.push({ color:'#007de6', points:[ [35.777689,-105.805588], [35.777805,-105.805448] ] });
				trk[t].segments.push({ color:'#0033e6', points:[ [35.777805,-105.805448], [35.777936,-105.805342] ] });
				trk[t].segments.push({ color:'#0033e6', points:[ [35.777936,-105.805342], [35.77818,-105.805144] ] });
				trk[t].segments.push({ color:'#001ae6', points:[ [35.77818,-105.805144], [35.778222,-105.805126] ] });
				trk[t].segments.push({ color:'#00ade6', points:[ [35.778222,-105.805126], [35.778223,-105.805125] ] });
				trk[t].segments.push({ color:'#0031e6', points:[ [35.778223,-105.805125], [35.778479,-105.805017] ] });
				trk[t].segments.push({ color:'#00d9e6', points:[ [35.778479,-105.805017], [35.778515,-105.804973] ] });
				trk[t].segments.push({ color:'#0068e6', points:[ [35.778515,-105.804973], [35.778543,-105.804958] ] });
				trk[t].segments.push({ color:'#0038e6', points:[ [35.778543,-105.804958], [35.778712,-105.804881] ] });
				trk[t].segments.push({ color:'#0038e6', points:[ [35.778712,-105.804881], [35.778835,-105.80479] ] });
				trk[t].segments.push({ color:'#2400e6', points:[ [35.778835,-105.80479], [35.779145,-105.804726] ] });
				trk[t].segments.push({ color:'#002ae6', points:[ [35.779145,-105.804726], [35.779227,-105.804694] ] });
				trk[t].segments.push({ color:'#0011e6', points:[ [35.779227,-105.804694], [35.779324,-105.804675] ] });
				trk[t].segments.push({ color:'#0038e6', points:[ [35.779324,-105.804675], [35.779639,-105.804629] ] });
				trk[t].segments.push({ color:'#000ae6', points:[ [35.779639,-105.804629], [35.779754,-105.804558] ] });
				trk[t].segments.push({ color:'#0031e6', points:[ [35.779754,-105.804558], [35.779793,-105.804522] ] });
				trk[t].segments.push({ color:'#0023e6', points:[ [35.779793,-105.804522], [35.779836,-105.804511] ] });
				trk[t].segments.push({ color:'#0061e6', points:[ [35.779836,-105.804511], [35.780132,-105.804372] ] });
				trk[t].segments.push({ color:'#0038e6', points:[ [35.780132,-105.804372], [35.780375,-105.804197] ] });
				trk[t].segments.push({ color:'#0007e6', points:[ [35.780375,-105.804197], [35.780609,-105.804029] ] });
				trk[t].segments.push({ color:'#0033e6', points:[ [35.780609,-105.804029], [35.780855,-105.803825] ] });
				trk[t].segments.push({ color:'#000ce6', points:[ [35.780855,-105.803825], [35.781088,-105.803596] ] });
				trk[t].segments.push({ color:'#2900e6', points:[ [35.781088,-105.803596], [35.781354,-105.803407] ] });
				trk[t].segments.push({ color:'#6200e6', points:[ [35.781354,-105.803407], [35.781589,-105.803299] ] });
				trk[t].segments.push({ color:'#9a00e6', points:[ [35.781589,-105.803299], [35.781824,-105.803129] ] });
				trk[t].segments.push({ color:'#c500e6', points:[ [35.781824,-105.803129], [35.782068,-105.802975] ] });
				trk[t].segments.push({ color:'#a100e6', points:[ [35.782068,-105.802975], [35.782371,-105.802737] ] });
				trk[t].segments.push({ color:'#8c00e6', points:[ [35.782371,-105.802737], [35.782581,-105.802496] ] });
				trk[t].segments.push({ color:'#3b00e6', points:[ [35.782581,-105.802496], [35.782766,-105.802228] ] });
				trk[t].segments.push({ color:'#0017e6', points:[ [35.782766,-105.802228], [35.7829,-105.80191] ] });
				trk[t].segments.push({ color:'#2700e6', points:[ [35.7829,-105.80191], [35.783107,-105.801652] ] });
				trk[t].segments.push({ color:'#008ae6', points:[ [35.783107,-105.801652], [35.783223,-105.801296] ] });
				trk[t].segments.push({ color:'#0005e6', points:[ [35.783223,-105.801296], [35.78337,-105.801082] ] });
				trk[t].segments.push({ color:'#0003e6', points:[ [35.78337,-105.801082], [35.78359,-105.800819] ] });
				trk[t].segments.push({ color:'#0033e6', points:[ [35.78359,-105.800819], [35.783824,-105.800545] ] });
				trk[t].segments.push({ color:'#005ce6', points:[ [35.783824,-105.800545], [35.783974,-105.800254] ] });
				trk[t].segments.push({ color:'#0063e6', points:[ [35.783974,-105.800254], [35.784005,-105.800138] ] });
				trk[t].segments.push({ color:'#00a8e6', points:[ [35.784005,-105.800138], [35.784016,-105.800058] ] });
				trk[t].segments.push({ color:'#000ae6', points:[ [35.784016,-105.800058], [35.784181,-105.799801] ] });
				trk[t].segments.push({ color:'#001ae6', points:[ [35.784181,-105.799801], [35.784259,-105.799562] ] });
				trk[t].segments.push({ color:'#1900e6', points:[ [35.784259,-105.799562], [35.784408,-105.799245] ] });
				trk[t].segments.push({ color:'#9a00e6', points:[ [35.784408,-105.799245], [35.784602,-105.799107] ] });
				trk[t].segments.push({ color:'#8e00e6', points:[ [35.784602,-105.799107], [35.784767,-105.798848] ] });
				trk[t].segments.push({ color:'#e300e6', points:[ [35.784767,-105.798848], [35.784998,-105.798597] ] });
				trk[t].segments.push({ color:'#c300e6', points:[ [35.784998,-105.798597], [35.785238,-105.798436] ] });
				trk[t].segments.push({ color:'#ca00e6', points:[ [35.785238,-105.798436], [35.785329,-105.798088] ] });
				trk[t].segments.push({ color:'#9300e6', points:[ [35.785329,-105.798088], [35.78561,-105.797975] ] });
				trk[t].segments.push({ color:'#c300e6', points:[ [35.78561,-105.797975], [35.785814,-105.797778] ] });
				trk[t].segments.push({ color:'#6500e6', points:[ [35.785814,-105.797778], [35.785891,-105.797448] ] });
				trk[t].segments.push({ color:'#009be6', points:[ [35.785891,-105.797448], [35.785799,-105.797102] ] });
				trk[t].segments.push({ color:'#00c9e6', points:[ [35.785799,-105.797102], [35.785742,-105.796746] ] });
				trk[t].segments.push({ color:'#00e6e2', points:[ [35.785742,-105.796746], [35.785686,-105.796586] ] });
				trk[t].segments.push({ color:'#00dde6', points:[ [35.785686,-105.796586], [35.785693,-105.796439] ] });
				trk[t].segments.push({ color:'#00e6c7', points:[ [35.785693,-105.796439], [35.785714,-105.796238] ] });
				trk[t].segments.push({ color:'#00e6b9', points:[ [35.785714,-105.796238], [35.785726,-105.796088] ] });
				trk[t].segments.push({ color:'#00e6c2', points:[ [35.785726,-105.796088], [35.785716,-105.795934] ] });
				trk[t].segments.push({ color:'#00e66b', points:[ [35.785716,-105.795934], [35.785757,-105.79569] ] });
				trk[t].segments.push({ color:'#00e684', points:[ [35.785757,-105.79569], [35.785718,-105.795342] ] });
				trk[t].segments.push({ color:'#00c2e6', points:[ [35.785718,-105.795342], [35.785689,-105.795158] ] });
				trk[t].segments.push({ color:'#008de6', points:[ [35.785689,-105.795158], [35.785658,-105.794967] ] });
				trk[t].segments.push({ color:'#0061e6', points:[ [35.785658,-105.794967], [35.78563,-105.794789] ] });
				trk[t].segments.push({ color:'#0035e6', points:[ [35.78563,-105.794789], [35.785602,-105.7946] ] });
				trk[t].segments.push({ color:'#6c00e6', points:[ [35.785602,-105.7946], [35.785473,-105.794272] ] });
				trk[t].segments.push({ color:'#0000e6', points:[ [35.785473,-105.794272], [35.785421,-105.79395] ] });
				trk[t].segments.push({ color:'#0005e6', points:[ [35.785421,-105.79395], [35.78534,-105.793656] ] });
				trk[t].segments.push({ color:'#7500e6', points:[ [35.78534,-105.793656], [35.785185,-105.793373] ] });
				trk[t].segments.push({ color:'#7700e6', points:[ [35.785185,-105.793373], [35.785073,-105.793026] ] });
				trk[t].segments.push({ color:'#8e00e6', points:[ [35.785073,-105.793026], [35.7849,-105.792762] ] });
				trk[t].segments.push({ color:'#00e60c', points:[ [35.7849,-105.792762], [35.784609,-105.792662] ] });
				trk[t].segments.push({ color:'#00e686', points:[ [35.784609,-105.792662], [35.78456,-105.792372] ] });
				trk[t].segments.push({ color:'#00d4e6', points:[ [35.78456,-105.792372], [35.784602,-105.792028] ] });
				trk[t].segments.push({ color:'#3fe600', points:[ [35.784602,-105.792028], [35.784417,-105.791736] ] });
				trk[t].segments.push({ color:'#42e600', points:[ [35.784417,-105.791736], [35.784262,-105.791431] ] });
				trk[t].segments.push({ color:'#2de600', points:[ [35.784262,-105.791431], [35.784107,-105.79115] ] });
				trk[t].segments.push({ color:'#9be600', points:[ [35.784107,-105.79115], [35.784079,-105.791127] ] });
				trk[t].segments.push({ color:'#a100e6', points:[ [35.784079,-105.791127], [35.784107,-105.79115] ] });
				trk[t].segments.push({ color:'#e67c00', points:[ [35.784107,-105.79115], [35.783797,-105.791164] ] });
				trk[t].segments.push({ color:'#cee600', points:[ [35.783797,-105.791164], [35.78351,-105.791187] ] });
				trk[t].segments.push({ color:'#e6b300', points:[ [35.78351,-105.791187], [35.783243,-105.791242] ] });
				trk[t].segments.push({ color:'#e66500', points:[ [35.783243,-105.791242], [35.78303,-105.791464] ] });
				trk[t].segments.push({ color:'#e67500', points:[ [35.78303,-105.791464], [35.782757,-105.791642] ] });
				trk[t].segments.push({ color:'#e67c00', points:[ [35.782757,-105.791642], [35.782522,-105.791841] ] });
				trk[t].segments.push({ color:'#e69c00', points:[ [35.782522,-105.791841], [35.782309,-105.792073] ] });
				trk[t].segments.push({ color:'#e6d600', points:[ [35.782309,-105.792073], [35.782054,-105.792221] ] });
				trk[t].segments.push({ color:'#cee600', points:[ [35.782054,-105.792221], [35.781779,-105.792362] ] });
				trk[t].segments.push({ color:'#00e61f', points:[ [35.781779,-105.792362], [35.78166,-105.792714] ] });
				trk[t].segments.push({ color:'#a0e600', points:[ [35.78166,-105.792714], [35.781458,-105.792968] ] });
				trk[t].segments.push({ color:'#e6a600', points:[ [35.781458,-105.792968], [35.781305,-105.79325] ] });
				trk[t].segments.push({ color:'#ace600', points:[ [35.781305,-105.79325], [35.781163,-105.793587] ] });
				trk[t].segments.push({ color:'#c3e600', points:[ [35.781163,-105.793587], [35.781053,-105.79387] ] });
				trk[t].segments.push({ color:'#00e601', points:[ [35.781053,-105.79387], [35.780992,-105.794207] ] });
				trk[t].segments.push({ color:'#00e60c', points:[ [35.780992,-105.794207], [35.780866,-105.794507] ] });
				trk[t].segments.push({ color:'#39e600', points:[ [35.780866,-105.794507], [35.780843,-105.794555] ] });
				trk[t].segments.push({ color:'#00e63d', points:[ [35.780843,-105.794555], [35.780843,-105.794561] ] });
				trk[t].segments.push({ color:'#3fe600', points:[ [35.780843,-105.794561], [35.780729,-105.794886] ] });
				trk[t].segments.push({ color:'#00e66f', points:[ [35.780729,-105.794886], [35.780717,-105.795004] ] });
				trk[t].segments.push({ color:'#00e611', points:[ [35.780717,-105.795004], [35.780696,-105.795076] ] });
				trk[t].segments.push({ color:'#2be600', points:[ [35.780696,-105.795076], [35.780535,-105.795332] ] });
				trk[t].segments.push({ color:'#e5e600', points:[ [35.780535,-105.795332], [35.780515,-105.795374] ] });
				trk[t].segments.push({ color:'#e68a00', points:[ [35.780515,-105.795374], [35.780502,-105.795388] ] });
				trk[t].segments.push({ color:'#70e600', points:[ [35.780502,-105.795388], [35.780428,-105.79572] ] });
				trk[t].segments.push({ color:'#04e600', points:[ [35.780428,-105.79572], [35.780375,-105.796043] ] });
				trk[t].segments.push({ color:'#46e600', points:[ [35.780375,-105.796043], [35.780324,-105.796393] ] });
				trk[t].segments.push({ color:'#00e666', points:[ [35.780324,-105.796393], [35.780364,-105.79673] ] });
				trk[t].segments.push({ color:'#aee600', points:[ [35.780364,-105.79673], [35.780226,-105.79704] ] });
				trk[t].segments.push({ color:'#00e636', points:[ [35.780226,-105.79704], [35.780109,-105.797437] ] });
				trk[t].segments.push({ color:'#46e600', points:[ [35.780109,-105.797437], [35.779886,-105.797622] ] });
				trk[t].segments.push({ color:'#00e682', points:[ [35.779886,-105.797622], [35.779786,-105.79795] ] });
				trk[t].segments.push({ color:'#00e6ab', points:[ [35.779786,-105.79795], [35.779744,-105.798113] ] });
				trk[t].segments.push({ color:'#59e600', points:[ [35.779744,-105.798113], [35.779643,-105.79826] ] });
				trk[t].segments.push({ color:'#01e600', points:[ [35.779643,-105.79826], [35.779501,-105.79861] ] });
				trk[t].segments.push({ color:'#24e600', points:[ [35.779501,-105.79861], [35.779496,-105.798958] ] });
				trk[t].segments.push({ color:'#00e603', points:[ [35.779496,-105.798958], [35.779447,-105.798948] ] });
				trk[t].segments.push({ color:'#6be600', points:[ [35.779447,-105.798948], [35.779438,-105.799101] ] });
				trk[t].segments.push({ color:'#e5e600', points:[ [35.779438,-105.799101], [35.779382,-105.799265] ] });
				trk[t].segments.push({ color:'#06e600', points:[ [35.779382,-105.799265], [35.77934,-105.799396] ] });
				trk[t].segments.push({ color:'#00e648', points:[ [35.77934,-105.799396], [35.779297,-105.799478] ] });
				trk[t].segments.push({ color:'#00e6e5', points:[ [35.779297,-105.799478], [35.779257,-105.79963] ] });
				trk[t].segments.push({ color:'#00e6ad', points:[ [35.779257,-105.79963], [35.779219,-105.799719] ] });
				trk[t].segments.push({ color:'#00e626', points:[ [35.779219,-105.799719], [35.779131,-105.799867] ] });
				trk[t].segments.push({ color:'#00e61d', points:[ [35.779131,-105.799867], [35.779019,-105.800205] ] });
				trk[t].segments.push({ color:'#00e62a', points:[ [35.779019,-105.800205], [35.778945,-105.800588] ] });
				trk[t].segments.push({ color:'#92e600', points:[ [35.778945,-105.800588], [35.778788,-105.800878] ] });
				trk[t].segments.push({ color:'#00b8e6', points:[ [35.778788,-105.800878], [35.778884,-105.801169] ] });
				trk[t].segments.push({ color:'#52e600', points:[ [35.778884,-105.801169], [35.778827,-105.801402] ] });
				trk[t].segments.push({ color:'#26e600', points:[ [35.778827,-105.801402], [35.7787,-105.801627] ] });
				trk[t].segments.push({ color:'#00e6e2', points:[ [35.7787,-105.801627], [35.778625,-105.801795] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.778625,-105.801795], [35.778515,-105.801896] ] });
				trk[t].segments.push({ color:'#00d4e6', points:[ [35.778515,-105.801896], [35.778413,-105.802013] ] });
				trk[t].segments.push({ color:'#00e6ce', points:[ [35.778413,-105.802013], [35.778324,-105.802121] ] });
				trk[t].segments.push({ color:'#00e6d0', points:[ [35.778324,-105.802121], [35.778215,-105.802265] ] });
				trk[t].segments.push({ color:'#00e6e2', points:[ [35.778215,-105.802265], [35.778088,-105.802469] ] });
				trk[t].segments.push({ color:'#00e692', points:[ [35.778088,-105.802469], [35.77798,-105.802598] ] });
				trk[t].segments.push({ color:'#00e6a9', points:[ [35.77798,-105.802598], [35.777902,-105.802736] ] });
				trk[t].segments.push({ color:'#00a8e6', points:[ [35.777902,-105.802736], [35.777827,-105.803071] ] });
				trk[t].segments.push({ color:'#00e634', points:[ [35.777827,-105.803071], [35.777731,-105.803207] ] });
				trk[t].segments.push({ color:'#79e600', points:[ [35.777731,-105.803207], [35.777562,-105.803264] ] });
				trk[t].segments.push({ color:'#00e64d', points:[ [35.777562,-105.803264], [35.777368,-105.803416] ] });
				trk[t].segments.push({ color:'#00e67b', points:[ [35.777368,-105.803416], [35.777206,-105.803494] ] });
				trk[t].segments.push({ color:'#00e61d', points:[ [35.777206,-105.803494], [35.777016,-105.80352] ] });
				trk[t].segments.push({ color:'#00e6c9', points:[ [35.777016,-105.80352], [35.776889,-105.803611] ] });
				trk[t].segments.push({ color:'#00e6a6', points:[ [35.776889,-105.803611], [35.776773,-105.803685] ] });
				trk[t].segments.push({ color:'#00e6a6', points:[ [35.776773,-105.803685], [35.776641,-105.803799] ] });
				trk[t].segments.push({ color:'#009fe6', points:[ [35.776641,-105.803799], [35.776525,-105.80395] ] });
				trk[t].segments.push({ color:'#01e600', points:[ [35.776525,-105.80395], [35.77634,-105.804089] ] });
				trk[t].segments.push({ color:'#11e600', points:[ [35.77634,-105.804089], [35.776199,-105.804171] ] });
				trk[t].segments.push({ color:'#00e641', points:[ [35.776199,-105.804171], [35.776064,-105.804299] ] });
				trk[t].segments.push({ color:'#00e611', points:[ [35.776064,-105.804299], [35.77591,-105.804439] ] });
				trk[t].segments.push({ color:'#00e6bb', points:[ [35.77591,-105.804439], [35.775779,-105.804653] ] });
				trk[t].segments.push({ color:'#00cde6', points:[ [35.775779,-105.804653], [35.775693,-105.804815] ] });
				trk[t].segments.push({ color:'#00bde6', points:[ [35.775693,-105.804815], [35.775611,-105.80496] ] });
				trk[t].segments.push({ color:'#00dbe6', points:[ [35.775611,-105.80496], [35.775492,-105.805142] ] });
				trk[t].segments.push({ color:'#00cfe6', points:[ [35.775492,-105.805142], [35.775405,-105.805287] ] });
				trk[t].segments.push({ color:'#00e6d7', points:[ [35.775405,-105.805287], [35.775303,-105.80543] ] });
				trk[t].segments.push({ color:'#00e674', points:[ [35.775303,-105.80543], [35.775147,-105.805575] ] });
				trk[t].segments.push({ color:'#01e600', points:[ [35.775147,-105.805575], [35.775019,-105.805673] ] });
				trk[t].segments.push({ color:'#00e654', points:[ [35.775019,-105.805673], [35.774844,-105.805879] ] });
				trk[t].segments.push({ color:'#67e600', points:[ [35.774844,-105.805879], [35.77471,-105.805932] ] });
				trk[t].segments.push({ color:'#00e60a', points:[ [35.77471,-105.805932], [35.774566,-105.80603] ] });
				trk[t].segments.push({ color:'#00e6b9', points:[ [35.774566,-105.80603], [35.774455,-105.80617] ] });
				trk[t].segments.push({ color:'#00e6c2', points:[ [35.774455,-105.80617], [35.774358,-105.806308] ] });
				trk[t].segments.push({ color:'#007ae6', points:[ [35.774358,-105.806308], [35.774286,-105.806573] ] });
				trk[t].segments.push({ color:'#0051e6', points:[ [35.774286,-105.806573], [35.774365,-105.80679] ] });
				trk[t].segments.push({ color:'#00e67d', points:[ [35.774365,-105.80679], [35.774499,-105.806895] ] });
				trk[t].segments.push({ color:'#56e600', points:[ [35.774499,-105.806895], [35.774622,-105.807076] ] });
				trk[t].segments.push({ color:'#00e6b0', points:[ [35.774622,-105.807076], [35.774686,-105.807366] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.774686,-105.807366], [35.774728,-105.807597] ] });
				trk[t].segments.push({ color:'#00e658', points:[ [35.774728,-105.807597], [35.774746,-105.80773] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.774746,-105.80773], [35.774817,-105.807948] ] });
				trk[t].segments.push({ color:'#00d6e6', points:[ [35.774817,-105.807948], [35.774975,-105.808081] ] });
				trk[t].segments.push({ color:'#00e689', points:[ [35.774975,-105.808081], [35.775112,-105.808158] ] });
				trk[t].segments.push({ color:'#00e699', points:[ [35.775112,-105.808158], [35.775347,-105.808296] ] });
				trk[t].segments.push({ color:'#00e6a2', points:[ [35.775347,-105.808296], [35.775528,-105.808416] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.775528,-105.808416], [35.775689,-105.808515] ] });
				trk[t].segments.push({ color:'#00b8e6', points:[ [35.775689,-105.808515], [35.77589,-105.808616] ] });
				trk[t].segments.push({ color:'#00e60a', points:[ [35.77589,-105.808616], [35.775965,-105.808725] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.775965,-105.808725], [35.776004,-105.808933] ] });
				trk[t].segments.push({ color:'#00cde6', points:[ [35.776004,-105.808933], [35.776019,-105.809142] ] });
				trk[t].segments.push({ color:'#0071e6', points:[ [35.776019,-105.809142], [35.776055,-105.809341] ] });
				trk[t].segments.push({ color:'#00e69b', points:[ [35.776055,-105.809341], [35.776077,-105.809468] ] });
				trk[t].segments.push({ color:'#00e65d', points:[ [35.776077,-105.809468], [35.776093,-105.809602] ] });
				trk[t].segments.push({ color:'#00e6c9', points:[ [35.776093,-105.809602], [35.776178,-105.809731] ] });
				trk[t].segments.push({ color:'#00e6e5', points:[ [35.776178,-105.809731], [35.776296,-105.809864] ] });
				trk[t].segments.push({ color:'#00e6d7', points:[ [35.776296,-105.809864], [35.776438,-105.809914] ] });
				trk[t].segments.push({ color:'#00e65d', points:[ [35.776438,-105.809914], [35.776527,-105.80997] ] });
				trk[t].segments.push({ color:'#00e686', points:[ [35.776527,-105.80997], [35.776767,-105.810049] ] });
				trk[t].segments.push({ color:'#00e6b9', points:[ [35.776767,-105.810049], [35.776954,-105.810156] ] });
				GV_Draw_Track(t);
				
				// Track #2
				t = 2; trk[t] = {info:[],segments:[]};
				trk[t].info.name = 'AlamoAspenVistaLoopProposed'; trk[t].info.desc = 'Approx length = 5 miles'; trk[t].info.clickable = true;
				trk[t].info.color = '#e60000'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#e6e600'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ color:'#6200e6', points:[ [35.777233,-105.809669], [35.776746,-105.808468] ] });
				trk[t].segments.push({ color:'#7300e6', points:[ [35.776746,-105.808468], [35.777164,-105.805893] ] });
				trk[t].segments.push({ color:'#0063e6', points:[ [35.777164,-105.805893], [35.780471,-105.802846] ] });
				trk[t].segments.push({ color:'#2b00e6', points:[ [35.780471,-105.802846], [35.78357,-105.799241] ] });
				trk[t].segments.push({ color:'#ba00e6', points:[ [35.78357,-105.799241], [35.785689,-105.796366] ] });
				trk[t].segments.push({ color:'#00e676', points:[ [35.785689,-105.796366], [35.785679,-105.796312] ] });
				trk[t].segments.push({ color:'#00e68b', points:[ [35.785679,-105.796312], [35.785631,-105.795475] ] });
				trk[t].segments.push({ color:'#008ae6', points:[ [35.785631,-105.795475], [35.785553,-105.794724] ] });
				trk[t].segments.push({ color:'#3b00e6', points:[ [35.785553,-105.794724], [35.785501,-105.794311] ] });
				trk[t].segments.push({ color:'#1000e6', points:[ [35.785501,-105.794311], [35.785331,-105.793716] ] });
				trk[t].segments.push({ color:'#6e00e6', points:[ [35.785331,-105.793716], [35.785074,-105.79311] ] });
				trk[t].segments.push({ color:'#008ae6', points:[ [35.785074,-105.79311], [35.784656,-105.792353] ] });
				trk[t].segments.push({ color:'#80e600', points:[ [35.784656,-105.792353], [35.784143,-105.791613] ] });
				trk[t].segments.push({ color:'#00e6b7', points:[ [35.784143,-105.791613], [35.784144,-105.791547] ] });
				trk[t].segments.push({ color:'#c7e600', points:[ [35.784144,-105.791547], [35.784085,-105.791461] ] });
				trk[t].segments.push({ color:'#e6d800', points:[ [35.784085,-105.791461], [35.784018,-105.791384] ] });
				trk[t].segments.push({ color:'#60e600', points:[ [35.784018,-105.791384], [35.783957,-105.791288] ] });
				trk[t].segments.push({ color:'#5be600', points:[ [35.783957,-105.791288], [35.783869,-105.791214] ] });
				trk[t].segments.push({ color:'#00bbe6', points:[ [35.783869,-105.791214], [35.78382,-105.79112] ] });
				trk[t].segments.push({ color:'#e63c00', points:[ [35.78382,-105.79112], [35.78374,-105.791177] ] });
				trk[t].segments.push({ color:'#e6a100', points:[ [35.78374,-105.791177], [35.783653,-105.791243] ] });
				trk[t].segments.push({ color:'#00e654', points:[ [35.783653,-105.791243], [35.783574,-105.791186] ] });
				trk[t].segments.push({ color:'#e6cd00', points:[ [35.783574,-105.791186], [35.783529,-105.791288] ] });
				trk[t].segments.push({ color:'#e3e600', points:[ [35.783529,-105.791288], [35.783437,-105.791299] ] });
				trk[t].segments.push({ color:'#1fe600', points:[ [35.783437,-105.791299], [35.783354,-105.791251] ] });
				trk[t].segments.push({ color:'#e6b300', points:[ [35.783354,-105.791251], [35.783263,-105.791259] ] });
				trk[t].segments.push({ color:'#00e662', points:[ [35.783263,-105.791259], [35.783188,-105.791188] ] });
				trk[t].segments.push({ color:'#e69a00', points:[ [35.783188,-105.791188], [35.783156,-105.791297] ] });
				trk[t].segments.push({ color:'#e66100', points:[ [35.783156,-105.791297], [35.78308,-105.791361] ] });
				trk[t].segments.push({ color:'#e69a00', points:[ [35.78308,-105.791361], [35.783017,-105.791467] ] });
				trk[t].segments.push({ color:'#e6bf00', points:[ [35.783017,-105.791467], [35.782957,-105.791558] ] });
				trk[t].segments.push({ color:'#e68100', points:[ [35.782957,-105.791558], [35.782868,-105.791609] ] });
				trk[t].segments.push({ color:'#e66300', points:[ [35.782868,-105.791609], [35.78278,-105.791631] ] });
				trk[t].segments.push({ color:'#e67100', points:[ [35.78278,-105.791631], [35.782693,-105.791663] ] });
				trk[t].segments.push({ color:'#b2e600', points:[ [35.782693,-105.791663], [35.782635,-105.791767] ] });
				trk[t].segments.push({ color:'#e62200', points:[ [35.782635,-105.791767], [35.782532,-105.79182] ] });
				trk[t].segments.push({ color:'#e62700', points:[ [35.782532,-105.79182], [35.782445,-105.791864] ] });
				trk[t].segments.push({ color:'#e68500', points:[ [35.782445,-105.791864], [35.78237,-105.791948] ] });
				trk[t].segments.push({ color:'#74e600', points:[ [35.78237,-105.791948], [35.782322,-105.792042] ] });
				trk[t].segments.push({ color:'#a9e600', points:[ [35.782322,-105.792042], [35.782263,-105.792126] ] });
				trk[t].segments.push({ color:'#e6a100', points:[ [35.782263,-105.792126], [35.782175,-105.792153] ] });
				trk[t].segments.push({ color:'#e69500', points:[ [35.782175,-105.792153], [35.782087,-105.792193] ] });
				trk[t].segments.push({ color:'#00e662', points:[ [35.782087,-105.792193], [35.781981,-105.792184] ] });
				trk[t].segments.push({ color:'#9ee600', points:[ [35.781981,-105.792184], [35.781902,-105.792247] ] });
				trk[t].segments.push({ color:'#e60b00', points:[ [35.781902,-105.792247], [35.781851,-105.792353] ] });
				trk[t].segments.push({ color:'#06e600', points:[ [35.781851,-105.792353], [35.781797,-105.792448] ] });
				trk[t].segments.push({ color:'#00e65b', points:[ [35.781797,-105.792448], [35.781706,-105.792534] ] });
				trk[t].segments.push({ color:'#a9e600', points:[ [35.781706,-105.792534], [35.781658,-105.792632] ] });
				trk[t].segments.push({ color:'#82e600', points:[ [35.781658,-105.792632], [35.781612,-105.792729] ] });
				trk[t].segments.push({ color:'#e6a800', points:[ [35.781612,-105.792729], [35.781549,-105.792814] ] });
				trk[t].segments.push({ color:'#04e600', points:[ [35.781549,-105.792814], [35.781471,-105.792886] ] });
				trk[t].segments.push({ color:'#00e606', points:[ [35.781471,-105.792886], [35.781456,-105.793011] ] });
				trk[t].segments.push({ color:'#b9e600', points:[ [35.781456,-105.793011], [35.781389,-105.793095] ] });
				trk[t].segments.push({ color:'#e60000', points:[ [35.781389,-105.793095], [35.781314,-105.793162] ] });
				trk[t].segments.push({ color:'#e65a00', points:[ [35.781314,-105.793162], [35.781278,-105.793266] ] });
				trk[t].segments.push({ color:'#dce600', points:[ [35.781278,-105.793266], [35.781237,-105.793371] ] });
				trk[t].segments.push({ color:'#39e600', points:[ [35.781237,-105.793371], [35.781237,-105.793498] ] });
				trk[t].segments.push({ color:'#1de600', points:[ [35.781237,-105.793498], [35.781203,-105.793612] ] });
				trk[t].segments.push({ color:'#e6d800', points:[ [35.781203,-105.793612], [35.781132,-105.793708] ] });
				trk[t].segments.push({ color:'#b9e600', points:[ [35.781132,-105.793708], [35.781089,-105.793813] ] });
				trk[t].segments.push({ color:'#7be600', points:[ [35.781089,-105.793813], [35.781042,-105.79391] ] });
				trk[t].segments.push({ color:'#56e600', points:[ [35.781042,-105.79391], [35.781005,-105.794012] ] });
				trk[t].segments.push({ color:'#24e600', points:[ [35.781005,-105.794012], [35.780948,-105.794102] ] });
				trk[t].segments.push({ color:'#00e63f', points:[ [35.780948,-105.794102], [35.780924,-105.794217] ] });
				trk[t].segments.push({ color:'#00e601', points:[ [35.780924,-105.794217], [35.780859,-105.794303] ] });
				trk[t].segments.push({ color:'#00e62f', points:[ [35.780859,-105.794303], [35.780847,-105.794414] ] });
				trk[t].segments.push({ color:'#2fe600', points:[ [35.780847,-105.794414], [35.780803,-105.794517] ] });
				trk[t].segments.push({ color:'#3de600', points:[ [35.780803,-105.794517], [35.780771,-105.794627] ] });
				trk[t].segments.push({ color:'#54e600', points:[ [35.780771,-105.794627], [35.780733,-105.794729] ] });
				trk[t].segments.push({ color:'#1be600', points:[ [35.780733,-105.794729], [35.78067,-105.794873] ] });
				trk[t].segments.push({ color:'#00e684', points:[ [35.78067,-105.794873], [35.780656,-105.794993] ] });
				trk[t].segments.push({ color:'#00e638', points:[ [35.780656,-105.794993], [35.780587,-105.795109] ] });
				trk[t].segments.push({ color:'#44e600', points:[ [35.780587,-105.795109], [35.780539,-105.795208] ] });
				trk[t].segments.push({ color:'#32e600', points:[ [35.780539,-105.795208], [35.780534,-105.795333] ] });
				trk[t].segments.push({ color:'#34e600', points:[ [35.780534,-105.795333], [35.780526,-105.795449] ] });
				trk[t].segments.push({ color:'#a5e600', points:[ [35.780526,-105.795449], [35.780484,-105.795555] ] });
				trk[t].segments.push({ color:'#3de600', points:[ [35.780484,-105.795555], [35.780467,-105.795667] ] });
				trk[t].segments.push({ color:'#00e648', points:[ [35.780467,-105.795667], [35.780468,-105.795791] ] });
				trk[t].segments.push({ color:'#00e644', points:[ [35.780468,-105.795791], [35.780467,-105.795926] ] });
				trk[t].segments.push({ color:'#e6c100', points:[ [35.780467,-105.795926], [35.780394,-105.795998] ] });
				trk[t].segments.push({ color:'#70e600', points:[ [35.780394,-105.795998], [35.780348,-105.796094] ] });
				trk[t].segments.push({ color:'#00e636', points:[ [35.780348,-105.796094], [35.780363,-105.796209] ] });
				trk[t].segments.push({ color:'#00e623', points:[ [35.780363,-105.796209], [35.78037,-105.796345] ] });
				trk[t].segments.push({ color:'#00e60c', points:[ [35.78037,-105.796345], [35.78037,-105.796491] ] });
				trk[t].segments.push({ color:'#6de600', points:[ [35.78037,-105.796491], [35.780345,-105.796601] ] });
				trk[t].segments.push({ color:'#4500e6', points:[ [35.780345,-105.796601], [35.780421,-105.796682] ] });
				trk[t].segments.push({ color:'#3be600', points:[ [35.780421,-105.796682], [35.780406,-105.796795] ] });
				trk[t].segments.push({ color:'#e69c00', points:[ [35.780406,-105.796795], [35.78031,-105.796838] ] });
				trk[t].segments.push({ color:'#c9e600', points:[ [35.78031,-105.796838], [35.780257,-105.796933] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.780257,-105.796933], [35.780272,-105.79705] ] });
				trk[t].segments.push({ color:'#00e6c2', points:[ [35.780272,-105.79705], [35.780272,-105.797161] ] });
				trk[t].segments.push({ color:'#00e603', points:[ [35.780272,-105.797161], [35.780236,-105.797271] ] });
				trk[t].segments.push({ color:'#8ee600', points:[ [35.780236,-105.797271], [35.780178,-105.797368] ] });
				trk[t].segments.push({ color:'#5de600', points:[ [35.780178,-105.797368], [35.780093,-105.797433] ] });
				trk[t].segments.push({ color:'#00e60f', points:[ [35.780093,-105.797433], [35.780018,-105.797509] ] });
				trk[t].segments.push({ color:'#44e600', points:[ [35.780018,-105.797509], [35.779947,-105.797577] ] });
				trk[t].segments.push({ color:'#90e600', points:[ [35.779947,-105.797577], [35.779854,-105.797635] ] });
				trk[t].segments.push({ color:'#00dde6', points:[ [35.779854,-105.797635], [35.779831,-105.797745] ] });
				trk[t].segments.push({ color:'#0081e6', points:[ [35.779831,-105.797745], [35.779863,-105.797867] ] });
				trk[t].segments.push({ color:'#00e631', points:[ [35.779863,-105.797867], [35.779809,-105.797962] ] });
				trk[t].segments.push({ color:'#00e648', points:[ [35.779809,-105.797962], [35.779761,-105.798059] ] });
				trk[t].segments.push({ color:'#00e62a', points:[ [35.779761,-105.798059], [35.779713,-105.79816] ] });
				trk[t].segments.push({ color:'#79e600', points:[ [35.779713,-105.79816], [35.779647,-105.79825] ] });
				trk[t].segments.push({ color:'#00e64b', points:[ [35.779647,-105.79825], [35.779624,-105.798368] ] });
				trk[t].segments.push({ color:'#00e682', points:[ [35.779624,-105.798368], [35.779614,-105.798491] ] });
				trk[t].segments.push({ color:'#00e6b0', points:[ [35.779614,-105.798491], [35.779627,-105.798618] ] });
				trk[t].segments.push({ color:'#00e67b', points:[ [35.779627,-105.798618], [35.779631,-105.798746] ] });
				trk[t].segments.push({ color:'#e6d600', points:[ [35.779631,-105.798746], [35.779601,-105.798855] ] });
				trk[t].segments.push({ color:'#e6ba00', points:[ [35.779601,-105.798855], [35.779546,-105.798944] ] });
				trk[t].segments.push({ color:'#b9e600', points:[ [35.779546,-105.798944], [35.779502,-105.799054] ] });
				trk[t].segments.push({ color:'#92e600', points:[ [35.779502,-105.799054], [35.779474,-105.79916] ] });
				trk[t].segments.push({ color:'#00e62a', points:[ [35.779474,-105.79916], [35.7795,-105.799277] ] });
				trk[t].segments.push({ color:'#00e69b', points:[ [35.7795,-105.799277], [35.779483,-105.7994] ] });
				trk[t].segments.push({ color:'#00e6d9', points:[ [35.779483,-105.7994], [35.779463,-105.799525] ] });
				trk[t].segments.push({ color:'#00e654', points:[ [35.779463,-105.799525], [35.779417,-105.799631] ] });
				trk[t].segments.push({ color:'#16e600', points:[ [35.779417,-105.799631], [35.779346,-105.799731] ] });
				trk[t].segments.push({ color:'#04e600', points:[ [35.779346,-105.799731], [35.779267,-105.799822] ] });
				trk[t].segments.push({ color:'#00d4e6', points:[ [35.779267,-105.799822], [35.779256,-105.799941] ] });
				trk[t].segments.push({ color:'#00e62d', points:[ [35.779256,-105.799941], [35.779202,-105.800037] ] });
				trk[t].segments.push({ color:'#00e6a9', points:[ [35.779202,-105.800037], [35.779187,-105.800147] ] });
				trk[t].segments.push({ color:'#00e6b2', points:[ [35.779187,-105.800147], [35.779181,-105.800278] ] });
				trk[t].segments.push({ color:'#00e638', points:[ [35.779181,-105.800278], [35.779154,-105.800394] ] });
				trk[t].segments.push({ color:'#32e600', points:[ [35.779154,-105.800394], [35.779121,-105.800503] ] });
				trk[t].segments.push({ color:'#99e600', points:[ [35.779121,-105.800503], [35.779074,-105.800602] ] });
				trk[t].segments.push({ color:'#99e600', points:[ [35.779074,-105.800602], [35.779029,-105.800704] ] });
				trk[t].segments.push({ color:'#00e6a4', points:[ [35.779029,-105.800704], [35.779044,-105.800839] ] });
				trk[t].segments.push({ color:'#00e6e2', points:[ [35.779044,-105.800839], [35.779072,-105.800967] ] });
				trk[t].segments.push({ color:'#00e63d', points:[ [35.779072,-105.800967], [35.779068,-105.801092] ] });
				trk[t].segments.push({ color:'#0be600', points:[ [35.779068,-105.801092], [35.779049,-105.801202] ] });
				trk[t].segments.push({ color:'#00e606', points:[ [35.779049,-105.801202], [35.779026,-105.801311] ] });
				trk[t].segments.push({ color:'#d5e600', points:[ [35.779026,-105.801311], [35.778952,-105.801387] ] });
				trk[t].segments.push({ color:'#1be600', points:[ [35.778952,-105.801387], [35.778708,-105.801773] ] });
				trk[t].segments.push({ color:'#00e6db', points:[ [35.778708,-105.801773], [35.777986,-105.802824] ] });
				trk[t].segments.push({ color:'#00e6d9', points:[ [35.777986,-105.802824], [35.777846,-105.8032] ] });
				trk[t].segments.push({ color:'#00e631', points:[ [35.777846,-105.8032], [35.776706,-105.803747] ] });
				trk[t].segments.push({ color:'#00e674', points:[ [35.776706,-105.803747], [35.775923,-105.804563] ] });
				trk[t].segments.push({ color:'#00d6e6', points:[ [35.775923,-105.804563], [35.77567,-105.805013] ] });
				trk[t].segments.push({ color:'#00e69b', points:[ [35.77567,-105.805013], [35.775035,-105.805764] ] });
				trk[t].segments.push({ color:'#00e65b', points:[ [35.775035,-105.805764], [35.774286,-105.806558] ] });
				trk[t].segments.push({ color:'#009de6', points:[ [35.774286,-105.806558], [35.774548,-105.806859] ] });
				trk[t].segments.push({ color:'#00e644', points:[ [35.774548,-105.806859], [35.774722,-105.807492] ] });
				trk[t].segments.push({ color:'#00e6a0', points:[ [35.774722,-105.807492], [35.774861,-105.807996] ] });
				trk[t].segments.push({ color:'#00e6bb', points:[ [35.774861,-105.807996], [35.776027,-105.808736] ] });
				trk[t].segments.push({ color:'#00e6b4', points:[ [35.776027,-105.808736], [35.776114,-105.809637] ] });
				trk[t].segments.push({ color:'#00e6b7', points:[ [35.776114,-105.809637], [35.777194,-105.810421] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				t = 2; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:35.7774393,lon:-105.8058974,name:'0.3 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:45.8,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7812265,lon:-105.8034976,name:'0.6 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:30.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7841817,lon:-105.7997988,name:'0.9 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:68.1,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7857342,lon:-105.7954863,name:'1.2 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:97.9,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7839303,lon:-105.7911580,name:'1.5 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:182.1,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7809009,lon:-105.7944239,name:'1.8 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:242.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7794065,lon:-105.7991932,name:'2.1 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:247.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7772720,lon:-105.8034622,name:'2.4 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:201.3,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7744585,lon:-105.8068633,name:'2.7 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',rotation:327.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7769540,lon:-105.8101560,name:'2.98 mi',desc:trk[1].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'Alamo Aspen VIsta [tickmarks]',track_number:1,dd:false});
				GV_Draw_Marker({lat:35.7780585,lon:-105.8050688,name:'0.3 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:36.8,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7814396,lon:-105.8017193,name:'0.6 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:43.3,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7845198,lon:-105.7979523,name:'0.9 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:47.7,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7851424,lon:-105.7932712,name:'1.2 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:117.6,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7821448,lon:-105.7921667,name:'1.5 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:200.2,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7803743,lon:-105.7966323,name:'1.8 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:319.2,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7790333,lon:-105.8012766,name:'2.1 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:255.4,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7758552,lon:-105.8046837,name:'2.4 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:235.3,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7755864,lon:-105.8084564,name:'2.7 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:332.8,track_number:2,dd:false});
				GV_Draw_Marker({lat:35.7771940,lon:-105.8104210,name:'2.87 mi',desc:trk[2].info.name,color:'white',icon:'tickmark',type:'tickmark',folder:'AlamoAspenVistaLoopProposed [tickmarks]',rotation:329.5,track_number:2,dd:false});
				
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?allow_export=1&form=google&google_api_key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&google_street_view=1&google_trk_mouseover=1&tickmark_interval=.3%20mi&trk_colorize=slope&trk_hue=60&trk_stats=1&units=us&wpt_driving_directions=1&add_elevation=NED1
		</script>
		
		
		
	</body>

</html>
