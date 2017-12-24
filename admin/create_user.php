<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
$fname = mysqli_real_escape_string($link, filter_input(INPUT_POST, 'firstname'));
$lname = mysqli_real_escape_string($link, filter_input(INPUT_POST, 'lastname'));
$uname = mysqli_real_escape_string($link, filter_input(INPUT_POST, 'usr'));
$tpass = filter_input(INPUT_POST, 'password');
$pword = mysqli_real_escape_string($link, password_hash($tpass, PASSWORD_DEFAULT));
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo "Invalid email address - please go back to the Registration Page";
} else {
    $email = mysqli_real_escape_string($link, $email);
}
$facbk = filter_input(INPUT_POST, 'facebook', FILTER_VALIDATE_URL);
$twitt = mysqli_real_escape_string($link, filter_input(INPUT_POST, 'twitter'));
$binfo = mysqli_real_escape_string($link, filter_input(INPUT_POST, 'bio'));
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
$passwd_exp = mysqli_real_escape_string($link, $exp_date);
$newuser = "INSERT INTO USERS (username,passwd,passwd_expire,last_name," .
        "first_name,email,facebook_url,twitter_handle,bio) " .
    "VALUES ('{$uname}','{$pword}','{$passwd_exp}','{$lname}','{$fname}'," .
            "'{$email}','{$facbk}','{$twitt}','{$binfo}');";
$insert = mysqli_query($link, $newuser);
/*
if (!insert) {
    if (Ktesa_Dbug) {
        debug_print("Could not insert new user info: " . mysqli_error($link));
    } else {
        user_error_msg('../mysql/',2,0);
    }
} else {
    echo '<p style="display:none;" id="uname">' . $uname . '</p>';
}
 * 
 */
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
<div style="margin-left:24px;">
    <h1>
        You have successfully registered!
    </h1> 
    <h3>
        You may now display registered user options on the main page to
        create and edit hikes.<br /><br />You are now logged in and can display
        user options by entering your user name in the provided field.<br />
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
