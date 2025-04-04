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
$year = $today['year'] + 1;
$month = $today['mon'];
$day = $today['mday'];
$exp_date = $year . "-" . $month . "-" . $day;

if ($submitter == 'create') {
    // New member:
    $firstname = filter_input(INPUT_POST, 'firstname');
    $lastname  = filter_input(INPUT_POST, 'lastname');
    $username  = filter_input(INPUT_POST, 'username');
    $email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    $newuser   = "INSERT INTO `USERS` (" .
        "username,last_name,first_name,email) " .
        "VALUES (:uname,:lastname,:firstname,:email);";
    $user = $pdo->prepare($newuser);
    $user->execute(
        array(
            ":uname"     => $username,
            ":lastname"  => $lastname,
            ":firstname" => $firstname, 
            ":email"     => $email
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

    $getUserReq = "SELECT * FROM `USERS`;";
    $users = $pdo->query($getUserReq)->fetchAll(PDO::FETCH_ASSOC);
    $match = false;
    foreach ($users as $user) {
        if (password_verify($code, $user['passwd'])) { 
            $_SESSION['username'] = $user['username'];
            $_SESSION['userid']   = $user['userid'];
            $_SESSION['cookie_state'] = "OK";
            $match = true;
            break;
        }
    }
    if (!$match) {
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
$days = 365; // Number of days before cookie expires
$expire = time() + 60*60*24*$days;
setcookie("nmh_id", $_SESSION['username'], $expire, "/", "", true, true);

echo "OK";
