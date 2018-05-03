<?php
require_once '../mysql/dbFunctions.php';
$link = connectToDb(__FILE__, __LINE__);
// tom - create your password here: this file will go away once we update USERS
$tom = 'newpassword';
$pword = mysqli_real_escape_string($link, password_hash($tom, PASSWORD_DEFAULT));
$newpasswd = "UPDATE USERS SET passwd='{$pword}' WHERE username='tom';";
mysqli_query($link, $newpasswd) or die("FAILED: " . mysqli_error($link));
echo "Password updated...";
