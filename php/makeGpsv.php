<?php 
function distance($lat1, $lon1, $lat2, $lon2) {
    if ($lat1 === $lat2 && $lon1 === $lon2) {
        return array (0,0);
    }
    $radlat1 = deg2rad($lat1);
    $radlat2 = deg2rad($lat2);
    $theta = $lon1 - $lon2;
    $dist = sin($radlat1) * sin($radlat2) +  cos($radlat1) * cos($radlat2) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    if (is_nan($miles)) {
        $err = $lat1 . ',' . $lon1 . '; ' . $lat2 . ',' . $lon2;
    }
    # angles using planar coords: ASSUME a minute/seconds in lat = minute/seconds in lng
    $dely = $lat2 - $lat1;
    $delx = $lon2 - $lon1;
    $radang = atan2($dely, $delx);
    $angle = rad2deg($radang);
    // Convert Euclid Angle to GPSV Rotation
    if ($dely >= 0) {
            if ($delx >= 0) {
                $rotation = 90.0 - $angle;	// Northeast
            } else {
                $rotation = 450.0 - $angle; // Northwest
            } 
    }
    else {
        $rotation = 90.0 + -$angle;	// South
    }
    $rotation = round($rotation);
    return array ($miles,$rotation);
}
# END FUNCTION

# Error messages:
$intro = '<p style="color:red;left-margin:12px;font-size:18px;">';
$close = '</p>';
$mapmsg = $intro . 'Could not open tmp map file - contact Site Master';
$trkmsg = $intro . 'Could not open track file: ';
$tsvmsg = $intro . 'Could not open tsv file ';

# Settings:
$noOfTrks = 1;  // for a single hike page, this is a reasonable constraint
$tno = 1;

# Using variables established in main: $hikeIndexNo, $hikeTitle, $gpsvFile, $jsonFile
# gpsvMap ini:
$extLoc = strrpos($gpsvFile,'.');
$gpsvMap = substr($gpsvFile,0,$extLoc);

# holding place for page's hike map
$tmpMap = '../maps/tmp/' . $gpsvMap . '.html';
if ( ($mapHandle = fopen($tmpMap,"w")) === false) {
    die ($mapmsg);
}
# Files: tsv  file
$gpsvPath = '../gpsv/' . $gpsvFile;
$gpsvData = file($gpsvPath);
if ($gpsvData === false) {
    die ($tsvmsg . $gpsvPath . $close);
} 
# Files: JSON track file
$trkPath = '../json/' . $jsonFile;
if (($track = fopen($trkPath,"r")) === false) {
    die ($trkmsg . $trkPath . $close );
}
/* 
 * Getting track data from track.json and formatting for gpsv:
 */
$jlats = [];
$jlons = [];
$jindx = 0;
while ( ($jsonData = fgets($track)) !== false ) {
    $latstrt = strpos($jsonData,":") + 2;
    $latend = strpos($jsonData,",");
    $latlgth = $latend - $latstrt;
    $jlats[$jindx] = substr($jsonData,$latstrt,$latlgth);
    $lonstrt = strpos($jsonData,"lng") + 6;
    $lonend = strpos($jsonData," ",$lonstrt);
    $lonlgth = $lonend - $lonstrt;
    $jlons[$jindx] = substr($jsonData,$lonstrt,$lonlgth);
    $jindx++;
}
fclose($track);
$north = $jlats[0];
$south = $north;
$east = $jlons[0];
$west = $east;
$seg = '[' . $north . ',' . $east . ']';
$hikeLgth = 0;
$tickMrk = round(0.3,1);
$ticks = [];
for ($i=1; $i<count($jlons)-1; $i++) {
    if ($jlats[$i] > $north) {
        $north = $jlats[$i];
    }
    if ($jlats[$i] < $south) {
        $south = $jlats[$i];
    }
    if ($jlons[$i] < $west) {
        $west = $jlons[$i];
    }
    if ($jlons[$i] > $east) {
        $east = $jlons[$i];
    }
    $seg .= ',[' . $jlats[$i] . ',' . $jlons[$i] . ']';
    $parms = distance($jlats[$i-1],$jlons[$i-1],$jlats[$i],$jlons[$i]);
    $hikeLgth += $parms[0];
    if ($hikeLgth > $tickMrk) {
        $tick = "GV_Draw_Marker({lat:" . $jlats[$i] . ",lon:" . $jlons[$i] .
            ",name:'" . $tickMrk . " mi',desc:'',color:trk[" . $tno . 
            "].info.color,icon:'tickmark',type:'tickmark',folder:'" . $hikeTitle .
            " [tickmarks]',rotation:" . $parms[1] . ",track_number:" . $tno . ",dd:false});";
        array_push($ticks,$tick);
        $tickMrk += round(0.3,1);
    }
}
$clat = $south + ($north - $south)/2;
$clon = $west + ($east - $west)/2;
$lastpoints = '[' . $jlats[$jindx-1] . ',' . $jlons[$jindx-1] . ']';
/*
 * Form the photo links from the gpsvData
 */
$plnks = [];
$headerline = true;
$defIconColor = 'red';
# default large nos to see if index value gets set...
$folder = 10000;
$desc = 10000;
$nme = 10000;
$tsvLat = 10000;
$tsvLng = 10000;
$thumb = 10000;
$albumUrl = 10000;
$icn = 10000;
$icolor = 10000;
foreach ($gpsvData as $entry) {
    # eliminate ending \n:
    $stripped = substr($entry,0,strlen($entry)-1);
    $rawdat = explode("\t",$stripped);
    if ($headerline) {
        # use header row to get indices
        for ($p=0; $p<count($rawdat); $p++) {
            switch ($rawdat[$p]) {
                case 'folder':
                    $folder = $p;
                    break;
                case 'desc':
                    $desc = $p;
                    break;
                case 'name':
                    $nme = $p;
                    break;
                case 'Latitude':
                    $tsvLat = $p;
                    break;
                case 'Longitude':
                    $tsvLng = $p;
                    break;
                case 'thumbnail':
                    $thumb = $p;
                    break;
                case 'url':
                    $albumUrl = $p;
                    break;
                case 'symbol':
                    $icn = $p;
                    break;
                case 'color':
                    $icolor = $p;
                    break;
                default:
                    break;
            }
        }
        $headerline = false;
    } else {
        if ($icolor === 10000) {
            $icon = $defIconColor;
        } else {
            #$out = "indx: " . $icolor . " value: " . (string)$rawdat[$icolor];
            $icon = $rawdat[$icolor];
        }
        if ($icn === 10000) {
            $mapicon = '';
        } else {
            $mapicon = $rawdat[$icn];
        }
        $procName = preg_replace("/'/","\'",$rawdat[$nme]);
        $procName = preg_replace('/"/','\"',$procName);
        $plnk = "GV_Draw_Marker({lat:" . $rawdat[$tsvLat] . ",lon:" . $rawdat[$tsvLng] . 
            ",name:'" . $procName . "',desc:'" . $rawdat[$desc] . 
            "',color:'" . $icon . "',icon:'" . $mapicon . 
            "',url:'" . $rawdat[$albumUrl] . "',thumbnail:'" . $rawdat[$thumb] .
            "',folder:'" . $rawdat[$folder] . "'});";
        array_push($plnks,$plnk);
    }   
}
/*
 * The next section copies the template for GPSV.html into a variable to be
 * passed to javascript to create the iframe with map.
 */
#gpsv html declaration:
$html = '<!DOCTYPE html>' . "\n";
$html .= '<html>' . "\n";
# gpsv <head> element:
$html .= '<head>' . "\n" .
        '    <title>' . $hikeTitle . '</title>' . "\n" .
        '    <base target="_top">' . "\n" .
        '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\n" .
        '    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">' . "\n" .
        '    <meta name="geo.position" content="' . $clat . ', ' . $clon . '" />' . "\n" .
        '    <meta name="ICBM" content="' . $clat . ', ' . $clon . '" />' . "\n" .
        '    <style type="text/css">' . "\n" .
        '       #gmap_div .gv_marker_info_window {' . "\n" .
        '           font-size:11px !important; ' . "\n" .
        '       }' . "\n" .
        '       #gmap_div .gv_label {' . "\n" .
        '           opacity:0.80; filter:alpha(opacity=80);' . "\n" .
        '           color:white; background-color:#333333; border:1px solid black; padding:1px;' . "\n" .
        '           font:9px Verdana !important;' . "\n" .
        '           font-weight:normal !important;' . "\n" .
        '       }' . "\n" .
        '    </style>' . "\n" .
        '</head>' . "\n" .
        '<body>' . "\n";
# gpsv initial script & map div:
$html .= '<script type="text/javascript">' . "\n" .
        "    google_api_key = 'AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU';" . "\n" .
        "    language_code = '';" . "\n" .
        "    if (document.location.toString().indexOf('http://www.gpsvisualizer.com') > -1) { google_api_key = ''; }" . "\n" .
        "    document.writeln('<script type=" . '"text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=geometry&amp;language=' . "'+(self.language_code?self.language_code:'')+'&amp;key='+(self.google_api_key?self.google_api_key:'')+'" . '"' . "><'+'/script>');" . "\n" .
        '</script>' . "\n" .
        '<div style="margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px;">' . "\n" .
        '    <div id="gmap_div" style="width:700px; height:700px; margin:0px;  background-color:#f0f0f0; float:left; overflow:hidden;">' . "\n" .
        '        <p align="center" style="font:10px Arial;">This map was created using <a target="_blank" href="http://www.gpsvisualizer.com/">GPS Visualizer</a>' . "s do-it-yourself geographic utilities.<br /><br />Please wait while the map data loads...</p>" . "\n" .
        '    </div>' . "\n" .
        '    <div id="gv_infobox" class="gv_infobox" style="font:11px Arial; border:solid #666666 1px; background-color:#ffffff; padding:4px; overflow:auto; display:none; max-width:400px;">' . "\n" .
        "        <!-- Although GPS Visualizer didn't create an legend/info box with your map, you can use this space for something else if you'd like; enable it by setting     gv_options.infobox_options.enabled to true -->" . "\n" .
        '    </div>' . "\n" .
        '    <div id="gv_tracklist" class="gv_tracklist" style="font:11px Arial; line-height:11px; background-color:#ffffff; overflow:auto; display:none;">' . "\n" .
        '    </div>' . "\n" .
        '    <div id="gv_marker_list" class="gv_marker_list" style="background-color:#ffffff; overflow:auto; display:none;">' . "\n" .
        '    </div>' . "\n" .
        '    <div id="gv_clear_margins" style="height:0px; clear:both;">' . "\n" .
        '    </div>' . "\n" .
        '</div>' . "\n";
# begin GPS Visualizer setup script
$html .= '<!-- begin GPS Visualizer setup script (must come after maps.google.com code) -->' . "\n";
$html .= '<script type="text/javascript">' . "\n"; 
$html .= '/* Global variables used by the GPS Visualizer functions (20170530080154): */' . "\n";
$html .= '    gv_options = {};' . "\n";     
$html .= '// basic map parameters:' . "\n";
$html .= '    gv_options.center = [' .$clat . ',' . $clon . '];  // [latitude,longitude] - be sure to keep the square brackets' . "\n";
$html .= "    gv_options.zoom = 'auto';  // higher number means closer view; can also be 'auto' for automatic zoom/center based on map elements" . "\n";
$html .= "    gv_options.map_type = 'GV_HYBRID';  // popular map_type choices are 'GV_STREET', 'GV_SATELLITE', 'GV_HYBRID', 'GV_TERRAIN', 'GV_OSM', 'GV_TOPO_US', 'GV_TOPO_WORLD' (http://www.gpsvisualizer.com/misc/google_map_types.html)" . "\n";
$html .= '    gv_options.map_opacity = 1.00;  // number from 0 to 1' . "\n";
$html .= '    gv_options.full_screen = true;  // true|false: should the map fill the entire page (or frame)?' . "\n";
$html .= '    gv_options.width = 700;  // width of the map, in pixels' . "\n";
$html .= '    gv_options.height = 300;  // height of the map, in pixels' . "\n";
$html .= "    gv_options.map_div = 'gmap_div';  // the name of the HTML div tag containing the map itself; usually 'gmap_div'" . "\n";
$html .= '    gv_options.doubleclick_zoom = true;  // true|false: zoom in when mouse is double-clicked?' . "\n";
$html .= '    gv_options.doubleclick_center = true;  // true|false: re-center the map on the point that was double-clicked?' . "\n";
$html .= "    gv_options.scroll_zoom = true; // true|false; or 'reverse' for down=in and up=out" . "\n";
$html .= '    gv_options.autozoom_adjustment = 0;' . "\n";
$html .= "    gv_options.centering_options = { 'open_info_window':true, 'partial_match':true, 'center_key':'center', 'default_zoom':null } // URL-based centering (e.g., ?center=name_of_marker&zoom=14)" . "\n";
$html .= '    gv_options.tilt = false; // true|false: allow Google to show 45-degree tilted aerial imagery?' . "\n";
$html .= '    gv_options.street_view = true; // true|false: allow Google Street View on the map' . "\n";
$html .= '    gv_options.animated_zoom = false; // true|false: may or may not work properly' . "\n";
$html .= '    gv_options.disable_google_pois = false;  // true|false: if you disable clickable POIs, you also lose the labels on parks, airports, etc.' . "\n";
$html .= '// widgets on the map:' . "\n";
$html .= "    gv_options.zoom_control = 'large'; // 'large'|'small'|'none'" . "\n";
$html .= '    gv_options.recenter_button = true; // true|false: is there a "click to recenter" option in the zoom control?' . "\n";
$html .= '    gv_options.scale_control = true; // true|false' . "\n";
$html .= '    gv_options.map_opacity_control = false;  // true|false: does it appear on the map itself?' . "\n";
$html .= '    gv_options.map_type_control = {};  // widget to change the background map' . "\n";
$html .= '    gv_options.map_type_control.visible = true;  // true|false: does it appear on the map itself?' . "\n";
$html .= '    gv_options.map_type_control.filter = false;  // true|false: when map loads, are irrelevant maps ignored?' . "\n";
$html .= '    gv_options.map_type_control.excluded = [];  // comma-separated list of quoted map IDs that will never show in the list ("included" also works)' . "\n";
$html .= '    gv_options.center_coordinates = true;  // true|false: show a "center coordinates" box and crosshair?' . "\n";
$html .= '    gv_options.measurement_tools = true;  // true|false: does it appear on the map itself?' . "\n";
$html .= "    gv_options.measurement_options = { visible:false, distance_color:'', area_color:'' }" . "\n";
$html .= '    gv_options.crosshair_hidden = true;  // true|false: hide the crosshair initially?' . "\n";
$html .= '    gv_options.mouse_coordinates = false;  // true|false: show a "mouse coordinates" box?' . "\n";
$html .= "    gv_options.utilities_menu = { 'maptype':true, 'opacity':true, 'measure':true, 'export':true };" . "\n";
$html .= '    gv_options.allow_export = true;  // true|false' . "\n";
$html .= '    gv_options.infobox_options = {}; // options for a floating info box (id="gv_infobox"), which can contain anything' . "\n";
$html .= '    gv_options.infobox_options.enabled = true;  // true|false: enable or disable the info box altogether' . "\n";
$html .= "    gv_options.infobox_options.position = ['RIGHT_TOP',4,84];  // [Google anchor name, relative x, relative y]" . "\n";
$html .= '    gv_options.infobox_options.draggable = true;  // true|false: can it be moved around the screen?' . "\n";
$html .= '    gv_options.infobox_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?' . "\n";
$html .= '// track-related options:' . "\n";
$html .= '    gv_options.track_tooltips = true; // true|false: should the name of a track appear on the map when you mouse over the track itself?' . "\n";
$html .= '    gv_options.tracklist_options = {}; // options for a floating list of the tracks visible on the map' . "\n";
$html .= '    gv_options.tracklist_options.enabled = true;  // true|false: enable or disable the tracklist altogether' . "\n";
$html .= "    gv_options.tracklist_options.position = ['RIGHT_TOP',4,32];  // [Google anchor name, relative x, relative y]" . "\n";
$html .= '    gv_options.tracklist_options.min_width = 100; // minimum width of the tracklist, in pixels' . "\n";
$html .= '    gv_options.tracklist_options.max_width = 180; // maximum width of the tracklist, in pixels' . "\n";
$html .= '    gv_options.tracklist_options.min_height = 0; // minimum height of the tracklist, in pixels; if the list is longer, scrollbars will appear' . "\n";
$html .= '    gv_options.tracklist_options.max_height = 310; // maximum height of the tracklist, in pixels; if the list is longer, scrollbars will appear' . "\n";
$html .= '    gv_options.tracklist_options.desc = true;  // true|false: should tracks descriptions be shown in the list' . "\n";
$html .= '    gv_options.tracklist_options.toggle = true;  // true|false: should clicking on a tracks name turn it on or off?' . "\n";
$html .= '    gv_options.tracklist_options.checkboxes = true;  // true|false: should there be a separate icon/checkbox for toggling visibility?' . "\n";
$html .= '    gv_options.tracklist_options.zoom_links = true;  // true|false: should each item include a small icon that will zoom to that track?' . "\n";
$html .= '    gv_options.tracklist_options.highlighting = true;  // true|false: should the track be highlighted when you mouse over the name in the list?' . "\n";
$html .= '    gv_options.tracklist_options.tooltips = false;  // true|false: should the name of the track appear on the map when you mouse over the name in the list?' . "\n";
$html .= '    gv_options.tracklist_options.draggable = true;  // true|false: can it be moved around the screen?' . "\n";
$html .= '    gv_options.tracklist_options.collapsible = true;  // true|false: can it be collapsed by double-clicking its top bar?' . "\n";
$html .= "    gv_options.tracklist_options.header = 'Tracks:'; // HTML code; be sure to put backslashes in front of any single quotes, and don't include any line breaks" . "\n";
$html .= "    gv_options.tracklist_options.footer = ''; // HTML code" . "\n";
$html .= '// marker-related options:' . "\n";
$html .= "    gv_options.default_marker = { color:'red',icon:'googlemini',scale:1 }; // icon can be a URL, but be sure to also include size:[w,h] and optionally anchor:[x,y]" . "\n";
$html .= '    gv_options.vector_markers = false; // are the icons on the map in embedded SVG format?' . "\n";
$html .= '    gv_options.marker_tooltips = true; // do the names of the markers show up when you mouse-over them?' . "\n";
$html .= '    gv_options.marker_shadows = true; // true|false: do the standard markers have "shadows" behind them?' . "\n";
$html .= "    gv_options.marker_link_target = '_blank'; // the name of the window or frame into which markers' URLs will load" . "\n";
$html .= '    gv_options.info_window_width = 0;  // in pixels, the width of the markers pop-up info "bubbles" (can be overridden by "window_width" in individual markers)' . "\n";
$html .= '    gv_options.thumbnail_width = 0;  // in pixels, the width of the markers thumbnails (can be overridden by thumbnail_width in individual markers)' . "\n";
$html .= '    gv_options.photo_size = [0,0];  // in pixels, the size of the photos in info windows (can be overridden by photo_width or photo_size in individual markers)' . "\n";
$html .= '    gv_options.hide_labels = false;  // true|false: hide labels when map first loads?' . "\n";
$html .= '    gv_options.labels_behind_markers = false; // true|false: are the labels behind other markers (true) or in front of them (false)?' . "\n";
$html .= '    gv_options.label_offset = [0,0];  // [x,y]: shift all markers labels (positive numbers are right and down)' . "\n";
$html .= '    gv_options.label_centered = false;  // true|false: center labels with respect to their markers?  (label_left is also a valid option.)' . "\n";
$html .= '    gv_options.driving_directions = true;  // put a small "driving directions" form in each markers pop-up window? (override with dd:true or dd:false in a markers options)' . "\n";
$html .= "    gv_options.garmin_icon_set = 'gpsmap'; // 'gpsmap' are the small 16x16 icons; change it to '24x24' for larger icons" . "\n";
$html .= '    gv_options.marker_list_options = {};  // options for a dynamically-created list of markers' . "\n";
$html .= '    gv_options.marker_list_options.enabled = false;  // true|false: enable or disable the marker list altogether' . "\n";
$html .= '    gv_options.marker_list_options.floating = true;  // is the list a floating box inside the map itself?' . "\n";
$html .= "    gv_options.marker_list_options.position = ['RIGHT_BOTTOM',6,38];  // floating list only: position within map" . "\n";
$html .= '    gv_options.marker_list_options.min_width = 160; // minimum width, in pixels, of the floating list' . "\n";
$html .= '    gv_options.marker_list_options.max_width = 160;  // maximum width' . "\n";
$html .= '    gv_options.marker_list_options.min_height = 0;  // minimum height, in pixels, of the floating list' . "\n";
$html .= '    gv_options.marker_list_options.max_height = 310;  // maximum height' . "\n";
$html .= '    gv_options.marker_list_options.draggable = true;  // true|false, floating list only: can it be moved around the screen?' . "\n";
$html .= '    gv_options.marker_list_options.collapsible = true;  // true|false, floating list only: can it be collapsed by double-clicking its top bar?' . "\n";
$html .= '    gv_options.marker_list_options.include_tickmarks = false;  // true|false: are distance/time tickmarks included in the list?' . "\n";
$html .= '    gv_options.marker_list_options.include_trackpoints = false;  // true|false: are "trackpoint" markers included in the list?' . "\n";
$html .= '    gv_options.marker_list_options.dividers = false;  // true|false: will a thin line be drawn between each item in the list?' . "\n";
$html .= '    gv_options.marker_list_options.desc = false;  // true|false: will the markers descriptions be shown below their names in the list?' . "\n";
$html .= '    gv_options.marker_list_options.icons = true;  // true|false: should the markers icons appear to the left of their names in the list?' . "\n";
$html .= '    gv_options.marker_list_options.thumbnails = false;  // true|false: should markers thumbnails be shown in the list?' . "\n";
$html .= '    gv_options.marker_list_options.folders_collapsed = false;  // true|false: do folders in the list start out in a collapsed state?' . "\n";
$html .= '    gv_options.marker_list_options.folders_hidden = false;  // true|false: do folders in the list start out in a hidden state?' . "\n";
$html .= '    gv_options.marker_list_options.collapsed_folders = []; // an array of folder names' . "\n";
$html .= '    gv_options.marker_list_options.hidden_folders = []; // an array of folder names' . "\n";
$html .= '    gv_options.marker_list_options.count_folder_items = false;  // true|false: list the number of items in each folder?' . "\n";
$html .= '    gv_options.marker_list_options.wrap_names = true;  // true|false: should markers names be allowed to wrap onto more than one line?' . "\n";
$html .= "    gv_options.marker_list_options.unnamed = '[unnamed]';  // what 'name' should be assigned to  unnamed markers in the list?" . "\n";
$html .= '    gv_options.marker_list_options.colors = false;  // true|false: should the names/descs of the points in the list be colorized the same as their markers?' . "\n";
$html .= "    gv_options.marker_list_options.default_color = '';  // default HTML color code for the names/descs in the list" . "\n";
$html .= '    gv_options.marker_list_options.limit = 0;  // how many markers to show in the list; 0 for no limit' . "\n";
$html .= '    gv_options.marker_list_options.center = false;  // true|false: does the map center upon a marker when you click its name in the list?' . "\n";
$html .= '    gv_options.marker_list_options.zoom = false;  // true|false: does the map zoom to a certain level when you click on a markers name in the list?' . "\n";
$html .= "    gv_options.marker_list_options.zoom_level = 17;  // if 'zoom' is true, what level should the map zoom to?" . "\n";
$html .= '    gv_options.marker_list_options.info_window = true;  // true|false: do info windows pop up when the markers names are clicked in the list?' . "\n";
$html .= '    gv_options.marker_list_options.url_links = false;  // true|false: do the names in the list become instant links to the markers URLs?' . "\n";
$html .= '    gv_options.marker_list_options.toggle = false;  // true|false: does a marker disappear if you click on its name in the list?' . "\n";
$html .= '    gv_options.marker_list_options.help_tooltips = false;  // true|false: do "tooltips" appear on marker names that tell you what happens when you click?' . "\n";
$html .= "    gv_options.marker_list_options.id = 'gv_marker_list';  // id of a DIV tag that holds the list" . "\n";
$html .= "    gv_options.marker_list_options.header = ''; // HTML code; be sure to put backslashes in front of any single quotes, and dont include any line breaks" . "\n";
$html .= "    gv_options.marker_list_options.footer = ''; // HTML code" . "\n";
$html .= '    gv_options.marker_filter_options = {};  // options for removing waypoints that are out of the current view' . "\n";
$html .= '    gv_options.marker_filter_options.enabled = false;  // true|false: should out-of-range markers be removed?' . "\n";
$html .= '    gv_options.marker_filter_options.movement_threshold = 8;  // in pixels, how far the map has to move to trigger filtering' . "\n";
$html .= '    gv_options.marker_filter_options.limit = 0;  // maximum number of markers to display on the map; 0 for no limit' . "\n";
$html .= '    gv_options.marker_filter_options.update_list = true;  // true|false: should the marker list be updated with only the filtered markers?' . "\n";
$html .= '    gv_options.marker_filter_options.sort_list_by_distance = false;  // true|false: should the marker list be sorted by distance from the center of the map?' . "\n";
$html .= '    gv_options.marker_filter_options.min_zoom = 0;  // below this zoom level, dont show any markers at all' . "\n";
$html .= "    gv_options.marker_filter_options.zoom_message = '';  // message to put in the marker list if the map is below the min_zoom threshold" . "\n";
$html .= "    gv_options.synthesize_fields = {}; // for example: {label:'{name}'} would cause all markers' names to become visible labels" . "\n";
$html .= "// Load GPS Visualizer's Google Maps functions (this must be loaded AFTER     gv_options are set):" . "\n";
$html .= "    if (window.location.toString().indexOf('https://') == 0) { // secure pages require secure scripts" . "\n";
$html .= "        document.writeln('<script src=" . '"https://gpsvisualizer.com/google_maps/functions3.js" type="text/javascript"><' . "'+'/script>');" . "\n";
$html .= '    } else {' . "\n";
$html .= "        document.writeln('<script src=" . '"http://maps.gpsvisualizer.com/google_maps/functions3.js" type="text/javascript">' . "<'+'/script>');" . "\n";
$html .= '    }' . "\n";
$html .= '</script>' . "\n";
$html .= '<!-- end GPSV setup script and styles; begin map-drawing script (they must be separate) -->' . "\n";
$html .=  '<script type="text/javascript">' . "\n";
$html .= '    function GV_Map() {' . "\n";
$html .= '        GV_Setup_Map();' . "\n";
$html .= '        // Track #1' . "\n";
$html .= '        t = 1; trk[t] = {info:[],segments:[]};' . "\n";
$html .= "        trk[t].info.name = '" . $hikeTitle . "'; trk[t].info.desc = ''; trk[t].info.clickable = true;" . "\n";
$html .= "        trk[t].info.color = '#e60000'; trk[t].info.width = 3; trk[t].info.opacity = 0.9; trk[t].info.hidden = false;" . "\n";
$html .= "        trk[t].info.outline_color = 'black'; trk[t].info.outline_width = 0; trk[t].info.fill_color = '#e60000'; trk[t].info.fill_opacity = 0;" . "\n";
$html .= '        trk[t].segments.push({ points:[' . $seg .  '] });' . "\n";
$html .= '        trk[t].segments.push({ points:[' . $lastpoints .  '] });' . "\n";
$html .= '        GV_Draw_Track(t);' . "\n";
$html .= "        t = 1; GV_Add_Track_to_Tracklist({bullet:'- ',name:trk[t].info.name,desc:trk[t].info.desc,color:trk[t].info.color,number:t});" . "\n";
       
for ($j=0; $j<count($ticks); $j++) {
  $html .= '        ' . $ticks[$j] . "\n";
}
for ($z=0; $z<count($plnks); $z++) {
 $html .= '        ' . $plnks[$z] . "\n";
}
                    
$html .= '        GV_Finish_Map();' . "\n";
$html .= '    }' . "\n";
$html .= '    GV_Map(); // execute the above code' . "\n";
$html .= '       // http://www.gpsvisualizer.com/map_input?allow_export=1&form=google&google_api_key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&google_street_view=1&google_trk_mouseover=1&tickmark_interval=.3%20mi&trk_stats=1&units=us&wpt_driving_directions=1&add_elevation=auto' . "\n";
$html .= '</script>' . "\n";  
$html .= '</body>' . "\n";
$html .= '</html>' . "\n";
fputs($mapHandle,$html);
fclose($mapHandle);
?>