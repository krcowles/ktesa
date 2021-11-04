<?php
/**
 * This script saves data present on tab1 ("Basic Data") of the hike page Editor.
 * With regard to gpx files: the normal situation allows for uploading one file
 * ("main") to be displayed on the hike page map. The user may, however, choose to
 * add additional files which will also be displayed on the same hike page map. Any
 * of these additional files may be excluded later, if so specified by the user. 
 * Once uploaded, the GPS data is stored in the EGPX and EMETA tables of the gpx
 * database.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require_once "../php/gpxFunctions.php";

$hikeNo     = filter_input(INPUT_POST, 'hikeNo');
$maintrk    = filter_input(INPUT_POST, 'mtrk'); // already uploaded track
$delgpx     = isset($_POST['dgpx']) ? $_POST['dgpx'] : null; // delete main gpx
$noincludes = isset($_POST['deladd']) ? $_POST['deladd'] : false;   
$addloc     = isset($_POST['addaloc']) ? true : false; 
$region     = filter_input(INPUT_POST, 'locregion'); 
$newloc     = filter_input(INPUT_POST, 'userloc');        

$_SESSION['uplmsg'] = ''; // return status to user on tab1
$addfiles   = array('addgpx1', 'addgpx2', 'addgpx3');
$redirect = "editDB.php?tab=1&hikeNo={$hikeNo}";
$pdoBindings = [];
$pdoBindings['hikeNo'] = $hikeNo;

// the current list of gpx filenos may be a comma-separated string
$maingpx = '';
$allgpx  = ['0'];
$lat = '';
$lng = '';
$getGpxReq = "SELECT `gpxlist`,`lat`,`lng` FROM `EHIKES` WHERE `indxNo` = ?;";
$getGpx  = $pdo->prepare($getGpxReq);
$getGpx->execute([$hikeNo]);
$gpxList = $getGpx->fetch(PDO::FETCH_ASSOC); // null when no files yet
if (!empty($gpxList['gpxlist'])) {
    $allgpx = explode(",", $gpxList['gpxlist']);
    $maingpx = $allgpx[0];
    if ($noincludes) { // need ints for comparison later
        foreach ($noincludes as &$value) {
            $value = (int)$value;
        }
    }
    $lat = empty($gpxList['lat']) ? '' : $gpxList['lat'];
    $lng = empty($gpxList['lng']) ? '' : $gpxList['lng'];
} 
/**
 * Retrieve names of gpx files from filenos.
 * NOTE:For multiple gpx files and/or multi-track files,
 * file #1, track #1 is used for miles/feet
 */
$fnames = [];
$miles  = '';
$feet   = '';
for ($m=0; $m<count($allgpx); $m++) {
    if ($allgpx[$m] !== '0') {
        $getNameReq = "SELECT `fname`,`length`,`min2max` FROM `EMETA` " .
            "WHERE `fileno`=?;";
        $getName = $gdb->prepare($getNameReq);
        $getName->execute([$allgpx[$m]]);
        $gfiledat = $getName->fetch(PDO::FETCH_ASSOC);
        array_push($fnames, $gfiledat['fname']);
        if ($allgpx[$m] === $maingpx) {
            $miles = $gfiledat['length'];
            $feet  = $gfiledat['min2max'];
        }
    } else {
        $fnames[0] = '';
    }

}
/**
 * This section handles the main gpx file delete and new gpx file upload.
 * Lat/lngs are always updated when a new file is deleted or uploaded.
 * When a main file is deleted, the additional files remain, if they exist.
 * Note: the delete gpx checkbox does not appear on tab1 if a main gpx file
 * is not specified. Also note that any waypoints associated with the gpx file
 * will also be deleted.
 */
if (isset($delgpx)) {
    deleteGpxData('new', $gdb, $maingpx);
    $file2delete = $fnames[0];
    $deltrk = '../json/' . $maintrk;
    if (!unlink($deltrk)) {
        throw new Exception("Could not remove {$deltrk} from site");
    }
    $allgpx[0] = 0;
    $newlist = '';
    $maingpx = '';
    $miles = '';
    $feet  = '';
    if (count($allgpx) > 1) { 
        $newlist = implode(",", $allgpx);
    }
    $udgpxreq = "UPDATE EHIKES SET `gpxlist`=?,`trk`=NULL,`lat`=NULL,`lng`=NULL" .
        " WHERE indxNo = ?;";
    $udgpx = $pdo->prepare($udgpxreq);
    $udgpx->execute([$newlist, $hikeNo]);
    $lat = '';
    $lng = '';
    $deletedLatLng = true;
    $_SESSION['uplmsg']
        .= "Deleted file {$file2delete} and it's associated track from site; ";
}

// IF a new/main gpx file was uploaded
$gpxfile = basename($_FILES['newgpx']['name']);
if (!empty($gpxfile)) {  // new upload
    $returndat = uploadGpxKmlFile(
        $pdo, $gdb, 'newgpx', $hikeNo, true, true, true
    );
    if (empty($_SESSION['user_alert']) && $returndat !== '0') {
        $trkdat = makeTrackFile($returndat[1], $returndat[2]);
        $newtrk = $trkdat[0];
        $lat = (int) ((float)($trkdat[1]) * LOC_SCALE);
        $lng = (int) ((float)($trkdat[2]) * LOC_SCALE);
        $maingpx = $returndat[0]; // a fileno in EMETA
        $allgpx[0] = $maingpx;
        $updtmain = implode(",", $allgpx);
        $newdatReq = "UPDATE EHIKES " .
            "SET `gpxlist`=?,`trk`=?,`lat`=?,`lng`=? WHERE `indxNo`=?;";
        $newdat = $pdo->prepare($newdatReq);
        $newdat->execute([$updtmain, $newtrk, $lat, $lng, $hikeNo]);
        // update $miles/$feet (from track1 only)
        $mfReq = "SELECT `length`,`min2max` FROM `EMETA` WHERE `fileno`=?;";
        $mf = $gdb->prepare($mfReq);
        $mf->execute([$maingpx]);
        $mfinfo = $mf->fetch(PDO::FETCH_ASSOC);
        $miles = $mfinfo['length'];
        $feet  = $mfinfo['min2max'];
    } else {
        header("Location: " . $redirect);
        exit;
    }
} 
// remove any current elements in $allgpx whose checkbox has been ticked
$orgsize = count($allgpx);
if ($orgsize > 1) {
    // use a copy of $allgpx to make any deletions
    $copygpx = $allgpx;
    // delete additional files only, not main ($x=1)
    for ($x=1; $x<=$orgsize; $x++) {
        if ($noincludes) {
            if (in_array($x, $noincludes)) {
                deleteGpxData('new', $gdb, $copygpx[$x]);
                unset($fnames[$x]);     // file name of deleted
                unset($copygpx[$x]);
            }
        }
    }
    $allgpx = array_values($copygpx); // re-index arrays
    $fnames = array_values($fnames);
}
// add any new gpx files specified by user
for ($j=0; $j<count($addfiles); $j++) {
    $filesName = $addfiles[$j];
    $gfile = $_FILES[$filesName];
    if ($gfile['name'] !== '') {
        $adder = uploadGpxKmlFile(
            $pdo, $gdb, $filesName, $hikeNo, true, true, false
        );
        if (empty($_SESSION['user_alert'])) {
            array_push($allgpx, $adder[0]);
            array_push($fnames, $adder[2]); 
        } else {
            header("Location: " . $redirect);
            exit;
        }
    }
}
// write out the updated gpx file list:
$gpx_file_list = implode(",", $allgpx);
$newlistReq = "UPDATE `EHIKES` SET `gpxlist`=? WHERE `indxNo` =?;";
$newlist = $pdo->prepare($newlistReq);
$newlist->execute([$gpx_file_list, $hikeNo]);

if ($addloc) {
    $loc = ['        <option value="' . $newloc . '">' . $newloc .
        '</option>' . PHP_EOL];
    $areas = file('localeBox.html');
    $position = 0;
    for ($j=0; $j<count($areas); $j++) {
        if (strpos($areas[$j], $region) !== false) {
            $position = $j + 1;
            break;
        }
    }
    array_splice($areas, $position, 0, $loc);
    file_put_contents('localeBox.html', $areas);
    // add this locale to the db so that it will display on page refresh
    $loc_binding = $newloc;
} else {
    $loc_binding = filter_input(INPUT_POST, 'locale');
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
$pdoBindings['locale'] = $loc_binding;
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
