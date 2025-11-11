<?php
/**
 * Manage MEMBER_PREFS: saved_maps
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";
$userid = validSession('manageMapNames.php');

$action = filter_input(INPUT_GET, 'act');
$map    = filter_input(INPUT_GET, 'map');

$namesReq = "SELECT `saved_maps` FROM `MEMBER_PREFS` WHERE `userid`=?;";
$getNames = $pdo->prepare($namesReq);
$getNames->execute([$userid]);
$mapnames = $getNames->fetch(PDO::FETCH_ASSOC);
$usermaps = explode(",", $mapnames['saved_maps']);
if (empty($usermaps[0])) { // entry may be "", cannot be NULL
    $usermaps = [];
}
if ($action === 'add') {
    // first make sure it's not a duplicate map name
    if (in_array($map, $usermaps)) {
        echo "DUP";
        exit;
    } else {
        array_push($usermaps, $map);
    }
} elseif ($action === 'delete') {
    if (!in_array($map, $usermaps)) {
        echo "Map not found";
        exit;
    }
    $indx = array_search($map, $usermaps);
    array_splice($usermaps, $indx, 1);
} else {
    echo "Invalid request";
    exit;
}
if (count($usermaps) > 0) {
    $newnames = implode(",", $usermaps);
} else {
    $newnames = '';
}
$updateReq = "UPDATE `MEMBER_PREFS` SET `saved_maps`=? WHERE `userid`=?;";
$update = $pdo->prepare($updateReq);
$update->execute([$newnames, $userid]);
echo "UPDATED";
