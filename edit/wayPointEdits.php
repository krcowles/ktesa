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
 * If the member has specified a waypoint format preference, pass that
 * to tab2display.php
 */
$wptPrefReq = "SELECT `wpt_format` FROM `MEMBER_PREFS` WHERE `userid`=?;";
$wptPref = $pdo->prepare($wptPrefReq);
$wptPref->execute([$_SESSION['userid']]);
$memberWptPref = $wptPref->fetch(PDO::FETCH_ASSOC);
$wpt_pref = $memberWptPref === false ? '' : $memberWptPref['wpt_format'];

/**
 * Retrieve any waypoints loaded into the EWAYPTS table: 'gpx' => came from
 * a gpx file [held separate so that a similar gpx file can be downloaded]; 
 * 'db' => entered by the user [not in the original gpx file] and saved in
 * the database table.
 */
$gpxWptCount = 0;
$gpxWptDes = [];
$gpxWptLat = [];
$gpxWptLng = [];
$gpxWptIcn = [];
$gpxDMlat  = [];
$gpxDMSlat = [];
$gpxDMlng  = [];
$gpxDMSlng = [];
$gpxWptId  = [];
$getGpxWayptsReq = "SELECT * FROM `EWAYPTS` WHERE `indxNo`={$hikeNo} AND " .
    "`type`='gpx';";
$gpxWaypoints = $pdo->query($getGpxWayptsReq)->fetchAll(PDO::FETCH_ASSOC);
foreach ($gpxWaypoints as $gpxpt) {
    $gpxWptCount++;
    array_push($gpxWptDes, $gpxpt['name']);
    array_push($gpxWptLat, $gpxpt['lat']/LOC_SCALE);
    array_push($gpxWptLng, $gpxpt['lng']/LOC_SCALE);
    array_push($gpxWptIcn, $gpxpt['sym']);
    array_push($gpxWptId, $gpxpt['wptId']);

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

/**
 * Database waypoints:
 */
$wayPointCount = 0;
$dbWptDes = [];
$dbWptLat = [];
$dbWptLng = [];
$dbWptIcn = [];
$dbDMlat  = [];
$dbDMSlat = [];
$dbDMlng  = [];
$dbDMSlng = [];
$dbWptId  = [];
$getDbWayptsReq = "SELECT * FROM `EWAYPTS` WHERE `indxNo`={$hikeNo} AND " .
    "`type`='db';";
$dbWaypoints = $pdo->query($getDbWayptsReq)->fetchAll(PDO::FETCH_ASSOC);
foreach ($dbWaypoints as $dbpt) {
    $wayPointCount++;
    array_push($dbWptDes, $dbpt['name']);
    array_push($dbWptLat, $dbpt['lat']/LOC_SCALE);
    array_push($dbWptLng, $dbpt['lng']/LOC_SCALE);
    array_push($dbWptIcn, $dbpt['sym']);
    array_push($dbWptId, $dbpt['wptId']);
}
if ($wayPointCount > 0) {
    foreach ($dbWptLat as &$Dlat) {
        // get fractional degrees
        $fractLatDeg = $Dlat - floor($Dlat);
        $MinLat = $fractLatDeg * 60;
        $DMlat = floor($Dlat) . "|" . round($MinLat, 5);
        array_push($dbDMlat, $DMlat);
        // get fractional seconds
        $fractLatMin = $MinLat - floor($MinLat);
        $SecLat = 60 * $fractLatMin;
        $DMSlat = floor($Dlat) . "|" . floor($MinLat) . "|" . round($SecLat, 3);
        array_push($dbDMSlat, $DMSlat);
    }
    foreach ($dbWptLng as &$Dlng) {
        $ablng = abs($Dlng);
        $fractLngDeg = $ablng - floor($ablng);
        $MinLng = $fractLngDeg * 60;
        $DMlng = "-" . floor($ablng) . "|" . round($MinLng, 5);
        array_push($dbDMlng, $DMlng);
        $fractLngMin = $MinLng - floor($MinLng);
        $SecLng = $fractLngMin * 60;
        $DMSlng = "-" . floor($ablng) . "|" . floor($MinLng) . "|" .
            round($SecLng, 3);
        array_push($dbDMSlng, $DMSlng);
    }
}
// for import to javascript
$jswLatDeg = json_encode($dbWptLat);
$jswLatDM  = json_encode($dbDMlat);
$jswLatDMS = json_encode($dbDMSlat);
$jswLngDeg = json_encode($dbWptLng);
$jswLngDM  = json_encode($dbDMlng);
$jswLngDMS = json_encode($dbDMSlng);

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
        $wptedits .= $icon_opts . '<br />' . PHP_EOL;
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
        $wptedits .= $icon_opts . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;Remove this waypoint:&nbsp;&nbsp;'
            . '<input id="gdel' . $m . '" type="checkbox" '
            . 'name="gdel[]" value="' . $gpxWptId[$m] . '" /><br />' . PHP_EOL;
        $wptedits .= 'Waypoint Latitude:' . PHP_EOL;
        $gpxlats = '<input type="hidden" name="glat[]" ' .
            'value="' . $gpxWptLat[$m] .'" />' . PHP_EOL;
        $gpxlngs = '<input  type="hidden" name="glng[]" ' .
            'value="' . $gpxWptLng[$m] .'" />' . PHP_EOL;
        $wptedits .= $gpxlats . $gpxlatdeg . $gpxlatdm . $gpxlatdms;
        $wptedits .= $gpxlngs . $gpxlngdeg . $gpxlngdm . $gpxlngdms .
            '<br /><br />' . PHP_EOL;
        $wptedits .= '<input type="hidden" name="gidx[]" value="'
            . $gpxWptId[$m] . '" />';
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
        $wptedits .= $icon_opts . '<br />' . PHP_EOL;
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
    // When both db & gpx pts exist, don't duplicate the format <select>
    if ($gpxWptCount === 0) {
        $wptedits .= $wptDescriptions . PHP_EOL;
    }
    $wptedits .= '<div id="wpts">' . PHP_EOL;
    for ($n=0; $n<$wayPointCount; $n++) {
        $wptedits .= '<p id="dicn' . $n . '" style="display:none;">'
            . $dbWptIcn[$n] . '</p>' . PHP_EOL;
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="ddes[]">'
            . $dbWptDes[$n] . '</textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL;
        $wptedits .= '<select id="dselicon' . $n . '" name="dsym[]" ' .
            'class="wpticons">' . PHP_EOL;
        $wptedits .= $icon_opts . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;Remove this waypoint:&nbsp;&nbsp;'
            . '<input id="ddel' . $n . '" type="checkbox" '
            . 'name="ddel[]" value="' . $dbWptId[$n] . '" /><br />' . PHP_EOL;
        $wptedits .= 'Waypoint Latitude:' . PHP_EOL;
        $latin     = str_replace('dblatval', $dbWptLat[$n], $dblats);
        $lngin     = str_replace('dblngval', $dbWptLng[$n], $dblngs);
        $wptedits .= $latin . $dblatdeg . $dblatdm . $dblatdms;
        $wptedits .= $lngin . $dblngdeg . $dblngdm . $dblngdms
            . '<br /><br />' . PHP_EOL;
        $str = $lngin . $dblngdeg . $dblngdm . $dblngdms;
        $wptedits .= '<input type="hidden" name="didx[]" value="'
            . $dbWptId[$n] . '" />';
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
        $wptedits .= $icon_opts . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= $newdblats . $newdblatdeg . $newdblatdm . $newdblatdms;
        $wptedits .= $newdblngs . $newdblngdeg . $newdblngdm . $newdblngdms
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;
}
