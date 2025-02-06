<?php
/**
 * This script sends the user a link to reset his/her password, or, if
 * a new user, set the password.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "gmail.php";
verifyAccess('ajax');

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    echo "The email {$email} is not valid";
    exit;
}
$type = filter_input(INPUT_POST, 'form');
if ($type === 'own') { // someone is requesting ownership :-)
    $from = $email;
    $from_note = "";
    $to = ADMIN;
    $name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Visitor';
    $replyTo = $from;
    $replyName = $name;
    $subject = 'Site Ownership';
    $message = filter_input(INPUT_POST, 'message');
} else {
    $usertype = isset($_POST['reg']) ? "&reg=y&code=" : "&code=";

    $usermsg = "<h2>Do not reply to this message</h2>";
    if ($type === 'reg') {
        $usermsg .= "<h3>Your registration request has been received.<br />" .
            "To complete your registration and sign in, your " .
            "one-time code is ";
        $subj = "Registration for NM Hikes";
    } elseif ($type === 'chg') {
        $usermsg .= "<h3>Your request to change/reset your nmhikes.com password " .
            "was received.<br />" . "To reset your password, your one-time code is ";
        $subj = "Password Reset for NM Hikes";
    } else {
        echo "Bad form type submitted: " . $type;
        exit;
    }

    // Create the one-time code link for use in the email message
    $href = '<br /><a href="' . $thisSiteUrl .
        '/accounts/unifiedLogin.php?form=renew' . $usertype;
    $register_req = "SELECT * FROM `USERS` WHERE `email` = :email;";
    $register = $pdo->prepare($register_req);
    $register->execute(["email" => $email]);
    $status = $register->fetch(PDO::FETCH_ASSOC);
    if ($status === false || !isset($status['userid'])) {
        echo "Your email '{$email}' was not located in our database";
        exit;
    }
    $from = "admin@nmhikes.com";
    $from_note = "Do not reply";
    $to = $email;
    $name = $status['username'];
    $replyTo = $from;
    $replyName = 'Admin';
    // there will be no pending session, so user id is needed
    $id = $status['userid'];
    $tmp_pass = bin2hex(random_bytes(5)); // 10 hex characters
    $hash = password_hash($tmp_pass, PASSWORD_DEFAULT);
    $savecodeReq = "UPDATE `USERS` SET `passwd` = ? WHERE `username` = ?;";
    $savecode = $pdo->prepare($savecodeReq);
    $savecode->execute([$hash, $name]);
    $subject = $subj;
    $message = $usermsg . $tmp_pass . "<br />Your username is " 
        . $name . "</p></h3>" . $href . $tmp_pass . "&ix=" . $id .
        '">Click here to complete</a>';
}
$mail->setFrom($from, $from_note);
$mail->addAddress($to, $name);
$mail->addReplyTo($replyTo, $replyName);
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->Body = $message;
@$mail->send();
echo "OK";
