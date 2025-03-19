<?php
/**
 * This script allows an admin to remove a hike from the EHIKES table,
 * including it's associated entries in the EGPSDAT, EREFS and ETSV tables.
 * It deletes only photos that were not transferred from a published hike.
 * If transferred and unedited, both 'thumb' and 'mid' values match
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$hikeNo = filter_input(INPUT_GET, 'hno');

$mainJson = getTrackFileNames($pdo, $hikeNo, 'edit');
$tracks = $mainJson[0]; // array of json filenames
$getGPSreq = "SELECT `url` FROM `EGPSDAT` WHERE `label` LIKE 'GPX%' AND " .
    "`indxNo`=?;";
$getGPS = $pdo->prepare($getGPSreq);
$getGPS->execute([$hikeNo]);
$gpsFields = $getGPS->fetchAll(PDO::FETCH_ASSOC);
foreach ($gpsFields as $gdata) {
    $gpsFiles = getGPSurlData($gdata['url']);
    $tracks = array_merge($tracks, $gpsFiles[1]);
}
foreach ($tracks as $json) {
    $efile = '../json/' . $json;
    if (file_exists($efile)) {
        unlink($efile);
    }
}

/**
 * Handle photos: if a new hike, all are deleted; if transferred from a
 * published hike, only photos that are NOT still resident in original
 * hike are deleted. In either case, retrieve all ETSV Data and preview
 * field in EHIKES to prepare
 */
$picloc   = getPicturesDirectory();
$thumbloc = str_replace('zsize', 'thumbs', $picloc);
$prevloc  = str_replace('zsize', 'previews', $picloc);
$delete_preview = false;
$ehikeStatus
    = $pdo->query("SELECT `stat`,`preview` FROM `EHIKES` WHERE `indxNo`={$hikeNo};")
    ->fetch(PDO::FETCH_ASSOC);
$estat = (int) $ehikeStatus['stat'];
$ePreview = $ehikeStatus['preview'];
$pPreview ='';
$getEpix = "SELECT `thumb`,`mid` FROM `ETSV` WHERE `indxNo`={$hikeNo};";
$etsv = $pdo->query($getEpix)->fetchAll(PDO::FETCH_KEY_PAIR);
$ethumbs = array_keys($etsv);
$commonPix = [];
if ($estat > 0) { // If a transferred hike, check photos against published
    // published hike data
    $getPrevReq = "SELECT `preview` FROM `HIKES` WHERE `indxNo`={$estat};";
    $pPrev = $pdo->query($getPrevReq)->fetch(PDO::FETCH_ASSOC);
    $pPreview = $pPrev['preview'];
    $getPubPix = "SELECT `thumb`,`mid` FROM `TSV` WHERE `indxNo`={$estat};";
    $ptsv = $pdo->query($getPubPix)->fetchAll(PDO::FETCH_KEY_PAIR);
    $published = array_keys($ptsv);
    // Find any pix that are common
    foreach ($etsv as $thumb => $mid) {
        if (in_array($thumb, $published)) {
            if ($mid == $ptsv[$thumb]) {
                array_push($commonPix, $thumb);
            }
        }
    }
    // $pPreview must exist to be published, so $ePreview exists
    if ($pPreview !== $ePreview) {  // it may have changed...
        $delete_preview = true;
    }
} else {
    if (!empty($ePreview)) {
        $delete_preview = true;
    }
}
if ($delete_preview) {
    $thumbpic = $thumbloc . $ePreview;
    $prevpic  = $prevLoc . $ePreview;
    if (file_exists($thumbpic)) {
        unlink($thumbpic);
    }
    if (file_exists($prevpic)) {
        unlink($prevpic);
    }
}
if (!empty($etsv)) {
    foreach ($etsv as $thumb => $mid) {
        if (!in_array($thumb, $commonPix)) {
            $photo = $picloc . $mid . "_" . $thumb . "_z.jpg";
            if (file_exists($photo)) {
                unlink($photo);
            }
        } 
    }
}
// Now delete EHIKES data with dependencies...
$deleteHikeReq = "DELETE FROM `EHIKES` WHERE `indxNo`=?;";
$deleteHike = $pdo->prepare($deleteHikeReq);
$deleteHike->execute([$hikeNo]);

$location = "../edit/hikeEditor.php?age=new";
header("Location: {$location}");
