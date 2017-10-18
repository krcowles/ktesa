<?php
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../php/';
    require_once "../php/000mysql_connect.php";
}