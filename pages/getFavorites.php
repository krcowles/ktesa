<?php
/**
 * This script is ajaxed to retrieve current favoites list
 * PHP Version 7.1
 * 
 * @package Home
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$userid = filter_input(INPUT_POST, 'userid');

$favreq = "SELECT `hikeNo` FROM `FAVORITES` WHERE `userid` = :userid;";
$usrfavs = $pdo->prepare($favreq);
$usrfavs->execute(["userid" => $userid]);
$favs = $usrfavs->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($favs);
