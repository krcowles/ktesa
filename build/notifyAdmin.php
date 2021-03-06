<?php
/**
 * This script is invoked when a user wishes to publish a hike-in-edit.
 * The admin will receive an email.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');

$hikeNo = filter_input(INPUT_GET, 'hikeNo');
$ehikeReq = "SELECT `usrid` FROM `EHIKES` WHERE `indxNo` = ?;";
$ehike = $pdo->prepare($ehikeReq);
$ehike->execute([$hikeNo]);
$hikeid = $ehike->fetch(PDO::FETCH_ASSOC);
$user = $hikeid['usrid'];

$subject = "Publish hike {$hikeNo}";
$message = "<h2>User {$user} requests publication of hike no {$hikeNo}</h2>";
$to = "krcowles29@gmail.com";
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
// Mail it
mail($to, $subject, $message, $headers);
echo "OK";
