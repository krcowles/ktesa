<?php
/**
 * Any hike tips or information data is saved from tab3 ('Descriptive Text')
 * with this script.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('post');

$hikeNo = filter_input(INPUT_POST, 'dno');
$htips = filter_input(INPUT_POST, 'tips');
$hinfo = filter_input(INPUT_POST, 'hinfo');
$vals = [];
if ($htips === '') {
    $valstr = 'tips = NULL, ';
} else {
    $valstr = 'tips = ?, ';
    // A space is needed before the Trail Tips logo...
    $vals[0] = $hitps[0] === ' ' ? $htips : ' ' . $htips;
}
if ($hinfo === '') {
    $valstr .= "info = NULL ";
} else {
    $valstr .= 'info = ? ';
    array_push($vals, $hinfo);
}
array_push($vals, $hikeNo);
$updtDescReq = "UPDATE EHIKES SET " . $valstr . "WHERE indxNo = ?;";
$descq = $pdo->prepare($updtDescReq);
$descq->execute($vals);
$redirect = "editDB.php?tab=3&hikeNo={$hikeNo}";
header("Location: {$redirect}");
