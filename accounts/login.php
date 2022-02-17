<?php
/**
 * This simple script completes a user's login by establishing the
 * remaining session variables. Up to this point, only the 'userid'
 * variable has been established.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";
verifyAccess('ajax');
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day

$userid = filter_input(INPUT_POST, 'ix');

$userReq = "SELECT `username`,`cookies` FROM `USERS` WHERE `userid`=?;";
$user_data = $pdo->prepare($userReq);
$user_data->execute([$userid]);
$vars = $user_data->fetch(PDO::FETCH_ASSOC);
$_SESSION['username']     = $vars['username'];
$_SESSION['userid']       = $userid;
$_SESSION['cookies']      = $vars['cookies'];
$_SESSION['cookie_state'] = "OK";
$cookie_expire = time() + 10 * UX_DAY * 365;
$cookie_name   = 'nmh_mstr';
if ($userid == '1') {
    $browser_cookie = 'mstr';
} elseif ($userid == '2') {
    $browser_cookie = 'mstr2';
} else {
    $cookie_name = 'nmh_id';
    $browser_cookie = $vars['username'];
    $cookie_expire  =  time() + UX_DAY * 365;
}
if ($vars['cookies'] === 'accept') {
    setcookie(
        $cookie_name, $browser_cookie, $cookie_expire, "/", "", true, true
    );
}
echo "OK";
