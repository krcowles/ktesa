<?php
/**
 * This is the script that extracts photos from specified upload
 * albums, and organizes them to be presented on a new page for
 * the user to select. At this point, the user selections will next
 * appear in the editor, where he/she may specify display of the photo
 * on either the hike page or hike map.
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$incl = $_POST['ps'];
$curlids = [];
$albums = [];
$lnk1 = '';
$lnk2 = '';
$j = 0;
foreach ($incl as $newalb) {
    $alnk = 'lnk' . $newalb;
    $atype = 'alb' . $newalb;
    $curlids[$j] = filter_input(INPUT_POST, $alnk);
    $albums[$j] = filter_input(INPUT_POST, $atype);
    $j++;
}
// get a separate copy of $curlids to find any associations with purl1 or purl2
$arrObj = new ArrayObject($curlids);
$avail = $arrObj->getArrayCopy();
$supplied = count($curlids);
// get current urls in EHIKES and compare to incoming album links
$lnkReq = "SELECT purl1,purl2 FROM EHIKES WHERE indxNo = {$hikeNo};";
$lnkQ = mysqli_query($link, $lnkReq) or die(
    __FILE__ . ": Line " . __LINE__ . "Failed to extract photo album " .
    "urls from EHIKES for hike {$hikeNo}; " . mysqli_error($link)
);
$dburl = [];
// get empty strings if fields are null
$purls = mysqli_fetch_row($lnkQ);
for ($a=0; $a<count($purls); $a++) {
    $dburl[$a] = fetch($purls[$a]);
}
mysqli_free_result($lnkQ);
// IF there is a future desire to delete an existing link, this is the place to code it;

// see if there are already existing links, get a count, and eliminate from the $avail list
$existing = 0;
for ($j=0; $j<count($dburl); $j++) {
    if ($dburl[$j] !== '') { // this purl already has a url in the db
        $existing++;
        if (in_array($dburl[$j], $avail)) {
            $offset = array_search($dburl[$j], $avail);
            array_splice($avail, $offset, 1);
        }
    }
}
// fill any empties with what is now available 
if ($existing < count($dburl)) {
    for ($k=0; $k<count($dburl); $k++) {
        if ($dburl[$k] == '') {
            if (count($avail) > 0) {
                $dburl[$k] = array_pop($avail);
            }
        }
    }
}
// update database values:
if ($dburl[0] === '') {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'purl1', 'indxNo', null, __FILE__, __LINE__
    );
} else {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'purl1', 'indxNo', $dburl[0], __FILE__, __LINE__
    );
}
if ($dburl[1] === '') {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'purl2', 'indxNo', null, __FILE__, __LINE__
    );
} else {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'purl2', 'indxNo', $dburl[1], __FILE__, __LINE__
    );
}
require 'getPicDat.php';
// all photos are now in picdat[], time sorted; create javascript var:
$ajaxArray = json_encode($picdat);
// some arrays are created below to display photos locally with name captions:
$picno = 0;
$phNames = []; // filename w/o extension, aka 'title'
$phDescs = []; // caption
$phPics = []; // capture the link for the mid-size version of the photo
$phWds = []; // width, but adjusted for row size, so table uses:
$rowHt = 220; // nominal choice for row height in div
foreach ($picdat as $pics) { 
    $pHeight = $pics['pHt'];
    $aspect = $rowHt/$pHeight;
    $pWidth = $pics['pWd'];
    $phWds[$picno] = floor($aspect * $pWidth);
    $phNames[$picno] = $pics['pic'];
    $phPics[$picno] = $pics['nsize'];
    $phDescs[$picno] = $pics['desc'];
    $picno += 1;
}
// create the js arrays to be passed to the accompanying script:
$jsTitles = '[';
for ($n=0; $n<count($phNames); $n++) {
    if ($n === 0) {
        $jsTitles .= '"' . $phNames[0] . '"';
    } else {
        $jsTitles .= ',"' . $phNames[$n] . '"';
    }
}
$jsTitles .= ']';
$jsDescs = '[';
for ($m=0; $m<count($phDescs); $m++) {
    if ($m === 0) {
        $jsDescs .= '"' . $phDescs[0] . '"';
    } else {
        $jsDescs .= ',"' . $phDescs[$m] . '"';
    }
}
$jsDescs .= ']';
