<?php
/**
 * This script saves data present on tab1 ("Basic Data") of the hike page Editor.
 * 
 * With regard to gpx files: the normal situation allows for uploading one file
 * ("main") to be displayed on the hike page map. The user may, however, choose to
 * add additional files which will also be displayed on the same hike page map. Any
 * of these additional files may be excluded later, if so specified by the user. 
 * However there is no current method to delete the additional files. If any gpx
 * file already exists at the time of the upload, the previous file will remain on
 * the server, and the new file will have '_DUP' appended to its name. 
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

$hikeNo     = filter_input(INPUT_POST, 'hikeNo');
$maintrk    = filter_input(INPUT_POST, 'mtrk'); // already uploaded track
$delgpx     = isset($_POST['dgpx']) ? $_POST['dgpx'] : null;
$noincludes = isset($_POST['deladd']) ? $_POST['deladd'] : false;
$addfiles   = array('addgpx1', 'addgpx2', 'addgpx3');            
$usrmiles   = filter_input(INPUT_POST, 'usrmiles');  // registers user changes
$usrfeet    = filter_input(INPUT_POST, 'usrfeet');   // registers user changes
$_SESSION['uplmsg'] = ''; // return status to user on tab1

// get the current list of gpx files from the database; comma-separated string
$getGpxReq = "SELECT `gpx` FROM `EHIKES` WHERE `indxNo` = ?;";
$getGpx  = $pdo->prepare($getGpxReq);
$getGpx->execute([$hikeNo]);
$gpxList = $getGpx->fetch(PDO::FETCH_ASSOC);
$allgpx = explode(",", $gpxList['gpx']); // empty string returns array[0] = ''
foreach ($allgpx as &$gpx) { // count($allgpx) always >= 1
    $gpx = trim($gpx);
}
$maingpx = $allgpx[0] !== '' ? $allgpx[0] : '';
if ($noincludes) { // need ints for comparison later
    foreach ($noincludes as &$value) {
        $value = (int)$value;
    }
}

$redirect = "editDB.php?tab=1&hikeNo={$hikeNo}";
$pdoBindings = [];
$pdoBindings['hikeNo'] = $hikeNo;

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
    $udgpxreq = "UPDATE EHIKES SET gpx = NULL, trk = NULL, lat = NULL, lng = NULL,
        miles = NULL, feet = NULL WHERE indxNo = ?;";
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
// IF a gpx file was uploaded:
$gpxfile = basename($_FILES['newgpx']['name']);
if (!empty($gpxfile)) {  // new upload
    $unique = uploadGpxKmlFile('newgpx', true, true);
    if (empty($_SESSION['user_alert'])) {
        $trkdat = makeTrackFile($unique);
        $newtrk = $trkdat[0];
        $lat = (int) ((float)($trkdat[1]) * LOC_SCALE);
        $lng = (int) ((float)($trkdat[2]) * LOC_SCALE);
        // only update trk, lat, lng: gpx may be a string list - saved later
        $newdatReq = "UPDATE EHIKES " .
            "SET trk = ?, lat = ?, lng = ? WHERE indxNo = ?;";
        $newdat = $pdo->prepare($newdatReq);
        $newdat->execute([$newtrk, $lat, $lng, $hikeNo]);
        $maingpx = pathinfo($unique, PATHINFO_BASENAME);
    } else {
        exit;
    }
} 
// current maingpx may have been deleted and/or replaced above:
$allgpx[0] = $maingpx;
// remove any current elements in $allgpx whose checkbox has been ticked
if ($allgpx[0] !== '') {
    $orgsize = count($allgpx);
    if ($orgsize > 1) {
        for ($x=1; $x<$orgsize; $x++) {
            if (in_array($x, $noincludes)) {
                $unlinkAdd = '../gpx/' . $allgpx[$x];
                if (!unlink($unlinkAdd)) {
                    throw new Exception(
                        "Could not remove additional file from site"
                    );
                }
                unset($allgpx[$x]);
            }
        }
        $allgpx = array_values($allgpx); // re-index array
    }
} else {
    $allgpx = [];
    $allgpx[0] = '';
}
// add any new gpx files specified by user
for ($j=0; $j<count($addfiles); $j++) {
    $filesName = $addfiles[$j];
    $gfile = $_FILES[$filesName]; // file array, i.e. ['name'], ['error'], etc.
    if ($gfile['name'] !== '') {
        $adder = uploadGpxKmlFile($filesName, true);
        if (empty($_SESSION['user_alert'])) {
            $newadder = pathinfo($adder, PATHINFO_BASENAME);
            array_push($allgpx, $newadder);
        } else {
            exit;
        }
    }
}
// write out the updated gpx file list:
$gpx_file_list = implode(",", $allgpx);
$newlistReq = "UPDATE `EHIKES` SET `gpx`=? WHERE `indxNo` =?;";
$newlist = $pdo->prepare($newlistReq);
$newlist->execute([$gpx_file_list, $hikeNo]);

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
 */ 
// Previous state of `cname` determines CLUSHIKES actions
$clusAssignmentReq = "SELECT `cname` FROM `EHIKES` WHERE `indxNo`=?;";
$clusAssignment = $pdo->prepare($clusAssignmentReq);
$clusAssignment->execute([$hikeNo]);
$assign = $clusAssignment->fetch(PDO::FETCH_ASSOC);
$current = $assign['cname'];
$currentClus = empty($current) ? false : $assign['cname'];
$delCHike = false;
$addCHike = false;

$cname = filter_input(INPUT_POST, 'clusters');
// 'clusters' posts as 'null' when clusters drop-down is empty

if ($currentClus === false && !empty($cname)) {
    $addCHike = true;
}
if ($currentClus && empty($cname)) {
    $delCHike = true;
}
if (!empty($cname)) {
    if ($currentClus && $currentClus !== $cname) { // assignmt changed
        $delCHike = true;
        $addCHike = true;
    }
    // Is this an unpublished cluster?
    $getStateReq = "SELECT `pub` FROM `CLUSTERS` WHERE `group`=?;";
    $getState = $pdo->prepare($getStateReq);
    $getState->execute([$cname]);
    $clusState = $getState->fetch(PDO::FETCH_ASSOC);
    if ($clusState['pub'] === 'N') {
        $clat = empty($_POST['cluslat']) ? null :
            filter_input(INPUT_POST, 'cluslat', FILTER_VALIDATE_FLOAT);
        $clng = empty($_POST['cluslng']) ? null :
            filter_input(INPUT_POST, 'cluslng', FILTER_VALIDATE_FLOAT);
        if ((!is_null($clat) && !$clat) || (!is_null($clng) && !$clng)) {
            $_SESSION['user_alert'] = "You have entered invalid data in\n" .
                "either cluster latitude or longitude";
            header("Location: {$redirect}");
            exit;
        }
        // php yields '0' for [constant * null]! Therefore:
        $clat = is_null($clat) ? null : LOC_SCALE * $clat;
        $clng = is_null($clng) ? null : LOC_SCALE * $clng;
        $updte_req = "UPDATE `CLUSTERS` SET `lat` = :lat, `lng` = :lng WHERE " .
            "`group` = :group;";
        $updte = $pdo->prepare($updte_req);
        $updte->execute(["lat" => $clat, "lng" => $clng, "group" => $cname]);
        /**
         * If there is a Cluster Page in-edit for this new group, update it's
         * lat/lng values. 
         * Note: if a Cluster Page for this new group was published, it must
         * already have had lat/lng specified (it won't publish otherwise).
         * Therefore, the only scenario to update is if the Cluster Page for
         * the new group is in-edit.
         */
        $checkForCPReq = "SELECT `pgTitle` FROM `EHIKES` WHERE `pgTitle`=?;";
        $checkForCP = $pdo->prepare($checkForCPReq);
        $checkForCP->execute([$cname]);
        $CP_InEdit = $checkForCP->fetch(PDO::FETCH_ASSOC);
        if ($CP_InEdit !== false) {
            $newLatLngReq = "UPDATE `EHIKES` SET `lat`=?,`lng`=? WHERE " .
                "`pgTitle`=?;";
            $newLatLng = $pdo->prepare($newLatLngReq);
            $newLatLng->execute([$clat, $clng, $cname]);
        }
    }
}
// update CLUSHIKES as appropriate
if ($delCHike) {
    $deleteCHikeReq = "DELETE FROM `CLUSHIKES` WHERE `indxNo`=?;";
    $deleteCHike = $pdo->prepare($deleteCHikeReq);
    $deleteCHike->execute([$hikeNo]);
}
if ($addCHike) {
    // get clusterid for $cname
    $cnameIdReq = "SELECT `clusid` FROM `CLUSTERS` WHERE `group`=:grp;";
    $cnameId = $pdo->prepare($cnameIdReq);
    $cnameId->execute(["grp" => $cname]);
    $cnId = $cnameId->fetch(PDO::FETCH_ASSOC);
    $id = $cnId['clusid'];
    $addClusHikeReq = "INSERT INTO `CLUSHIKES` (`indxNo`,`pub`,`cluster`) " .
        "VALUES(?,'N',?);";
    $addClusHike = $pdo->prepare($addClusHikeReq);
    $addClusHike->execute([$hikeNo, $id]);
}

// Back to EHIKES: setup variables for saving to db:
$pdoBindings['pgTitle'] = filter_input(INPUT_POST, 'pgTitle');
$pdoBindings['locale'] = filter_input(INPUT_POST, 'locale');
$pdoBindings['cname'] = $cname;
$pdoBindings['logistics'] = filter_input(INPUT_POST, 'logistics');
$pdoBindings['diff'] = filter_input(INPUT_POST, 'diff');
$pdoBindings['fac'] = filter_input(INPUT_POST, 'fac');
$pdoBindings['wow'] = filter_input(INPUT_POST, 'wow');
$pdoBindings['seasons'] = filter_input(INPUT_POST, 'seasons');
$pdoBindings['expo'] = filter_input(INPUT_POST, 'expo');
// FILTER_VALIDATE_URL returning false for Google Maps! Can't get
// online answer to that...
$dirs = filter_input(INPUT_POST, 'dirs');
if (empty($dirs)) {
    $dirs = '';
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

header("Location: {$redirect}");
