<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Map</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="35.7421949; -105.8605195" />
		<meta name="ICBM" content="35.7421949, -105.8605195" />
	</head>
	<body style="margin:0px;">
		<script type="text/javascript">
			google_api_key = 'AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU'; // Your project's Google Maps API key goes here (https://code.google.com/apis/console)
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
			<div id="gmap_div" style="width:700px; height:700px; margin:0px; margin-right:12px; background-color:#f0f0f0; float:left; overflow:hidden;">
				<p align="center" style="font:10px Arial;">This map was created using <a target="_blank" href="http://www.gpsvisualizer.com/">GPS Visualizer</a>'s do-it-yourself geographic utilities.<br /><br />Please wait while the map data loads...</p>
			</div>
				
			<div id="gv_infobox" class="gv_infobox" style="font:11px Arial; border:solid #666666 1px; background-color:#ffffff; padding:4px; overflow:auto; display:none; max-width:400px;">
				<!-- Although GPS Visualizer didn't create an legend/info box with your map, you can use this space for something else if you'd like; enable it by setting gv_options.infobox_options.enabled to true -->
			<?php
				include 'map_geoloc.php';
			?>			
			</div>


			<div id="gv_tracklist" class="gv_tracklist" style="font:11px Arial; line-height:11px; background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_marker_list" class="gv_marker_list" style="background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_clear_margins" style="height:0px; clear:both;"><!-- clear the "float" --></div>
		</div>

		
		<!-- begin GPS Visualizer setup script (must come after maps.google.com code) -->
		<script type="text/javascript">
			/* Global variables used by the GPS Visualizer functions (20160625180032): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [35.7421949040145,-105.860519525595];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 14;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
			gv_options.map_type = 'GV_HYBRID';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_TOPO_US', 'GV_TOPO_WORLD', 'GV_OSM' (http://www.gpsvisualizer.com/misc/google_map_types.html)
			gv_options.map_opacity = 1.00;  // number from 0 to 1
			gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?
			gv_options.width = 700;  // width of the map, in pixels
			gv_options.height = 700;  // height of the map, in pixels
			
			gv_options.map_div = 'gmap_div';  // the name of the HTML "div" tag containing the map itself; usually 'gmap_div'
			gv_options.doubleclick_zoom = true;  // true|false: zoom in when mouse is double-clicked?
			gv_options.doubleclick_center = true;  // true|false: re-center the map on the point that was double-clicked?
			gv_options.mousewheel_zoom = true; // true|false; or 'reverse' for down=in and up=out
			gv_options.autozoom_adjustment = 0;
			gv_options.centering_options = { 'open_info_window':true, 'partial_match':true, 'center_key':'center', 'default_zoom':null } // URL-based centering (e.g., ?center=name_of_marker&zoom=14)
			gv_options.tilt = false; // true|false: allow Google to show 45-degree tilted aerial imagery?
			gv_options.street_view = false; // true|false: allow Google Street View on the map
			gv_options.animated_zoom = false; // true|false: may or may not work properly
			gv_options.disable_google_pois = false;  // true|false: if you disable clickable POIs, you also lose the labels on parks, airports, etc.
				
			// widgets on the map:
			gv_options.zoom_control = 'large'; // 'large'|'small'|'none'
			gv_options.recenter_button = true; // true|false: is there a 'double-click to recenter' option in the zoom control?
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
			  gv_options.infobox_options.position = ['LEFT_TOP',76,6];  // [Google anchor name, relative x, relative y]
			  gv_options.infobox_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.infobox_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			gv_options.utilities_menu = true;  // true|false
			gv_options.allow_export = false;  // true|false

			// track-related options:
			gv_options.track_tooltips = false; // true|false: should the name of a track appear on the map when you mouse over the track itself?
			gv_options.tracklist_options = {}; // options for a floating list of the tracks visible on the map
			  gv_options.tracklist_options.enabled = false;  // true|false: enable or disable the tracklist altogether
			  gv_options.tracklist_options.position = ['RIGHT_TOP',4,32];  // [Google anchor name, relative x, relative y]
			  gv_options.tracklist_options.min_width = 100; // minimum width of the tracklist, in pixels
			  gv_options.tracklist_options.max_width = 180; // maximum width of the tracklist, in pixels
			  gv_options.tracklist_options.min_height = 0; // minimum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.max_height = 310; // maximum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.desc = false;  // true|false: should tracks' descriptions be shown in the list
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
			gv_options.default_marker = { color:'orange',icon:'googlemini',scale:1 }; // icon can be a URL, but be sure to also include size:[w,h] and optionally anchor:[x,y]
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
			gv_options.driving_directions = false;  // put a small "driving directions" form in each marker's pop-up window? (override with dd:true or dd:false in a marker's options)
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
			  gv_options.marker_list_options.wrap_names = true;  // true|false: should marker's names be allowed to wrap onto more than one line?
			  gv_options.marker_list_options.unnamed = '[unnamed]';  // what 'name' should be assigned to  unnamed markers in the list?
			  gv_options.marker_list_options.colors = false;  // true|false: should the names/descs of the points in the list be colorized the same as their markers?
			  gv_options.marker_list_options.default_color = '';  // default HTML color code for the names/descs in the list
			  gv_options.marker_list_options.limit = 0;  // how many markers to show in the list; 0 for no limit
			  gv_options.marker_list_options.center = false;  // true|false: does the map center upon a marker when you click its name in the list?
			  gv_options.marker_list_options.zoom = false;  // true|false: does the map zoom to a certain level when you click on a marker's name in the list?
			  gv_options.marker_list_options.zoom_level = 16;  // if 'zoom' is true, what level should the map zoom to?
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
	
			// Reset certain map options based on url parameters
			<?php
				include 'map_gv_options1.php';
			?>			
			
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
				background-color:#333333; border:1px solid black; padding:1px;
				color:white; font:9px Verdana,sans-serif !important; font-weight:normal !important;
				opacity:0.80; filter:alpha(opacity=80);
			}
			
		</style>
		
		<!-- end GPSV setup script and styles; begin map-drawing script (they must be separate) -->
		<script type="text/javascript">
			function GV_Map() {
			  
				GV_Setup_Map();
	
			<?php
				include 'ChamisaSpecificMap.php';
			?>			

			
				
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?form=google&trk_list=0&wpt_color=orange
		</script>
		
		
		
	</body>

</html>
