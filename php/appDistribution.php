<?php
/**
 * This script notifies the admin to add a member to the app
 * distribution list.
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

$member_email = filter_input(INPUT_POST, 'email'); // "none" if android
$phone_os     = filter_input(INPUT_POST, 'phone');

$userDataReq
    = "SELECT `first_name`,`last_name`,`email` FROM `USERS` WHERE `userid`=?;";
$userData = $pdo->prepare($userDataReq);
$userData->execute([$_SESSION['userid']]);
$userSubset = $userData->fetch(PDO::FETCH_ASSOC);
$first = $userSubset['first_name'];
$last  = $userSubset['last_name'];
$member_email = $member_email !== 'none' ?: $userSubset['email'];

$msg = <<<EOM
<div>Add the following member to the $phone_os app distribution list: 
<ul>
<li>$first</li>
<li>$last</li>
<li>$member_email</li>
</ul>
</div>
EOM;
$message = $msg;
$subject = "App Request";
$mail->isHTML(true);
// 'From' must match the SMTP-authenticated account (ADMIN) for alignment -
// same class of bug as resetMail.php. Since this script IS the safety net
// that's supposed to tell you when things break, a silent failure here
// meant you had no way of knowing resetMail.php was failing either.
$mail->setFrom(ADMIN, 'NM Hikes Admin');
$mail->addAddress(ADMIN, 'Admin');
$mail->Subject = $subject;
$mail->Body = $message;
if (!$mail->send()) {
    error_log(
        "ajaxError.php: failed to send admin notification: " . 
        $mail->ErrorInfo
    );
}
