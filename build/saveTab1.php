<?php
/**
 * This script saves any changes made (or data as is) on tab1 ("Basic Data")
 * of the hike page Editor, including uploading/deleting of the main gpx file
 * (track file). If a gpx file already exists, it may be deleted, or otherwise
 * replaced by a newly specified file via tab 1's browse button. When a new
 * gpx file is uploaded without deleting the previous file (if any), the
 * previous file is not deleted. This script is invoked when the user submits
 * the data via the 'Apply' button on tab1.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require_once "../php/gpxFunctions.php";

$hikeNo = filter_input(INPUT_POST, 'hikeNo');
$pdoBindings = [];
$pdoBindings['hikeNo'] = $hikeNo;
$usr      = filter_input(INPUT_POST, 'usr');
$maingpx  = filter_input(INPUT_POST, 'mgpx'); // may be empty
$maintrk  = filter_input(INPUT_POST, 'mtrk'); // may be empty
$delgpx   = isset($_POST['dgpx']) ? $_POST['dgpx'] : null;
$usrmiles = filter_input(INPUT_POST, 'usrmiles');  // registers user changes
$usrfeet  = filter_input(INPUT_POST, 'usrfeet');   // registers user changes
$_SESSION['uplmsg'] = ''; // return status to user on tab1

// Check current `cname` field to ensure lat/lng gets updated if `cname` exists
$updateCluster = false;
$state_req = "SELECT `cname`,`lat`,`lng` FROM `EHIKES` WHERE `indxNo` = ?;";
$clus_state = $pdo->prepare($state_req);
$clus_state->execute([$hikeNo]);
$state = $clus_state->fetch(PDO::FETCH_ASSOC);
if (!empty($state['cname'])) {
    if (empty($state['lat']) || empty($state['lng'])) {
        $updateCluster = true;
    }
}

/**
 * This section handles the main gpx file delete and new gpx file upload.
 * Miles/ft entries are not automatically overwritten when gpx file is deleted
 * or new one is uploaded, unless the user checks the 'Calculate Miles/Feet' 
 * checkbox. Lat/lngs are always updated when a new file is deleted or uploaded.
 * Note: the delete gpx checkbox does not appear if a main gpx file is not
 * specified
 */
$mftNulls = false; 
$deletedLatLng = false;
if (isset($delgpx)) {
    $delgpx = '../gpx/' . $maingpx;
    if (!unlink($delgpx)) {
        throw new Exception("Could not remove {$delgpx} from site");
    }
    $deltrk = '../json/' . $maintrk;
    if (!unlink($deltrk)) {
        throw new Exception("Could not remove {$deltrk} from site");
    }
    $maingpx = '';
    $udgpxreq = "UPDATE EHIKES SET 
        gpx = NULL, trk = NULL, 
        lat = NULL, lng = NULL,
        miles = NULL, feet = NULL 
        WHERE indxNo = ?;";
    $udgpx = $pdo->prepare($udgpxreq);
    $udgpx->execute([$hikeNo]);
    $lat = '';
    $lng = '';
    $deletedLatLng = true;
    $_SESSION['uplmsg']
        .= "Deleted file {$maingpx} and it's associated track from site; ";
    if ($usrmiles === "NO" && $usrfeet === "NO") { // don't overwrite user changes
        $miles = '';
        $feet  = '';
        $mftNulls = true;
    }  
}
if (!$mftNulls) { // get POSTED miles/feet if not nulled above
    $miles = filter_input(INPUT_POST, 'miles');
    $feet  = filter_input(INPUT_POST, 'feet');
}
if (!$deletedLatLng) {
    $lat = empty($_POST['lat']) ? 
        '' : (int) ((float)(filter_input(INPUT_POST, 'lat')) * LOC_SCALE);
    $lng = empty($_POST['lng']) ?
        '' : (int) ((float)(filter_input(INPUT_POST, 'lng')) * LOC_SCALE);
    
}
$gpxfile = basename($_FILES['newgpx']['name']);
if (!empty($gpxfile)) {  // new upload
    $gpxtype = fileTypeAndLoc($gpxfile);
    if ($gpxtype[2] === 'gpx') {
        $gpxupl = validateUpload("newgpx", "../gpx/");
        $newgpx = $gpxupl[0];
        $_SESSION['uplmsg'] .= $gpxupl[1];
        $trkdat = makeTrackFile($newgpx, "../gpx/");
        $newtrk = $trkdat[0];
        $lat = (int) ((float)($trkdat[2]) * LOC_SCALE);
        $lng = (int) ((float)($trkdat[3]) * LOC_SCALE);
        $newgpxq = "UPDATE EHIKES " .
            "SET gpx = ?, trk = ?, lat = ?, lng = ? " .
            "WHERE indxNo = ?;";
        $ngpx = $pdo->prepare($newgpxq);
        $ngpx->execute([$newgpx, $newtrk, $lat, $lng, $hikeNo]);
        $maingpx = $newgpx;
    } else {
        $_SESSION['uplmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
                "File Type NOT .gpx for {$gpxfile}.</p>";
    }
}
/**
 * If the user selected 'Calculate From GPX', then those values will
 * be used instead of any existing values in the miles and feet fields. 
 */
if (isset($_POST['mft'])) {
    if (empty($maingpx)) {
        $_SESSION['uplmsg'] .= "<br />No gpx file has been uploaded for this hike; " 
            . "Miles/Feet Calculations cannot be performed";
        // miles/feet remain as previously defined
    } else {
        $gpxPath = "../gpx/" . $maingpx;
        $gpxdat = simplexml_load_file($gpxPath);
        if ($gpxdat === false) {
            throw new Exception("Failed to open {$gpxPath}");
        }
        if ($gpxdat->rte->count() > 0) {
            $gpxdat = convertRtePts($gpxdat);
        }
        $noOfTrks = $gpxdat->trk->count();
        // threshold in meters to filter out elevation and distance value variation
        // set by default if command line parameter(s) is not given
        $elevThresh = 1.0;
        $distThresh = 5.0;
        $maWindow = 3;

        // calculate stats for all tracks:
        $pup = (float)0;
        $pdwn = (float)0;
        $pmax = (float)0;
        $pmin = (float)50000;
        $hikeLgthTot = (float)0;
        for ($k=0; $k<$noOfTrks; $k++) {
            $calcs = getTrackDistAndElev(
                0, $k, "", $gpxPath, $gpxdat, false, null,
                null, $distThresh, $elevThresh, $maWindow
            );
            $hikeLgthTot += $calcs[0];
            if ($calcs[1] > $pmax) {
                $pmax = $calcs[1];
            }
            if ($calcs[2] < $pmin) {
                $pmin = $calcs[2];
            }
            $pup  += $calcs[3];
            $pdwn += $calcs[4];
        } // end for: PROCESS EACH TRK

        $totalDist = $hikeLgthTot / 1609;
        $miles = round($totalDist, 1, PHP_ROUND_HALF_DOWN);
        $elev = ($pmax - $pmin) * 3.28084;
        if ($elev < 100) { // round to nearest 10
            $adj = round($elev/10, 0, PHP_ROUND_HALF_UP);
            $feet = 10 * $adj;
        } elseif ($elev < 1000) { // 100-999: round to nearest 50
            $adj = $elev/100;
            $lead = substr($adj, 0, 1);
            $n5 = $lead + 0.50;
            $n2 = $lead + 0.25;
            if ($adj > $n5) {
                $adj = $lead + 1;
            } elseif ($adj >$n2) {
                $adj = $lead + 0.5;
            } else {
                $adj = $lead;
            }
            $feet = 100 * $adj;
        } else { // 1000+: round to nearest 100
            $adj = round($elev/100, 0, PHP_ROUND_HALF_UP);
            $feet = 100 * $adj;
        }
    }
} 

/**
 * CLUSTER ASSIGNMENT PROCESSING:
 *  The order of changes processed are in the following priority:
 *     1. New Group added
 *     2. Existing assignment deleted
 *     3. Group Assignment Changed
 *     4. Nothing Changed
*/ 
$nxtGrp = isset($_POST['nxtGrp']) ?
    filter_input(INPUT_POST, 'nxtGrp') : 'NO';
$rmClus = isset($_POST['rmClus']) ?
    filter_input(INPUT_POST, 'rmClus') : 'NO';
$clat = empty($lat) ? null : $lat;
$clng = empty($lng) ? null : $lng;
// If cluster was entered previously w/no lat/lng
if ($updateCluster) {
    $updte_req = "UPDATE `CLUSTERS` SET `lat` = :lat, `lng` = :lng WHERE " .
        "`group` = :group;";
    $updte = $pdo->prepare($updte_req);
    $updte->execute(
        ["lat" => $clat, "lng" => $clng, "group" => $state['cname']]
    );
}

// 1. This category 'overrules' any other conditions
if ($nxtGrp === 'YES') {
    $cname = filter_input(INPUT_POST, 'newgname');
    // If no lat/lng now, will be updated later when entered
    $insert_req = "INSERT INTO `CLUSTERS` (`group`,`lat`,`lng`,`page`) " .
        "VALUES (?, ?, ?, '0');";
    $insert = $pdo->prepare($insert_req);
    $insert->execute([$cname, $clat, $clng]);
    // 2. Next in order, delete if so marked
} elseif ($rmClus === 'YES') {
    $cname = '';
    // 3. & 4. Whatever the select-box entry, use it
} else {
    $cname = filter_input(INPUT_POST, 'clusters');
}

// setup variables for saving to db:
$pdoBindings['pgTitle'] = filter_input(INPUT_POST, 'pgTitle');
$pdoBindings['locale'] = filter_input(INPUT_POST, 'locale');
$pdoBindings['cname'] = $cname;
$pdoBindings['logistics'] = filter_input(INPUT_POST, 'logistics');
$pdoBindings['diff'] = filter_input(INPUT_POST, 'diff');
$pdoBindings['fac'] = filter_input(INPUT_POST, 'fac');
$pdoBindings['wow'] = filter_input(INPUT_POST, 'wow');
$pdoBindings['seasons'] = filter_input(INPUT_POST, 'seasons');
$pdoBindings['expo'] = filter_input(INPUT_POST, 'expo');
// get an unfiltered input first to check for empty string:
$rawdir = filter_input(INPUT_POST, 'dirs');
if (empty($rawdir)) {
    $dirs = '';
} else {
    $dirs = filter_input(INPUT_POST, 'dirs', FILTER_VALIDATE_URL);
    if ($dirs === false) {
        $dirs = "--- INVALID URL DETECTED ---";
    }
}
$pdoBindings['dirs'] = $dirs;

$svreq = "UPDATE EHIKES SET " .
    "pgTitle = :pgTitle, locale = :locale, cname = :cname, " .
    "logistics = :logistics, diff = :diff, fac = :fac, wow = :wow, " .
    "seasons = :seasons, expo = :expo, dirs = :dirs " .
    "WHERE indxNo = :hikeNo";
$basic = $pdo->prepare($svreq);
$basic->execute($pdoBindings);
// Preserve NULLs in miles/feet when entry is empty
$milesFeet = [];
$mfquery = "UPDATE EHIKES SET miles = ";
if (empty($miles)) {
    $mfquery .= "NULL, feet = ";
} else {
    $mfquery .= "?, feet = ";
    $milesFeet[0] = $miles;
}
if (empty($feet)) {
    $mfquery .= "NULL ";
} else {
    $mfquery .= "? ";
    array_push($milesFeet, $feet);
}
$mfquery .= "WHERE indxNo = ?;";
array_push($milesFeet, $hikeNo);
$pdo->prepare($mfquery)->execute($milesFeet);
// preserve NULLs in lat/lng when empty
$data = [];
$latlng = "UPDATE EHIKES SET lat = ";
if (empty($lat)) {
    $latlng .= "NULL, lng = ";
} else {
    $latlng .= "?, lng =  ";
    $data[0] = $lat;
}
if (empty($lng)) {
    $latlng .= "NULL ";
} else {
    $latlng .= "? ";
    array_push($data, $lng);
}
$latlng .= "WHERE indxNo = ?;";
array_push($data, $hikeNo);
$pdo->prepare($latlng)->execute($data);

$redirect = "editDB.php?hikeNo={$hikeNo}&usr={$usr}&tab=1";
header("Location: {$redirect}");
