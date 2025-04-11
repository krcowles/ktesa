<?php
/**
 * This is a simple script to log out the curent user by unsetting
 * the user's cookies and the session variables associated with login.
 * Note: when an expired user (cookie or login) has been detected,
 * the user is removed from the USERS table.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

if (isset($_GET['expire']) && $_GET['expire'] === 'Y') {
    $removeReq = "DELETE FROM `USERS` WHERE `userid`=?;";
    $remove = $pdo->prepare($removeReq);
    $remove->execute([$_SESSION['userid']]);
}
if (isset($_GET['redo']) && $_GET['redo'] === 'Y' ) { // no $_SESSION['userid'] yet
    $username = filter_input(INPUT_GET, 'user');
    $removeReq = "DELETE FROM `USERS` WHERE `username`=?;";
    $remove = $pdo->prepare($removeReq);
    $remove->execute([$username]);
}
$admin = false;
if (isset($_SESSION['userid'])) {  // since session may have expired
    if ($_SESSION['userid'] === '1' || $_SESSION['userid'] === '2') {
        setcookie('nmh_mstr', '', 0, '/');
        $admin = true;
    }
}
if (!$admin) {
    setcookie('nmh_id', '', 0, '/');
}
unset($_SESSION['username']);
unset($_SESSION['userid']);
unset($_SESSION['cookie_state']);
unset($_SESSION['club_member']);
