<?php
/**
 * The hike page editor is utilized to update information contained
 * in the database, whether for a new hike or an existing hike.
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../docs/
 */
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
// import GET parameters
$hikeNo = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
if (isset($_SESSION['activeTab'])) {
    $dispTab = $_SESSION['activeTab'];
} else {
    $dispTab = 1;
}
// data for drop-down boxes
$selectData = dropdownData();
$groups = $selectData[0];
$cdata = $selectData[1];
$cnames = [];
foreach ($cdata as $cstring) {
    $separator = strpos($cstring, ":") + 1;
    $name = substr($cstring, $separator, strlen($cstring)-$separator);
    array_push($cnames, $name);
}
$groupCount = count($groups);
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
