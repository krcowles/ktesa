<?php
/**
 * This script is invoked when a user an ajax error was encountered
 * in production mode. The admin is notified of the error and its code.
 * Because of the number of ajax calls, the message construction has
 * many optios.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');

$errmsg = filter_input(INPUT_POST, 'err'); // always present
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'no user';

$admin_msg = "User " . $username . " encountered an ajax error: " . 
    PHP_EOL . $errmsg . PHP_EOL;
$to = "krcowles29@gmail.com";
$subject = "User ajax error";
mail($to, $subject, $admin_msg);
