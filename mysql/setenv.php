<?php
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../mysql/';
    require_once "../mysql/000mysql_connect.php";
}
/*
error_reporting(-1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");
 */
