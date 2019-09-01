<?php
/**
 * This script will update the USERS table with the form information 
 * entered by the new user on Registration.html.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$submitter = filter_input(INPUT_POST, 'submitter');
$username  = filter_input(INPUT_POST, 'username');
$user_pass = filter_input(INPUT_POST, 'password');
$password  = password_hash($user_pass, PASSWORD_DEFAULT);
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
$lastname  = filter_input(INPUT_POST, 'lastname');
$firstname = filter_input(INPUT_POST, 'firstname');
$email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo "Invalid email address - please go back to the Registration Page";
} 
$facebook  = filter_input(INPUT_POST, 'facebook', FILTER_VALIDATE_URL);
$twitter   = filter_input(INPUT_POST, 'twitter');
$bio       = filter_input(INPUT_POST, 'bio');
if ($submitter == 'create') {
    $newuser = "INSERT INTO USERS (" .
    "username,passwd,passwd_expire,last_name,first_name,email,facebook_url," .
    "twitter_handle,bio) VALUES (:uname,:passwd,:pass_exp,:lastnme,:firstnme," .
    ":email,:fbk,:twit,:bio);";
    $user = $pdo->prepare($newuser);
    $user->execute(
        array(":uname" =>  $username, ":passwd" => $password,
        ":pass_exp" => $exp_date, ":lastnme" => $lastname, 
        ":firstnme" => $firstname, ":email" => $email,
        ":fbk" => $facebook, ":twit" => $twitter,":bio" => $bio
        )
    );
} else { // update user
    $updateuser = "UPDATE USERS SET username=?, passwd=?, passwd_expire=?," .
        "last_name=?,first_name=?,email=?,facebook_url=?,twitter_handle=?," .
        "bio=? WHERE username=?;";
    $update = $pdo->prepare($updateuser);
    $update->execute(
        array($username, $password, $exp_date, $lastname, $firstname,
        $email, $facebook, $twitter, $bio, $username
        )
    );
}
// always try to set a user cookie:
$days = 365; // Number of days before cookie expires
$expire = time()+60*60*24*$days;
setcookie("nmh_id", $username, $expire, "/");
if ($submitter == 'create') {
    echo "DONE";
} else {
    header("Location: ../index.html");
}
