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
session_start();
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
$dispTab = filter_input(INPUT_GET, 'tab');
// data for drop-down boxes
$selectData = dropdownData('cls');
$cnames = array_values($selectData);
$groups = array_keys($selectData);
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
$curr_gpx = $hikeGpx;
$curr_trk = fetch($hike['trk']);
$hikeLat = fetch($hike['lat']);
$hikeLng = fetch($hike['lng']);
$hikeUrl1 = fetch($hike['purl1']);
$hikeUrl2 = fetch($hike['purl2']);
$hikeDirs = fetch($hike['dirs']);
$hikeTips = fetch($hike['tips']);
$hikeDetails = fetch($hike['info']);
mysqli_free_result($hikeq);
// References for tab4:
$refreq = "SELECT * FROM EREFS WHERE indxNo = '{$hikeNo}';";
$refq = mysqli_query($link, $refreq) or die(
    "editDB.php: Failed to extract references from EREFS: " .
    mysqli_error($link)
);
$noOfRefs = mysqli_num_rows($refq);
$rtypes = [];
$rit1s = [];
$rit2s = [];
while ($refs = mysqli_fetch_assoc($refq)) {
    $reftype = fetch($refs['rtype']);
    array_push($rtypes, $reftype);
    $ritem1 = fetch($refs['rit1']);
    array_push($rit1s, $ritem1);
    $ritem2 = fetch($refs['rit2']);
    array_push($rit2s, $ritem2);
}
mysqli_free_result($refq);
// Create the book drop-down options:
$bkReq = "SELECT * FROM BOOKS;";
$bks = mysqli_query($link, $bkReq) or die(
    __FILE__ . " " . __LINE__ . "Failed to get book list: " .
    mysqli_error($link)
);
$bkopts = '';  // html for drop-down boxes
$defauth = ''; // default author when first populating selection boxes
$titles = '['; // arrays for javascript
$authors = '[';
while ($bkitem = mysqli_fetch_assoc($bks)) {
    $titles .= '"' . $bkitem['title'] . '",';
    $authors .= '"' . $bkitem['author'] . '",';
    if ($defauth === '') {
        $defauth = $bkitem['author'];
    }
    $bkopts .= '<option value="' . $bkitem['indxNo'] . '">' . 
        $bkitem['title'] . '</option>' . PHP_EOL;
}
$titles = substr($titles, 0, strlen($titles)-1) . ']';
$authors = substr($authors, 0, strlen($authors)-1) . ']';
// GPS Data for tab 4:
$gpsreq = "SELECT * FROM EGPSDAT WHERE indxNo = '{$hikeNo}' " .
    "AND (datType = 'P' OR datType = 'A');";
$gps = mysqli_query($link, $gpsreq) or die(
    __FILE__ . " Line " . __LINE__ . ": Failed to extract GPS Data "
    . "from EGPSDAT: " . mysqli_error($link)
);
$gpsDbCnt = mysqli_num_rows($gps);
$pl = array();
$pu = array();
$pc = array();
for ($k=0; $k<$gpsDbCnt; $k++) {
    $gpsdat = mysqli_fetch_assoc($gps);
    $pl[$k] = fetch($gpsdat['label']);
    $pu[$k] = fetch($gpsdat['url']);
    $pc[$k] = fetch($gpsdat['clickText']);
}
mysqli_free_result($gps);
