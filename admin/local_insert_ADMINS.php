<?php
require "local_mysql_connect.php";
$hash = password_hash('000ktesa9',PASSWORD_DEFAULT);
$a = mysqli_real_escape_string($link,$hash);
$tpass = password_hash('1234',PASSWORD_DEFAULT);
$tst = mysqli_real_escape_string($link,$tpass);
$bio = mysqli_real_escape_string($link,"One of the geniuses behind this site :-)");
$admin = "INSERT INTO USERS (username,passwd,last_name,first_name,email) " .
    "VALUES ('SiteMaster','{$a}','Master','Site','krcowles29@gmail.com');";
$tom = "INSERT INTO USERS (username,passwd,last_name,first_name,email,bio) " .
    "VALUES ('tmptom','{$a}','Sandberg','Tom','tjsandberg@yahoo.com','{$bio}');";
$ken = "INSERT INTO USERS (username,passwd,last_name,first_name,email,bio) " .
    "VALUES ('kroc','{$a}','Cowles','Ken','krcowles29@gmail.com','{$bio}');";
$test = "INSERT INTO USERS (username,passwd,last_name,first_name,email,bio) " .
    "VALUES ('test','{$tst}','Test','Test','no@no.com','big');";

$load_admin = mysqli_query($link,$admin);
$load_tom = mysqli_query($link,$tom);
$load_ken = mysqli_query($link,$ken);
$load_test = mysqli_query($link,$test);
if (!$load_admin || !$load_tom || !$load_ken) {
    die("Did not load data set for USERS table: " . mysqli_error());
}
echo "DONE";
?>