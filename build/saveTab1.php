<?php
/**
 * This script saves any changes made (or data as is) on tab1 ("Basic Data")
 * of the hike page Editor. 
 * PHP Version 7.0
 * 
 * @package Editiing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
session_start();
$_SESSION['activeTab'] = 1;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
require_once "buildFunctions.php";
$hikeNo = filter_input(INPUT_POST, 'hno');
$uid = filter_input(INPUT_POST, 'usr');
/**
 *  Marker, cluster info may have changed during edit
 * If not, previous values must be retained:
 */
$marker = filter_input(INPUT_POST, 'pmrkr');
$clusGrp = filter_input(INPUT_POST, 'pclus'); // current db value
$cgName = filter_input(INPUT_POST, 'pcnme'); // current db value
/**
 * Note: The value obtained from the drop-down will be only the name of the 
 * cluster group (cname) and not its letter value. Therefore, the letter
 * values (cgroup) must be extracted and correlation to the names established.
 * If this is a new group, the group letter has not yet been established.
 */ 
$groups = [];
$cnames = [];
// First, the HIKES table cluster group info:
$clusreq = "SELECT cgroup, cname FROM HIKES;";
$clusq = mysqli_query($link, $clusreq) or die(
    __FILE__ . " Line " . __LINE__ . " Failed to get cluster info from HIKES: "
    . mysqli_error($link)
);
while ($clusDat = mysqli_fetch_assoc($clusq)) {
    $cgrp = fetch($clusDat['cgroup']);
    if ($cgrp !== '') {
        // no duplicates please (NOTE: "array_unique" leaves holes)
        $match = false;
        for ($i=0; $i<count($groups); $i++) {
            if ($cgrp == $groups[$i]) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            array_push($groups, $cgrp);
            array_push($cnames, fetch($clusDat['cname']));
        }
    }
}
mysqli_free_result($clusq);
// Next, the EHIKES cluster group info:
$eclusreq = "SELECT cgroup, cname FROM EHIKES;";
$eclusq = mysqli_query($link, $eclusreq) or die(
    __FILE__ . " Line " . __LINE__ . " Failed to get cluster info from EHIKES: "
    . mysqli_error($link)
);
while ($eclusDat = mysqli_fetch_assoc($eclusq)) {
    $ecgrp = fetch($eclusDat['cgroup']);
    if ($ecgrp !== '') { // will also be empty if newgroup specified
        $match = false;
        for ($i=0; $i<count($groups); $i++) {
            if ($ecgrp == $groups[$i]) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            array_push($groups, $ecgrp);
            array_push($cnames, fetch($eclusDat['cname']));
        }
    }
}
mysqli_free_result($eclusq);
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
// 2.
} elseif (isset($nextGrp) && $nextGrp === 'YES') {
    $availLtrs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $doubleLtrs = 'AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRSSTTUUVVWWXXYYZZ';
    // add another group of letters later if needed
    $nextmem = filter_input(INPUT_POST, 'grpcnt', FILTER_SANITIZE_NUMBER_INT);
    // group letters are assigned sequentially
    if ($nextmem < 26) {
        $newgrp = substr($availLtrs, $nextmem, 1);
    } else {
        // assign from doubleLtrs:
        $pos = 2*($nextmem - 26);
        $newgrp = substr($doubleLtrs, $pos, 2);
    }  // elseif more groups of letters are added later...
    $marker = 'Cluster';
    $clusGrp = $newgrp;
    $cgName = filter_input(INPUT_POST, 'newgname');
// 3. (NOTE: marker will be assigned to 'Cluster' regardless of 
//       whether previously cluster type or not
} elseif ($grpChg  === 'YES') {
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
    $cgName = $newname;
// 4.
} else {
    //  No Changes Assigned to marker, clusGrp, cgName
}
$clName = mysqli_real_escape_string($link, $cgName);
/**
 * If the user selected 'Calculate From GPX', then those values will
 * be used instead of any existing values in the miles and feet fields. 
 */
if (isset($_POST['mft'])) {
    $gpxin = filter_input(INPUT_POST, 'gpx');
    if ($gpxin == '') {
        die("No gpx file has been uploaded for this hike");
    }
    $gfile = "../gpx/" . $gpxin;
    $gdat = simplexml_load_file($gfile);
    if ($gdat === false) {
        die(__FILE__ . "Line " . __LINE__ . " Failed to open {$gfile}");
    }
    if ($gdat->rte->count() > 0) {
        $gdat = convertRtePts($gdat);
    }
    $noOfTrks = $gdat->trk->count();
    $totalDist = 0;
    $minElev = 20000;
    $maxElev = 0;
    for ($j=0; $j<$noOfTrks; $j++) {
        $init = true;
        foreach (genLatLng($gdat, $j) as $geo) {
            // calculate distance between last pt and this one
            if ($init) {
                $prevLat = $geo[0];
                $prevLng = $geo[1];
                $init = false;
            } else {
                $nxtdist = distance($prevLat, $prevLng, $geo[0], $geo[1]);
                $totalDist += $nxtdist[0];
                $prevLat = $geo[0];
                $prevLng = $geo[1];
            }
            // record min/max elevations (note: watch out for elev === 0.0000)
            if ($geo[2] < $minElev) {
                if ($geo[2] > 10) {
                    $minElev = $geo[2];
                }
            }
            if ($geo[2] > $maxElev) {
                $maxElev = $geo[2];
            }
        }
    }
    $lgth = round($totalDist, 1, PHP_ROUND_HALF_DOWN);
    $elev = $maxElev - $minElev;
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
