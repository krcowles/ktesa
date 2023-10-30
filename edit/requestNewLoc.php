<?php
/**
 * This script is invoked when a user adds a new location for a hike on Tab1.
 * It will be necessary for the admin to add GPS data for the location in
 * order to publish the hike, and the 'localeBox.html' has already been
 * updated in saveTab1.php. This script will send an email to the admin to take
 * the necessary states to also update 'areas.json'.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
// session_start() and require "global_boot.php" have already been invoked
// in saveTab1.php

$subject = "Update Areas.json";
$message = "<h2>User {$_SESSION['userid']}: {$_SESSION['username']} " .
    "requests the addition of a location: " . $newloc . " in " .
    $basic_data['region'] . "</h2>";

$mail->isHTML(true);
$mail->setFrom('admin@nmhikes.com', 'Do not reply');
$mail->addAddress(ADMIN, 'Admin');
$mail->Subject = $subject;
$mail->Body = $message;
@$mail->send();
// Mail it

