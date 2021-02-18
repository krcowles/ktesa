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
$cookies   = filter_input(INPUT_POST, 'cookies');

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
    $cookies   = $cookies === 'nochoice' ? 'reject' : $cookies;
    $email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    // Has this email already been registered?
    $regmails
        = $pdo->query("SELECT `email` FROM `USERS`;")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array($email, $regmails)) {
        echo "Email already registered";
        exit;
    }
    $newuser   = "INSERT INTO `USERS` (" .
        "username,last_name,first_name,email,cookies) " .
        "VALUES (:uname,:lastname,:firstname,:email,:cookies);";
    $user = $pdo->prepare($newuser);
    $user->execute(
        array(
            ":uname" =>  $username,
            ":lastname" => $lastname,
            ":firstname" => $firstname, 
            ":email" => $email,
            ":cookies" => $cookies
        )
    );
    if (!$user) {
        echo "Failed to store registration:\ncontact Administrator";
    } else {
        echo "OK";
    }
    exit;
} elseif ($submitter == 'change') {
    $code = filter_input(INPUT_POST, 'code');
    $user_pass = filter_input(INPUT_POST, 'password');
    $password  = password_hash($user_pass, PASSWORD_DEFAULT);
    if (!empty($code)) { // when $code == '', the user is already logged in
        $getUserReq = "SELECT * FROM `USERS`;";
        $users = $pdo->query($getUserReq)->fetchAll(PDO::FETCH_ASSOC);
        $match = false;
        foreach ($users as $user) {
            if (password_verify($code, $user['passwd'])) { 
                $_SESSION['username'] = $user['username'];
                $_SESSION['userid']   = $user['userid'];
                $_SESSION['expire']   = $exp_date;
                $_SESSION['cookies']  = $cookies;
                $_SESSION['cookie_state'] = "OK";
                $match = true;
                break;
            }
        }
        if (!$match) {
            echo "NOCODE";
            exit;
        }
    }  // session variables already exist for logged in renewer
    $updateuser = "UPDATE `USERS` SET `passwd`=?, `passwd_expire`=?, " .
        "`cookies`=? WHERE `username`=?;";
    $update = $pdo->prepare($updateuser);
    $update->execute(
        array($password, $exp_date, $cookies, $_SESSION['username'])
    );
}
// set cookie if user has accepted cookie use
if ($_SESSION['cookies'] === 'accept') {
    $days = 365; // Number of days before cookie expires
    $expire = time()+60*60*24*$days;
    setcookie("nmh_id", $_SESSION['username'], $expire, "/", "", true, true);
}
echo "OK";
