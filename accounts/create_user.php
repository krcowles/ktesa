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
$submitter = isset($_POST['submitter']) ?
    filter_input(INPUT_POST, 'submitter') : false;
if (!$submitter) {
    throw new Exception("No submitter received in create_user.php");
}
$cookies   = filter_input(INPUT_POST, 'cookies');
$user_pass = filter_input(INPUT_POST, 'password');
$password  = password_hash($user_pass, PASSWORD_DEFAULT);
$username  = filter_input(INPUT_POST, 'username');
$lastname 
    = isset($_POST['lastname']) ? filter_input(INPUT_POST, 'lastname') : false;
$firstname
    = isset($_POST['firstname']) ? filter_input(INPUT_POST, 'firstname') : false;
$email
    = isset($_POST['email']) ?
    filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) : false;
$today = getdate();
$year = $today['year'] + 1;
$month = $today['mon'];
$day = $today['mday'];
$exp_date = $year . "-" . $month . "-" . $day;

if ($submitter == 'create') {
    // New member:
    $cookies = $cookies === 'nochoice' ? 'reject' : $cookies;
    $newuser = "INSERT INTO `USERS` (" .
        "username,passwd,passwd_expire,last_name,first_name,email,facebook_url) " .
        "VALUES (:uname,:passwd,:pass_exp,:lastnme,:firstnme,:email,:cookies);";
    $user = $pdo->prepare($newuser);
    $user->execute(
        array(
            ":uname" =>  $username, ":passwd" => $password,
            ":pass_exp" => $exp_date, ":lastnme" => $lastname, 
            ":firstnme" => $firstname, ":email" => $email,
            ":cookies" => $cookies
        )
    );
    // get uid from updated table:
    $newidReq = "SELECT `userid` FROM `USERS` ORDER BY 1 DESC LIMIT 1;";
    $newid = $pdo->query($newidReq)->fetch(PDO::FETCH_ASSOC);
    $_SESSION['username'] = $username;
    $_SESSION['userid']   = $newid['userid'];
    $_SESSION['expire']   = $exp_date;
    $_SESSION['cookies']  = $cookies;
    $_SESSION['cookie_state'] = "OK";
} else {
    // Current member: change password
    $oldpass = filter_input(INPUT_POST, 'oldpass');
    if (empty($username)) { 
        // 'Forgot Password' scenario - no login credentials yet
        $getUserReq = "SELECT * FROM `USERS`;";
        $users = $pdo->query($getUserReq)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            if (password_verify($oldpass, $user['passwd'])) { 
                $username = $user['username'];
                $_SESSION['username'] = $username;
                $_SESSION['userid']   = $user['userid'];
                $_SESSION['cookies']  = $cookies;
                $_SESSION['cookie_state'] = "OK";
                break;
            }
        }
        if (empty($username)) {
            throw new Exception("The entered One-Time Code was not located in create_user.php");
        }
        
    }
    $_SESSION['expire'] = $exp_date;
    $updateuser = "UPDATE `USERS` SET `passwd`=?, `passwd_expire`=?, " .
        "`facebook_url`=? WHERE `username`=?;";
    $update = $pdo->prepare($updateuser);
    $update->execute(
        array($password, $exp_date, $cookies, $username)
    );
}
// set cookie if user has accepted cookie use
if ($_SESSION['cookies'] === 'accept') {
    $days = 365; // Number of days before cookie expires
    $expire = time()+60*60*24*$days;
    setcookie("nmh_id", $username, $expire, "/", "", true, true);
}
header("Location: ../index.html");
