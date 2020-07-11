<?php
/**
 * This script converts the 'old' HIKES table to the new
 * clustering model.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

// All VC's now have marker = 'Cluster'
$vc_req = "SELECT `indxNo` FROM `HIKES` WHERE `marker`='Visitor Ctr';";
$vcs = $pdo->query($vc_req)->fetchAll(PDO::FETCH_COLUMN);
foreach ($vcs as $vc) {
    $clus = "UPDATE `HIKES` SET `marker`='Cluster' WHERE `indxNo` = ?;";
    $update = $pdo->prepare($clus);
    $update->execute([$vc]);
}
// Each of the 'At VC' hikes have already been placed in CLUSHIKES
$atvc_req = "SELECT `indxNo` FROM `HIKES` WHERE `marker`='At VC';";
$atvcs = $pdo->query($atvc_req)->fetchAll(PDO::FETCH_COLUMN);
foreach ($atvcs as $atvc) {
    $clus = "UPDATE `HIKES` SET `marker`='Cluster' WHERE `indxNo` = ?;";
    $update = $pdo->prepare($clus);
    $update->execute([$atvc]);
}
header("Location: admintools.php");
