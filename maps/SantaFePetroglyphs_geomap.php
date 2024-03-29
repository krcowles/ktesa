<?php
require "../php/global_boot.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>laCieneguilla + Santa_Fe_Petroglyphs</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="35.605538; -106.122682" />
		<meta name="ICBM" content="35.605538, -106.122682" />
	</head>
	<body style="margin:0px;">
		<script type="text/javascript">
			google_api_key = '<?=API_KEY;?>'; // Your project's Google Maps API key goes here (https://code.google.com/apis/console)
			language_code = '';
			if (document.location.toString().indexOf('http://www.gpsvisualizer.com') > -1) { google_api_key = ''; }
			document.writeln('<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=geometry&amp;language='+(self.language_code?self.language_code:'')+'&amp;key='+(self.google_api_key?self.google_api_key:'')+'"><'+'/script>');
			
			thunderforest_api_key = ''; // To display OpenStreetMap tiles from ThunderForest, you need a key (http://www.thunderforest.com/docs/apikeys/)
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
			</div>


			<div id="gv_tracklist" class="gv_tracklist" style="font:11px Arial; line-height:11px; background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_marker_list" class="gv_marker_list" style="background-color:#ffffff; overflow:auto; display:none;"><!-- --></div>

			<div id="gv_clear_margins" style="height:0px; clear:both;"><!-- clear the "float" --></div>
		</div>

		
		<!-- begin GPS Visualizer setup script (must come after maps.google.com code) -->
		<script type="text/javascript">
			/* Global variables used by the GPS Visualizer functions (20170312182025): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [35.605538,-106.122682];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 16;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
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
			gv_options.map_opacity_control = false;  // true|false: does it appear on the map itself?
			gv_options.map_type_control = {};  // widget to change the background map
			  gv_options.map_type_control.visible = true;  // true|false: does it appear on the map itself?
			  gv_options.map_type_control.filter = false;  // true|false: when map loads, are irrelevant maps ignored?
			  gv_options.map_type_control.excluded = [];  // comma-separated list of quoted map IDs that will never show in the list ('included' also works)
			gv_options.center_coordinates = true;  // true|false: show a "center coordinates" box and crosshair?
			gv_options.measurement_tools = true;  // true|false: does it appear on the map itself?
			gv_options.measurement_options = { visible:false, distance_color:'', area_color:'' };
			gv_options.crosshair_hidden = true;  // true|false: hide the crosshair initially?
			gv_options.mouse_coordinates = false;  // true|false: show a "mouse coordinates" box?
			gv_options.utilities_menu = { 'maptype':true, 'opacity':true, 'measure':true, 'export':true };
			gv_options.allow_export = true;  // true|false
			
			gv_options.infobox_options = {}; // options for a floating info box (id="gv_infobox"), which can contain anything
			  gv_options.infobox_options.enabled = true;  // true|false: enable or disable the info box altogether
			  gv_options.infobox_options.position = ['LEFT_TOP',52,6];  // [Google anchor name, relative x, relative y]
			  gv_options.infobox_options.draggable = true;  // true|false: can it be moved around the screen?
			  gv_options.infobox_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?
			// track-related options:
			gv_options.track_tooltips = true; // true|false: should the name of a track appear on the map when you mouse over the track itself?
			gv_options.tracklist_options = {}; // options for a floating list of the tracks visible on the map
			  gv_options.tracklist_options.enabled = true;  // true|false: enable or disable the tracklist altogether
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
			gv_options.default_marker = { color:'orange',icon:'googlemini',scale:1 }; // icon can be a URL, but be sure to also include size:[w,h] and optionally anchor:[x,y]
			gv_options.vector_markers = false; // are the icons on the map in embedded SVG format?
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
			  gv_options.marker_list_options.enabled = true;  // true|false: enable or disable the marker list altogether
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
				trk[t].info.name = 'Santa Fe Petroglyphs'; trk[t].info.desc = '<br />[<a target="_blank" href="https://www.endomondo.com/users/7544338/workouts/884902444">Santa Fe Petroglyphs</a>]'; trk[t].info.clickable = true;
				trk[t].info.color = '#e60000'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#e60000'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ points:[ [35.60895,-106.119968],[35.608755,-106.120146],[35.60872,-106.120325],[35.608706,-106.120533],[35.608705,-106.120716],[35.608708,-106.120823],[35.608724,-106.121031],[35.608788,-106.121241],[35.608854,-106.121405],[35.608876,-106.121598],[35.608848,-106.121745],[35.608745,-106.121891],[35.608599,-106.121961],[35.608485,-106.121989],[35.608288,-106.122032],[35.608149,-106.122088],[35.607982,-106.122127],[35.607863,-106.122161],[35.60771,-106.1222],[35.60757,-106.122235],[35.60738,-106.122283],[35.607232,-106.122329],[35.607087,-106.122369],[35.606933,-106.122406],[35.606834,-106.122447],[35.606718,-106.122469],[35.606587,-106.122495],[35.606404,-106.122509],[35.606243,-106.122458],[35.606111,-106.122448],[35.606007,-106.122573],[35.60572,-106.122683],[35.605566,-106.122745],[35.60536,-106.122794],[35.605212,-106.122845],[35.605025,-106.122895],[35.604939,-106.123004],[35.605009,-106.123318],[35.604925,-106.123615],[35.604848,-106.123928],[35.604543,-106.124075],[35.604283,-106.123984],[35.604028,-106.12411],[35.603753,-106.124305],[35.603526,-106.124481],[35.603342,-106.124694],[35.603175,-106.124844],[35.602899,-106.124908],[35.602638,-106.124985],[35.602406,-106.125155],[35.602126,-106.125308],[35.602279,-106.125398],[35.602533,-106.125365],[35.602777,-106.125189],[35.602919,-106.125139],[35.603197,-106.125023],[35.603454,-106.124851],[35.603611,-106.124712],[35.603757,-106.124589],[35.603915,-106.124509],[35.604019,-106.124457],[35.604141,-106.124402],[35.604275,-106.124362],[35.604399,-106.124344],[35.60454,-106.12433],[35.604813,-106.124197],[35.604948,-106.123901],[35.605036,-106.123606],[35.604966,-106.123283],[35.604944,-106.12296],[35.605074,-106.122876],[35.605199,-106.122825],[35.60533,-106.122794],[35.605433,-106.12277],[35.605572,-106.122743],[35.605672,-106.122705],[35.605768,-106.122661],[35.60588,-106.122642],[35.605906,-106.122641],[35.605974,-106.122574],[35.606207,-106.122408],[35.606332,-106.122482],[35.60645,-106.122484],[35.606625,-106.122463],[35.606804,-106.122427],[35.606901,-106.1224],[35.607033,-106.122354],[35.607243,-106.122301],[35.607391,-106.122254],[35.607579,-106.122218],[35.607656,-106.122196],[35.607841,-106.122146],[35.608048,-106.122093],[35.608226,-106.122044],[35.608407,-106.122002],[35.608524,-106.121965],[35.608691,-106.121919],[35.608767,-106.121846],[35.608818,-106.121704],[35.60885,-106.121568],[35.608823,-106.121367],[35.608747,-106.121163],[35.608722,-106.120991],[35.608694,-106.120747],[35.6087,-106.120528],[35.60871,-106.120265],[35.608749,-106.12007],[35.608843,-106.119982],[35.608881,-106.119966],[35.608881,-106.119966] ] });
				trk[t].segments.push({ points:[ [35.608881,-106.119966] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:35.6062028,lon:-106.1224550,name:'0.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Santa Fe Petroglyphs [tickmarks]',rotation:176.5,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6029168,lon:-106.1249039,name:'0.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Santa Fe Petroglyphs [tickmarks]',rotation:190.7,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6050284,lon:-106.1235711,name:'0.9 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Santa Fe Petroglyphs [tickmarks]',rotation:104.9,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6086279,lon:-106.1219364,name:'1.2 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Santa Fe Petroglyphs [tickmarks]',rotation:12.6,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6088810,lon:-106.1199660,name:'1.33 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Santa Fe Petroglyphs [tickmarks]',track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6050028,lon:-106.1238722,name:'Kokopellis abound',desc:'Kokopellis2',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/32559861694/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/4/3882/32559861694_83e9c9e254_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6038778,lon:-106.1242750,name:'Hands and spirals are popular themes',desc:'HandSpiral',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/33019726270/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/3/2810/33019726270_96c485632c_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6039556,lon:-106.1241778,name:'Unknown bird character',desc:'BirdBath',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/33246992072/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/1/643/33246992072_8c5d6c962a_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6035250,lon:-106.1243639,name:'A view from the hills with Kokopelli',desc:'KokopelliVista',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/33246998812/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/1/640/33246998812_5f3283ce9a_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6033889,lon:-106.1246417,name:'Many animals are depicted here',desc:'AnimalKingdom',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/33362145746/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/1/718/33362145746_52fc83ed54_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6034889,lon:-106.1246611,name:'Characters often have odd shapes',desc:'CleftHead',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/33247015722/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/4/3874/33247015722_7feb121671_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6027000,lon:-106.1250083,name:'One of many spiral shapes - with crossbars',desc:'Nautilus',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/32559846204/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/1/650/32559846204_9c35ff9b7a_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.6025972,lon:-106.1250528,name:'Occassionally a saw-tooth style is seen',desc:'OddStyle',color:'orange',icon:'',url:'https://www.flickr.com/photos/139088815@N08/32559886334/in/album-72157681340745155',thumbnail:'https://c1.staticflickr.com/4/3693/32559886334_2aaa3b0c06_q.jpg',folder:'Folder1'});
				
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?allow_export=1&form=google&google_api_key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&google_street_view=1&google_trk_mouseover=1&tickmark_interval=.3%20mi&trk_stats=1&units=us&wpt_color=orange&wpt_driving_directions=1&wpt_list=name&add_elevation=auto
		</script>
		
		
		
	</body>

</html>
