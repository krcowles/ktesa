<?php
/**
 * This is a simple script to log out the curent user by unsetting
 * the user's cookies and session variables associated with login
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

if (isset($_GET['expire']) && $_GET['expire'] === 'Y') {
    $removeReq = "DELETE FROM `USERS` WHERE `username`=?;";
    $remove = $pdo->prepare($removeReq);
    $remove->execute([$_SESSION['username']]);
    unset($_SESSION['cancel']);
}
setcookie('nmh_mstr', '', 0, '/');
setcookie('nmh_id', '', 0, '/');
unset($_SESSION['username']);
unset($_SESSION['userid']);
unset($_SESSION['expire']);
unset($_SESSION['cookies']);
unset($_SESSION['cookie_state']);

echo "Done";
