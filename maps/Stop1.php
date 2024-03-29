<?php
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Stop_1 + stop1</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="34.1083253; -106.8229731" />
		<meta name="ICBM" content="34.1083253, -106.8229731" />
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
			<div id="gmap_div" style="width:700px; height:435px; margin:0px; margin-right:12px; background-color:#f0f0f0; float:left; overflow:hidden;">
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
			/* Global variables used by the GPS Visualizer functions (20180405183142): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [34.1083252709359,-106.822973107919];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 17;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
			gv_options.map_type = 'GV_HYBRID';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_OSM', 'GV_TOPO_US', 'GV_TOPO_WORLD' (http://www.gpsvisualizer.com/misc/google_map_types.html)
			gv_options.map_opacity = 1.00;  // number from 0 to 1
			gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?
			gv_options.width = 700;  // width of the map, in pixels
			gv_options.height = 435;  // height of the map, in pixels
			
			gv_options.map_div = 'gmap_div';  // the name of the HTML "div" tag containing the map itself; usually 'gmap_div'
			gv_options.doubleclick_zoom = true;  // true|false: zoom in when mouse is double-clicked?
			gv_options.doubleclick_center = true;  // true|false: re-center the map on the point that was double-clicked?
			gv_options.scroll_zoom = true; // true|false; or 'reverse' for down=in and up=out
			gv_options.page_scrolling = true; // true|false; does the map relenquish control of the scroll wheel when embedded in scrollable pages?
			gv_options.autozoom_adjustment = 0;
			gv_options.centering_options = { 'open_info_window':true, 'partial_match':true, 'center_key':'center', 'default_zoom':null } // URL-based centering (e.g., ?center=name_of_marker&zoom=14)
			gv_options.street_view = true; // true|false: allow Google Street View on the map
			gv_options.tilt = false; // true|false: allow Google to show 45-degree tilted aerial imagery?
			gv_options.animated_zoom = false; // true|false: may or may not work properly
			gv_options.disable_google_pois = false;  // true|false: if you disable clickable POIs, you also lose the labels on parks, airports, etc.
			
			// widgets on the map:
			gv_options.zoom_control = 'large'; // 'large'|'small'|'none'
			gv_options.recenter_button = true; // true|false: is there a 'click to recenter' option in the zoom control?
			gv_options.scale_control = true; // true|false
			gv_options.map_opacity_control = 'utilities';  // 'map'|'utilities'|'both'|false: where does the opacity control appear?
			gv_options.map_type_control = {};  // widget to change the background map
			  gv_options.map_type_control.placement = 'both'; // 'map'|'utilities'|'both'|false: where does the map type control appear?
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
			  gv_options.tracklist_options.max_height = 177; // maximum height of the tracklist, in pixels; if the list is longer, scrollbars will appear
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

			// marker-related options:
			gv_options.default_marker = { color:'red',icon:'googlemini',scale:1 }; // icon can be a URL, but be sure to also include size:[w,h] and optionally anchor:[x,y]
			gv_options.vector_markers = false; // are the icons on the map in embedded SVG format?
			gv_options.marker_tooltips = true; // do the names of the markers show up when you mouse-over them?
			gv_options.marker_shadows = true; // true|false: do the standard markers have "shadows" behind them?
			gv_options.marker_link_target = '_blank'; // the name of the window or frame into which markers' URLs will load
			gv_options.info_window_width = 300;  // in pixels, the width of the markers' pop-up info "bubbles" (can be overridden by 'window_width' in individual markers)
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
			  gv_options.marker_list_options.max_height = 177;  // maximum height
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
			  gv_options.marker_list_options.zoom_level = 18;  // if 'zoom' is true, what level should the map zoom to?
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
				document.writeln('<script src="https://gpsvisualizer.com/google_maps/functions3.js" type="text/javascript"><'+'/script>');
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
				opacity:0.90; filter:alpha(opacity=90);
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
				trk[t].info.name = 'Stop 1'; trk[t].info.desc = '0.6860 mi'; trk[t].info.clickable = true;
				trk[t].info.color = '#fd2500'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false; trk[t].info.z_index = null;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#fd2500'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ points:[ [34.10913429223,-106.824787203223],[34.1091344598681,-106.824786281213],[34.1091345436871,-106.824785359204],[34.1091408301145,-106.824776055291],[34.1091412492096,-106.824782928452],[34.1091245692223,-106.824821820483],[34.1092216316611,-106.824890300632],[34.1091720107943,-106.82484453544],[34.1091582644731,-106.824831711128],[34.1090950649232,-106.824790639803],[34.1090517304838,-106.824783599004],[34.1089345514774,-106.824758369476],[34.108830364421,-106.824745377526],[34.108645291999,-106.824692739174],[34.1085153724998,-106.824643202126],[34.1083612293005,-106.824615709484],[34.1082403622568,-106.82461428456],[34.108116729185,-106.824504900724],[34.1081047430634,-106.824476737529],[34.1080974508077,-106.824434073642],[34.1081439703703,-106.824245732278],[34.1081649251282,-106.824085637927],[34.1081794258207,-106.824052110314],[34.1081173997372,-106.823915233836],[34.1080787591636,-106.823883550242],[34.1079311538488,-106.82380836457],[34.1079142224044,-106.823807023466],[34.1078100353479,-106.823754804209],[34.1077149007469,-106.823658915237],[34.1077095363289,-106.823639133945],[34.1076229512691,-106.823553303257],[34.1075533814728,-106.823476776481],[34.1075109690428,-106.823344007134],[34.1075054369867,-106.823306791484],[34.1074870806187,-106.823219200596],[34.1074854880571,-106.823194390163],[34.1074872482568,-106.823184415698],[34.1074644494802,-106.823073020205],[34.107450703159,-106.823047287762],[34.1074476856738,-106.822993643582],[34.1074367053807,-106.822843775153],[34.1074391361326,-106.822833213955],[34.1074841469526,-106.822713101283],[34.1075479332358,-106.822596425191],[34.107627728954,-106.822512354702],[34.107712302357,-106.822428870946],[34.1078060120344,-106.822242708877],[34.1078046709299,-106.822220664471],[34.1078452393413,-106.822085799649],[34.1079098638147,-106.821887232363],[34.1079548746347,-106.821709871292],[34.1079610772431,-106.82168925181],[34.1079738177359,-106.821572491899],[34.1078745760024,-106.821466628462],[34.1078850533813,-106.821422958747],[34.1078672837466,-106.821392616257],[34.1078798566014,-106.821276862174],[34.1078704688698,-106.821226906031],[34.1078902501613,-106.821135627106],[34.1078777611256,-106.821072846651],[34.1078758332878,-106.821071840823],[34.1079013142735,-106.82107988745],[34.1079096961766,-106.821055915207],[34.1078314930201,-106.82111794129],[34.107884131372,-106.821194384247],[34.1079029906541,-106.821205699816],[34.1079495102167,-106.821182733402],[34.1079725604504,-106.821145769209],[34.1079987119883,-106.821126993746],[34.1080222651362,-106.821082318202],[34.1080257017165,-106.821071589366],[34.1080094408244,-106.8210911192],[34.1079778410494,-106.821200922132],[34.1079753264785,-106.821268312633],[34.1079844627529,-106.821279879659],[34.1079718898982,-106.821339139715],[34.1079729795456,-106.821376187727],[34.1079982090741,-106.821509962901],[34.1079737339169,-106.821574755013],[34.1079606581479,-106.821594620124],[34.1079385299236,-106.821689167991],[34.1079224366695,-106.821764102206],[34.1078755818307,-106.821893518791],[34.1078364383429,-106.822038609535],[34.1078296490014,-106.822059899569],[34.1077739931643,-106.822204655036],[34.1077849734575,-106.822278248146],[34.1076884139329,-106.822418058291],[34.1075675468892,-106.822515204549],[34.1075386293232,-106.822539512068],[34.1075251344591,-106.822587121278],[34.1075199376792,-106.822621319443],[34.107442740351,-106.822760878131],[34.1074256412685,-106.822809157893],[34.1074289102107,-106.822970174253],[34.1074324306101,-106.822989284992],[34.1074538882822,-106.823110571131],[34.1074600070715,-106.823129346594],[34.1074643656611,-106.823264043778],[34.107519434765,-106.823382647708],[34.1074970550835,-106.823450792581],[34.1075339354575,-106.82347945869],[34.1076310817152,-106.823579203337],[34.1076874081045,-106.823639469221],[34.107754798606,-106.823728317395],[34.107823446393,-106.82376679033],[34.1079715546221,-106.823822781444],[34.1080993786454,-106.82388891466],[34.1081519331783,-106.824054121971],[34.1081709600985,-106.824067868292],[34.108174983412,-106.824098629877],[34.1081760730594,-106.824121847749],[34.1081050783396,-106.824374897406],[34.1080864705145,-106.824434995651],[34.1080891527236,-106.824473133311],[34.108100887388,-106.82450951077],[34.108232986182,-106.824604310095],[34.1083737183362,-106.824625600129],[34.108521156013,-106.824660971761],[34.1086748801172,-106.82471645996],[34.1088313702494,-106.824751663953],[34.1089666541666,-106.824744706973],[34.109163461253,-106.824752669781],[34.1092249006033,-106.824763566256],[34.1092176083475,-106.824814025313],[34.1092166025192,-106.824829531834],[34.1092093940824,-106.824826765805],[34.109189864248,-106.824812600389],[34.1091725975275,-106.824801033363] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:34.1079424,lon:-106.8215390,name:'0.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Stop 1 [tickmarks]',rotation:138.5,track_number:1,dd:false});
				GV_Draw_Marker({lat:34.1081167,lon:-106.8245209,name:'0.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Stop 1 [tickmarks]',rotation:329.3,track_number:1,dd:false});
				GV_Draw_Marker({lat:34.1091726,lon:-106.8248010,name:'0.69 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Stop 1 [tickmarks]',rotation:151.0,track_number:1,dd:false});
				GV_Draw_Marker({lat:34.1074722,lon:-106.8232500,name:'Enter description here',desc:'BentleyLeadsTheWay',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/40060412461/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4756/40060412461_372e2fe664_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:34.1079889,lon:-106.8213194,name:'Enter description here',desc:'BursumSprings',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/40027824602/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4618/40027824602_5fe8526eb4_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:34.1079417,lon:-106.8212361,name:'Enter description here',desc:'KarenScouts',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/26187076448/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4750/26187076448_b62e08f8c1_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:34.1078528,lon:-106.8213722,name:'Enter description here',desc:'LookingOut',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/26187065048/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4755/26187065048_a0a11e960d_q.jpg',folder:'Folder1'});
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?allow_export=1&form=google&google_api_key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&google_street_view=1&google_trk_mouseover=1&tickmark_interval=.3%20mi&trk_stats=1&units=us&wpt_driving_directions=1&add_elevation=auto
		</script>
		
		
		
	<div style='text-align: right;position: fixed;z-index:9999999;bottom: 0; width: 100%;cursor: pointer;line-height: 0;display:block !important;'><a title="000webhost logo" rel="nofollow" target="_blank" href="https://www.000webhost.com/free-website-sign-up?utm_source=000webhostapp&amp;utm_campaign=000_logo&amp;utm_campaign=ss-footer_logo&amp;utm_medium=000_logo&amp;utm_content=website"><img src="https://cdn.rawgit.com/000webhost/logo/e9bd13f7/footer-powered-by-000webhost-white2.png" alt="000webhost logo"></a></div></body>

</html>
