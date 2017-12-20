<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>20160108_222718 + 20160108_GPSVinput</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="35.735679; -105.8620385" />
		<meta name="ICBM" content="35.735679, -105.8620385" />
	</head>

	<body style="margin:0px;">
		<script type="text/javascript">
			google_api_key = 'AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU'; // Your project's Google Maps API key goes here (https://code.google.com/apis/console)
			language_code = '';
			if (document.location.toString().indexOf('http://www.gpsvisualizer.com') > -1) { google_api_key = ''; }
			document.writeln('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=geometry&amp;language='+self.language_code+'&amp;key='+self.google_api_key+'"><'+'/script>');
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

			<div id='gv_infobox' class='gv_infobox' style='font:11px Arial; border:solid #666666 1px; background-color:#ffffff; padding:4px; overflow:auto; display:none; max-width:400px;'>
				<!-- Although GPS Visualizer didn't create an legend/info box with your map, you can use this space for something else if you'd like; enable it by setting gv_options.infobox_options.enabled to true -->
			<?php if (isset($_GET[show_geoloc]) == false || $_GET[show_geoloc] == "true") {
                echo "
				<p><a href='javascript:GV_Geolocate({marker:true,info_window:true})' style='font-size:12px'>Geolocate me!</a></p>
			";
}?>
			</div>

			<div id="gv_tracklist" class="gv_tracklist" style="font:11px Arial; line-height:11px; background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_marker_list" class="gv_marker_list" style="background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_clear_margins" style="height:0px; clear:both;"><!-- clear the "float" --></div>
		</div>

		
		<!-- begin GPS Visualizer setup script (must come after maps.google.com code) -->
		<script type="text/javascript">
			/* Global variables used by the GPS Visualizer functions (20161204180336): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [35.74163425,-106.085644124157];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 'auto';  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
			gv_options.map_type = 'GV_HYBRID';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_OSM', 'GV_TOPO_US', 'GV_TOPO_WORLD' (http://www.gpsvisualizer.com/misc/google_map_types.html)
			gv_options.map_opacity = 1.00;  // number from 0 to 1
			gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?
			gv_options.width = 700;  // width of the map, in pixels
			gv_options.height = 700;  // height of the map, in pixels
			
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
			  gv_options.infobox_options.position = ['LEFT_TOP',52,6];  // [Google anchor name, relative x, relative y]
			  gv_options.infobox_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.infobox_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			gv_options.utilities_menu = true;  // true|false
			gv_options.allow_export = true;  // true|false

			// track-related options:
			gv_options.track_tooltips = true; // true|false: should the name of a track appear on the map when you mouse over the track itself?
			gv_options.tracklist_options = {}; // options for a floating list of the tracks visible on the map
			  gv_options.tracklist_options.enabled = <?php if (isset($_GET[tracklist_options_enabled])) {
                    echo $_GET[tracklist_options_enabled];

} else {
    echo "true";
}?>;  // true|false: enable or disable the tracklist altogether
			  gv_options.tracklist_options.position = ['RIGHT_TOP',4,32];  // [Google anchor name, relative x, relative y]
			  gv_options.tracklist_options.min_width = 100; // minimum width of the tracklist, in pixels
			  gv_options.tracklist_options.max_width = 180; // maximum width of the tracklist, in pixels
			  gv_options.tracklist_options.min_height = 0; // minimum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.max_height = 310; // maximum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
			  gv_options.tracklist_options.desc = true;  // true|false: should tracks' descriptions be shown in the list
			  gv_options.tracklist_options.toggle = true;  // true|false: should clicking on a track's name turn it on or off?
			  gv_options.tracklist_options.checkboxes = true;  // true|false: should there be a separate icon/checkbox for toggling visibility?
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
			  gv_options.marker_list_options.enabled = <?php if (isset($_GET[marker_list_options_enabled])) {
                    echo $_GET[marker_list_options_enabled];

} else {
    echo "true";
}?>;;  // true|false: enable or disable the marker list altogether
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
		<!-- end GPSV setup script and styles; begin map-drawing script (they must be separate) -->

		<script type="text/javascript">
			function GV_Map() {
			  
				GV_Setup_Map();
				
				// Track #1
				t = 1; trk[t] = {info:[],segments:[]};
				trk[t].info.name = '20160108_222718'; trk[t].info.desc = '<br />[<a target="_blank" href="https://www.endomondo.com/workouts/654308101/7544338">endomondo</a>]'; trk[t].info.clickable = true;
				trk[t].info.color = '#e60000'; trk[t].info.width = 3; trk[t].info.opacity = 0.9;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#e60000'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ points:[ [35.729327,-105.865872],[35.729607,-105.865701],[35.729751,-105.865637],[35.730025,-105.865461],[35.730155,-105.865405],[35.730421,-105.865243],[35.730653,-105.865117],[35.730813,-105.865064],[35.731002,-105.865005],[35.731181,-105.864995],[35.731337,-105.864952],[35.731479,-105.864902],[35.731649,-105.864856],[35.731792,-105.8648],[35.731985,-105.8647],[35.732083,-105.864648],[35.73223,-105.864546],[35.732337,-105.864464],[35.7325,-105.864355],[35.732596,-105.864293],[35.732637,-105.86422],[35.732751,-105.864165],[35.732866,-105.863976],[35.733031,-105.863855],[35.733179,-105.863786],[35.733263,-105.863753],[35.733419,-105.863649],[35.733634,-105.863633],[35.733791,-105.863587],[35.733923,-105.863663],[35.734224,-105.863688],[35.734435,-105.863594],[35.734599,-105.863471],[35.734782,-105.863402],[35.734758,-105.863414],[35.734908,-105.863282],[35.734984,-105.863153],[35.73508,-105.863112],[35.735118,-105.863103],[35.735125,-105.863104],[35.735361,-105.86283],[35.735539,-105.862757],[35.7356,-105.862712],[35.735666,-105.862661],[35.735767,-105.862639],[35.735785,-105.862646],[35.736051,-105.862511],[35.736314,-105.862332],[35.736475,-105.862239],[35.736492,-105.862219],[35.736558,-105.862171],[35.736654,-105.862089],[35.736819,-105.862007],[35.736995,-105.861978],[35.737076,-105.862043],[35.737181,-105.862027],[35.737336,-105.862011],[35.737476,-105.862017],[35.737645,-105.861875],[35.737916,-105.861688],[35.738079,-105.861665],[35.738242,-105.861564],[35.738281,-105.861533],[35.73832,-105.861513],[35.738453,-105.861368],[35.738522,-105.861303],[35.738667,-105.861209],[35.738753,-105.861184],[35.739027,-105.861131],[35.739179,-105.86109],[35.739293,-105.861023],[35.739588,-105.860971],[35.739687,-105.860961],[35.739841,-105.860971],[35.739992,-105.860938],[35.740222,-105.860732],[35.740298,-105.860753],[35.740356,-105.86068],[35.740595,-105.860497],[35.740625,-105.86045],[35.740658,-105.86039],[35.740818,-105.860128],[35.740916,-105.86003],[35.740992,-105.859943],[35.741099,-105.859847],[35.741271,-105.859558],[35.741383,-105.859267],[35.741532,-105.859001],[35.741663,-105.85886],[35.741723,-105.858838],[35.741739,-105.858821],[35.742017,-105.858651],[35.742109,-105.858633],[35.74212,-105.85861],[35.742398,-105.858453],[35.742491,-105.858397],[35.742717,-105.858205],[35.742516,-105.858388],[35.742317,-105.858442],[35.742133,-105.858481],[35.741933,-105.858555],[35.741874,-105.858563],[35.741772,-105.858583],[35.741619,-105.858687],[35.741465,-105.85875],[35.741276,-105.858734],[35.741077,-105.858717],[35.740921,-105.858681],[35.740825,-105.858472],[35.740663,-105.858434],[35.740516,-105.858384],[35.740399,-105.858486],[35.740307,-105.858592],[35.740189,-105.85863],[35.740165,-105.858639],[35.740047,-105.858975],[35.740014,-105.859218],[35.739951,-105.859411],[35.739847,-105.859577],[35.739696,-105.8596],[35.739559,-105.859548],[35.739372,-105.859576],[35.739134,-105.859785],[35.739043,-105.859901],[35.738908,-105.859981],[35.738847,-105.860065],[35.738799,-105.860073],[35.738744,-105.860113],[35.738665,-105.86018],[35.738486,-105.860302],[35.738373,-105.8603],[35.738365,-105.860294],[35.738222,-105.860205],[35.73808,-105.860157],[35.737936,-105.859954],[35.73775,-105.859931],[35.737702,-105.859916],[35.737619,-105.85984],[35.737434,-105.859773],[35.737321,-105.859752],[35.737287,-105.859687],[35.737258,-105.859655],[35.737156,-105.859684],[35.736922,-105.859733],[35.737101,-105.860015],[35.737215,-105.860203],[35.737368,-105.86049],[35.737411,-105.860519],[35.737391,-105.86065],[35.73733,-105.86086],[35.737278,-105.860946],[35.737246,-105.860967],[35.736993,-105.861089],[35.73691,-105.861074],[35.736811,-105.861031],[35.73664,-105.860927],[35.736382,-105.861067],[35.73624,-105.861109],[35.736057,-105.86105],[35.735814,-105.86116],[35.735674,-105.86115],[35.735567,-105.861179],[35.735556,-105.861174],[35.735523,-105.86148],[35.735397,-105.861606],[35.735352,-105.861606],[35.735341,-105.861625],[35.7351,-105.861836],[35.734955,-105.861861],[35.734815,-105.861845],[35.734752,-105.861858],[35.734673,-105.861812],[35.734545,-105.861631],[35.734529,-105.861573],[35.734501,-105.861539],[35.734339,-105.861683],[35.73422,-105.861763],[35.73397,-105.861581],[35.733871,-105.8615],[35.733744,-105.861419],[35.73375,-105.861618],[35.733777,-105.861967],[35.733804,-105.862152],[35.733896,-105.862363],[35.733919,-105.86241],[35.733951,-105.862514],[35.733753,-105.862756],[35.733435,-105.862838],[35.733313,-105.862851],[35.733137,-105.862909],[35.732966,-105.862887],[35.732865,-105.862885],[35.73273,-105.862879],[35.732685,-105.863017],[35.732675,-105.863125],[35.732664,-105.863179],[35.732525,-105.863261],[35.732428,-105.863335],[35.73232,-105.863411],[35.732207,-105.863499],[35.732062,-105.863517],[35.731903,-105.863506],[35.73173,-105.863426],[35.731682,-105.863437],[35.731612,-105.863445],[35.731604,-105.86361],[35.731603,-105.863745],[35.731569,-105.863852],[35.731532,-105.863977],[35.731322,-105.864112],[35.731295,-105.864129],[35.731016,-105.864254],[35.730874,-105.864245],[35.730802,-105.864182],[35.730708,-105.864321],[35.730548,-105.864359],[35.730471,-105.864388],[35.730313,-105.864468],[35.730169,-105.864542],[35.730042,-105.86456],[35.730033,-105.864513],[35.729903,-105.864566],[35.729757,-105.864524],[35.72976,-105.864668],[35.729546,-105.864886],[35.729393,-105.864944],[35.729182,-105.864964],[35.728985,-105.864921],[35.728806,-105.864955],[35.728641,-105.864956],[35.728708,-105.865316],[35.728712,-105.865526],[35.728712,-105.865526] ] }); // track 1 segment 1
				trk[t].segments.push({ points:[ [35.728712,-105.865526] ] }); // track 1 segment 2
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:35.7298050,lon:-105.8656082,name:'Enter description here',desc:'20160108_152823',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23668092873',thumbnail:'https://c2.staticflickr.com/2/1588/23668092873_df8d72aa47_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7291489,lon:-105.8661346,name:'Enter description here',desc:'20160108_153134',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23999522710',thumbnail:'https://c2.staticflickr.com/2/1448/23999522710_9f82c72723_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7325821,lon:-105.8643036,name:'Enter description here',desc:'20160108_153408',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23927070759',thumbnail:'https://c2.staticflickr.com/2/1679/23927070759_ff8bc3efbd_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7347336,lon:-105.8634491,name:'Enter description here',desc:'20160108_153910',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23668081673',thumbnail:'https://c2.staticflickr.com/2/1520/23668081673_da8bb6cde8_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7356033,lon:-105.8627090,name:'Enter description here',desc:'20160108_154140',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23999301150',thumbnail:'https://c2.staticflickr.com/2/1690/23999301150_b585a7e2d8_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7357826,lon:-105.8626480,name:'Enter description here',desc:'20160108_154212',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/24186710662',thumbnail:'https://c2.staticflickr.com/2/1657/24186710662_eba2210c4b_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7291489,lon:-105.8661346,name:'Enter description here',desc:'20160108_154317',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/24295122285',thumbnail:'https://c2.staticflickr.com/2/1526/24295122285_9044519dc8_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7383041,lon:-105.8614807,name:'Enter description here',desc:'20160108_154801',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23666886464',thumbnail:'https://c2.staticflickr.com/2/1562/23666886464_ec00dc6783_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7383041,lon:-105.8614807,name:'Enter description here',desc:'20160108_155001',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23668063533',thumbnail:'https://c2.staticflickr.com/2/1570/23668063533_fea94eca9f_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7426872,lon:-105.8582916,name:'Enter description here',desc:'20160108_160557',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/24186697302',thumbnail:'https://c2.staticflickr.com/2/1533/24186697302_b8117ccab4_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7417488,lon:-105.8585815,name:'Enter description here',desc:'20160108_160804',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/24186691152',thumbnail:'https://c2.staticflickr.com/2/1447/24186691152_9c3ff9a4a2_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7401886,lon:-105.8586273,name:'Enter description here',desc:'20160108_161053',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23999270880',thumbnail:'https://c2.staticflickr.com/2/1480/23999270880_125a2e1760_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7401886,lon:-105.8586273,name:'Enter description here',desc:'20160108_161136',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/24268969826',thumbnail:'https://c2.staticflickr.com/2/1704/24268969826_2e64503e57_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7383652,lon:-105.8602905,name:'Enter description here',desc:'20160108_161629',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/24294869705',thumbnail:'https://c2.staticflickr.com/2/1709/24294869705_661a13b44d_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7337646,lon:-105.8618622,name:'Enter description here',desc:'20160108_163221',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23668036133',thumbnail:'https://c2.staticflickr.com/2/1460/23668036133_4cc22704f7_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7339516,lon:-105.8625641,name:'Enter description here',desc:'20160108_163328',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23668030513',thumbnail:'https://c2.staticflickr.com/2/1512/23668030513_54db386cd9_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7300262,lon:-105.8645096,name:'Enter description here',desc:'20160108_164246',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23999249370',thumbnail:'https://c2.staticflickr.com/2/1480/23999249370_b7f7ca397f_m.jpg',folder:'Folder2'});
				GV_Draw_Marker({lat:35.7287216,lon:-105.8655395,name:'Enter description here',desc:'20160108_164622',color:'',icon:'',url:'https://www.flickr.com/photos/30474783@N06/23666613704',thumbnail:'https://c2.staticflickr.com/2/1516/23666613704_8e7caed9b0_m.jpg',folder:'Folder2'});
				
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?form=google&wpt_list=desc
		</script>
		
		
		
	</body>

</html>
