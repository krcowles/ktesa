<?php
/**
 * This script is invoked when a user routine encounters an ajax error
 * in production mode. The admin is notified of the error and its code.
 * Because of the number of ajax calls, the message construction has
 * many options.
 * PHP Version 8.3.9
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
// 'From' must match the SMTP-authenticated account (ADMIN) for alignment -
// same class of bug as resetMail.php. Since this script IS the safety net
// that's supposed to tell you when things break, a silent failure here
// meant you had no way of knowing resetMail.php was failing either.
$mail->setFrom(ADMIN, 'NM Hikes Error Reporter');
$mail->addAddress(ADMIN, 'Admin');
$mail->Subject = $subject;
$mail->Body = $message;
if (!$mail->send()) {
    error_log("ajaxError.php: failed to send admin notification: " . $mail->ErrorInfo);
}
