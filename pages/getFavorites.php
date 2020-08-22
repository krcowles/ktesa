<?php
/**
 * This script is ajaxed to retrieve current favoites list
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

if (isset($_SESSION['userid'])) {
    $favreq = "SELECT `hikeNo` FROM `FAVORITES` WHERE `userid` = :userid;";
    $usrfavs = $pdo->prepare($favreq);
    $usrfavs->execute(["userid" => $_SESSION['userid']]);
    $favs = $usrfavs->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($favs);
} else {
    echo '';
}
