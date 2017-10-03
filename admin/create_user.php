<?php
require_once "../mysql/local_mysql_connect.php";
$fname = mysqli_real_escape_string($link,filter_input(INPUT_POST,'firstname'));
$lname = mysqli_real_escape_string($link,filter_input(INPUT_POST,'lastname'));
$uname = mysqli_real_escape_string($link,filter_input(INPUT_POST,'usr'));
$pword = mysqli_real_escape_string($link,crypt(filter_input(INPUT_POST,'password')));
$email = filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo "Invalid email address - please go back to the Registration Page";
} else {
    $email = mysqli_real_escape_string($link,$email);
}
$facbk = filter_input(INPUT_POST,'facebook',FILTER_VALIDATE_URL);
$twitt = mysqli_real_escape_string($link,filter_input(INPUT_POST,'twitter'));
$binfo = mysqli_real_escape_string($link,filter_input(INPUT_POST,'bio'));
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
$passwd_exp = mysqli_real_escape_string($link,$exp_date);
$newuser = "INSERT INTO USERS " .
    "(username,passwd,passwd_expire,"
        . "last_name,first_name,email,facebook_url,"
        . "twitter_handle,bio) " .
    "VALUES ( '{$uname}','{$pword}','{$passwd_exp}',"
        . "'{$lname}','{$fname}','{$email}','{$facbk}',"
        . "'{$twitt}','{$binfo}');";
$insert = mysqli_guery($link,$newuser);
if (!insert) {
    if (Ktesa_Dbug) {
        debug_print("Could not insert new user info: " . mysqli_error());
    } else {
        user_error_msg('../mysql/',2,0);
    }
}
# HTML FOR SUCCESS HERE....
?>