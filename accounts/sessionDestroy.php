<?php
/**
 * This script is used only for testing login activities. For example,
 * when a USERS passwd_expire field is modified to require renewal, in
 * order to test the process, assuming the user has a cookie set, the 
 * session must be expired.
 * PHP 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date* 
 */
session_start();
unset($_SESSION['username']);
unset($_SESSION['userid']);
unset($_SESSION['expire']);
unset($_SESSION['cookies']);
unset($_SESSION['cookie_state']);
echo "Done";
