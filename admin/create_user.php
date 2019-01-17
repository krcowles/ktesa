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
$uname = filter_input(INPUT_POST, 'usr');
$tpass = filter_input(INPUT_POST, 'password');
$pword = password_hash($tpass, PASSWORD_DEFAULT);
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
$lname = filter_input(INPUT_POST, 'lastname');
$fname = filter_input(INPUT_POST, 'firstname');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo "Invalid email address - please go back to the Registration Page";
} 
$facbk = filter_input(INPUT_POST, 'facebook', FILTER_VALIDATE_URL);
$twitt = filter_input(INPUT_POST, 'twitter');
$binfo = filter_input(INPUT_POST, 'bio');
$newuser = "INSERT INTO USERS (
    username,
    passwd,
    passwd_expire,
    last_name,
    first_name,
    email,
    facebook_url,
    twitter_handle,
    bio
    ) VALUES (
        :uname,
        :passwd,
        :pass_exp,
        :lastnme,
        :firstnme,
        :email,
        :fbk,
        :twit,
        :bio
    );";
$user = $pdo->prepare($newuser);
try {
    $user->execute(
        array(
            ":uname" =>  $uname,
            ":passwd" => $pword,
            ":pass_exp" => $exp_date,
            ":lastnme" => $lname,
            ":firstnme" => $fname,
            ":email" => $email,
            ":fbk" => $facbk,
            ":twit" => $twitt,
            ":bio" => $binfo
            )
    );
}
catch (PDOException $e) {
    pdo_err("INSERT INTO USERS", $e);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Successful Registration</title>
    <meta charset="utf-8" />
    <meta name="description" content="Successful Registration" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #eaeaea; }
    </style>
</head>
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Registration Complete</p>
<p id="usrid" style="display:none"><?= $uname;?></p>
<div style="margin-left:24px;">
    <h1>
        < <?= $uname;?> > You have successfully registered!
    </h1> 
    <h3>
        You may now display registered user options on the main page to
        create and edit hikes.<br /><br />You are now logged in and can display
        user options.<br />
        <em id="cookies" style="color:brown;"></em><br />
        Click on the link below:
    </h3>
    <p style="font-size:18px;color:brown;">
        <a href="../index.html">Main Page Link</a><br />
    </p>
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="cookie_check.js"></script>
</body>
</html>
