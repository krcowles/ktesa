<?php
/**
 * This script will look for incomplete data that would prevent the EHIKE
 * from being published. The script is used to preview a hike when it is 
 * being submitted for publication.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');

$hikeNo    = filter_input(INPUT_GET, 'hikeNo');
$groupPage = filter_input(INPUT_GET, 'cluster');
$clusterPage = $groupPage === 'Y' ? true : false;
$msgout      = '';

$query = "SELECT * FROM EHIKES WHERE indxNo = :hikeNo;";
$ehk = $pdo->prepare($query);
$ehk->execute(["hikeNo" => $hikeNo]);
$ehike = $ehk->fetch(PDO::FETCH_ASSOC);
if ($ehike === false) {
    throw new Exception("EHIKE data not found for indxNo {$hikeNo}");
}
$clusPgField = $ehike['pgTitle']; // used if publishing a new cluster page
$cname = $ehike['cname'];
$proposed = strpos($ehike['pgTitle'], '[Proposed]') === false ? false : true;
/**
 * Validate key data: some data must not be 'empty' in order to prevent
 * viewing problems; see comments below
 */
if ($clusterPage) {
    // Data omission here prevents displaying cluster on main map (mapJsData.php)
    $cpClustersReq = "SELECT `lat`,`lng` FROM `CLUSTERS` WHERE `group`=?;";
    $clusterData = $pdo->prepare($cpClustersReq);
    $clusterData->execute([$clusPgField]);
    $cdat = $clusterData->fetch(PDO::FETCH_ASSOC);
    if (is_null($cdat['lat']) || is_null($cdat['lng'])) {
        $msgout 
            .= "<p class='brown'>Missing lat or lng for Cluster {$clusPgField}</p>";
    }
} else {
    // Data omission here will cause issues in mapJsData.php on home page,
    // or other problems (including execution errors)
    if (!empty($cname)) {
        // if a group hike page, validate data in CLUSTERS (whether or not published)
        $clusterDataReq = "SELECT * FROM `CLUSTERS` WHERE `group`=?;";
        $clusterData = $pdo->prepare($clusterDataReq);
        $clusterData->execute([$cname]);
        $clusdat = $clusterData->fetch(PDO::FETCH_ASSOC);
        if ($clusdat === false) {
            throw new Exception("Could not find {$cname} in CLUSTERS");
        }
        if (empty($clusdat['lat']) || empty($clusdat['lng'])) {
            $msgout .= '<p class="brown">Missing lat or lng data in CLUSTERS</p>';
        }
    }
    if (empty($ehike['miles']) || empty($ehike['feet'])|| empty($ehike['diff'])) {
        $msgout .= '<p class="brown">Missing miles, feet, or difficulty data</p>';
    }
    if (empty($ehike['lat']) || empty($ehike['lng'])) {
        $msgout .= '<p class="brown">Missing lat or lng data</p>';
    }
    if (!$proposed) {
        if (empty($ehike['last_hiked'])) {
            $msgout .= '<p class="brown">Missing last_hiked data (from photos)</p>';
        }
        if (empty($ehike['preview'])) {
            $msgout .= '<p class="brown">Missing preview/thumb data</p>';
        }
        if (empty($ehike['bounds'])) {
            $msgout .= '<p class="brown">Missing hike bounds box</p>';
        }
    }
}
if (empty($ehike['dirs'])) {
    $msgout .= '<p class="brown">Missing directions link</p>';
}
if ($msgout == '') {
    echo "OK";
} else {
    echo $msgout;
}
