<?php
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Stop_3 + stop3</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="34.0772472; -106.7815357" />
		<meta name="ICBM" content="34.0772472, -106.7815357" />
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
			/* Global variables used by the GPS Visualizer functions (20180406060428): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [34.0772472164443,-106.781535727372];  // [latitude,longitude] - be sure to keep the square brackets
			gv_options.zoom = 17;  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements
			gv_options.map_type = 'GV_HYBRID';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_OSM', 'GV_TOPO_US', 'GV_TOPO_WORLD' (http://www.gpsvisualizer.com/misc/google_map_types.html)
			gv_options.map_opacity = 1.00;  // number from 0 to 1
			gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?
			gv_options.width = 700;  // width of the map, in pixels
			gv_options.height = 700;  // height of the map, in pixels
			
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
				trk[t].info.name = 'Stop 3'; trk[t].info.desc = '0.6340 mi'; trk[t].info.clickable = true;
				trk[t].info.color = '#525fff'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false; trk[t].info.z_index = null;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#525fff'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ points:[ [34.0761694405228,-106.781223351136],[34.0762057341635,-106.781246569008],[34.0762342326343,-106.781246569008],[34.0762501582503,-106.781311612576],[34.0762166306376,-106.78134245798],[34.0761955920607,-106.78128185682],[34.0761759784073,-106.7812091019],[34.0761797502637,-106.781185632572],[34.076185785234,-106.781085804105],[34.0761523414403,-106.780994776636],[34.0761494077742,-106.780917998403],[34.0761142875999,-106.780925039202],[34.0761076658964,-106.780917495489],[34.076109174639,-106.780931157991],[34.0761204902083,-106.780916489661],[34.0761470608413,-106.780932666734],[34.0761635731906,-106.780955046415],[34.0760896448046,-106.780906850472],[34.0761023014784,-106.780901653692],[34.0761046484113,-106.780911125243],[34.0760625712574,-106.780906012282],[34.0760601405054,-106.780801657587],[34.0760690253228,-106.78073736839],[34.0760789997876,-106.780749941245],[34.0760528482497,-106.780675174668],[34.0760480705649,-106.780663104728],[34.0760520938784,-106.780625218526],[34.0760707017034,-106.780598228797],[34.0761043969542,-106.780461855233],[34.0761139523238,-106.780437631533],[34.0761080849916,-106.780438302085],[34.0761268604547,-106.780431177467],[34.0761206578463,-106.780440565199],[34.0761157963425,-106.780530000106],[34.0760902315378,-106.780585488304],[34.0761219989508,-106.780709540471],[34.0761227533221,-106.780743319541],[34.0761247649789,-106.780775003135],[34.0761236753315,-106.780826468021],[34.0761095099151,-106.780899977311],[34.0761074982584,-106.780946999788],[34.0761019662023,-106.781074656174],[34.0761337336153,-106.78121865727],[34.0761416964233,-106.781229553744],[34.0761165507138,-106.781256878749],[34.076088052243,-106.781280431896],[34.076069444418,-106.781380930915],[34.0761331468821,-106.781459720805],[34.0762488171458,-106.781520573422],[34.0762953367084,-106.781546473503],[34.0763852745295,-106.781577821821],[34.076447384432,-106.781625347212],[34.0765455365181,-106.781648145989],[34.0766366478056,-106.781685361639],[34.0767413377762,-106.781754931435],[34.07680160366,-106.781854843721],[34.0768047887832,-106.781926508993],[34.0768337901682,-106.782035976648],[34.0768569242209,-106.782154329121],[34.0768696647137,-106.782193640247],[34.0768898651004,-106.782236555591],[34.0769437607378,-106.78229380399],[34.0769958123565,-106.782362032682],[34.0770418290049,-106.782449288294],[34.0770868398249,-106.782520366833],[34.0771449264139,-106.782535705715],[34.0772258955985,-106.782567724586],[34.0773079544306,-106.782605946064],[34.0773348603398,-106.782661182806],[34.0772389713675,-106.782690854743],[34.0772354509681,-106.782661769539],[34.0771941281855,-106.782610388473],[34.0771704073995,-106.782570658252],[34.0771740116179,-106.782546350732],[34.0772125683725,-106.782440152019],[34.0772678889334,-106.782398829237],[34.0772973932326,-106.782394805923],[34.0773871634156,-106.782412324101],[34.0774104651064,-106.782402684912],[34.0774299949408,-106.782408636063],[34.0774981398135,-106.782421125099],[34.0775246266276,-106.782406959683],[34.0775698889047,-106.782365720719],[34.0776075236499,-106.782310735434],[34.0776524506509,-106.782257594168],[34.077657898888,-106.782236387953],[34.0776722319424,-106.78220378235],[34.0777535364032,-106.782091716304],[34.0777653548867,-106.782068666071],[34.0777803584933,-106.781940422952],[34.0777940209955,-106.781906308606],[34.0778080187738,-106.78186272271],[34.0778495091945,-106.781800780445],[34.0778731461614,-106.781784435734],[34.0779202524573,-106.781745711342],[34.0780325699598,-106.781743448228],[34.0780704561621,-106.781749818474],[34.0781083423644,-106.78182978183],[34.0781724639237,-106.781917624176],[34.0782302152365,-106.781943775713],[34.0782489068806,-106.781948553398],[34.0783698577434,-106.782016865909],[34.0783774852753,-106.782020973042],[34.0784171316773,-106.782037988305],[34.0784461330622,-106.782050058246],[34.0784753859043,-106.782056679949],[34.0784707758576,-106.782064642757],[34.0784897189587,-106.782072857022],[34.0785442851484,-106.782058021054],[34.0785526670516,-106.78205575794],[34.0785727836192,-106.782054165378],[34.0786083228886,-106.782040335238],[34.0785918943584,-106.782047543675],[34.0785746276379,-106.782055087388],[34.0785609651357,-106.782058943063],[34.078556606546,-106.782051147893],[34.0784728713334,-106.78198652342],[34.0784431993961,-106.781979817897],[34.0783945005387,-106.781974704936],[34.0783349890262,-106.781916031614],[34.0781998727471,-106.781898261979],[34.0781211666763,-106.781831458211],[34.0780539438128,-106.781758535653],[34.0779775008559,-106.781751830131],[34.0778987109661,-106.781759960577],[34.0778466593474,-106.781785022467],[34.0777805261314,-106.781789548695],[34.0777231939137,-106.781777478755],[34.0776802785695,-106.781733892858],[34.0776464156806,-106.781677398831],[34.0775842219591,-106.781561728567],[34.0775920171291,-106.781515041366],[34.0775974653661,-106.781526273116],[34.0775969624519,-106.781567176804],[34.0775740798563,-106.781490733847],[34.0775695536286,-106.78147925064],[34.0775506105274,-106.781394341961],[34.077560249716,-106.781350420788],[34.0775675419718,-106.781328041106],[34.0775629319251,-106.781297111884],[34.0775275602937,-106.78117280826],[34.0774766821414,-106.781136179343],[34.0774711500853,-106.781126204878],[34.0774248819798,-106.781057389453],[34.0774118062109,-106.781028304249],[34.0773320943117,-106.78098924458],[34.077158337459,-106.781017156318],[34.0769652184099,-106.781092174351],[34.0767337940633,-106.781146153808],[34.0765172895044,-106.7811848782],[34.0763573627919,-106.781195690855],[34.0762038901448,-106.781207006425],[34.0761850308627,-106.781207257882],[34.0761874616146,-106.781220585108],[34.0761227533221,-106.781224440783] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:34.0773171,lon:-106.7823986,name:'0.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Stop 3 [tickmarks]',rotation:350.8,track_number:1,dd:false});
				GV_Draw_Marker({lat:34.0766002,lon:-106.7811700,name:'0.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Stop 3 [tickmarks]',rotation:188.4,track_number:1,dd:false});
				GV_Draw_Marker({lat:34.0761228,lon:-106.7812244,name:'0.63 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'Stop 3 [tickmarks]',rotation:182.8,track_number:1,dd:false});
				GV_Draw_Marker({lat:34.0758861,lon:-106.7803806,name:'Enter description here',desc:'ShiningRocks',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/39162125495/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4757/39162125495_2dbbbfb33f_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:34.0776139,lon:-106.7815556,name:'Enter description here',desc:'BursumBentley',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/39162116295/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4672/39162116295_1e6c329a88_q.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:34.0768568,lon:-106.7820022,name:'Enter description here',desc:'BursumArroyo',color:'pink',icon:'',url:'https://www.flickr.com/photos/139088815@N08/39349960644/in/album-72157690088502492',thumbnail:'https://c1.staticflickr.com/5/4624/39349960644_9f05515b10_q.jpg',folder:'Folder1'});
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?allow_export=1&form=google&google_api_key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&google_street_view=1&google_trk_mouseover=1&tickmark_interval=.3%20mi&trk_stats=1&units=us&wpt_driving_directions=1&add_elevation=auto
		</script>
		
		
		
	<div style='text-align: right;position: fixed;z-index:9999999;bottom: 0; width: 100%;cursor: pointer;line-height: 0;display:block !important;'><a title="000webhost logo" rel="nofollow" target="_blank" href="https://www.000webhost.com/free-website-sign-up?utm_source=000webhostapp&amp;utm_campaign=000_logo&amp;utm_campaign=ss-footer_logo&amp;utm_medium=000_logo&amp;utm_content=website"><img src="https://cdn.rawgit.com/000webhost/logo/e9bd13f7/footer-powered-by-000webhost-white2.png" alt="000webhost logo"></a></div></body>

</html>
