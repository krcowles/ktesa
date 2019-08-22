<?php
/**
 * This is a simple script to log out the curent user.
 * PHP Version 7.1
 * 
 * @package Main
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

setcookie('nmh_mstr', '', 0, '/');
setcookie('nmh_id', '', 0, '/');
// in case of session registration:
if (array_key_exists('loggedin', $_SESSION)) {
    unset($_SESSION['loggedin']);
}
echo "Done";
