<?php
/**
 * This script is invoked when a user an ajax error was encountered
 * in production mode. The admin is notified of the error and its code.
 * Because of the number of ajax calls, the message construction has
 * many optios.
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

$errmsg = filter_input(INPUT_POST, 'err'); // always present
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'no user';

$message = "User " . $username . " encountered an ajax error: " . 
    PHP_EOL . $errmsg . PHP_EOL;
$subject = "User ajax error";
$mail->isHTML(true);
$mail->setFrom('admin@nmhikes.com', 'Do not reply');
$mail->addAddress(ADMIN, 'Admin');
$mail->Subject = $subject;
$mail->Body = $message;
@$mail->send();
