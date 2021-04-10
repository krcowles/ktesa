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

$getHike = filter_input(INPUT_GET, 'hikeNo'); // published indxNo
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
 * Place HIKES data into EHIKES
 */

$xfrReq = "INSERT INTO `EHIKES` (`pgTitle`,`usrid`,`stat`,`locale`," .
    "`cname`,`logistics`,`miles`,`feet`,`diff`,`fac`,`wow`,`seasons`," .  
    "`expo`,`gpx`,`trk`,`lat`,`lng`,`purl1`,`purl2`,`dirs`,`tips`," .
    "`info`,`dThresh`,`eThresh`,`maWin`)" .
    "SELECT `pgTitle`,?,?,`locale`,?,`logistics`,`miles`,`feet`,`diff`," .
    "`fac`,`wow`,`seasons`,`expo`,`gpx`,`trk`,`lat`,`lng`,`purl1`," .
    "`purl2`,`dirs`,`tips`,`info`,`dThresh`,`eThresh`,`maWin` " .
    "FROM `HIKES` WHERE `indxNo` = ?;";
$query = $pdo->prepare($xfrReq);
$query->execute([$userid, $getHike, $cname, $getHike]);
// Fetch the new hike no in EHIKES:
$indxReq = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1;";
$indxq = $pdo->query($indxReq);
$indxNo = $indxq->fetch(PDO::FETCH_NUM);
$hikeNo = $indxNo[0];

/*
 * PLace TSV data into ETSV
 */
if (!$cluspg) {
    $xfrTsvReq = "INSERT INTO `ETSV` (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) SELECT ?,folder,title," .
        "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr,org FROM " .
        "`TSV` WHERE `indxNo` = ?;";
    $tsvq = $pdo->prepare($xfrTsvReq);
    $tsvq->execute([$hikeNo, $getHike]);
    /*
    * Place GPSDATA into EGPSDATA
    */
    $gpsDatReq = "INSERT INTO `EGPSDAT` (indxNo,datType,label,`url`,clickText) " .
        "SELECT ?,datType,label,`url`,clickText FROM `GPSDAT` WHERE " .
        "`indxNo` = ?;";
    $gpsq = $pdo->prepare($gpsDatReq);
    $gpsq->execute([$hikeNo, $getHike]);
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
