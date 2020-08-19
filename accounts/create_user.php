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
require "../php/global_boot.php";
$submitter = isset($_POST['submitter']) ?
    filter_input(INPUT_POST, 'submitter') : false;
if (!$submitter) {
    throw new Exception("No submitter received in create_user");
}
$username  = filter_input(INPUT_POST, 'username');
$user_pass = filter_input(INPUT_POST, 'password');
$password  = password_hash($user_pass, PASSWORD_DEFAULT);
$lastname  = filter_input(INPUT_POST, 'lastname');
$firstname = filter_input(INPUT_POST, 'firstname');
$email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$today = getdate();
$month = $today['mon'];
$day = $today['mday'];
if ($month > 6) {
    $year = $today['year'] + 1;
    $month -= 6;
} else {
    $year = $today['year'];
    $month += 6;
}
$exp_date = $year . "-" . $month . "-" . $day;

// New members
if ($submitter == 'create') {
    $newuser = "INSERT INTO `USERS` (" .
        "username,passwd,passwd_expire,last_name,first_name,email) " .
        "VALUES (:uname,:passwd,:pass_exp,:lastnme,:firstnme,:email);";
    $user = $pdo->prepare($newuser);
    $user->execute(
        array(
            ":uname" =>  $username, ":passwd" => $password,
            ":pass_exp" => $exp_date, ":lastnme" => $lastname, 
            ":firstnme" => $firstname, ":email" => $email,
        )
    );
} else { // Renew
    $updateuser = "UPDATE `USERS` SET `passwd`=?, `passwd_expire`=? " .
        "WHERE `username`=?;";
    $update = $pdo->prepare($updateuser);
    $update->execute(
        array($password, $exp_date, $username)
    );
}
// always try to set a user cookie:
$days = 365; // Number of days before cookie expires
$expire = time()+60*60*24*$days;
setcookie("nmh_id", $username, $expire, "/");
header("Location: ../index.html");
