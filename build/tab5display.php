<?php
/**
 * This script constitutes the portion of the hike page editor which
 * deals with file uploads. The current files available for upload
 * are the hike page gpx & track files (for map & elevation chart),
 * and gpx, kml, or html files used for additional hike information.
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../docs/
 */
// NOTE: This file is included in editDB.php, so MySQL connection exists
$gpx_req = "SELECT gpx,trk FROM EHIKES WHERE indxNo = {$hikeNo};";
$gpx_query = mysqli_query($link, $gpx_req) or die(
    __FILE__ . " Line " . __LINE__ . ": Could not extract gpx " .
    "file name from EHIKES hike no. {$hikeNo}: " . mysqli_error($link)
);
$gpxdat = mysqli_fetch_row($gpx_query);
$curr_gpx = $gpxdat[0];
$curr_trk = $gpxdat[1];
echo '<input type="hidden" name="mgpx" value="' . $curr_gpx . '" />';
echo '<input type="hidden" name="mtrk" value="' . $curr_trk . '" />';
?>
<input type="hidden" name="hno5" value="<?= $hikeNo;?>" />
<input type="hidden" name="usr5" value="<?= $uid;?>" />
<h3>File Upload for Hike Page Map and Track: (.gpx file)</h3>
<p><em style="text-decoration:underline;">Warning:</em> If you delete an
    existing gpx file, published hikes may be affected; you may simply
    specify a new file to override the current settings for this hike.</p>
<?php
if (isset($_SESSION['uplmsg']) && $_SESSION['uplmsg'] !== '') {
    echo '<p style="font-size:18px;color:darkblue;">The following action has ' .
        'resulted from your latest "APPLY": ';
    echo $_SESSION['uplmsg'] . "</p>";
    $_SESSION['uplmsg'] = '';
}
?>
<span style="color:brown;">Current Main Hike Track File: </span>
<?php
if ($curr_gpx == '') {
    echo '<em>None Specified</em><br />'; 
} else {
    echo "<em>{$curr_gpx}</em>&nbsp;&nbsp;&nbsp;&nbsp;" .
    '<span style="color:brown;">Check to Delete&nbsp;&nbsp;</span>' .
    '<input type="checkbox" name="dgpx" /><br />';
}
?>
<ul>
    <li><span style="color:brown;">Upload new gpx file:&nbsp;</span>
        <input type="file" name="newgpx" /></li>
</ul>
<h3>File Upload for 'Related Hike Information' (types .gpx, .kml, .html):</h3>
<p>Note: These files are generally useful for proposed hike track data
and/or maps</p>
<?php
/**
 * Conditonal message after upload:
 */
if (isset($_SESSION['gpsmsg']) && $_SESSION['gpsmsg'] !== '') {
    echo '<p style="font-size:18px;color:darkblue;">The following ' .
        'action has resulted from your latest "APPLY": ';
    echo $_SESSION['gpsmsg'] . "</p>";
    $_SESSION['gpsmsg'] = '';
}
?>
<span style="font-weight:bold;margin-bottom:0px;color:black;">
    Upload New Data File:<br />
<em style="font-weight:normal;">
    - Note: A Reference Will Automatically Be Added When Upload
    Is Complete</em></span><br />
<ul style="margin-top:0px;" id="relgpx">
    <li>Track Data Uploads:<br />
        <label style="color:brown;">Upload New File&nbsp;(Accepted file types:
        gpx, kml)</label>&nbsp;<input type="file" name="newgps" /></li>
    <li>Map Uploads:<br />
        <label style="color:brown;">Upload New File&nbsp;(Accepted file type:
        html)</label>&nbsp;<input type="file" name="newmap" /></li>
</ul>
<div style="margin-left:8px;">
    <p style="font-size:20px;font-weight:bold;">Upload File(s)&nbsp;
        <input type="submit" name="savePg" value="Apply" /></p>
</div>