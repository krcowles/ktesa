<?php
/**
 * When a user wishes to edit a page already published (whether hike or
 * cluster page), this script will extract the data from the page's db
 * tables and copy it into EHIKES et al where it can be edited. When a
 * published page is undergoing edit, the 'stat' field in EHIKES will be
 * set to the published indxNo. The user is then directed to either the
 * hike page editor or to the cluster page editor accordingly. When edits
 * are complete the admin can then publish the EHIKE which updates the
 * HIKES and associated tables with the newly edited data. Note that for
 * (non-cluster) pages, all json data will be eliminated first then
 * replaced with the edit json data. For this reason, this script will
 * track the newly created edit json files in the file 'pub_xfrs.txt' for
 * comparison during publication so that the admin can be advised of the
 * necessary actions to take on git when it is updated on localhost.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$getHike = filter_input(INPUT_GET, 'hikeNo'); // PUBLISHED indxNo
$cluspg  = isset($_GET['clus']) && $_GET['clus'] === 'y' ? true : false;
$userid  = $_SESSION['userid'];
$xfrs    = "../admin/pub_xfrs.txt";
$json_xfrs = [];

/**
 * Since `EHIKES` depends on `cname` to identify association w/new or
 * published cluster groups, it must be specified to ensure proper
 * transfer. `HIKES` no longer supports `cname`.
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
    "`expo`,`gpx`,`lat`,`lng`,`bounds`,`preview`,`purl1`,`purl2`,`dirs`,`tips`," .
    "`info`,`last_hiked`)" .
    "SELECT `pgTitle`,?,?,`locale`,?,`logistics`,`miles`,`feet`,`diff`," .
    "`fac`,`wow`,`seasons`,`expo`,`gpx`,`lat`,`lng`,`bounds`,`preview`,`purl1`," .
    "`purl2`,`dirs`,`tips`,`info`,`last_hiked` " .
    "FROM `HIKES` WHERE `indxNo` = ?;";
$query = $pdo->prepare($xfrReq);
$query->execute([$userid, $getHike, $cname, $getHike]);

// Fetch the new hike no in EHIKES:
$indxReq = "SELECT `indxNo` FROM `EHIKES` ORDER BY `indxNo` DESC LIMIT 1;";
$indxq  = $pdo->query($indxReq);
$indxNo = $indxq->fetch(PDO::FETCH_NUM);
$hikeNo = $indxNo[0]; // EHIKES indxNo
$prefix = "P:" . $getHike . ";E:" . $hikeNo . ":";

if (!$cluspg) {
    if (file_exists($xfrs)) {
        $existing_xfrs = file_get_contents($xfrs);
        $json_xfrs = explode(",", $existing_xfrs);
    } else {
        $json_xfrs = [];
    }
    /**
     * Copy published HIKES `gpx` json files [currently referenced in EHIKES
     * `gpx` field] and convert them to equivalent EHIKES json files. Then
     * update the `gpx` field currently in EHIKES, as it still contains the
     * published HIKES `gpx` field data.
     * Function Notes: 
     * 1. getGpxArray() returns the `gpx` field entry as an associative php
     *    array of elements containing track data: 'main', 'add1', 'add2', 'add3',
     *    each track is associated with its corresponding data: gpx filename [key]
     *    and array of json filenames [value].
     * 2. getTrackFileNames() returns: an array of json file names [0],
     *    comma-separated string of file names [1], and the main gpx file name [2].
     */
    // establish arrays for EHIKES `gpx` array-of-json-filenames
    $main_val = [];
    $add1_val = [];
    $add2_val = [];
    $add3_val = [];
    $new_json_names = [];
    $old_gpx_array = getGpxArray($pdo, $hikeNo, 'edit');
    foreach ($old_gpx_array as $type => $value) {
        switch ($type) {
        case "main" :
            $ftype = 'mn';
            break;
        case "add1" :
            $ftype = 'a1';
            break;
        case "add2" :
            $ftype = 'a2';
            break;
        case "add3" :
            $ftype = 'a3';
        }
        if (!empty($value)) {
            $gpx_filename = array_keys($value)[0];
            $json_array = array_values($value)[0];
            foreach ($json_array as $json_file) {
                // $value is an array with gpx => array of json filenames
                $dash_loc  = strpos($json_file, "_");
                $extension = substr($json_file, $dash_loc);
                $new_name  = "e" . $ftype . $hikeNo . $extension;
                array_push($new_json_names, $prefix . $new_name);
                $to_loc   = "../json/" . $new_name;
                $from_loc = "../json/" . $json_file;
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
                case "a3":
                    array_push($add3_val, $new_name);
                }
            }
        }
    }
    // EHIKES `gpx` field entry:
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
    $old_gpx_array["add3"] = empty($add3_gpx) ? [] : array($add3_gpx => $add3_val);
    $new_gpx_array = json_encode($old_gpx_array);
    $updateGpxReq = "UPDATE `EHIKES` SET `gpx`=? WHERE `indxNo`=?;";
    $updateGpx = $pdo->prepare($updateGpxReq);
    $updateGpx->execute([$new_gpx_array, $hikeNo]);
    /*
     * Place GPSDATA into EGPSDATA
     * All GPX entries are json objects consisting of a single file name as 'key',
     * and an array of json file names as 'value'.
     * NOTE: gpx/json for urls fixed later...
     */
    $gpsDatReq = "INSERT INTO `EGPSDAT` (`indxNo`,`label`,`url`,`clickText`) " .
        "SELECT ?,`label`,`url`,`clickText` FROM `GPSDAT` WHERE " .
        "`indxNo` = ?;";
    $gpsq = $pdo->prepare($gpsDatReq);
    $gpsq->execute([$hikeNo, $getHike]);

    /**
     * EGPSDATA needs to update the gpx file pointer & json file[s];
     * Each 'url' contains json encoded data for 1 gpx file
     * w/corresponding json file[s]
     * Note": getGPSurlData() returns: the original gpx file name [0]; 
     * its array of json filenames [1]
     */
    $egpsDataReq = "SELECT * FROM `EGPSDAT` WHERE `label` LIKE 'GPX%' AND " .
        "`indxNo`=?;";
    $egpsData = $pdo->prepare($egpsDataReq);
    $egpsData->execute([$hikeNo]);
    $allGps = $egpsData->fetchAll(PDO::FETCH_ASSOC);
    $new_json = [];
    foreach ($allGps as $gps) {
        $new_gpsdat = [];
        $egpsUrlField = getGPSurlData($gps['url']);
        foreach ($egpsUrlField[1] as $pjson) {
            $dash_loc  = strpos($pjson, "_");
            $extension = substr($pjson, $dash_loc);
            $new_name  = "egp" . $hikeNo . $extension;
            $to_loc   = "../json/" . $new_name;
            $from_loc = "../json/" . $pjson;
            if (!copy($from_loc, $to_loc)) {
                throw new Exception("Could not relocate {$pjson}");
            }
            array_push($new_gpsdat, $new_name);
            array_push($new_json, $prefix . $new_name);
        }
        $newEntry = [$egpsUrlField[0] => $new_gpsdat];
        $new_gps_array = json_encode($newEntry);
        $updateGpsReq = "UPDATE `EGPSDAT` SET `url`=? WHERE `datId`=?;";
        $updateGps = $pdo->prepare($updateGpsReq);
        $updateGps->execute([$new_gps_array, $gps['datId']]);
    }
    /**
     * UPDATE EWAYPTS
     */
    $ewptsReq = "INSERT INTO `EWAYPTS` (`indxNo`,`type`,`name`,`lat`," .
        "`lng`,`sym`) SELECT ?,`type`,`name`,`lat`,`lng`,`sym` " .
        "FROM `WAYPTS` WHERE `indxNo`=?;";
    $ewpts = $pdo->prepare($ewptsReq);
    $ewpts->execute([$hikeNo, $getHike]);
        
    /*
     * PLace TSV data into ETSV
     */
    $xfrTsvReq = "INSERT INTO `ETSV` (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) SELECT ?,folder,title," .
        "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr,org FROM " .
        "`TSV` WHERE `indxNo` = ?;";
    $tsvq = $pdo->prepare($xfrTsvReq);
    $tsvq->execute([$hikeNo, $getHike]);

}
/*
 * Place REFS data into EREFS
 */
$refDatReq = "INSERT INTO `EREFS` (indxNo,rtype,rit1,rit2) SELECT " .
    "?,rtype,rit1,rit2 FROM `REFS` WHERE `indxNo` = ?;";
$refq = $pdo->prepare($refDatReq);
$refq->execute([$hikeNo, $getHike]);
/**
 * Save info regarding transferred json files that are now ejson files:
 */
if (!empty($json_xfrs)) {
    $json_xfrs = array_merge($json_xfrs, $new_json_names, $new_json);
    $json_xfred = implode(",", $json_xfrs);
    file_put_contents($xfrs, $json_xfred);
}

// Redirect to appropriate editor
$redirect = $cluspg ?
    "editClusterPage.php?hikeNo={$hikeNo}" : "editDB.php?tab=1&hikeNo={$hikeNo}";
header("Location: {$redirect}");
