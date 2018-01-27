<?php
session_start();
$_SESSION['activeTab'] = 1;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'hno');
$uid = filter_input(INPUT_POST, 'usr');
/* Marker, cluster info may have changed during edit
 * If not, previous values must be retained:
 */
$marker = filter_input(INPUT_POST, 'pmrkr');
$clusGrp = filter_input(INPUT_POST, 'pclus');
$cgName = filter_input(INPUT_POST, 'pcnme');
# Non-edited items need to be saved as well:
$hikeColl = filter_input(INPUT_POST, 'col');
# Extract cluster list - and inherent 1 to 1 association with cluster name:
$groups = [];
$cnames = [];
$clusreq = "SELECT cgroup, cname FROM HIKES;";
$clusq = mysqli_query($link, $clusreq);
if (!$clusq) {
    die("saveTab1.php: Failed to get cluster info from HIKES: " .
        mysqli_error($link));
}
while ($clusDat = mysqli_fetch_assoc($clusq)) {
    $cgrp = $clusDat['cgroup'];
    if ($cgrp !== 0) {
        # no duplicates please (NOTE: "array_unique" leaves holes)
        $match = false;
        for ($i=0; $i<count($groups); $i++) {
            if ($cgrp == $groups[$i]) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            array_push($groups, $cgrp);
            array_push($cnames, $clusDat['cname']);
        }
    }
}
mysqli_free_result($clusq);
$pg = filter_input(INPUT_POST, 'hname');
$hTitle = mysqli_real_escape_string($link, $pg);
$hUser = mysqli_real_escape_string($link, $uid);
$loc = filter_input(INPUT_POST, 'locale');
$hLoc = mysqli_real_escape_string($link, $loc);
$hColl = mysqli_real_escape_string($link, $hikeColl);
/*  CLUSTER/MARKER ASSIGNMENT PROCESSING:
 *     The order of changes processed are in the following priority:
 *     1. Existing assignment deleted: Marker changes to "Normal"
 *     2. New Group Assignment
 *     3. Group Assignment Changed
 *     4. Nothing Changed
*/
$delClus = filter_input(INPUT_POST, 'rmclus');
$nextGrp = filter_input(INPUT_POST, 'nxtg');
$grpChg = filter_input(INPUT_POST, 'chgd');
# 1.
if (isset($delClus) && $delClus === 'YES') {
    $marker = 'Normal';
    $clusGrp = '';
    $cgName = '';
# 2.
} elseif (isset($nextGrp) && $nextGrp === 'YES') {
    $availLtrs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $doubleLtrs = 'AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRSSTTUUVVWWXXYYZZ';
    # add another group of letters later if needed
    $nextmem = filter_input(INPUT_POST, 'grpcnt', FILTER_SANITIZE_NUMBER_INT);
    # group letters are assigned sequentially
    if ($nextmem < 26) {
        $newgrp = substr($availLtrs, $nextmem, 1);
    } else {
        #assign from doubleLtrs:
        $pos = 2*($nextmem - 26);
        $newgrp = substr($doubleLtrs, $pos, 2);
    }  # elseif more groups of letters are added later...
    $marker = 'Cluster';
    $clusGrp = $newgrp;
    $cgName = filter_input(INPUT_POST, 'newgname');
# 3. (NOTE: marker will be assigned to 'Cluster' regardless of 
#       whether previously cluster type or not
} elseif ($grpChg  === 'YES') {
    $marker = 'Cluster';
    $newname = filter_input(INPUT_POST, 'htool');
    # get association with group letter
    for ($i=0; $i<count($cnames); $i++) {
        if ($cnames[$i] == $newname) {
            $newgrp = $groups[$i];
            break;
        }
    }
    $clusGrp = $newgrp;
    $cgName = $newname;
# 4.
} else {
    # No Changes Assigned to marker, clusGrp, cgName
}
$clName = mysqli_real_escape_string($link, $cgName);
/**
 * NOTE: a means to change the 'hike at Visitor Center' location has not
 * yet been implemented, so 'collection' is not modified
 */
$log = filter_input(INPUT_POST, 'htype');
$hType = mysqli_real_escape_string($link, $log);
$lgth = filter_input(INPUT_POST, 'hlgth');
$hLgth = mysqli_real_escape_string($link, $lgth);
$ht = filter_input(INPUT_POST, 'helev');
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
$hGpx = mysqli_real_escape_string($link, $gpxfile);
$hTrk = mysqli_real_escape_string($link, $trkfile);
$elat = filter_input(INPUT_POST, 'hlat', FILTER_VALIDATE_FLOAT);
if ($elat) {
    $hLat = mysqli_real_escape_string($link, $elat);
} elseif ($newvals) {
    $hLat = mysqli_real_escape_string($link, $lat);
} else {
    $hLat = 0.0000;
}
$elng = filter_input(INPUT_POST, 'hlon', FILTER_VALIDATE_FLOAT);
if ($elng) {
    $hLon = mysqli_real_escape_string($link, $elng);
} elseif ($newvals) {
    $hLon = mysqli_real_escape_string($link, $lng);
} else {
    $hLon = 0.0000;
}
$hAdd1 = mysqli_real_escape_string($link, $addon1);
$hAdd2 = mysqli_real_escape_string($link, $addon2);
$url1 = filter_input(INPUT_POST, 'purl1');
$hPurl1 = mysqli_real_escape_string($link, $url1);
$url2 = filter_input(INPUT_POST, 'purl2');
$hPurl2 = mysqli_real_escape_string($link, $url2);
$dirs = filter_input(INPUT_POST, 'gdirs');
$hDirs = mysqli_real_escape_string($link, $dirs);
// The hike data will be updated, first without lat/lng or miles/elev
$saveHikeReq = "UPDATE EHIKES SET pgTitle = '{$hTitle}'," .
    "locale = '{$hLoc}',marker = '{$marker}'," .
    "cgroup = '{$clusGrp}',cname = '{$clName}',logistics = '{$hType}'," .
    "diff = '{$hDiff}',fac = '{$hFac}',wow = '{$hWow}'," .
    "seasons = '{$hSeas}',expo = '{$hExpos}',purl1 = '{$hPurl1}'," .
    "purl2 = '{$hPurl2}',dirs = '{$hDirs}' WHERE indxNo = {$hikeNo};";
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
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
