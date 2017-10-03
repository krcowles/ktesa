<?php
if ( !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ) {
    header('http/1.1 401 Authenticate');
    header('WWW-Authenticate: Basic realm="New Mexico Hikes"');
    exit("Valid Name/Password Required - Sorry!");
} 
require_once "../mysql/local_mysql_connect.php";
$getlogin = sprintf("SELECT userid,username,passwd FROM USERS " .
    "WHERE username = '%s' AND passwd = '%s';",
    mysqli_real_escape_string($link,$_SERVER['PHP_AUTH_USER']),
    mysqli_real_escape_string($link,$_SERVER['PHP_AUTH_PW']));
$logindat = mysqli_query($link,$getlogin);
if (mysqli_num_rows($logindat) == 1) {
    $success = mysqli_fetch_assoc($logindat);
    $curr_userid = $success['userid'];
    $curr_username = $success['username'];
    $curr_passwd = $success['passwd'];
} else {  # not in USER table
    header('http/1.1 401 Authenticate');
    header('WWW-Authenticate: Basic realm="New Mexico Hikes"');
    exit("Valid Name/Password Required - Sorry!");
}
?>