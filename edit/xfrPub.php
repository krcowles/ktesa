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
 * CAVEAT: For now, a hike can only be associated with one cluster group...
 * PHP Version 7.4
 * 
 * @package Ktesa
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
 * Some hike pages will have associated CLUSHIKES, if in a cluster group:
 */ 
$cname = '';
if (!$cluspg) {
    $clusHikeStatReq = "SELECT `cluster` FROM `CLUSHIKES` WHERE " .
        "`indxNo`=? AND `pub`='Y';";
    $clusHikeStat = $pdo->prepare($clusHikeStatReq);
    $clusHikeStat->execute([$getHike]);
    // any cluster group(s) associated with this hike indxNo?
    $clusHike = $clusHikeStat->fetchAll(PDO::FETCH_ASSOC);
    if (count($clusHike) > 0) { // no multiple cluster group assigns yet...
        $clusid = $clusHike[0]['cluster']; 
        $getGroupNameReq = "SELECT `group` FROM `CLUSTERS` WHERE `clusid`=?;";
        $getGroupName = $pdo->prepare($getGroupNameReq);
        $getGroupName->execute([$clusid]);
        $groupName = $getGroupName->fetch(PDO::FETCH_ASSOC);
        $cname = $groupName['group'];
    }

    /*
    * Place HIKES data into EHIKES 
    * The new 'gpxlist' cannot be constructed for EHIKES until gpx files are
    * uploaded to the EMETA/EGPX db and have associated filenos identified
    */
    // Retrieve the gpxlist from the published hike
    $getFileListReq = "SELECT `gpxlist` FROM `HIKES` WHERE `indxNo`=?;";
    $getFileList = $pdo->prepare($getFileListReq);
    $getFileList->execute([$getHike]);
    $fileList = $getFileList->fetch(PDO::FETCH_NUM);
    $gpxlist = $fileList[0];
    $files = explode(",", $gpxlist);
    $ehikelist = [];
    // walk through the data transfer of gpx data for each file and save new fileno
    foreach ($files as $pubno) {
        // last fileno in EMETA will id the next fileno to be written
        $lastFnoReq = "SELECT `fileno` FROM `EMETA` ORDER BY `fileno` DESC LIMIT 1;";
        $lastFileno = $gdb->query($lastFnoReq)->fetch(PDO::FETCH_NUM);
        if ($lastFileno === false) {
            $nextno = 1;
        } else {
            $nextno = $lastFileno[0] + 1;
        }
        array_push($ehikelist, $nextno);
        xfrGpxData('xfr', $nextno, $pubno, $gdb);
    }
    $egpxlist = implode(",", $ehikelist);
} else {
    $egpxlist = '';
}
// now enter all data into EHIKES from HIKES whether Hike Page or Cluster Page
$xfrReq = "INSERT INTO `EHIKES` (`pgTitle`,`usrid`,`stat`,`locale`," .
    "`cname`,`logistics`,`miles`,`feet`,`diff`,`fac`,`wow`,`seasons`," .  
    "`expo`,`gpxlist`,`trk`,`lat`,`lng`,`preview`,`purl1`,`purl2`,`dirs`," .
    "`tips`,`info`,`last_hiked`,`dThresh`,`eThresh`,`maWin`)" .
    "SELECT `pgTitle`,:usrid,:stat,`locale`,:grp,`logistics`,`miles`,`feet`," .
    "`diff`,`fac`,`wow`,`seasons`,`expo`,:glst,`trk`,`lat`,`lng`,`preview`," .
    "`purl1`,`purl2`,`dirs`,`tips`,`info`,`last_hiked`,`dThresh`,`eThresh`," .
    "`maWin` FROM `HIKES` WHERE `indxNo` = :ino;";
$query = $pdo->prepare($xfrReq);
$query->execute(
    ["usrid" => $userid, "stat" =>$getHike, 
    "grp" => $cname, "glst" => $egpxlist, "ino" => $getHike]
);
// Fetch the new hike no in EHIKES:
$indxReq = "SELECT `indxNo` FROM `EHIKES` ORDER BY `indxNo` DESC LIMIT 1;";
$indxq = $pdo->query($indxReq);
$indxNo = $indxq->fetch(PDO::FETCH_NUM);
$hikeNo = $indxNo[0];

if (!$cluspg) {
    /**
     * PLace TSV data into ETSV
     */
    $xfrTsvReq = "INSERT INTO `ETSV` (`indxNo`,`folder`,`title`,`hpg`," .
        "`mpg`,`desc`,`lat`,`lng`,`thumb`,`alblnk`,`date`,`mid`,`imgHt`,".
        "`imgWd`,`iclr`,`org`) SELECT ?,`folder`,`title`,`hpg`,`mpg`,`desc`,".
        "`lat`,`lng`,`thumb`,`alblnk`,`date`,`mid`,`imgHt`,`imgWd`," .
        "`iclr`,`org` FROM `TSV` WHERE `indxNo` = ?;";
    $tsvq = $pdo->prepare($xfrTsvReq);
    $tsvq->execute([$hikeNo, $getHike]);
    /**
    * Place GPSDATA into EGPSDATA
    * As above, the filenos for EGPSDAT cannot be identified until the GPX
    * data has been uploaded.
    */
    $pubGPSreq = "SELECT `datId`,`fileno`,`label` FROM `GPSDAT` WHERE `indxNo`=?;";
    $pubGPS = $pdo->prepare($pubGPSreq);
    $pubGPS->execute([$getHike]);
    $gpsdatFiles = $pubGPS->fetchAll(PDO::FETCH_ASSOC);
    foreach ($gpsdatFiles as $gfile) {
        // id the new fileno for EGPSDAT
        $lastGnoReq = "SELECT `fileno` FROM `EMETA` ORDER BY `fileno` DESC LIMIT 1;";
        $lastGFileno = $gdb->query($lastGnoReq)->fetch(PDO::FETCH_NUM);
        if ($lastGFileno === false) {
            $newfno = 1;
        } else {
            $newfno = $lastGFileno[0] + 1;
        }
        // some GPSDAT entries may not be GPX files...
        $label = trim($gfile['label']);
        if ($label === 'GPX:' || $label === 'GPX') {  
            xfrGpxData('xfr', $newfno, $gfile['fileno'], $gdb);
            // now stash the $newfno into EGPSDAT
            $gpsDatReq = "INSERT INTO `EGPSDAT` (`indxNo`,`fileno`,`label`," .
                "`clickText`) SELECT ?,?,`label`,`clickText` FROM `GPSDAT` " .
                "WHERE `indxNo`=? AND `fileno`=?;";
            $gpsq = $pdo->prepare($gpsDatReq);
            $gpsq->execute([$hikeNo, $newfno, $getHike, $gfile['fileno']]); 
        } else {
            // non gpx file entry (kml or html [map])
            $nonGpxItem = "INSERT INTO `EGPSDAT` (`indxNo`,`label`,`clickText`) " .
                "SELECT ?,`label`,`clickText` FROM `GPSDAT` WHERE `datId`=?;";
            $nonGpx = $pdo->prepare($nonGpxItem);
            $nonGpx->execute([$hikeNo, $gfile['datId']]);
        }
    } 
}
/*
 * Place REFS data into EREFS
 */
$refDatReq = "INSERT INTO `EREFS` (`indxNo`,`rtype`,`rit1`,`rit2`) SELECT " .
    "?,`rtype`,`rit1`,`rit2` FROM `REFS` WHERE `indxNo` = ?;";
$refq = $pdo->prepare($refDatReq);
$refq->execute([$hikeNo, $getHike]);

// Redirect to appropriate editor
$redirect = $cluspg ?
    "editClusterPage.php?hikeNo={$hikeNo}" : "editDB.php?tab=1&hikeNo={$hikeNo}";
header("Location: {$redirect}");
