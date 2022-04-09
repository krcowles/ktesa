<?php
require "../php/global_boot.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>LaCieneguilla + laCieneguilla</title>
		<base target="_top"></base>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta name="geo.position" content="35.6052509; -106.1226546" />
		<meta name="ICBM" content="35.6052509, -106.1226546" />
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
			/* Global variables used by the GPS Visualizer functions (20170312182301): */
			gv_options = {};
			
			// basic map parameters:
			gv_options.center = [35.6052509089932,-106.122654578649];  // [latitude,longitude] - be sure to keep the square brackets
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
				trk[t].info.name = 'La Cieneguilla'; trk[t].info.desc = ''; trk[t].info.clickable = true;
				trk[t].info.color = '#fd2500'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#fd2500'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ points:[ [35.6087276805192,-106.120188916102],[35.608724327758,-106.120223868638],[35.6087229028344,-106.120250858366],[35.6087226513773,-106.12026183866],[35.6087223999202,-106.120266700163],[35.6087221484631,-106.120276339352],[35.6087263394147,-106.120302993804],[35.6087273452431,-106.120319338515],[35.608727009967,-106.12033450976],[35.6087266746908,-106.12035965547],[35.6087232381105,-106.120385807008],[35.6087203044444,-106.120411455631],[35.6087169516832,-106.120443223044],[35.6087180413306,-106.120472559705],[35.608719130978,-106.120500639081],[35.6087159458548,-106.120532322675],[35.6087146047503,-106.12056568265],[35.6087160296738,-106.120613040403],[35.6087143532932,-106.120664756745],[35.608713934198,-106.120732482523],[35.6087184604257,-106.120814708993],[35.6087213102728,-106.120894504711],[35.6087229028344,-106.120938509703],[35.6087313685566,-106.12103045918],[35.6087525747716,-106.12116037868],[35.6088015250862,-106.121287699789],[35.608872352168,-106.121467072517],[35.6088603660464,-106.121700759977],[35.6088135950267,-106.1218303442],[35.6086590327322,-106.121964789927],[35.6086426880211,-106.121967472136],[35.6084684282541,-106.122010638937],[35.6082754768431,-106.12205372192],[35.6082149595022,-106.12207702361],[35.6079858820885,-106.12212899141],[35.6077502667904,-106.12219135277],[35.6075646914542,-106.122242147103],[35.6073586642742,-106.12229864113],[35.6073276512325,-106.122306352481],[35.6071093026549,-106.122354716063],[35.6069055385888,-106.122416742146],[35.6067013554275,-106.122467704117],[35.6065137684345,-106.122508021072],[35.6062899716198,-106.122466111556],[35.6060989480466,-106.122491592541],[35.6060273665935,-106.122618494555],[35.6058917474002,-106.122683119029],[35.6058862153441,-106.122683370486],[35.6058538611978,-106.122690662742],[35.6057839561254,-106.12271505408],[35.605605924502,-106.122783282772],[35.6053609214723,-106.122845895588],[35.6051417347044,-106.122898114845],[35.6049569975585,-106.122972043231],[35.6049401499331,-106.123121157289],[35.6049502920359,-106.123223500326],[35.6050123181194,-106.123351156712],[35.6050710752606,-106.123434556648],[35.6050756014884,-106.123506389558],[35.6050329376012,-106.123585095629],[35.6050099711865,-106.123619880527],[35.6049569975585,-106.123648043722],[35.6049489509314,-106.123658102006],[35.6049045268446,-106.123715685681],[35.6049758568406,-106.123742088675],[35.6049645412713,-106.123808473349],[35.6049729231745,-106.123870583251],[35.6049813888967,-106.123898997903],[35.6050057802349,-106.123882485554],[35.6049974821508,-106.123897572979],[35.6050154194236,-106.123906709254],[35.6050073727965,-106.123932860792],[35.6049942970276,-106.123932190239],[35.6049806345254,-106.123939398676],[35.6049647089094,-106.123932274058],[35.6049181055278,-106.123917102814],[35.6048770342022,-106.123922215775],[35.604834118858,-106.123948702589],[35.6048132479191,-106.12395809032],[35.6047943048179,-106.123960521072],[35.6047558318824,-106.123998910189],[35.6047194544226,-106.124032940716],[35.6046910397708,-106.124046854675],[35.604622811079,-106.12407325767],[35.6046099867672,-106.124095972627],[35.6045361422002,-106.124103432521],[35.6045086495578,-106.124144755304],[35.6044806540012,-106.124180294573],[35.6044529099017,-106.124170739204],[35.6044285185635,-106.124131428078],[35.6044246628881,-106.124094380066],[35.6043734494597,-106.124053560197],[35.6043384969234,-106.124043669552],[35.6042829249054,-106.124019781128],[35.6042403448373,-106.123992037028],[35.6042136065662,-106.124006286263],[35.6041657458991,-106.124030342326],[35.6041674222797,-106.124049285427],[35.6041619740427,-106.124058170244],[35.6041664164513,-106.124053392559],[35.6041730381548,-106.124048950151],[35.6041437853128,-106.124065881595],[35.6041292846203,-106.124090189114],[35.6041036359966,-106.124106952921],[35.6040991097689,-106.124110892415],[35.6040801666677,-106.124116256833],[35.6040555238724,-106.124118687585],[35.6040230859071,-106.124149868265],[35.6039845291525,-106.124165542424],[35.6039465591311,-106.124167470261],[35.6039051525295,-106.124205524102],[35.6039271969348,-106.124234106392],[35.6039311364293,-106.124255396426],[35.6039178092033,-106.124266125262],[35.6039071641862,-106.124262521043],[35.6038920767605,-106.124277357012],[35.6038859579712,-106.124272998422],[35.6038791686296,-106.124296216294],[35.6038708705455,-106.124271322042],[35.6038493290544,-106.124300323427],[35.6038595549762,-106.124303257093],[35.6038597226143,-106.124268807471],[35.6038159690797,-106.124265957624],[35.6037692818791,-106.124256486073],[35.6037405319512,-106.124340640381],[35.603777160868,-106.124314656481],[35.6037614867091,-106.124297557399],[35.6037538591772,-106.124297054484],[35.6036646757275,-106.124344244599],[35.6036020629108,-106.124381124973],[35.6035573035479,-106.124417502433],[35.6035395339131,-106.124443653971],[35.6035288050771,-106.124486820772],[35.6035169027746,-106.124499393627],[35.6035195011646,-106.124494699761],[35.6035241112113,-106.124509200454],[35.603523440659,-106.124498220161],[35.6035143882036,-106.12450609915],[35.6035196688026,-106.124542728066],[35.6034991331398,-106.124546080828],[35.6034884043038,-106.124563347548],[35.6034799385816,-106.124588744715],[35.6034774240106,-106.124593103305],[35.6034504342824,-106.124615734443],[35.6034468300641,-106.124623864889],[35.6034392025322,-106.124625205994],[35.6034189183265,-106.124641718343],[35.60342813842,-106.124628223479],[35.6034105364233,-106.124613806605],[35.6034000590444,-106.124631911516],[35.6033771764487,-106.124630486593],[35.6033562216908,-106.124654375017],[35.6033722311258,-106.124647250399],[35.6033843848854,-106.1246644333],[35.6033618375659,-106.124676419422],[35.6033619213849,-106.124647669494],[35.603364687413,-106.124689076096],[35.6033335905522,-106.12472595647],[35.6033371947706,-106.124735344201],[35.6033347640187,-106.124733164907],[35.6033267173916,-106.124760322273],[35.6033228617162,-106.124762250111],[35.6033271364868,-106.124761914834],[35.6032793596387,-106.124812960625],[35.6032528728247,-106.124818995595],[35.6032263860106,-106.124824192375],[35.603185147047,-106.12483240664],[35.6031859852374,-106.124828718603],[35.6031922716647,-106.124832490459],[35.603192942217,-106.124830730259],[35.603185063228,-106.124821091071],[35.6031461711973,-106.124849338084],[35.6031166668981,-106.124840034172],[35.603055646643,-106.12483844161],[35.6030436605215,-106.124844811857],[35.6030090432614,-106.124863671139],[35.6029633618891,-106.12486534752],[35.602912735194,-106.124896360561],[35.6028744298965,-106.124927541241],[35.6028552353382,-106.124925781041],[35.6027905270457,-106.124926535413],[35.6027458515018,-106.124974982813],[35.6027032714337,-106.125004738569],[35.6026905309409,-106.124993171543],[35.6026967335492,-106.124990321696],[35.6027343682945,-106.124958889559],[35.6027605198324,-106.12491555512],[35.6027796305716,-106.12490859814],[35.6027721706778,-106.124936677516],[35.6027281656861,-106.12497087568],[35.6026831548661,-106.124967606738],[35.6026197876781,-106.125027872622],[35.6026011798531,-106.125029884279],[35.6025729328394,-106.125056706369],[35.6025519780815,-106.125050419942],[35.6025478709489,-106.125053353608],[35.6025236472487,-106.125075817108],[35.6024958193302,-106.125083277002],[35.6024524848908,-106.125114373863],[35.6024113297462,-106.125116217881],[35.6024073902518,-106.125155612826],[35.6023992598057,-106.12515267916],[35.6023633014411,-106.125174639747],[35.6023291870952,-106.125177321956],[35.6023187097162,-106.125203641132],[35.6023247446865,-106.125214369968],[35.6023251637816,-106.125231552869],[35.602289037779,-106.125239850953],[35.6022788118571,-106.125245969743],[35.6022949889302,-106.125251669437],[35.6022675801069,-106.125236833468],[35.6022453680634,-106.125254351646],[35.6022167019546,-106.125250412151],[35.6022053863853,-106.125255860388],[35.6021925620735,-106.125271199271],[35.6021803244948,-106.125278826803],[35.602167416364,-106.125296764076],[35.6021865271032,-106.125291734934],[35.6021778937429,-106.125304726884],[35.6021783128381,-106.125298524275],[35.6021751277149,-106.1252704449],[35.6021483056247,-106.125227697194],[35.6020997744054,-106.125271199271],[35.6020798254758,-106.125310426578],[35.6021129339933,-106.12534143962],[35.6021273508668,-106.125358287245],[35.6021230760962,-106.125339008868],[35.6021135207266,-106.125337164849],[35.6021016184241,-106.125328112394],[35.6020758021623,-106.125292405486],[35.6020752154291,-106.125238174573],[35.6020362395793,-106.125202383846],[35.6020024605095,-106.125196432695],[35.6019675917923,-106.125163324177],[35.6019410211593,-106.125144129619],[35.6019117683172,-106.125133149326],[35.6018921546638,-106.125061148778],[35.6018405221403,-106.125056287274],[35.601757876575,-106.12506357953],[35.6017228402197,-106.125052766874],[35.6016988679767,-106.12502628006],[35.6016824394464,-106.125004738569],[35.6016612332314,-106.124988477677],[35.6016603112221,-106.124953106046],[35.6016489118338,-106.124939108267],[35.6016215868294,-106.124909184873],[35.6016094330698,-106.12481941469],[35.6016110256314,-106.124728387222],[35.6016355007887,-106.124711288139],[35.6017391011119,-106.124644987285],[35.6018386781216,-106.124594863504],[35.601982427761,-106.124520348385],[35.6020998582244,-106.124468296766],[35.6022428534925,-106.124407611787],[35.6023618765175,-106.124353464693],[35.6024596933275,-106.124253133312],[35.6025006808341,-106.124204602093],[35.6026575062424,-106.124114580452],[35.6027638725936,-106.124109718949],[35.6028187740594,-106.124057751149],[35.6029274873435,-106.123947026208],[35.6029671337456,-106.123927915469],[35.6031262222677,-106.123892460018],[35.6032730732113,-106.123802354559],[35.6033794395626,-106.123666735366],[35.603399220854,-106.123645277694],[35.6035272963345,-106.123603368178],[35.6036624126136,-106.123521225527],[35.6037345807999,-106.123473448679],[35.6039126124233,-106.123361634091],[35.6040162965655,-106.123319473118],[35.6041890475899,-106.123237665743],[35.6042656581849,-106.123202797025],[35.60431628488,-106.123131215572],[35.6043088249862,-106.123107746243],[35.6042970065027,-106.123085282743],[35.6043208949268,-106.122974306345],[35.6044576875865,-106.122885290533],[35.6045651435852,-106.122872969136],[35.6047193706036,-106.122875232249],[35.6048901937902,-106.122885374352],[35.6049676425755,-106.122882356867],[35.605087839067,-106.122867856175],[35.6052310019732,-106.122836926952],[35.605354718864,-106.122793089598],[35.6054509431124,-106.122765680775],[35.605642804876,-106.122717820108],[35.605780268088,-106.122660571709],[35.6058869697154,-106.12264146097],[35.6059438828379,-106.122620338574],[35.6060060765594,-106.122544901446],[35.6060748081654,-106.122445911169],[35.6061691045761,-106.122407857329],[35.606274548918,-106.122457226738],[35.606417292729,-106.122491173446],[35.6065677478909,-106.12247700803],[35.6067376490682,-106.122439373285],[35.6069052871317,-106.122391177341],[35.6070815585554,-106.122343903407],[35.6072567403316,-106.122299730778],[35.6073834747076,-106.122252456844],[35.6075526215136,-106.122213480994],[35.6077024061233,-106.122166458517],[35.6078884005547,-106.122124297544],[35.6080775801092,-106.122072916478],[35.6081893108785,-106.122053638101],[35.6083704438061,-106.121990690008],[35.6085535883904,-106.121943080798],[35.6087038759142,-106.12189530395],[35.6087741162628,-106.12183637917],[35.6088019441813,-106.121818525717],[35.6088388245553,-106.121749542654],[35.6088923849165,-106.121569331735],[35.6088726874441,-106.121522560716],[35.6088523194194,-106.121522393078],[35.6088471226394,-106.121530355886],[35.6088506430387,-106.121511412784],[35.6088396627456,-106.121504204348],[35.6088368967175,-106.121496912092],[35.6088425964117,-106.121490960941],[35.608851229772,-106.121499091387],[35.6088251620531,-106.121396245435],[35.6087345536798,-106.121191894636],[35.6086991820484,-106.121023083106],[35.6087004393339,-106.1209023837],[35.6086879502982,-106.120732063428],[35.6086871121079,-106.1206513457],[35.6086880341172,-106.120440540835],[35.6087031215429,-106.120222695172],[35.6087653990835,-106.120050195605],[35.6087823305279,-106.120036784559],[35.6088715977967,-106.119950870052],[35.608872352168,-106.11997182481],[35.608885595575,-106.119963359088] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:35.6059869,lon:-106.1226378,name:'0.3 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'La Cieneguilla [tickmarks]',rotation:201.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6034379,lon:-106.1246263,name:'0.6 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'La Cieneguilla [tickmarks]',rotation:213.5,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6027331,lon:-106.1241111,name:'0.9 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'La Cieneguilla [tickmarks]',rotation:2.1,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6066035,lon:-106.1224691,name:'1.2 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'La Cieneguilla [tickmarks]',rotation:10.2,track_number:1,dd:false});
				GV_Draw_Marker({lat:35.6088856,lon:-106.1199634,name:'1.48 mi',desc:'',color:trk[1].info.color,icon:'tickmark',type:'tickmark',folder:'La Cieneguilla [tickmarks]',rotation:27.5,track_number:1,dd:false});
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
