<?php
/**
 * The hike page editor allows the user to update information contained
 * in the database, whether for a new hike or an existing hike. Any changes
 * made by the user will not become effective until the edited hike is published.
 * When this module is invoked from the hikeEditor, the tab display setting
 * will be "1". If the user clicks on 'Apply' for any tab, that same tab will
 * display again with refreshed data.
 * PHP Version 7.0
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../docs/
 */
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
$dispTab = filter_input(INPUT_GET, 'tab');
// data for drop-down boxes
$selectData = dropdownData('allclus');
$cnames = $selectData[0];
$groups = $selectData[1];
$groupCount = count($groups);
$dbCount = $selectData[2];
// assign existing hike data
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = {$hikeNo};";
$hikeq = mysqli_query($link, $hikereq) or die(
    __FILE__ . " Line " . __LINE__ . 
    "Failed to extract hike data from EHIKES: " . mysqli_error($link)
);
$hike = mysqli_fetch_assoc($hikeq);
$hikeTitle = trim($hike['pgTitle']);  // this should never be null
$hikeLocale = fetch($hike['locale']);
$hikeMarker = fetch($hike['marker']);  // this also should never be null...
$hikeColl = fetch($hike['collection']);
$hikeClusGrp = fetch($hike['cgroup']);
$hikeGrpTip = fetch($hike['cname']);
// Special case: when a new page requests to add a new group, advise the js
if ($hikeMarker === 'Cluster' && $hikeClusGrp === '') {
    $hikeMarker = 'Normal';
    $grpReq = "YES";
} else {
    $grpReq = "NO";
}
$hikeStyle = fetch($hike['logistics']);
$hikeMiles = fetch($hike['miles']);
$hikeFeet = fetch($hike['feet']);
$hikeDiff = fetch($hike['diff']);
$hikeFac = fetch($hike['fac']);
$hikeWow = fetch($hike['wow']);
$hikeSeasons = fetch($hike['seasons']);
$hikeExpos = fetch($hike['expo']);
$hikeGpx = fetch($hike['gpx']);
$hikeLat = fetch($hike['lat']);
$hikeLng = fetch($hike['lng']);
$hikeUrl1 = fetch($hike['purl1']);
$hikeUrl2 = fetch($hike['purl2']);
$hikeDirs = fetch($hike['dirs']);
$hikeTips = fetch($hike['tips']);
$hikeDetails = fetch($hike['info']);
mysqli_free_result($hikeq);
