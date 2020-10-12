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

$site_loc = '';
// find this site's location for <a> element
$org_dir = getcwd();
chdir('..'); // start at current site's root
// repeat until $documentRoot is reached
$site_dir = getcwd();
while (basename($site_dir) !== basename($documentRoot)) {
    $site_loc = basename($site_dir) . '/' . $site_loc;
    chdir('..');
    $site_dir = getcwd();
}
chdir($org_dir);

$href = '<br /><br /><a href="https://nmhikes.com/' . $site_loc .
    'accounts/renew.php?code=';
$msg = <<<LNK
<h2>Do not reply to this message</h2>
<h3>Your request to reset your nmhikes.com password was received<br />
Your temporary password is: 
LNK;

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    echo "The email {$email} is not valid";
} else {
    $register_req = "SELECT * FROM `USERS` WHERE `email` = :email;";
    $register = $pdo->prepare($register_req);
    $register->execute(["email" => $email]);
    $status = $register->fetch(PDO::FETCH_ASSOC);
    if ($status === false) {
        echo "Your email {$email} was not located in our database";
    } else { 
        $name = $status['username'];
        $tmp_pass = bin2hex(random_bytes(5)); // 10 hex characters
        $hash = password_hash($tmp_pass, PASSWORD_DEFAULT);
        $savecodeReq = "UPDATE `USERS` SET `passwd` = ? WHERE `username` = ?;";
        $savecode = $pdo->prepare($savecodeReq);
        $savecode->execute([$hash, $name]);
        $to  = $email;
        $subject = 'Password Reset for NM Hikes';
        $message = $msg . $tmp_pass . $href . $tmp_pass .
            '">Click here to reset</a></h3>';
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        // Mail it
        mail($to, $subject, $message, $headers);
        echo "OK";
    }
}
