<?php
/**
 * This script is ajaxed to make changes in the FAVORITES table when
 * a user clicks or unclicks a favorites icon.
 * PHP Version 7.1
 * 
 * @package Home
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$action = filter_input(INPUT_POST, 'action');
$uid    = filter_input(INPUT_POST, 'id');
$hikeno = filter_input(INPUT_POST, 'no');
if ($action === 'add') {
    $addfav = "INSERT INTO `FAVORITES` (`userid`, `hikeNo`) VALUES " .
        "(:uid, :hike);";
    $add = $pdo->prepare($addfav);
    try {
        $add->execute(["uid" => $uid, "hike" => $hikeno]);
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
    $rem->execute(["uid" => $uid, "hike" => $hikeno]);
}
