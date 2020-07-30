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

$msg = <<<LNK
<h2>Your request to reset your nmhikes.com password was received</h2>
<p><a href="https://nmhikes.com/accounts/renew.php?user=
LNK;

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    echo "The email {$email} is not valid";
} else {
    $register_req = "SELECT `username` FROM `USERS` WHERE `email` = :email;";
    $register = $pdo->prepare($register_req);
    $register->execute(["email" => $email]);
    $status = $register->fetch(PDO::FETCH_ASSOC);
    if ($status === false) {
        echo "Your email {$email} was not located in our database";
    } else { 
        $name = $status['username'];
        $to  = $email;
        $subject = 'Password Reset for NM Hikes';
        $message = $msg . $name . '">Click here to reset</a></p>';
        $headers = 'From: The nmhikes.com admin team' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        // Mail it
        mail($to, $subject, $message, $headers);
        echo "OK";
    }

}
