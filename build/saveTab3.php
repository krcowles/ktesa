<?php
/**
 * Any hike tips or information data is saved from tab3 ('Descriptive Text')
 * with this script.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$hikeNo = filter_input(INPUT_POST, 'dno');
$usr = filter_input(INPUT_POST, 'did');
$htips = filter_input(INPUT_POST, 'tips');
$hinfo = filter_input(INPUT_POST, 'hinfo');
$vals = [];
if ($htips === '') {
    $valstr = 'tips = NULL, ';
} else {
    $valstr = 'tips = ?, ';
    $vals[0] = $htips;
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
$redirect = "editDB.php?hikeNo={$hikeNo}&usr={$usr}&tab=3";
header("Location: {$redirect}");
