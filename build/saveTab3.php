<?php
/**
 * Any hike tips or information data is saved from tab3 ('Descriptive Text')
 * with this script.
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
session_start();
$_SESSION['activeTab'] = 3;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'dno');
$uid = filter_input(INPUT_POST, 'did');
$htips = filter_input(INPUT_POST, 'tips');
$etips = mysqli_real_escape_string($link, $htips);
$hinfo = filter_input(INPUT_POST, 'hinfo');
if ($hinfo == '') {
    $einfo = '';
} else {
    $einfo = mysqli_real_escape_string($link, $hinfo);
}
$updtDescReq = "UPDATE EHIKES SET tips = '{$etips}',info = '{$einfo}' WHERE " .
    "indxNo = {$hikeNo};";
$updtDesc = mysqli_query($link, $updtDescReq) or die(
    "saveTab3.php: Failed to update EHIKES with new tips/info " .
    "for hike {$hikeNo}: " . mysqli_error($link)
);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
