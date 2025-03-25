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
require "../accounts/gmail.php";
verifyAccess('ajax');

$hikeNo   = filter_input(INPUT_GET, 'hikeNo');
$hikeName = filter_input(INPUT_GET, 'name');
// Notify EHIKES that a publish request is pending:
$pdo->query("UPDATE `EHIKES` SET `pubreq`='Y' WHERE `indxNo`={$hikeNo};");
// Get user's id
$ehikeReq = "SELECT `usrid` FROM `EHIKES` WHERE `indxNo` = ?;";
$ehike = $pdo->prepare($ehikeReq);
$ehike->execute([$hikeNo]);
$hikeid = $ehike->fetch(PDO::FETCH_ASSOC);
$user = $hikeid['usrid'];
$subject = "Publish hike {$hikeNo}";
$message = "<h2>User {$user} requests publication of hike: {$hikeName} " .
    " [hike number {$hikeNo}]</h2>";
// Mail it
$mail->isHTML(true);
$mail->setFrom('admin@nmhikes.com', 'Do not reply');
$mail->addAddress(ADMIN, 'Admin');
$mail->Subject = $subject;
$mail->Body = $message;
@$mail->send();
echo "OK";
