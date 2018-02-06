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
$supplied = count($curlids);
// if album link isn't already stored, save it in EHIKES
// get current urls in EHIKES and compare to incoming album links
$lnkReq = "SELECT purl1,purl2 FROM EHIKES WHERE indxNo = {$hikeNo};";
$lnkQ = mysqli_query($link, $lnkReq) or die(
    "uploadPhotos.php: Line " . __LINE__ . "Failed to extract photo album " .
    "urls from EHIKES for hike {$hikeNo}; " . mysqli_error($link)
);
$dburl = [];
// get empty strings if fields are null
$purls = mysqli_fetch_row($lnkQ);
for ($a=0; $a<count($purls); $a++) {
    $dburl[$a] = fetch($purls[$a]);
}
// IF there is a future desire to delete an existing link, this
// is the place to code it on uploads:
// start looking at incoming albums and see if = existing link
$i = 0;
for ($j=0; $j<count($dburl); $j++) {
    if ($dburl[$j] === '') {
        $match = false;
        for ($k=0; $k<count($dburl); $k++) {
            if ($curlids[$i] == $dburl[$k]) {
                $match = true;
            }
        }
        if (!$match) {
            // place this $curlids into the empty $dburl
            $dburl[$j] = $curlids[$i];
        }
        $i++;
        if ($i >= count($curlids)) {
            break;
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
// all photos are now in picdat[], time sorted
// add other info needed for the server to process the results:
$passInfo = array(
    "folder" => 999,
    "pic" => $hikeNo,
    "desc" => $usr,
    "alb" => $includes,
    "org" => '',
    "thumb" => '',
    "nsize" => '',
    "pHt" => '',
    "pWd" => '',
    "taken" => '',
    "lat" => '',
    "lng" => '',
    "gpsdate" => '',
    "gpstime" => ''
);
// some arrays are created to display photos locally with name captions:
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
/* The technique here will be to create a temporary table to store all
 * uploaded pix and then xfr those selected into ETSV on the submitted page
 */
/*
$nodup = mysqli_query($link, "DROP TABLE IF EXISTS tmpPix") or die(
    "uploadPhotos.php: DROP TABLE IF EXISTS failed: " . mysqli_error($link)
);
$tmpReq = "CREATE TABLE tmpPix LIKE TSV;";
$tmp = mysqli_query($link, $tmpReq) or die(
    "uploadPhotos.php: Failed to create tmp table for photos uploaded: " .
    mysqli_error($link)
);
for ($j=0; $j<$picno; $j++) {
    $fl = mysqli_real_escape_string($link, $folders[$j]);
    $ti = mysqli_real_escape_string($link, $phNames[$j]);
    $ds = mysqli_real_escape_string($link, $phDescs[$j]);
    $lt = mysqli_real_escape_string($link, floatval($lats[$j]));
    $ln = mysqli_real_escape_string($link, floatval($lngs[$j]));
    $th = mysqli_real_escape_string($link, $thumbs[$j]);
    $al = mysqli_real_escape_string($link, $alblinks[$j]);
    $dt = mysqli_real_escape_string($link, $dates[$j]);
    $md = mysqli_real_escape_string($link, $phPics[$j]);
    $ih = mysqli_real_escape_string($link, intval($phHts[$j]));
    $iw = mysqli_real_escape_string($link, intval($pWds[$j]));
    $ic = mysqli_real_escape_string($link, $icolors[$j]);
    $og = mysqli_real_escape_string($link, $orgs[$j]);
    $addReq = "INSERT INTO tmpPix (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) VALUES ({$hikeNo}," .
        "'{$fl}','{$ti}','N','N','{$ds}',{$lt},{$ln},'{$th}','{$al}'," .
        "'{$dt}','{$md}',{$ih},{$iw},'{$ic}','{$og}');";
    $addem = mysqli_query($link, $addReq) or die(
        "uploadPhotos.php: Failed to add photos to tmpPix table: " .
        msyqli_error($link)
    );
}
*/

