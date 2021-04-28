<?php
/**
 * This script sends the user a link to reset his/her password
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$type = filter_input(INPUT_POST, 'form');

$usermsg = "<h2>Do not reply to this message</h2>";
if ($type === 'reg') {
    $usermsg .= "<h3>Your registration request has been received<br />";
    $userclose = "to complete your registration and sign in</h3>";
    $subj = "Registration for NM Hikes";
} elseif ($type === 'req') {
    $usermsg .= "<h3>Your request to change/reset your nmhikes.com password " .
        "was received<br />";
    $userclose = "to reset your password</h3>";
    $subj = "Password Reset for NM Hikes";

}
$usermsg .= "Your one-time password is: ";

// Create the one-time code link for use in the email message
$href = '<br /><br /><a href="' . $thisSiteUrl .
    '/accounts/unifiedLogin.php?form=renew&code=';


$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    echo "The email {$email} is not valid";
    exit;
} else {
    $register_req = "SELECT * FROM `USERS` WHERE `email` = :email;";
    $register = $pdo->prepare($register_req);
    $register->execute(["email" => $email]);
    $status = $register->fetch(PDO::FETCH_ASSOC);
    if ($status === false) {
        echo "Your email '{$email}' was not located in our database";
    } else { 
        $name = $status['username'];
        $tmp_pass = bin2hex(random_bytes(5)); // 10 hex characters
        $hash = password_hash($tmp_pass, PASSWORD_DEFAULT);
        $savecodeReq = "UPDATE `USERS` SET `passwd` = ? WHERE `username` = ?;";
        $savecode = $pdo->prepare($savecodeReq);
        $savecode->execute([$hash, $name]);
        $to  = $email;
        $subject = $subj;
        $message = $usermsg . $tmp_pass . $href . $tmp_pass .
            '">Click here </a>' . $userclose;
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        // Mail it
        mail($to, $subject, $message, $headers);
        echo "OK";
    }
}
