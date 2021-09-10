<?php
/**
 * Extract latest date from photos and write to db
 * * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$getHikesReq = "SELECT `indxNo`,`pgTitle` FROM `HIKES`;";
$allHikes = $pdo->query($getHikesReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$hikes = [];
foreach ($allHikes as $indx => $hike) {
    if (strpos($hike, 'Group') === false && strpos($hike, '[Proposed]') === false) {
        array_push($hikes, $indx);
    } 
}
$test = 1;
foreach ($hikes as $hike) {
    $getPixReq
        = "SELECT `date` FROM `TSV` WHERE `indxNo`=? ORDER BY date DESC LIMIT 1;";
    $photoDates = $pdo->prepare($getPixReq);
    $photoDates->execute([$hike]);
    $latest = $photoDates->fetch(PDO::FETCH_ASSOC);
    $lastHiked = substr($latest['date'], 0, 10);
    $writeDateReq = "UPDATE `HIKES` SET `last_hiked` = '{$lastHiked}' WHERE " .
        "`indxNo` = {$hike};";
    $writeDate = $pdo->query($writeDateReq);
    $test++;
}
// the 1st 6 entries in CLUSTERS are the group hikes w/photos; 
for ($j=1; $j<=6; $j++) {
    $getGroupsReq = "SELECT `indxNo` FROM `CLUSHIKES` WHERE `cluster` = {$j};";
    $groupHikes = $pdo->query($getGroupsReq)->fetchAll(PDO::FETCH_COLUMN);
    $groupDates = [];
    foreach ($groupHikes as $hike) {
        $getPixReq = "SELECT `date` FROM `TSV` WHERE `indxNo`=? ORDER BY date " .
                "DESC LIMIT 1;";
        $photoDates = $pdo->prepare($getPixReq);
        $photoDates->execute([$hike]);
        $latest = $photoDates->fetch(PDO::FETCH_ASSOC);
        $lastHiked = substr($latest['date'], 0, 10);
        array_push($groupDates, $lastHiked);
    }
    rsort($groupDates);
    $mostRecent = $groupDates[0];
    $lastHiked = substr($mostRecent, 0, 10);
    $writeDateReq = "UPDATE `HIKES` SET `last_hiked` = '{$lastHiked}' WHERE " .
        "`indxNo` = {$j};";
    $writeDate = $pdo->query($writeDateReq);
}
