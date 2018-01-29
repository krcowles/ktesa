<?php
/**
 * This file will process file uploads. Data is posted from the
 * tab5display.php script. For the 'main' gpx file (the one used
 * to create the hike page map and elevation chart), a corresponding
 * track file will also be created. Database tables are updated.
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../docs/
 */
session_start();
$_SESSION['activeTab'] = 5;
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
// posted data from tab5display.php:
$hikeNo = filter_input(INPUT_POST, 'hno5');
$uid = filter_input(INPUT_POST, 'usr5');
$maingpx = filter_input(INPUT_POST, 'mgpx');
$maintrk = filter_input(INPUT_POST, 'mtrk');
$delgpx = filter_input(INPUT_POST, 'dgpx');
/**
 * This section handles the main gpx file upload/delete
 */
$dels = false;
if (isset($delgpx)) {
    $delgpx = '../gpx/' . $maingpx;
    if (!unlink($delgpx)) {
        die(
            __FILE__ . " Line " . __LINE__ . 
            ": Did not remove {$delgpx} from site"
        );
    }
    $deltrk = '../json/' . $maintrk;
    if (!unlink($deltrk)) {
        die(
            __FILE__ . " Line " . __LINE__ .
            ": Did not remove {$deltrk} from site"
        );
    }
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'gpx', 'indxNo', null, __FILE__, __LINE__
    );
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'trk', 'indxNo', null, __FILE__, __LINE__
    );
    $delmsg = "<p>Deleted files {$maingpx} and {$maintrk} from site</p>";
    $dels = true;
}
$_SESSION['uplmsg'] = '';
$gpxfile = basename($_FILES['newgpx']['name']);
$gpxtype = fileTypeAndLoc($gpxfile);
if ($gpxtype[2] === 'gpx') {
    $gpxupl = validateUpload("newgpx", "../gpx/", "/octet-stream/");
    $newgpx = $gpxupl[0];
    if ($dels) {
        $sendBack = $delmsg . $gpxupl[1];
    } else {
        $sendBack = $gpxupl[1];
    }
    $_SESSION['uplmsg'] .= $sendBack;
    $trkdat = makeTrackFile($newgpx, "../gpx/");
    $newtrk = $trkdat[0];
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'gpx', 'indxNo', $newgpx, __FILE__, __LINE__
    );
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'trk', 'indxNo', $newtrk, __FILE__, __LINE__
    );
} elseif ($gpxfile == '') {
    $newgpx = 'No file specified';
} else {
    $_SESSION['uplmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .gpx for {$gpxfile}.</p>";
}
/**
 * This section handles the upload of new datafiles: gpx or kml or map.
 */
$_SESSION['gpsmsg'] = '';
$gpsupl = basename($_FILES['newgps']['name']);
if ($gpsupl !== '') {
    $gpsok = true;
    $gpstype = fileTypeAndLoc($gpsupl);
    switch ($gpstype[2]) {
    case 'gpx':
        $newlbl = 'GPX:';
        $newcot = 'Track File';
        break;
    case 'kml':
        $newlbl = 'KML:';
        $newcot = "Google Earth File";
        break;
    default:
        $gpsok = false;
    }
    if ($gpsok) {
        $upload = validateUpload("newgps", $gpstype[0], $gpstype[1]);
        $_SESSION['gpsmsg'] .= $upload[1];
        $newurl = $gpstype[0] . $upload[0];
        $newlnk = mysqli_real_escape_string($link, $newurl);
        $newgpsreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`,clickText) " .
            "VALUES ('{$hikeNo}','P','{$newlbl}','{$newlnk}','{$newcot}');";
        $newgps = mysqli_query($link, $newgpsreq) or die(
            "saveTab5.php: Failed to insert new gps file loc for hike {$hikeNo}: " .
            mysqli_error($link)
        );
        $_SESSION['gpsmsg'] .= "<br /><em>A default 'Label' and " .
            "'Click-on-Text' have been provided for {$gpsupl}: see " .
            "'Related Hike Info' tab; You may edit these and Apply " .
            "changes as desired.</em>";
    } else {
        $_SESSION['gpsmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .gpx or .kml for {$gpsupl}.</p>";
    }
}
$mapupl = basename($_FILES['newmap']['name']);
if ($mapupl !== '') {
    $mapok = true;
    $maptype = fileTypeAndLoc($mapupl);
    switch ($maptype[2]) {
    case 'html':
        $newlbl = "MAP:";
        $newcot = 'Area Map';
        break;
    default:
        $mapok = false;
    }
    if ($mapok) {
        $upload = validateUpload("newmap", $maptype[0], $maptype[1]);
        $_SESSION['gpsmsg'] .= $upload[1];
        $newurl = $maptype[0] . $upload[0];
        $newlnk = mysqli_real_escape_string($link, $newurl);
        $newmapreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`,clickText) " .
            "VALUES ('{$hikeNo}','P','{$newlbl}','{$newlnk}','{$newcot}');";
        $newmap = mysqli_query($link, $newmapreq) or die(
            "saveTab5.php: Failed to insert new gps file loc for hike {$hikeNo}: " .
            mysqli_error($link)
        );
        $_SESSION['gpsmsg'] .= "<br /><em>A default 'Label' and " .
            "'Click-on-Text' have been provided for {$mapupl}: see " .
            "'Related Hike Info' tab; You may edit these and Apply " .
            "changes as desired.</em>";
    } else {
        $_SESSION['gpsmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .html for {$mapupl}.</p>";
    }
}
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");

