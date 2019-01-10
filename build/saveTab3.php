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
$uid = filter_input(INPUT_POST, 'did');
$htips = filter_input(INPUT_POST, 'tips');
$hinfo = filter_input(INPUT_POST, 'hinfo');
$updtDescReq = "UPDATE EHIKES SET tips = ?, info = ? WHERE indxNo = ?;";
$descq = $pdo->prepare($updtDescReq);
$descq->execute([$htips, $hinfo, $hikeNo]);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}&tab=3";
header("Location: {$redirect}");
