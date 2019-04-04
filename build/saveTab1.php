<?php
/**
 * This script saves any changes made (or data as is) on tab1 ("Basic Data")
 * of the hike page Editor, including uploading of the main gpx (track) file.
 * If a gpx file already exists, it may be deleted, or otherwise replaced by a
 * newly specified file via tab 1's browse button. It is invoked when the user
 * submits the data via the 'Apply' button on tab1.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require_once "../php/gpxFunctions.php";
$saves = []; // placeholders for pdo query
$hikeNo = filter_input(INPUT_POST, 'hno');
$saves['hikeno'] = $hikeNo;
$hUser = filter_input(INPUT_POST, 'usr');
$maingpx = filter_input(INPUT_POST, 'mgpx'); // may be empty
$maintrk = filter_input(INPUT_POST, 'mtrk'); // may be empty
$delgpx = isset($_POST['dgpx']) ? $_POST['dgpx'] : null;
$latLngSet = false;
$_SESSION['uplmsg'] = ''; // return status to user on tab1
/**
 * This section handles the main gpx file upload/delete.
 * Note: the delete checkbox does not appear if main gpx file is not specified
 */
if (isset($delgpx)) {
    $delgpx = '../gpx/' . $maingpx;
    if (!unlink($delgpx)) {
        throw new Exception(
            __FILE__ . " Line " . __LINE__ . 
            ": Did not remove {$delgpx} from site"
        );
    }
    $deltrk = '../json/' . $maintrk;
    if (!unlink($deltrk)) {
        throw new Exception(
            __FILE__ . " Line " . __LINE__ .
            ": Did not remove {$deltrk} from site"
        );
    }
    $maingpx = '';
    $udgpxreq = "UPDATE EHIKES SET 
        gpx = NULL, trk = NULL, 
        lat = NULL, lng = NULL,
        miles = NULL, feet = NULL 
        WHERE indxNo = ?;";
    $udgpx = $pdo->prepare($udgpxreq);
    $udgpx->execute([$hikeNo]);
    $latLngSet = true;
    $lat = '';
    $lng = '';
    $_SESSION['uplmsg']
        .= "Deleted file {$maingpx} and it's associated track from site; ";
}
$gpxfile = basename($_FILES['newgpx']['name']);
if (!empty($gpxfile)) {  // No new upload
    $gpxtype = fileTypeAndLoc($gpxfile);
    if ($gpxtype[2] === 'gpx') {
        $gpxupl = validateUpload("newgpx", "../gpx/");
        $newgpx = $gpxupl[0];
        $_SESSION['uplmsg'] .= $gpxupl[1];
        $trkdat = makeTrackFile($newgpx, "../gpx/");
        $newtrk = $trkdat[0];
        $lat = $trkdat[2];
        $lng = $trkdat[3];
        $newgpxq = "UPDATE EHIKES " .
            "SET gpx = ?, trk = ?, lat = ?, lng = ? " .
            "WHERE indxNo = ?;";
        $ngpx = $pdo->prepare($newgpxq);
        $ngpx->execute([$newgpx, $newtrk, $lat, $lng, $hikeNo]);
        $maingpx = $newgpx;
        $latLngSet = true;
    } else {
        $_SESSION['uplmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
                "File Type NOT .gpx for {$gpxfile}.</p>";
    }
}
/**
 *  Marker, cluster info may have changed during edit
 * If not, previous values must be retained:
 */
$marker = filter_input(INPUT_POST, 'pmrkr');
$clusGrp = filter_input(INPUT_POST, 'pclus'); // current db value
$cgName = filter_input(INPUT_POST, 'pcnme'); // current db value
// Acquire all cluster assingments, old & new:
$clusterdata = dropdownData($pdo, 'cls'); 
$groups = array_keys($clusterdata);
$cnames = array_values($clusterdata);
/**
 *   CLUSTER/MARKER ASSIGNMENT PROCESSING:
 *     The order of changes processed are in the following priority:
 *     1. Existing assignment deleted: Marker changes to "Normal"
 *     2. New Group Assignment
 *     3. Group Assignment Changed
 *     4. Nothing Changed
*/
$delClus = filter_input(INPUT_POST, 'rmclus');
$nextGrp = filter_input(INPUT_POST, 'nxtg');
$grpChg = filter_input(INPUT_POST, 'chgd');
// 1.
if (isset($delClus) && $delClus === 'YES') {
    $marker = 'Normal';
    $clusGrp = '';
    $cgName = '';
} elseif (isset($nextGrp) && $nextGrp === 'YES') {
    // 2.
    $availLtrs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $doubleLtrs = 'AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRSSTTUUVVWWXXYYZZ';
    // get the last letter used (NOTE: some may be skipped without effect)
    $last_assigned = $groups[count($groups)-1];
    if (strlen($last_assigned) === 1) {
        if ($last_assigned === 'Z') {
            $newgrp = "AA";
        } else {
            for ($k=0; $k<strlen($availLtrs); $k++) {
                if ($last_assigned === substr($availLtrs, $k, 1)) {
                    $newgrp = substr($availLtrs, $k+1, 1);
                    break;
                }
            }
        }
    } else {
        for ($n=0; $n<strlen($doubleLtrs)/2; $n++) {
            if ($last_assigned === substr($doubleLtrs, 2*$n, 2)) {
                $newgrp = substr($doubleLtrs, 2*($n+1), 2);
                break;
            }
        }
    }
    $marker = 'Cluster';
    $clusGrp = $newgrp;
    $cgName = filter_input(INPUT_POST, 'newgname');
} elseif ($grpChg  === 'YES') {
    // 3. (NOTE: marker will be assigned to 'Cluster' regardless of 
    //       whether previously cluster type or not
    $marker = 'Cluster';
    $newname = filter_input(INPUT_POST, 'htool');
    // get association with group letter
    for ($i=0; $i<count($cnames); $i++) {
        if ($cnames[$i] == $newname) {
            $newgrp = $groups[$i];
            break;
        }
    }
    $clusGrp = $newgrp;
    $cgName = $newname;;
} else {
    // 4.
    //  No Changes Assigned to marker, clusGrp, cgName
}
// setup variables for saving to db:
$saves['hName'] = filter_input(INPUT_POST, 'hname');
$saves['hLoc'] = filter_input(INPUT_POST, 'locale');
$saves['hGrp'] = $clusGrp;
$saves['cNme'] = $cgName;
/**
 * If the user selected 'Calculate From GPX', then those values will
 * be used instead of any existing values in the miles and feet fields. 
 */
if (isset($_POST['mft'])) {
    if (empty($maingpx)) {
        $_SESSION['uplmsg'] .= "<br />No gpx file has been uploaded for this hike; " 
            . "Miles/Feet Calculations cannot be performed";
        $lgth = '';
        $ht = '';
    } else {
        $gpxPath = "../gpx/" . $maingpx;
        $gpxdat = simplexml_load_file($gpxPath);
        if ($gpxdat === false) {
            throw new Exception(__FILE__ . "Line " . __LINE__ . " Failed to open {$gpxPath}");
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
                $k, "", $gpxPath, $gpxdat, false, null,
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
        $lgth = round($totalDist, 1, PHP_ROUND_HALF_DOWN);
        $elev = ($pmax - $pmin) * 3.28084;
        if ($elev < 100) { // round to nearest 10
            $adj = round($elev/10, 0, PHP_ROUND_HALF_UP);
            $ht = 10 * $adj;
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
            $ht = 100 * $adj;
        } else { // 1000+: round to nearest 100
            $adj = round($elev/100, 0, PHP_ROUND_HALF_UP);
            $ht = 100 * $adj;
        }
    }
} else {
    if (empty($maingpx)) {
        $lgth = '';
        $ht = '';
    } else {
        $lgth = filter_input(INPUT_POST, 'hlgth');
        $ht = filter_input(INPUT_POST, 'helev');
    }
}

/**
 * NOTE: a means to change the 'hike at Visitor Center' location has not
 * yet been implemented, so 'collection' is not modified
 */
$saves['hMrkr'] = $marker;
$saves['hType'] = filter_input(INPUT_POST, 'htype');
$saves['hDiff'] = filter_input(INPUT_POST, 'hdiff');
$saves['hFac'] = filter_input(INPUT_POST, 'hfac');
$saves['hWow'] = filter_input(INPUT_POST, 'hwow');
$saves['hSeas'] = filter_input(INPUT_POST, 'hsea');
$saves['hExpo'] = filter_input(INPUT_POST, 'hexp');
$hLgth = $lgth;
$hElev = $ht;
if (!$latLngSet) {
    $hLat = filter_input(INPUT_POST, 'hlat', FILTER_VALIDATE_FLOAT);
    if (!$hLat) {
        $hLat = '';
    }
    $hLon = filter_input(INPUT_POST, 'hlon', FILTER_VALIDATE_FLOAT);
    if (!$hLon) {
        $hLon = '';
    }
} else {
    $hLat = $lat;
    $hLon = $lng;
}
$saves['hDirs'] = filter_input(INPUT_POST, 'gdirs');
// The hike data will be updated, first without lat/lng or miles/elev
$svreq = "UPDATE EHIKES " .
    "SET pgTitle = :hName, locale = :hLoc, marker = :hMrkr, " .
    "cgroup = :hGrp, cname = :cNme, logistics = :hType, diff = :hDiff, " .
    "fac = :hFac, wow = :hWow, seasons = :hSeas, expo = :hExpo, dirs = :hDirs" .
    " WHERE indxNo = :hikeno";
$t1 = $pdo->prepare($svreq);
$t1->execute($saves);
// Preserve null in miles/elevation when no entry was input
if ($hLgth == '') {
    $lgthReq = "UPDATE EHIKES SET miles = NULL WHERE indxNo = ?;";
    $milesq = $pdo->prepare($lgthReq);
    $milesq->execute([$hikeNo]);
} else {
    $lgthReq = "UPDATE EHIKES SET miles = ? WHERE indxNo = ?;";
    $milesq = $pdo->prepare($lgthReq);
    $milesq->execute([$hLgth, $hikeNo]);
}
if ($hElev == '') {
    $eleReq = "UPDATE EHIKES SET feet = NULL WHERE indxNo = ?;";
    $elevq = $pdo->prepare($eleReq);
    $elevq->execute([$hikeNo]);
} else {
    $eleReq = "UPDATE EHIKES SET feet = ? WHERE indxNo = ?;";
    $elevq = $pdo->prepare($eleReq);
    $elevq->execute([$hElev, $hikeNo]);
}
// Preserve null in lat/lng when no entry (or bad entry) was input
if (empty($hLat) || empty($hLon)) {
    $latlng = "UPDATE EHIKES SET lat = NULL, lng = NULL WHERE indxNo = ?;";
    $llq = $pdo->prepare($latlng);
    $llq->execute([$hikeNo]);
} else {
    $latlng = "UPDATE EHIKES SET lat = ?, lng = ? WHERE indxNo = ?;";
    $llq = $pdo->prepare($latlng);
    $llq->execute([$hLat, $hLon, $hikeNo]);
}

$redirect = "editDB.php?hno={$hikeNo}&usr={$hUser}&tab=1";
header("Location: {$redirect}");
