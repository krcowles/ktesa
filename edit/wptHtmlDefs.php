<?php
/**
 * This module contains only html definitions for the wayPointEdits.php
 * script, and is supplied simply to keep its functional logic easier to
 * read.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
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

$wptstyle = <<<WPTSTYLE
<span id="type_desc">* Select the style for waypoint display:&nbsp;&nbsp;
<select id="wptstyle" name="wpt_format">
    <option value="deg">Decimal Degrees</option>
    <option value="dm">Degrees Decimal Minutes</option>
    <option value="dms">Degrees Minutes Seconds</option>
</select></span>
WPTSTYLE;
$iconNote = '<p><strong>Use "Parking" icon when trail begins very close
    to parking area<br />Also, when editing an already saved
    (existing) datum, the display formats will show old values until
    the changed value is saved</strong></p>';
$wptDescriptions = $wptstyle . $iconNote;
   
// Drop-down select box for icons currently supported
require "gpxWaypointSymbols.php";
$icons = $select_sym;

/**
 * All waypoints are presented in a format selected by the user in the select
 * drop-down (#wptstyle). The formats are 1) decimal degrees [deg]; 2) degrees
 * and minutes [dm]; 3) degrees, minutes, and seconds [dms]. The default presentation
 * is decimal degrees. Only one number per each waypoint's lat & lng is actually
 * saved, and that is held in its hidden input element, updated by js when a user
 * changes the waypoint's corresponding textarea.
 */

 /**
 * Formats available for lat/lngs when no previous lat/lngs exist: all entries
 * will be saved in the db. Also, since this case is never presented when there
 * are some lat/lngs already in the db, the 'new' entries for the latter also
 * use these definitions.
 */
// new database lat formats
$newdblats    = '<input type="hidden" name="nlat[]" />' . PHP_EOL;
$newdblatdeg  = '<span class="show_deg"><textarea class="tstyle4 deg noneg">' .
    '</textarea> º&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$newdblatdm   = '<span class="show_dm"><textarea class="tstyle1 dm noneg">' .
    '</textarea> º <textarea class="tstyle5 dm noneg"></textarea> ' . "'" .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$newdblatdms  = '<span class="show_dms"><textarea class="tstyle1 dms noneg">' .
    '</textarea> º <textarea class="tstyle1 dms noneg"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms noneg"></textarea> "' .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
// new database lng formats
$newdblngs = '<input type="hidden" name="nlng[]" />' . PHP_EOL;
$newdblngdeg = '<span class="show_deg"><textarea class="tstyle4 deg lng_neg">' .
    '</textarea> º</span>' . PHP_EOL;
$newdblngdm  = '<span class="show_dm"><textarea class="tstyle1 dm lng_neg">' .
    '</textarea> º <textarea class="tstyle5 dm noneg"></textarea> ' . 
    "'" . '</span>' . PHP_EOL;
$newdblngdms = '<span class="show_dms"><textarea class="tstyle1 dms lng_neg">' .
    '</textarea> º <textarea class="tstyle1 dms noneg"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms noneg"></textarea> '. '"' . '</span>' . PHP_EOL;

 /**
  * Formats for gpxfile <wpts>
  */
// gpx lat formats
$gpxlatdeg  = '<span class="show_deg glat_deg">' .
    '<textarea class="tstyle4 deg"></textarea> º'
    . '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$gpxlatdm   = '<span class="show_dm glat_dm">' .
    '<textarea class="tstyle1 dm"></textarea> º ' .
    '<textarea class="tstyle5 dm"></textarea> ' . "'" .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$gpxlatdms  = '<span class="show_dms glat_dms">' .
    '<textarea class="tstyle1 dms"></textarea> º ' .
    '<textarea class="tstyle1 dms"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms"></textarea> "' .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
// gpx lng formats
$gpxlngdeg  = '<span class="show_deg glng_deg">' .
    '<textarea class="tstyle4 deg"></textarea> º</span>' . PHP_EOL;
$gpxlngdm   = '<span class="show_dm glng_dm">' .
    '<textarea class="tstyle1 dm"></textarea> º ' .
    '<textarea class="tstyle5 dm"></textarea> ' . "'" . '</span>' . PHP_EOL;
$gpxlngdms  = '<span class="show_dms glng_dms">' .
    '<textarea class="tstyle1 dms"></textarea> º ' .
    '<textarea class="tstyle1 dms noneg"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms"></textarea> '. '"' . '</span>' . PHP_EOL;
 /**
  * Formats for new gpxfile <wpts> to be added to the file
  */
// new gpx lat formats
$newgpxLats = '<input type="hidden" name="nglat[]" />' . PHP_EOL;
$newglatdeg  = '<span class="show_deg"><textarea class="tstyle4 deg noneg">' .
    '</textarea> º&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$newglatdm   = '<span class="show_dm"><textarea class="tstyle1 dm noneg">' .
    '</textarea> º <textarea class="tstyle5 dm noneg"></textarea> ' . "'" .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$newglatdms  = '<span class="show_dms"><textarea class="tstyle1 dms noneg">' .
    '</textarea> º <textarea class="tstyle1 dms noneg"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms noneg"></textarea> "' .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
// new gpx lng formats
$newgpxLngs = '<input type="hidden" name="nglng[]" />' . PHP_EOL;
$newglngdeg  = '<span class="show_deg">' .
    '<textarea class="tstyle4 deg lng_neg"></textarea> º</span>'. PHP_EOL;
$newglngdm   = '<span class="show_dm">' .
    '<textarea class="tstyle1 dm lng_neg"></textarea> º ' .
    '<textarea class="tstyle5 dm noneg"></textarea> ' . "'" . '</span>' . PHP_EOL;
$newglngdms  = '<span class="show_dms"><textarea class="tstyle1 dms lng_neg">' .
    '</textarea>º <textarea class="tstyle1 dms noneg"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms noneg"></textarea> '. '"' . '</span>' . PHP_EOL;
/**
 * Formats for existing database waypoint lat/lngs
 */
// database lat formats
$dblats   = '<input type="hidden" name="dlat[]" value="dblatval"/>' . PHP_EOL;
$dblatdeg = '<span class=" show_deg dlat_deg">' .
    '<textarea class="tstyle4 deg"></textarea> º' .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$dblatdm  = '<span class="show_dm dlat_dm">' .
    '<textarea class="tstyle1 dm"></textarea> º ' .
    '<textarea class="tstyle5 dm"></textarea> ' . "'" .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
$dblatdms = '<span class="show_dms dlat_dms">' .
    '<textarea class="tstyle1 dms"></textarea> º ' .
    '<textarea class="tstyle1 dms"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms"></textarea> "' .
    '&nbsp;&nbsp;Longitude:</span>' . PHP_EOL;
// database lng formats
$dblngs = '<input type="hidden" name="dlng[]" value="dblngval" />' . PHP_EOL;
$dblngdeg  = '<span class="show_deg dlng_deg">' .
    '<textarea class="tstyle4 deg"></textarea> º</span>' . PHP_EOL;
$dblngdm   = '<span class="show_dm dlng_dm">' .
    '<textarea class="tstyle1 dm"></textarea> º '.
    '<textarea class="tstyle5 dm"></textarea> ' . "'" . '</span>' . PHP_EOL;
$dblngdms  = '<span class="show_dms dlng_dms">' .
    '<textarea class="tstyle1 dms"></textarea> º ' .
    '<textarea class="tstyle1 dms"></textarea> ' . "' " .
    '<textarea class="tstyle5 dms"></textarea> '. '"' . '</span>' . PHP_EOL;
