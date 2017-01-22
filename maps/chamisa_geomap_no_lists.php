<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>chamisa + Chamisa Trail</title>
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
			<div id="gv_infobox" class="gv_infobox" style="font:11px Arial; border:solid #666666 1px; background-color:#ffffff; padding:4px; overflow:auto; display:none; max-width:400px;">
				<!-- Although GPS Visualizer didn't create an legend/info box with your map, you can use this space for something else if you'd like; enable it by setting gv_options.infobox_options.enabled to true -->
			<?php if (isset($_GET[show_geoloc]) == true && $_GET[show_geoloc] == "true") {echo "
				<p><a href='javascript:GV_Geolocate({marker:true,info_window:true})' style='font-size:12px'>Geolocate me!</a></p>
			";}?>
			  gv_options.tracklist_options.enabled = <?php if (isset($_GET[tracklist_options_enabled])) {echo $_GET[tracklist_options_enabled];} else {echo "false";}?>;  // true|false: enable or disable the tracklist altogether
			  gv_options.marker_list_options.enabled = <?php if (isset($_GET[marker_list_options_enabled])) {echo $_GET[marker_list_options_enabled];} else {echo "false";}?>;;  // true|false: enable or disable the marker list altogether
			</div>
			
			
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
				
				// Track #1
				t = 1; trk[t] = {info:[],segments:[]};
				trk[t].info.name = 'Chamisa Trail'; trk[t].info.desc = ''; trk[t].info.clickable = true;
				trk[t].info.color = 'blue'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;
				trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = 'blue'; trk[t].info.fill_opacity = 0;
				trk[t].segments.push({ points:[ [35.7284100260586,-105.865974761546],[35.7284052483737,-105.865972246975],[35.7284168992192,-105.865962691605],[35.7284127920866,-105.865920279175],[35.7285245228559,-105.865958165377],[35.7287112716585,-105.865982053801],[35.7287416979671,-105.865932684392],[35.7287328131497,-105.865866467357],[35.7287194021046,-105.865664044395],[35.7287454698235,-105.86548266001],[35.7287351600826,-105.865321056917],[35.7286633271724,-105.865169428289],[35.7286080066115,-105.864967843518],[35.7286936696619,-105.864893747494],[35.7288069091737,-105.86491570808],[35.7289655786008,-105.864907745272],[35.7291403412819,-105.864941943437],[35.7293078117073,-105.864941021428],[35.7294438499957,-105.864908918738],[35.7295209635049,-105.864858794957],[35.729687763378,-105.864741532132],[35.7297347858548,-105.864589484408],[35.7297341991216,-105.864526703954],[35.7298920303583,-105.864479932934],[35.7300197705626,-105.864514717832],[35.730055058375,-105.864525027573],[35.7301928568631,-105.864478843287],[35.7303318288177,-105.864441879094],[35.7304692082107,-105.864394940436],[35.7306213397533,-105.864339452237],[35.7307049911469,-105.864177681506],[35.7307717110962,-105.864116577432],[35.730903474614,-105.864188913256],[35.7310290355235,-105.864230822772],[35.7310759741813,-105.864213975146],[35.7312211487442,-105.864133005962],[35.7313346397132,-105.864078020677],[35.731492722407,-105.863983472809],[35.7315318658948,-105.863849781454],[35.731529854238,-105.863824384287],[35.7315142638981,-105.863725980744],[35.7315009366721,-105.863566473126],[35.7315232325345,-105.863351393491],[35.7315730210394,-105.863323649392],[35.7318497914821,-105.863414509222],[35.7318939641118,-105.863446528092],[35.732026649639,-105.863466141745],[35.7321776077151,-105.863476451486],[35.7322056032717,-105.86347184144],[35.7323312480003,-105.863353321329],[35.7324906717986,-105.863238237798],[35.7325433101505,-105.863184593618],[35.7325775083154,-105.863155173138],[35.7326083537191,-105.86306146346],[35.7326124608517,-105.862974291667],[35.7326108682901,-105.862940093502],[35.7326014805585,-105.862733814865],[35.7326955255121,-105.862728282809],[35.7329243514687,-105.862800115719],[35.7329984474927,-105.862810593098],[35.7332162093371,-105.862844791263],[35.7333144452423,-105.862828865647],[35.7334948237985,-105.862806737423],[35.733599178493,-105.862759631127],[35.7337908726186,-105.862674470991],[35.7338896952569,-105.862629879266],[35.7339278329164,-105.862598866224],[35.733937472105,-105.862558716908],[35.7339256536216,-105.862511610612],[35.7338534016162,-105.862323688343],[35.7337779644877,-105.862073069438],[35.7337706722319,-105.861851200461],[35.7337466161698,-105.861675264314],[35.7337289303541,-105.861442834139],[35.7337871007621,-105.861406540498],[35.7339078839868,-105.861498573795],[35.7340771146119,-105.861617345363],[35.7342661265284,-105.861673336476],[35.7342883385718,-105.861657075584],[35.7343153283,-105.861639641225],[35.7344479300082,-105.861503519118],[35.7345956191421,-105.861585326493],[35.7346522808075,-105.86167903617],[35.7346754986793,-105.861724885181],[35.734701231122,-105.861753551289],[35.7347957789898,-105.861829491332],[35.7348901592195,-105.861851451918],[35.7349558733404,-105.861851535738],[35.7350862957537,-105.861828485504],[35.735124014318,-105.861808955669],[35.7352199032903,-105.861754808575],[35.7352972682565,-105.861641401425],[35.7354501541704,-105.861551463604],[35.7355180475861,-105.86142280139],[35.7355192210525,-105.861364128068],[35.7355027925223,-105.861237226054],[35.7355134375393,-105.86113370955],[35.7356073148549,-105.861076461151],[35.7356290239841,-105.86107830517],[35.7358120009303,-105.861101187766],[35.7359276711941,-105.861056763679],[35.736138978973,-105.86104712449],[35.7362096384168,-105.861070845276],[35.7363170944154,-105.861060703173],[35.7364224549383,-105.860935728997],[35.736524797976,-105.86089826189],[35.7366677932441,-105.86091385223],[35.7368468306959,-105.860983505845],[35.7369464077055,-105.861036060378],[35.7370332442224,-105.860988451168],[35.7370871398598,-105.860963389277],[35.7372463960201,-105.860822489485],[35.7372839469463,-105.860773371533],[35.7373381778598,-105.860727103427],[35.7374045625329,-105.860616210848],[35.7374069932848,-105.860568350181],[35.7374017126858,-105.860526273027],[35.7373510021716,-105.86038663052],[35.7372899819165,-105.860292166471],[35.7372402772307,-105.860219160095],[35.7371099386364,-105.860042050481],[35.7370259519666,-105.859889248386],[35.7369183283299,-105.859654052183],[35.7370687834918,-105.85962052457],[35.7372308894992,-105.859542824328],[35.7374249305576,-105.859662937],[35.7374600507319,-105.859675174579],[35.7376236654818,-105.859723035246],[35.7376301195472,-105.859726890922],[35.7377281039953,-105.859812637791],[35.7377686724067,-105.85982915014],[35.7378495577723,-105.85990794003],[35.7379388250411,-105.859907353297],[35.738089196384,-105.860002990812],[35.7381865940988,-105.860108938068],[35.738372085616,-105.860265009105],[35.7384376320988,-105.860279593617],[35.7385154999793,-105.860269367695],[35.7386735826731,-105.860169539228],[35.7387747522444,-105.860088150948],[35.7388122193515,-105.860085468739],[35.7388510275632,-105.860065100715],[35.7389040011913,-105.859978180379],[35.7389242015779,-105.859954375774],[35.7390108704567,-105.859811799601],[35.7390389498323,-105.859771231189],[35.7390651013702,-105.859744492918],[35.7391329109669,-105.859718089923],[35.7391933444887,-105.85970023647],[35.7392782531679,-105.859559336677],[35.7394809275866,-105.859528407454],[35.7394992839545,-105.859533017501],[35.7396794948727,-105.859583560377],[35.7397987693548,-105.85956688039],[35.739891724661,-105.859444336966],[35.740002701059,-105.859212493524],[35.7400559261441,-105.858985343948],[35.73999742046,-105.858779903501],[35.7400114182383,-105.858713686466],[35.7400778029114,-105.858636908233],[35.7402154337615,-105.858568176627],[35.7403420004994,-105.858448734507],[35.7403953932226,-105.858451332897],[35.7403787132353,-105.858446387574],[35.7403886877,-105.858424007893],[35.7404037751257,-105.858373967931],[35.7405145000666,-105.858320239931],[35.7406904362142,-105.858401460573],[35.740837790072,-105.85849349387],[35.7409439887851,-105.858628610149],[35.7410877384245,-105.858687199652],[35.7412577234209,-105.858702789992],[35.7414147164673,-105.858673872426],[35.7414891477674,-105.858623413369],[35.741717973724,-105.858577396721],[35.7419066503644,-105.858510844409],[35.7419229112566,-105.858498606831],[35.7420599553734,-105.858450578526],[35.7422089856118,-105.858417470008],[35.7423725165427,-105.858369274065],[35.7425163500011,-105.858321916312],[35.7426656316966,-105.858268775046],[35.742717012763,-105.858261398971],[35.7427121512592,-105.858262656257],[35.7427412364632,-105.858243042603],[35.7427996583283,-105.858226111159],[35.7428134046495,-105.858227116987],[35.7427872531116,-105.858245305717],[35.742759341374,-105.858230721205],[35.7427621912211,-105.858230805025],[35.7427510432899,-105.858308840543],[35.7427547313273,-105.858283778653],[35.7427764404565,-105.858133826405],[35.7427484448999,-105.85808319971],[35.7426927890629,-105.858031902462],[35.7425619475543,-105.857958560809],[35.7424871809781,-105.857956130058],[35.7424062956125,-105.857907850295],[35.7423776295036,-105.857899719849],[35.7421702612191,-105.857885135338],[35.742000611499,-105.857857475057],[35.7418954186141,-105.857755299658],[35.7417617272586,-105.857694111764],[35.7417308818549,-105.857614064589],[35.741762900725,-105.857496215031],[35.7418884616345,-105.857494873926],[35.7420198060572,-105.857508033514],[35.7421816606075,-105.857385406271],[35.7424021046609,-105.857364535332],[35.7425614446402,-105.857295636088],[35.7425726763904,-105.857285745442],[35.7428446691483,-105.857181474566],[35.7430268079042,-105.857155825943],[35.7430909294635,-105.8571283333],[35.7432140596211,-105.857004700229],[35.7433813624084,-105.856999251992],[35.7435108628124,-105.857020625845],[35.7437163870782,-105.857101846486],[35.7438617292792,-105.85717250593],[35.7440046407282,-105.857227658853],[35.7441578619182,-105.857226317748],[35.7443391624838,-105.857202596962],[35.7445471175015,-105.857101175934],[35.7447595987469,-105.857077874243],[35.7449735049158,-105.857024565339],[35.7451650314033,-105.856967987493],[35.7452662847936,-105.856960192323],[35.7454409636557,-105.856892969459],[35.7455946877599,-105.856896992773],[35.7457785028964,-105.856912247837],[35.7458674348891,-105.856903782114],[35.7460406050086,-105.856886431575],[35.7463502325118,-105.856925575063],[35.7464838400483,-105.857006208971],[35.7465458661318,-105.857063792646],[35.7465802319348,-105.857098242268],[35.7467078883201,-105.857210140675],[35.7467257417738,-105.857242913917],[35.7468214631081,-105.857315082103],[35.7470431644469,-105.857336288318],[35.7471652887762,-105.857263533399],[35.747357737273,-105.857263449579],[35.7475547119975,-105.857200669125],[35.7477972842753,-105.857113162056],[35.7480651699007,-105.857038144022],[35.7482318859547,-105.856984499842],[35.7482956722379,-105.856970502064],[35.7483530882746,-105.856973603368],[35.748581495136,-105.856947703287],[35.7487608678639,-105.856970921159],[35.7489749416709,-105.856956671923],[35.7490229699761,-105.857057506219],[35.7490932941437,-105.857140151784],[35.7491296716034,-105.857191868126],[35.7492279913276,-105.857373252511],[35.7493893429637,-105.857437960804],[35.7494929432869,-105.857469309121],[35.7496406324208,-105.857494035736],[35.7497910037637,-105.857508201152],[35.749834086746,-105.857568802312],[35.7499935943633,-105.85772998631],[35.7501342426986,-105.857717916369],[35.750291235745,-105.857664607465],[35.7504512462765,-105.857737949118],[35.7506193034351,-105.85780014284],[35.7508168648928,-105.857886057347],[35.7509284280241,-105.857961243019],[35.7510989159346,-105.858044391498],[35.7513554859906,-105.858060484752],[35.7515602558851,-105.858063418418],[35.7517107948661,-105.858089989051],[35.7519647665322,-105.858069034293],[35.7519771717489,-105.858070794493],[35.7519996352494,-105.858082529157],[35.7520191650838,-105.858092252165],[35.7521986216307,-105.858189398423],[35.7522049918771,-105.858318312094],[35.7522917445749,-105.858436748385],[35.7523888070136,-105.858430042863],[35.7525097578764,-105.858210604638],[35.7525841053575,-105.858136173338],[35.752659291029,-105.858095604926],[35.7527396734804,-105.858105160296],[35.7527947425842,-105.858148159459],[35.752839082852,-105.858142124489],[35.7528895419091,-105.858207000419],[35.7529332116246,-105.858232565224],[35.7529364805669,-105.858244718984],[35.7529359776527,-105.858249077573],[35.7529280986637,-105.858237007633],[35.7529432699084,-105.858294675127],[35.7529125921428,-105.85828403011],[35.7528633065522,-105.858206581324],[35.7528592832386,-105.858120918274],[35.7528032921255,-105.858118990436],[35.7527276035398,-105.858063166961],[35.7527528330684,-105.858067190275],[35.7527736201882,-105.857955794781],[35.7527777273208,-105.857916735113],[35.7528039626777,-105.857813889161],[35.7528156135231,-105.857800059021],[35.7528359815478,-105.857766866684],[35.7529541663826,-105.857554050162],[35.7529852632433,-105.857503674924],[35.7529926393181,-105.857475679368],[35.7529855985194,-105.857514068484],[35.7530007697642,-105.857526054606],[35.7530571799725,-105.857540639117],[35.7531763706356,-105.857555894181],[35.7531109917909,-105.85753259249],[35.7530642207712,-105.857498478144],[35.7529872749001,-105.857483809814],[35.7529666554183,-105.857470147312],[35.7529772166163,-105.857441229746],[35.7530078105628,-105.857404181734],[35.7530590239912,-105.857354812324],[35.7531745266169,-105.857185078785],[35.7533088047057,-105.857093883678],[35.7533202879131,-105.857088267803],[35.7533515524119,-105.857068905607],[35.7534669712186,-105.857001766562],[35.7535995729268,-105.856926329434],[35.7536813803017,-105.856881989166],[35.7538226153702,-105.856839576736],[35.7539361901581,-105.856745615602],[35.7540406286716,-105.856639668345],[35.7542071770877,-105.856535565108],[35.7543282117695,-105.856502372772],[35.7544307224452,-105.856428444386],[35.7545293774456,-105.85633800365],[35.7545636594296,-105.856316462159],[35.754733979702,-105.85631897673],[35.7547378353775,-105.856322832406],[35.7547594606876,-105.856401119381],[35.7547725364566,-105.856419811025],[35.7547981850803,-105.85639776662],[35.7548337243497,-105.856310259551],[35.7548638992012,-105.856271618977],[35.7549171242863,-105.856186039746],[35.7549950759858,-105.85616231896],[35.7550916355103,-105.856129294261],[35.7550867740065,-105.856138514355],[35.755090713501,-105.85612334311],[35.7550751231611,-105.856090066954],[35.7548746280372,-105.856152428314],[35.7547665014863,-105.856223925948],[35.7547371648252,-105.856239181012],[35.7547807507217,-105.856234403327],[35.75479734689,-105.85627136752],[35.7547751348466,-105.856310762465],[35.7547850254923,-105.856290059164],[35.7547804992646,-105.856263656169],[35.7548203133047,-105.856232643127],[35.7548319641501,-105.856196517125],[35.7547773141414,-105.856185620651],[35.7547632325441,-105.856138598174],[35.7548698503524,-105.856039524078],[35.7550349738449,-105.855973139405],[35.7551928050816,-105.855844058096],[35.7553503010422,-105.855709696189],[35.7554948050529,-105.855554966256],[35.7556243892759,-105.855455389246],[35.7557475194335,-105.855323038995],[35.7558595854789,-105.855178786442],[35.7559195999056,-105.855120867491],[35.7560199312866,-105.854973932728],[35.7560340128839,-105.854966137558],[35.7559764292091,-105.854981811717],[35.7558343559504,-105.855099493638],[35.755767384544,-105.855195214972],[35.755627322942,-105.855329995975],[35.7555208727717,-105.855387747288],[35.7553766202182,-105.855492772534],[35.7552460301667,-105.855593523011],[35.7551217265427,-105.855661751702],[35.7549318764359,-105.855791252106],[35.7547844387591,-105.855877166614],[35.7546590454876,-105.855978252366],[35.7544717937708,-105.856088642031],[35.7544636633247,-105.856099538505],[35.7544051576406,-105.856178328395],[35.7543313968927,-105.856246389449],[35.7542892359197,-105.856268852949],[35.7540994696319,-105.856416542083],[35.7539722323418,-105.856522992253],[35.7538845576346,-105.856563895941],[35.7537413109094,-105.85664444603],[35.7536758482456,-105.856651989743],[35.7536124810576,-105.856682918966],[35.7534952182323,-105.856756092981],[35.753364963457,-105.85687821731],[35.7531284261495,-105.857040407136],[35.7529773004353,-105.857310388237],[35.7528634741902,-105.857595289126],[35.7527844328433,-105.857782373205],[35.7527262624353,-105.857868958265],[35.7526949141175,-105.857983706519],[35.752601288259,-105.85806065239],[35.7525533437729,-105.858119828627],[35.7524363324046,-105.858222926036],[35.7523108553141,-105.858397940174],[35.7522660959512,-105.858310516924],[35.7521515153348,-105.858203312382],[35.7519622519612,-105.858058054],[35.7518060971051,-105.858055287972],[35.7516084518284,-105.857992088422],[35.7514166738838,-105.857883123681],[35.7511966489255,-105.857848422602],[35.7510384824127,-105.857772231102],[35.7508493866771,-105.857584476471],[35.7506700977683,-105.857476769015],[35.7504660822451,-105.857383226976],[35.7502798363566,-105.857660835609],[35.7501047383994,-105.857688076794],[35.7500397786498,-105.857618758455],[35.7499824464321,-105.857569053769],[35.7498523592949,-105.857490850613],[35.74975496158,-105.857505686581],[35.7495104614645,-105.857485150918],[35.7492999918759,-105.857551116496],[35.7491662167013,-105.85738808848],[35.7491430826485,-105.857229754329],[35.7491584215313,-105.856983577833],[35.749014923349,-105.856957342476],[35.7488066330552,-105.856998916715],[35.7486278470606,-105.857035042718],[35.7484222389758,-105.857038060203],[35.7482056505978,-105.857064798474],[35.7480224221945,-105.857067815959],[35.7478616572917,-105.857123471797],[35.7476606592536,-105.857200333849],[35.747471479699,-105.857246853411],[35.7473190966994,-105.857261521742],[35.7471845671535,-105.857306616381],[35.7469054497778,-105.857326230034],[35.746731357649,-105.8572582528],[35.7465947326273,-105.85708332248],[35.746448719874,-105.856945607811],[35.746268844232,-105.856872266158],[35.746205644682,-105.856851479039],[35.7460287027061,-105.856844354421],[35.7459245156497,-105.856825578958],[35.7457560393959,-105.856798253953],[35.7455438096076,-105.856918785721],[35.7453141454607,-105.856870925054],[35.7451164163649,-105.856959102675],[35.7449000794441,-105.856988942251],[35.744646107778,-105.857033617795],[35.7444446068257,-105.857074521482],[35.7442462909967,-105.85705306381],[35.7440232485533,-105.857026157901],[35.7438696082681,-105.857002437115],[35.7437246013433,-105.856867069378],[35.7437255233526,-105.85687042214],[35.7435576338321,-105.856896573678],[35.7433295622468,-105.856965892017],[35.743295615539,-105.856978800148],[35.7431755866855,-105.857099164277],[35.7430861517787,-105.85710763],[35.7429066952318,-105.857137637213],[35.7428000774235,-105.857217013836],[35.7426190283149,-105.857295133173],[35.7425231393427,-105.857336204499],[35.7424034457654,-105.857312148437],[35.742354914546,-105.857324134558],[35.7422211393714,-105.857476182282],[35.7420103345066,-105.857503255829],[35.7418683450669,-105.857546171173],[35.7418534252793,-105.857703331858],[35.7419157028198,-105.857778852805],[35.7419851049781,-105.857851272449],[35.7420391682535,-105.857892930508],[35.7420752104372,-105.857907682657],[35.7421472109854,-105.857971720397],[35.7421721890569,-105.857972810045],[35.7421971671283,-105.857972810045],[35.7422415073961,-105.857983874157],[35.7424187846482,-105.858009187505],[35.7425758615136,-105.858063586056],[35.7427093014121,-105.858161486685],[35.7427103910595,-105.858208257705],[35.7426779530942,-105.858278581873],[35.7426854129881,-105.858327616006],[35.7427258137614,-105.85830305703],[35.7427641190588,-105.858312109485],[35.7427818048745,-105.858306577429],[35.7427588384598,-105.858314372599],[35.7427392248064,-105.858314791694],[35.7426876761019,-105.858309092],[35.7426978182048,-105.85833163932],[35.7426783721894,-105.858356617391],[35.7426760252565,-105.858343793079],[35.7426497898996,-105.858335578814],[35.742674600333,-105.858329376206],[35.7426832336932,-105.858359718695],[35.7426426652819,-105.858365753666],[35.7425306830555,-105.858436664566],[35.7425104826689,-105.858438760042],[35.7423808146268,-105.858504893258],[35.7423359714448,-105.858541354537],[35.7421941496432,-105.858611008152],[35.7420571055263,-105.858697341755],[35.7419682573527,-105.858739083633],[35.7418887130916,-105.858765235171],[35.7417263556272,-105.858862632886],[35.7415702845901,-105.858961120248],[35.7414733059704,-105.859084334224],[35.7414028979838,-105.859221378341],[35.7413944322616,-105.859283907339],[35.7412811927497,-105.859512230381],[35.7411827892065,-105.859723705798],[35.7411748263985,-105.859788665548],[35.7411323301494,-105.85982915014],[35.741046750918,-105.859895367175],[35.7409926876426,-105.859981784597],[35.7409784384072,-105.860006259754],[35.7408252172172,-105.860152775422],[35.7407469302416,-105.86027498357],[35.7406689785421,-105.860425690189],[35.7405862491578,-105.860557118431],[35.7404883485287,-105.860611768439],[35.7404108159244,-105.86068100296],[35.7403943873942,-105.860708830878],[35.7403511367738,-105.8607934881],[35.7402160204947,-105.86082466878],[35.7401614543051,-105.860851658508],[35.7401247415692,-105.860876888037],[35.7399794831872,-105.861005801708],[35.7398038823158,-105.861064558849],[35.7397827599198,-105.861081238836],[35.7396409381181,-105.861097415909],[35.7394389342517,-105.861060032621],[35.7393176481128,-105.861069504172],[35.7391859684139,-105.861137397587],[35.7390304002911,-105.86123236455],[35.7388785202056,-105.861299335957],[35.7386994827539,-105.861184336245],[35.7385639473796,-105.861311824992],[35.7385264802724,-105.8613457717],[35.7383816409856,-105.861477702856],[35.7382491230965,-105.861604101956],[35.7380577642471,-105.86169202812],[35.737885935232,-105.861708372831],[35.7378450315446,-105.861696135253],[35.7376421894878,-105.861812308431],[35.7375908922404,-105.861914651468],[35.7374272774905,-105.862001487985],[35.7373357471079,-105.862062592059],[35.737190740183,-105.862125288695],[35.7370740640908,-105.862057479098],[35.7368500996381,-105.861977515742],[35.7368526980281,-105.862007522956],[35.7367724832147,-105.862029064447],[35.7367601618171,-105.862031159922],[35.7366283144802,-105.862136771902],[35.7365931104869,-105.862167784944],[35.7364699803293,-105.86226769723],[35.7364491093904,-105.862280270085],[35.7364014163613,-105.862324777991],[35.736249871552,-105.862358976156],[35.7362330239266,-105.862366687506],[35.7361259870231,-105.862505408004],[35.7361070439219,-105.862576738],[35.736067565158,-105.862617474049],[35.7360494602472,-105.862639434636],[35.7359854225069,-105.862688133493],[35.7359390705824,-105.862695761025],[35.7358446903527,-105.8627031371],[35.7357764616609,-105.862683858722],[35.7356154453009,-105.86272652261],[35.735515197739,-105.862768432125],[35.7353755552322,-105.862912097946],[35.7353411894292,-105.862930621952],[35.7351916562766,-105.863089961931],[35.7351019699126,-105.863122483715],[35.735063161701,-105.863160621375],[35.7349401153624,-105.863249050453],[35.7347943540663,-105.863365475088],[35.7346214354038,-105.863520205021],[35.7344301603734,-105.863644257188],[35.7342973072082,-105.863725896925],[35.7341445051134,-105.86376093328],[35.7340627815574,-105.863756826147],[35.7340387254953,-105.863751545548],[35.7340024318546,-105.863731596619],[35.7339616119862,-105.863735619932],[35.7338461931795,-105.863693710417],[35.7337269186974,-105.863710055128],[35.7335255015641,-105.86367786862],[35.7334826700389,-105.86368817836],[35.7334420178086,-105.863706534728],[35.7332421094179,-105.863809045404],[35.733136748895,-105.863847099245],[35.733020408079,-105.86384399794],[35.7329228427261,-105.863967044279],[35.7328149676323,-105.864098807797],[35.7328053284436,-105.864110542461],[35.732761323452,-105.864169383422],[35.732737518847,-105.864211795852],[35.7327361777425,-105.864209700376],[35.7326047495008,-105.864317240193],[35.732431076467,-105.864449255168],[35.732356980443,-105.864502061158],[35.732182636857,-105.864607421681],[35.7320199441165,-105.864712698385],[35.7319860812277,-105.864732395858],[35.7319013401866,-105.86479106918],[35.7317493762821,-105.864864327013],[35.7316228933632,-105.864872876555],[35.7315915450454,-105.864879079163],[35.7315465342253,-105.864891316742],[35.7313812430948,-105.864960635081],[35.7313195522875,-105.864972034469],[35.731127941981,-105.865018051118],[35.7309302128851,-105.865057948977],[35.7308537699282,-105.865083681419],[35.7307152170688,-105.865134559572],[35.7305883150548,-105.865190215409],[35.730536179617,-105.865206811577],[35.7304069306701,-105.865294570103],[35.7303770072758,-105.865304460749],[35.7303148135543,-105.865336982533],[35.7302615884691,-105.86536581628],[35.7302093692124,-105.865397751331],[35.7301683817059,-105.865434380248],[35.7299984805286,-105.865521552041],[35.7298473548144,-105.865623056889],[35.7296659704298,-105.865714922547],[35.729465810582,-105.865815337747],[35.7292707636952,-105.865911813453],[35.7290872838348,-105.866019520909],[35.7290339749306,-105.866040894762],[35.7289551850408,-105.866072913632],[35.7288774847984,-105.866061598063],[35.7286948431283,-105.866066208109],[35.728506417945,-105.86603586562],[35.7284374348819,-105.866032177582],[35.7283659372479,-105.866061933339],[35.728355795145,-105.866033770144] ] });
				GV_Draw_Track(t);
				
				t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});
				
				
				GV_Draw_Marker({lat:35.7339333,lon:-105.8625167,name:'Ready for a snowy hike',desc:'WinterWorthy',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27295030694/in/photostream/',thumbnail:'https://c7.staticflickr.com/8/7503/27295030694_487bf861cc.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.7426872,lon:-105.8582916,name:'Same place, different time!',desc:'WinterVersion',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27872535606/in/photostream/',thumbnail:'https://c7.staticflickr.com/8/7127/27872535606_9a2142e255.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.7298050,lon:-105.8656082,name:'Heading back on a snowy day',desc:'WinterTrip',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27806067682/in/photostream/',thumbnail:'https://c3.staticflickr.com/8/7626/27806067682_32b2f4974e.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.7312408,lon:-105.8649025,name:'Jack takes the lead on Chamisa',desc:'JackBlazesTheTrail',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27806040252/in/photostream/',thumbnail:'https://c5.staticflickr.com/8/7500/27806040252_9f507083f9.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.7427472,lon:-105.8581625,name:'Which way to go?',desc:'Decisions',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27295044574/in/photostream/',thumbnail:'https://c7.staticflickr.com/8/7439/27295044574_4254e17081.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.7299917,lon:-105.8654917,name:'Summer\'s return path',desc:'ClosingTheLoop',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27806051982/in/photostream/',thumbnail:'https://c7.staticflickr.com/8/7450/27806051982_f7918d4a43.jpg',folder:'Folder1'});
				GV_Draw_Marker({lat:35.7327611,lon:-105.8642583,name:'Bill explores the beautiful valley',desc:'BillInTheValley',color:'',icon:'',url:'https://www.flickr.com/photos/139088815@N08/27872545616/in/photostream/',thumbnail:'https://c1.staticflickr.com/8/7309/27872545616_531117cd93.jpg',folder:'Folder1'});
				
				
				GV_Finish_Map();
					
			  
			}
			GV_Map(); // execute the above code
			// http://www.gpsvisualizer.com/map_input?form=google&trk_list=0&wpt_color=orange
		</script>
		
		
		
	</body>

</html>
