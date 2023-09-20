<?php
/**
 * This file constructs the html for editing waypoints on tab2. Any displayed
 * html on that tab is based on which waypoints, if any, already exist either
 * in the gpx file or in the database. New waypoints can be added in all cases.
 * Multiple formats for lat/lngs are supported. Database waypoints are defined
 * in photoSelect.php.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "wptHtmlDefs.php";

/**
 * If the current gpx file has embedded waypoints, they are retrieved here
 * and will be presented in tab2 for editing, along with any waypoints 
 * associated with the page that are contained in the database (see 
 * photoSelect.php, which extracts db wpts from the ETSV table while gathering
 * photo data). After 'Apply, waypoints embedded in the gpx file remain
 * embedded there (as well as new gpx wpts); waypoints present in the database
 * will remain there (as well as new db wpts).
 */
$gpxloc = '../gpx/' . $curr_gpx;
$gpxWptCount = 0;
$gpxWptDes = [];
$gpxWptLat = [];
$gpxWptLng = [];
$gpxWptIcn = [];
$gpxDMlat  = [];
$gpxDMSlat = [];
$gpxDMlng  = [];
$gpxDMSlng = [];
// NOTE: 'file_exists' treats an empty dir as existing: [../gpx/ returns 'true']
if (!empty($curr_gpx) && file_exists($gpxloc)) {
    $rawgpx = simplexml_load_file($gpxloc);
    $gpxWptCount = $rawgpx->wpt->count();
    for ($j=0; $j<$gpxWptCount; $j++) {
        //
        $gpxWptDes[$j] = $rawgpx->wpt[$j]->name;
        $gpxWptLat[$j] = $rawgpx->wpt[$j]['lat']->__toString();
        $gpxWptLng[$j] = $rawgpx->wpt[$j]['lon']->__toString();
        $gpxWptIcn[$j] = $rawgpx->wpt[$j]->sym;
    }
}
if ($gpxWptCount > 0) {
    /**
     * GPX Waypoints can also be presented on the page as Degrees/Minutes or as
     * Degrees/Minutes/Seconds. The other formats are calculated here for
     * selection by the js
     */
    foreach ($gpxWptLat as &$Dlat) {
        // get fractional degrees
        $fractLatDeg = $Dlat - floor($Dlat);
        $MinLat = $fractLatDeg * 60;
        $DMlat = floor($Dlat) . "|" . round($MinLat, 5);
        array_push($gpxDMlat, $DMlat);
        // get fractional seconds
        $fractLatMin = $MinLat - floor($MinLat);
        $SecLat = 60 * $fractLatMin;
        $DMSlat = floor($Dlat) . "|" . floor($MinLat) . "|" .
            round($SecLat, 3);
        array_push($gpxDMSlat, $DMSlat);
    }
    foreach ($gpxWptLng as &$Dlng) {
        // requires using absolute values instead of negatives:
        $ablng = abs($Dlng);
        $fractLngDeg = $ablng - floor($ablng);
        $MinLng = $fractLngDeg * 60;
        $DMlng = "-" . floor($ablng) . "|" . round($MinLng, 5);
        array_push($gpxDMlng, $DMlng);
        $fractLngMin = $MinLng - floor($MinLng);
        $SecLng = $fractLngMin * 60;
        $DMSlng = "-" . floor($ablng) . "|" . floor($MinLng) . "|" .
            round($SecLng, 3);
        array_push($gpxDMSlng, $DMSlng);
    }
}
// for import to javascript
$jsgpxLatDeg = json_encode($gpxWptLat);
$jsgpxLatDM  = json_encode($gpxDMlat);
$jsgpxLatDMS = json_encode($gpxDMSlat);
$jsgpxLngDeg = json_encode($gpxWptLng);
$jsgpxLngDM  = json_encode($gpxDMlng);
$jsgpxLngDMS = json_encode($gpxDMSlng);
//

$wptedits = '';
/**
 * For each displayed lat/lng, only one of three textarea lines will appear,
 * depending on the format selected. The actual saved value will be held in
 * a hidden input and updated by the js when data changes. The input is posted,
 * but the various textareas are not, hence there is no 'name' attribute for these.
 */

// Three possible states:
// 1. No waypoints exist yet
if ($gpxWptCount === 0 && $wayPointCount === 0) { 
    $wptedits .= $noPrevious . PHP_EOL;
    $wptedits .= $wptDescriptions . PHP_EOL;
    $wptedits .= '<div id="npts">' . PHP_EOL;
    for ($l=0; $l<3; $l++) {
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="ndes[]"></textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL; 
        $wptedits .= '<select class="wpticons" name="nsym[]">' . PHP_EOL;
        $wptedits .= $icons . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= $newdblats . $newdblatdeg . $newdblatdm . $newdblatdms;
        $wptedits .= $newdblngs . $newdblngdeg . $newdblngdm . $newdblngdms
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;
}
// 2. Some waypoints are present in the gpx file
if ($gpxWptCount > 0) {
    $wptedits .= $gpxWpts . PHP_EOL;
    $wptedits .= $wptDescriptions . PHP_EOL;
    $wptedits .= '<div id="gpts">' . PHP_EOL;
    for ($m=0; $m<$gpxWptCount; $m++) {
        $wptedits .= '<p id="gicn' . $m . '" style="display:none;">'
            . $gpxWptIcn[$m] . '</p>' . PHP_EOL;
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="gdes[]">'
            . $gpxWptDes[$m] . '</textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL;
        $wptedits .= '<select id="gselicon' . $m . '" name="gsym[]" ' . 
            'class="wpticons">' . PHP_EOL;
        $wptedits .= $icons . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;Remove this waypoint:&nbsp;&nbsp;'
            . '<input id="gdel' . $m . '" type="checkbox" '
            . 'name="gdel[]" value="g' . $m . '" /><br />' . PHP_EOL;
        $wptedits .= 'Waypoint Latitude:' . PHP_EOL;
        $gpxlats = '<input type="hidden" name="glat[]" ' .
            'value="' . $gpxWptLat[$m] .'" />' . PHP_EOL;
        $gpxlngs = '<input  type="hidden" name="glng[]" ' .
            'value="' . $gpxWptLng[$m] .'" />' . PHP_EOL;
        $wptedits .= $gpxlats . $gpxlatdeg . $gpxlatdm . $gpxlatdms;
        $wptedits .= $gpxlngs . $gpxlngdeg . $gpxlngdm . $gpxlngdms .
            '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;
    // place for new additions
    $wptedits .= '<p style="color:brown;">You may add the following waypoints '
        . '<strong>to the GPX file</strong></p>' . PHP_EOL;
    $wptedits .= '<div id="ngpts">' . PHP_EOL;
    for ($k=0; $k<2; $k++) {
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" '
            . 'name="ngdes[]"></textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL; 
        $wptedits .= '<select class="wpticons" name="ngsym[]">' . PHP_EOL;
        $wptedits .= $icons . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= $newgpxLats . $newglatdeg . $newglatdm . $newglatdms;
        $wptedits .= $newgpxLngs . $newglngdeg . $newglngdm . $newglngdms
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;
}
// 3. Some waypoints are present in the database
if ($wayPointCount > 0) {
    $wptedits .= $dbWpts . PHP_EOL;
    $wptedits .= $wptDescriptions . PHP_EOL;
    $wptedits .= '<div id="wpts">' . PHP_EOL;
    for ($n=0; $n<$wayPointCount; $n++) {
        $wptedits .= '<p id="dicn' . $n . '" style="display:none;">'
            . $wicn[$n] . '</p>' . PHP_EOL;
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="ddes[]">'
            . $wdes[$n] . '</textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL;
        $wptedits .= '<select id="dselicon' . $n . '" name="dsym[]" ' .
            'class="wpticons">' . PHP_EOL;
        $wptedits .= $icons . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;Remove this waypoint:&nbsp;&nbsp;'
            . '<input id="ddel' . $n . '" type="checkbox" '
            . 'name="ddel[]" value="d' . $n . '" /><br />' . PHP_EOL;
        $wptedits .= 'Waypoint Latitude:' . PHP_EOL;
        $dblats    = str_replace('dblatval', $wlat[$n], $dblats);
        $dblngs    = str_replace('dblngval', $wlng[$n], $dblngs);
        $wptedits .= $dblats . $dblatdeg . $dblatdm . $dblatdms;
        $wptedits .= $dblngs . $dblngdeg . $dblngdm . $dblngdms
            . '<br /><br />' . PHP_EOL;
        $wptedits .= '<input type="hidden" name="didx[]" value="'
            . $wids[$n] . '" />';
    }
    $wptedits .= '</div>';
    // place for new additions: NOTE 'name' same as when no pts exist
    $wptedits .= '<p style="color:brown;">You may add the following waypoints '
        . '<strong>to the database</strong></p>' . PHP_EOL;
    $wptedits .= '<div id="npts">' . PHP_EOL;
    for ($l=0; $l<2; $l++) {
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="ndes[]"></textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL; 
        $wptedits .= '<select class="wpticons" name="nsym[]">' . PHP_EOL;
        $wptedits .= $icons . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= $newdblats . $newdblatdeg . $newdblatdm . $newdblatdms;
        $wptedits .= $newdblngs . $newdblngdeg . $newdblngdm . $newdblngdms
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;
}
