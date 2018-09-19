<?php
/**
 * This script saves any changes made (or data as is) on tab1 ("Basic Data")
 * of the hike page Editor, including uploading of the main gpx (track) file.
 * If a gpx file already exists, it may be deleted, or otherwise replaced by a
 * newly specified file via tab 1's browse button.
 * PHP Version 7.0
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require_once "../mysql/dbFunctions.php";
require_once "buildFunctions.php";
require_once "../php/gpxFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'hno');
$uid = filter_input(INPUT_POST, 'usr');
$maingpx = filter_input(INPUT_POST, 'mgpx');
$maintrk = filter_input(INPUT_POST, 'mtrk');
$delgpx = filter_input(INPUT_POST, 'dgpx');
$_SESSION['uplmsg'] = '';
$upld4latlng = false;
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
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'lat', 'indxNo', null, __FILE__, __LINE__
    );
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'lng', 'indxNo', null, __FILE__, __LINE__
    );
    $_SESSION['uplmsg']
        .= "Deleted file {$maingpx} and it's associated track from site; ";
}
$gpxfile = basename($_FILES['newgpx']['name']);
$gpxtype = fileTypeAndLoc($gpxfile);
if ($gpxtype[2] === 'gpx') {
    $gpxupl = validateUpload("newgpx", "../gpx/");
    $newgpx = $gpxupl[0];
    $_SESSION['uplmsg'] .= $gpxupl[1];
    $trkdat = makeTrackFile($newgpx, "../gpx/");
    $newtrk = $trkdat[0];
    $lat = $trkdat[2];
    $lng = $trkdat[3];
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'gpx', 'indxNo', $newgpx, __FILE__, __LINE__
    );
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'trk', 'indxNo', $newtrk, __FILE__, __LINE__
    );
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'lat', 'indxNo', $lat, __FILE__, __LINE__
    );
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'lng', 'indxNo', $lng, __FILE__, __LINE__
    );
    $upld4latlng = true;
} elseif ($gpxfile == '') {
    $newgpx = 'No file specified';
} else {
    $_SESSION['uplmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .gpx for {$gpxfile}.</p>";
}
/**
 *  Marker, cluster info may have changed during edit
 * If not, previous values must be retained:
 */
$marker = filter_input(INPUT_POST, 'pmrkr');
$clusGrp = filter_input(INPUT_POST, 'pclus'); // current db value
$cgName = filter_input(INPUT_POST, 'pcnme'); // current db value
// Acquire all cluster assingments, old & new:
$clusterdata = dropdownData('cls'); 
$groups = array_keys($clusterdata);
$cnames = array_values($clusterdata);
// setup variables for saving to db:
$pg = filter_input(INPUT_POST, 'hname');
$hTitle = mysqli_real_escape_string($link, $pg);
$hUser = mysqli_real_escape_string($link, $uid);
$loc = filter_input(INPUT_POST, 'locale');
$hLoc = mysqli_real_escape_string($link, $loc);
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
$clName = mysqli_real_escape_string($link, $cgName);


/**
 * If the user selected 'Calculate From GPX', then those values will
 * be used instead of any existing values in the miles and feet fields. 
 */
if (isset($_POST['mft'])) {
    if ($maingpx == '') {
        die("No gpx file has been uploaded for this hike");
    }
    $gpxPath = "../gpx/" . $maingpx;
    $gpxdat = simplexml_load_file($gpxPath);
    if ($gpxdat === false) {
        die(__FILE__ . "Line " . __LINE__ . " Failed to open {$gpxPath}");
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

    // debug arrays stored in system tmp directory:
    $handleDfa = gpsvDebugFileArray($gpxPath);
    $handleDfc = gpsvDebugComputeArray($gpxPath);

    // calculate stats for all tracks:
    $pup = (float)0;
    $pdwn = (float)0;
    $pmax = (float)0;
    $pmin = (float)50000;
    $hikeLgthTot = (float)0;
    for ($k=0; $k<$noOfTrks; $k++) {
        $calcs = getTrackDistAndElev(
            $k, "", $gpxPath, $gpxdat, true, $handleDfa,
            $handleDfc, $distThresh, $elevThresh, $maWindow
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

    // Do debug output (summary stats for entire hike)
    fputs(
        $handleDfc,
        sprintf("hikeLgthTot,%.2f mi", $hikeLgthTot / 1609) .
        sprintf(",pmax %.2fm,%.2fft", $pmax, $pmax * 3.28084) .
        sprintf(",pmin:%.2fm,%.2fft", $pmin, $pmin * 3.28084) .
        sprintf(",pup:%.2fm,%.2fft", $pup, $pup * 3.28084) .
        sprintf(",pdwn:%.2fm,%.2fft", $pdwn, $pdwn * 3.28084) .
        PHP_EOL .
        "distThresh:{$distThresh},elevThresh:{$elevThresh}" .
        ",maWindow:{$maWindow}" . PHP_EOL
    );
    fclose($handleDfa);
    fclose($handleDfc);

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
} else {
    $lgth = filter_input(INPUT_POST, 'hlgth');
    $ht = filter_input(INPUT_POST, 'helev');
}

/**
 * NOTE: a means to change the 'hike at Visitor Center' location has not
 * yet been implemented, so 'collection' is not modified
 */
$log = filter_input(INPUT_POST, 'htype');
$hType = mysqli_real_escape_string($link, $log);
$hLgth = mysqli_real_escape_string($link, $lgth);
$hElev = mysqli_real_escape_string($link, $ht);
$diff = filter_input(INPUT_POST, 'hdiff');
$hDiff = mysqli_real_escape_string($link, $diff);
$fac = filter_input(INPUT_POST, 'hfac');
$hFac = mysqli_real_escape_string($link, $fac);
$wow = filter_input(INPUT_POST, 'hwow');
$hWow = mysqli_real_escape_string($link, $wow);
$seas = filter_input(INPUT_POST, 'hsea');
$hSeas = mysqli_real_escape_string($link, $seas);
$expo = filter_input(INPUT_POST, 'hexp');
$hExpos = mysqli_real_escape_string($link, $expo);
if (!$upld4latlng) {
    $elat = filter_input(INPUT_POST, 'hlat', FILTER_VALIDATE_FLOAT);
    if ($elat) {
        $hLat = mysqli_real_escape_string($link, $elat);
    } else {
        $hLat = 0.0000;
    }
    $elng = filter_input(INPUT_POST, 'hlon', FILTER_VALIDATE_FLOAT);
    if ($elng) {
        $hLon = mysqli_real_escape_string($link, $elng);
    } else {
        $hLon = 0.0000;
    }
}
$dirs = filter_input(INPUT_POST, 'gdirs');
$hDirs = mysqli_real_escape_string($link, $dirs);
// The hike data will be updated, first without lat/lng or miles/elev
$saveHikeReq = "UPDATE EHIKES SET pgTitle = '{$hTitle}'," .
    "locale = '{$hLoc}',marker = '{$marker}'," .
    "cgroup = '{$clusGrp}',cname = '{$clName}',logistics = '{$hType}'," .
    "diff = '{$hDiff}',fac = '{$hFac}',wow = '{$hWow}'," .
    "seasons = '{$hSeas}',expo = '{$hExpos}',dirs = '{$hDirs}' " .
    "WHERE indxNo = {$hikeNo};";
$saveHike = mysqli_query($link, $saveHikeReq) or die(
    __FILE__ . " Line " . __LINE__ . " Failed to save new data to EHIKES: " .
        mysqli_error($link)
);
// Preserve null in miles/elevation when no entry was input
if ($hLgth == '') {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'miles', 'indxNo', null, __FILE__, __LINE__
    );
} else {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'miles', 'indxNo', $hLgth, __FILE__, __LINE__
    );
}
if ($hElev == '') {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'feet', 'indxNo', null, __FILE__, __LINE__
    );
} else {
    updateDbRow(
        $link, 'EHIKES', $hikeNo, 'feet', 'indxNo', $hElev, __FILE__, __LINE__
    );
}
if (!$upld4latlng) {
    // Preserve null in lat/lng when no entry (or bad entry) was input
    if ($hLat === 0.0000 || $hLon === 0.000) {
        updateDbRow(
            $link, 'EHIKES', $hikeNo, 'lat', 'indxNo', null, __FILE__, __LINE__
        );
        updateDbRow(
            $link, 'EHIKES', $hikeNo, 'lng', 'indxNo', null, __FILE__, __LINE__
        );
    } else {
        updateDbRow(
            $link, 'EHIKES', $hikeNo, 'lat', 'indxNo', $hLat, __FILE__, __LINE__
        );
        updateDbRow(
            $link, 'EHIKES', $hikeNo, 'lng', 'indxNo', $hLon, __FILE__, __LINE__
        );
    }
}
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}&tab=1";
header("Location: {$redirect}");
