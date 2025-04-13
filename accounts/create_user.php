<?php
/**
 * This script will update the USERS table with the form information 
 * entered by the new user on Registration.php.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');

$submitter = filter_input(INPUT_POST, 'submitter');

$today = getdate();
$year = $today['year'] + 2;
$month = $today['mon'];
$day = $today['mday'];
$exp_date = $year . "-" . $month . "-" . $day;

if ($submitter == 'create') {
    // New member:
    $firstname = filter_input(INPUT_POST, 'firstname');
    $lastname  = filter_input(INPUT_POST, 'lastname');
    $username  = filter_input(INPUT_POST, 'username');
    $email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $clubber   = filter_input(INPUT_POST, 'cmem');
    $club_field = $clubber == 'on' ? 'Y' : 'N';

    $newuser   = "INSERT INTO `USERS` (" .
        "username,last_name,first_name,email,club_member) " .
        "VALUES (:uname,:lastname,:firstname,:email,:club);";
    $user = $pdo->prepare($newuser);
    $user->execute(
        array(
            ":uname"     => $username,
            ":lastname"  => $lastname,
            ":firstname" => $firstname, 
            ":email"     => $email,
            ":club"      => $club_field
        )
    );
    if (!$user) {
        echo "Registration: database write error for {$username} at {$email}.";
    } else {
        echo "OK";
    }
    exit;
} elseif ($submitter == 'change') {
    $code      = filter_input(INPUT_POST, 'code');
    $user_pass = filter_input(INPUT_POST, 'password');
    $password  = password_hash($user_pass, PASSWORD_DEFAULT);
    $user      = filter_input(INPUT_POST, 'user');

    $getUserReq = "SELECT * FROM `USERS` WHERE `userid`='{$user}';";
    $prereg = $pdo->query($getUserReq)->fetch(PDO::FETCH_ASSOC);
    if (password_verify($code, $prereg['passwd'])) { 
        $_SESSION['username'] = $prereg['username'];
        $_SESSION['userid']   = $user;
        $_SESSION['cookie_state'] = "OK";
    } else {
        echo "NOCODE";
        exit;
    }
    $updateuser = "UPDATE `USERS` SET `passwd`=?, `passwd_expire`=? " .
        " WHERE `userid`=?;";
    $update = $pdo->prepare($updateuser);
    $update->execute(
        array($password, $exp_date, $_SESSION['userid'])
    );
}
$days = 730; // Number of days before cookie expires
$expire = time() + 60*60*24*$days; // time is in seconds
setcookie("nmh_id", $_SESSION['username'], $expire, "/", "", true, true);

echo "OK";
