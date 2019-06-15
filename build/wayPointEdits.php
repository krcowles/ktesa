<?php
/**
 * This file constructs the html for editing waypoints based on 
 * which, if any, waypoints may exist either in the gpx file or in 
 * the database
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$wptedits = '';

// Header when no waypoints exist yet:
$noPrevious = <<<NEWPTS
<!-- New Waypoints When None Previously Exist -->
<p style="color:brown;"><em>There are currently no waypoints associated
    with this hike. You may add waypoints below; they will be saved to
    the database.</em></p>
NEWPTS;

// Header when waypoints exist in gpx file
$gpxWpts = <<<GPXPTS
<!-- GPX File Waypoints -->
<p style="color:brown;"><em>The following waypoints were identified in
the gpx file. Any edits made will be saved to the file and not to the
database.</em></p>
GPXPTS;

// Header when waypoints exist in the database
$dbWpts = <<<DBPTS
<!-- DATABASE Waypoints -->
<p style="color:brown;"><em>The following waypoints were found in the database
associated with this hike. Any changes will be saved to the database.</em></p>
DBPTS;

$wptDescriptions = '<p>NOTE: Waypoint descriptions appear during mouseover</p>';

// Drop-down select box for icons currently supported
$icons = <<<WPTICONS
    <option value="googlemini">[Default] Google</option>
    <option value="Flag, Red">Red Flag</option>
    <option value="Flag, Blue">Blue Flag</option>
    <option value="Flag, Green">Green Flag</option>
    <option value="Flag, Yellow">Yellow Flag</option>
    <option value="Trail Head">Hiker</option>
    <option value="Triangle, Red">Red Triangle</option>
    <option value="Triangle, Yellow">Yellow Triangle</option>
</select>
WPTICONS;
// Three possible states:
// 1.
if ($gpxWptCount === 0 && $wayPointCount === 0) { 
    $wptedits .= $noPrevious . PHP_EOL;
    $wptedits .= $wptDescriptions . PHP_EOL;
    $wptedits .= '<div id="npts">' . PHP_EOL;
    for ($l=0; $l<3; $l++) {
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="ndes[]"></textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL; 
        $wptedits .= '<select name="nsym[]">' . PHP_EOL;
        $wptedits .= $icons . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" name="nlat[]"></textarea>'
            . '&nbsp;&nbsp;Longitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" name="nlng[]"></textarea>'
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;
}
// 2.
if ($gpxWptCount > 0) {
    $wptedits .= $gpxWpts . PHP_EOL;
    $wptedits .= $wptDescriptions . PHP_EOL;
    $wptedits .= '<div id="gpts">' . PHP_EOL;
    for ($m=0; $m<$gpxWptCount; $m++) {
        // for initialization of drop-down
        $wptedits .= '<p id="gicn' . $m . '" style="display:none;">'
            . $gpxWptIcn[$m] . '</p>' . PHP_EOL;
        // presentation on page
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="gdes[]">'
            . $gpxWptDes[$m] . '</textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL;
        $wptedits .= '<select id="selgicon' . $m . '" name="gsym[]">' . PHP_EOL;
        $wptedits .= $icons . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;Remove this waypoint:'
            . '<input id="gdel' . $m . '" type="checkbox" '
            . 'name="gdel[]" value="g' . $m . '" /><br />' . PHP_EOL;
        $wptedits .= 'Waypoint Latitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" '
            . 'name="glat[]">' . $gpxWptLat[$m] . '</textarea>'
            . '&nbsp;&nbsp;Longitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" '
            . 'name="glng[]">' . $gpxWptLng[$m] . '</textarea>'
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>' . PHP_EOL;

    $wptedits .= '<p style="color:brown;">You may add the following waypoints '
        . '<strong>to the GPX file</strong></p>' . PHP_EOL;
    for ($k=0; $k<2; $k++) {
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" '
            . 'name="ngdes[]"></textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL; 
        $wptedits .= '<select name="ngsym[]">' . PHP_EOL;
        $wptedits .= $icons . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" name="nglat[]"></textarea>'
            . '&nbsp;&nbsp;Longitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" name="nglng[]"></textarea>'
            . '<br /><br />' . PHP_EOL;
    }
}
// 3.
if ($wayPointCount > 0) {
    $wptedits .= $dbWpts . PHP_EOL;
    $wptedits .= $wptDescriptions . PHP_EOL;
    $wptedits .= '<div id="dpts">' . PHP_EOL;
    for ($n=0; $n<$wayPointCount; $n++) {
        // for initialization of drop-down box
        $wptedits .= '<p id="dicn' . $n . '" style="display:none;">'
            . $wicn[$n] . '</p>' . PHP_EOL;
        // presentation on page
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" name="ddes[]">'
            . $wdes[$n] . '</textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL;
        $wptedits .= '<select id="seldicon' . $n . '" name="dsym[]">' . PHP_EOL;
        $wptedits .= $icons . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;Remove this waypoint:'
            . '<input id="ddel' . $n . '" type="checkbox" '
            . 'name="ddel[]" value="d' . $n . '" /><br />' . PHP_EOL;
        $wptedits .= 'Waypoint Latitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" '
            . 'name="dlat[]">' . $wlat[$n] . '</textarea>'
            . '&nbsp;&nbsp;Longitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" '
            . 'name="dlng[]">' . $wlng[$n] . '</textarea>'
            . '<br /><br />' . PHP_EOL;
    }
    $wptedits .= '</div>';

    $wptedits .= '<p style="color:brown;">You may add the following waypoints '
        . '<strong>to the database</strong></p>' . PHP_EOL;
    for ($i=0; $i<2; $i++) {
        $wptedits .= 'Description:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle2" '
            . 'name="nddes[]"></textarea>' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;';
        $wptedits .= 'Icon:' . PHP_EOL; 
        $wptedits .= '<select name="ndsym[]">' . PHP_EOL;
        $wptedits .= $icons . '<br />' . PHP_EOL;
        $wptedits .= '&nbsp;&nbsp;&nbsp;Waypoint Latitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" name="ndlat[]"></textarea>'
            . '&nbsp;&nbsp;Longitude:' . PHP_EOL;
        $wptedits .= '<textarea class="tstyle4 coords" name="ndlng[]"></textarea>'
            . '<br /><br />' . PHP_EOL;
    }
}
