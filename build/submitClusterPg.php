<?php
/**
 * This script enters a new cluster page into EHIKES with minimum
 * data. The user is redirected to the cluster page editor.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
require "../php/global_boot.php";

$page       = filter_input(INPUT_GET, 'choice');
$newClusGrp = isset($_GET['new']) && $_GET['new'] == 'y' ? true : false;

// Save basic data for new page in EHIKES (NOTE: do not save `cname`)
$newClusPgReq = "INSERT INTO `EHIKES` (`pgTitle`,`usrid`,`stat`) VALUES (?,?,'0');";
$newClusPg = $pdo->prepare($newClusPgReq);
$newClusPg->execute([$page, $_SESSION['userid']]);
// get new indxNo
$indxNoReq = "SELECT `indxNo` FROM `EHIKES` WHERE `pgTitle`=?;";
$indxNoRecord = $pdo->prepare($indxNoReq);
$indxNoRecord->execute([$page]);
$indxNo = $indxNoRecord->fetch(PDO::FETCH_ASSOC);

/**
 * Cluster pages that are already published have 'page' no's assigned in the
 * CLUSTERS table where the 'page' field is the HIKES 'indxNo' of the Cluster
 * page. For NEW Cluster pages, a temp. 'page' no is assigned where the 'page'
 * field = -1 * indxNo in EHIKES. This allows the code to identify the EHIKES
 * page no corresponding to the new Cluster page and display properly in the
 * hikePageTemplate.php script. At the same time, the negative number will prevent
 * the map code from forming a link to the new "in-edit" Cluster Page (until
 * published). In the case of PUBLISHED Cluster pages with a copy in-edit,
 * the hikePageTemplate.php will continue to show the correct published
 * version of the page, not the in-edit version. 
 */
$pageNo = -1 * (int) $indxNo['indxNo'];
if ($newClusGrp) {
    $addGroupReq = "INSERT INTO `CLUSTERS` (`group`,`pub`,`page`) " .
        "VALUES (?, 'N', ?);";
    $addGroup = $pdo->prepare($addGroupReq);
    $addGroup->execute([$page, $pageNo]);
} else { // group already exists...
    $newClusPgReq = "UPDATE `CLUSTERS` SET `page`=? WHERE `group`=?;";
    $newClusPg = $pdo->prepare($newClusPgReq);
    $newClusPg->execute([$pageNo, $page]);
}

$redir = "editClusterPage.php?hikeNo={$indxNo['indxNo']}";
header("Location: {$redir}");
