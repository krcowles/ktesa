<?php
/**
 * When a user wishes to edit a page already published (whether hike or
 * cluster page), this script will extract the data from the page's db
 * tables and copy it into EHIKES et al where it can be edited. When a
 * published page is undergoing edit, the 'stat' field in EHIKES will be
 * set to the published indxNo. The user is then directed to either the
 * hike page editor or to the cluster page editor accordingly. When edits
 * are complete the admin can then publish the EHIKE which updates the
 * HIKES and associated tables with the newly edited data.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$getHike = filter_input(INPUT_GET, 'hikeNo'); // PUBLISHED indxNo
$cluspg  = isset($_GET['clus']) && $_GET['clus'] === 'y' ? true : false;
$userid = $_SESSION['userid'];

/**
 * Since EHIKES depends on 'cname' to identify association w/new or
 * published cluster groups, it must be specified to ensure proper
 * transfer. HIKES no longer supports 'cname'.
 */ 
$cname = '';
$clusHikeStatReq = "SELECT `cluster` FROM `CLUSHIKES` WHERE " .
    "`indxNo`=? AND `pub`='Y';";
$clusHikeStat = $pdo->prepare($clusHikeStatReq);
$clusHikeStat->execute([$getHike]);
$clusHike = $clusHikeStat->fetchAll(PDO::FETCH_ASSOC); // always an array
if (count($clusHike) > 0) {
    $clusid = $clusHike[0]['cluster'];
    $getGroupNameReq = "SELECT `group` FROM `CLUSTERS` WHERE `clusid`=?;";
    $getGroupName = $pdo->prepare($getGroupNameReq);
    $getGroupName->execute([$clusid]);
    $groupName = $getGroupName->fetch(PDO::FETCH_ASSOC);
    $cname = $groupName['group'];
}
/*
 * Place HIKES data into EHIKES: NOTE: gpx will have production json names
 */

$xfrReq = "INSERT INTO `EHIKES` (`pgTitle`,`usrid`,`stat`,`locale`," .
    "`cname`,`logistics`,`miles`,`feet`,`diff`,`fac`,`wow`,`seasons`," .  
    "`expo`,`gpx`,`lat`,`lng`,`preview`,`purl1`,`purl2`,`dirs`,`tips`," .
    "`info`,`last_hiked`)" .
    "SELECT `pgTitle`,?,?,`locale`,?,`logistics`,`miles`,`feet`,`diff`," .
    "`fac`,`wow`,`seasons`,`expo`,`gpx`,`lat`,`lng`,`preview`,`purl1`," .
    "`purl2`,`dirs`,`tips`,`info`,`last_hiked` " .
    "FROM `HIKES` WHERE `indxNo` = ?;";
$query = $pdo->prepare($xfrReq);
$query->execute([$userid, $getHike, $cname, $getHike]);

// Fetch the new hike no in EHIKES:
$indxReq = "SELECT `indxNo` FROM `EHIKES` ORDER BY `indxNo` DESC LIMIT 1;";
$indxq = $pdo->query($indxReq);
$indxNo = $indxq->fetch(PDO::FETCH_NUM);
$hikeNo = $indxNo[0]; // EHIKES indxNo

if (!$cluspg) {
    /*
     * PLace TSV data into ETSV
     */
    $xfrTsvReq = "INSERT INTO `ETSV` (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) SELECT ?,folder,title," .
        "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr,org FROM " .
        "`TSV` WHERE `indxNo` = ?;";
    $tsvq = $pdo->prepare($xfrTsvReq);
    $tsvq->execute([$hikeNo, $getHike]);
    /*
     * Place GPSDATA into EGPSDATA
     * NOTE: gpx/json for urls fixed later...
     */
    $gpsDatReq = "INSERT INTO `EGPSDAT` (`indxNo`,`label`,`url`,`clickText`) " .
        "SELECT ?,`label`,`url`,`clickText` FROM `GPSDAT` WHERE " .
        "`indxNo` = ?;";
    $gpsq = $pdo->prepare($gpsDatReq);
    $gpsq->execute([$hikeNo, $getHike]);

    /**
     * Transfer published json files and reset gpx field in EHIKES;
     */
    $previousJson = getTrackFileNames($pdo, $getHike, 'pub')[0];
    $main_val = [];
    $add1_val = [];
    $add2_val = [];
    $add3_val = [];
    foreach ($previousJson as $json) {
        $ftype     = substr($json, 1, 2);  // 'mn', 'a1', 'a2', or 'a3'
        $dash_loc  = strpos($json, "_");
        $extension = substr($json, $dash_loc);
        $new_name  = "e" . $ftype . $hikeNo . $extension;
        $to_loc   = "../json/" . $new_name;
        $from_loc = "../json/" . $json;  
        // Published hike still requires its files, so copy, not move!
        if (!copy($from_loc, $to_loc)) {
            throw new Exception("Could not relocate {$json}");
        }
        switch ($ftype) {
        case "mn":
            array_push($main_val, $new_name);
            break;
        case "a1":
            array_push($add1_val, $new_name);
            break;
        case "a2":
            array_push($add2_val, $new_name);
            break;
        default:
            array_push($add3_val, $new_name);
        }
    }
    $old_gpx_array = getGpxArray($pdo, $hikeNo, 'edit'); // same as 'pub' right now
    $main_gpx = array_keys($old_gpx_array['main'])[0];
    $add1_gpx = empty($old_gpx_array['add1']) ? 
        '' : array_keys($old_gpx_array['add1'])[0];
    $add2_gpx = empty($old_gpx_array['add2']) ? 
        '' : array_keys($old_gpx_array['add2'])[0];
    $add3_gpx = empty($old_gpx_array['add3']) ? 
        '' : array_keys($old_gpx_array['add3'])[0];
    // update gpx array - assign new values
    $old_gpx_array["main"] = array($main_gpx => $main_val);
    $old_gpx_array["add1"] = empty($add1_gpx) ? [] : array($add1_gpx => $add1_val);
    $old_gpx_array["add2"] = empty($add2_gpx) ? [] : array($add2_gpx => $add2_val);
    $old_gpx_array["add1"] = empty($add3_gpx) ? [] : array($add3_gpx => $add1_val);
    $new_gpx_array = json_encode($old_gpx_array);
    $updateGpxReq = "UPDATE `EHIKES` SET `gpx`=? WHERE `indxNo`=?;";
    $updateGpx = $pdo->prepare($updateGpxReq);
    $updateGpx->execute([$new_gpx_array, $hikeNo]);
    /**
     * UPDATE EWAYPTS
     */
    $ewptsReq = "INSERT INTO `EWAYPTS` (`indxNo`,`type`,`name`,`lat`," .
        "`lng`,`sym`) SELECT ?,`type`,`name`,`lat`,`lng`,`sym` " .
        "FROM `WAYPTS` WHERE `indxNo`=?;";
    $ewpts = $pdo->prepare($ewptsReq);
    $ewpts->execute([$hikeNo, $getHike]);
    /**
     * EGPSDATA needs to update the gpx file pointer & json file[s];
     * Each 'url' contains json encoded data for 1 gpx file
     * w/corresponding json file[s]
     */
    $egpsDataReq = "SELECT * FROM `EGPSDAT` WHERE `label` LIKE 'GPX%' AND " .
        "`indxNo`=?;";
    $egpsData = $pdo->prepare($egpsDataReq);
    $egpsData->execute([$hikeNo]);
    $allGps = $egpsData->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allGps as $gps) {
        $egpsUrlField = getGPSurlData($gps['url']);
        $new_json = [];
        foreach ($egpsUrlField[1] as $pjson) {
            $dash_loc  = strpos($pjson, "_");
            $extension = substr($pjson, $dash_loc);
            $new_name  = "egps" . $hikeNo . $extension;
            $to_loc   = "../json/" . $new_name;
            $from_loc = "../json/" . $pjson;
            if (!copy($from_loc, $to_loc)) {
                throw new Exception("Could not relocate {$pjson}");
            }
            array_push($new_json, $new_name);
        }
        $newEntry = [$egpsUrlField[0] => $new_json];
        $new_gps_array = json_encode($newEntry);
        $updateGpsReq = "UPDATE `EGPSDAT` SET `url`=? WHERE `datId`=?;";
        $updateGps = $pdo->prepare($updateGpsReq);
        $updateGps->execute([$new_gps_array, $gps['datId']]);
    }
}
/*
 * Place REFS data into EREFS
 */
$refDatReq = "INSERT INTO `EREFS` (indxNo,rtype,rit1,rit2) SELECT " .
    "?,rtype,rit1,rit2 FROM `REFS` WHERE `indxNo` = ?;";
$refq = $pdo->prepare($refDatReq);
$refq->execute([$hikeNo, $getHike]);

// Redirect to appropriate editor
$redirect = $cluspg ?
    "editClusterPage.php?hikeNo={$hikeNo}" : "editDB.php?tab=1&hikeNo={$hikeNo}";
header("Location: {$redirect}");
