<?php
/**
 * This script notifies the admin concerning issues
 * encountered during legacy.js execution on mobile
 * platform.
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

$member = filter_input(INPUT_POST, 'member');
$issue  = filter_input(INPUT_POST, 'issue');
$msg = <<<EOM
<div>
Userid $member encountered an issue on
mobile device when executing legacy.js:
$issue
</div>
EOM;
$message = $msg;
$subject = "Member Issue";
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
