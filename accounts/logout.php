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

setcookie('nmh_mstr', '', 0, '/');
setcookie('nmh_id', '', 0, '/');
unset($_SESSION['username']);
unset($_SESSION['userid']);
unset($_SESSION['expire']);

echo "Done";
