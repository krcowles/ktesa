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
require "../php/global_boot.php";
verifyAccess('ajax');
require "gmail.php";


$type = filter_input(INPUT_POST, 'form');
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
    echo "Bad form type submitted: " . $form;
    exit;
}

// Create the one-time code link for use in the email message
$href = '<br /><a href="' . $thisSiteUrl .
    '/accounts/unifiedLogin.php?form=renew' . $usertype;


$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    echo "The email {$email} is not valid";
    exit;
} else {
    $register_req = "SELECT * FROM `USERS` WHERE `email` = :email;";
    $register = $pdo->prepare($register_req);
    $register->execute(["email" => $email]);
    $status = $register->fetch(PDO::FETCH_ASSOC);
    if ($status === false || !isset($status['userid'])) {
        echo "Your email '{$email}' was not located in our database";
    } else { 
        $name = $status['username'];
        // there will be no pending session, so user id is needed
        $id = $status['userid'];
        $tmp_pass = bin2hex(random_bytes(5)); // 10 hex characters
        $hash = password_hash($tmp_pass, PASSWORD_DEFAULT);
        $savecodeReq = "UPDATE `USERS` SET `passwd` = ? WHERE `username` = ?;";
        $savecode = $pdo->prepare($savecodeReq);
        $savecode->execute([$hash, $name]);
        $to  = $email;
        $subject = $subj;
        $message = $usermsg . $tmp_pass . "</h3><p>Your username is " 
            . $name . "</p>" . $href . $tmp_pass . "&ix=" . $id .
            '">Click here to complete</a>';
        $mail->isHTML(true);
        $mail->setFrom('webmaster@nmhikes.com', 'Do not reply');
        $mail->addAddress($email, 'nmhikes.com User');
        $mail->Subject = $subject;
        $mail->Body = $message;
        @$mail->send();
        /**
         * Note: can't send on localhost:25
         */
        /*$email = (new Email())
            ->from('admin@nmhikes.com')
            ->to($to)
            ->subject($subject)
            ->html($message);
        $mailer->send($email);    
        */
        echo "OK";
    }
}
