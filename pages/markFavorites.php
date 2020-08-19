<?php
/**
 * This script is ajaxed to make changes in the FAVORITES table when
 * a user clicks or unclicks a favorites icon.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$action = filter_input(INPUT_POST, 'action');
$hikeno = filter_input(INPUT_POST, 'no');
$userid = $_SESSION['userid'];

if ($action === 'add') {
    $addfav = "INSERT INTO `FAVORITES` (`userid`, `hikeNo`) VALUES " .
        "(:uid, :hike);";
    $add = $pdo->prepare($addfav);
    try {
        $add->execute(["uid" => $userid, "hike" => $hikeno]);
    }
    catch (Exception $e) {
        if ($e->getCode() <> 23000) { // Ignore duplicate detect exception
            throw $e;  // Send all others to standard exception handler
        }
    }
} else {
    $remfav = "DELETE FROM `FAVORITES` WHERE `userid` = :uid " .
        "AND `hikeNo` = :hike;";
    $rem = $pdo->prepare($remfav);
    $rem->execute(["uid" => $userid, "hike" => $hikeno]);
}
