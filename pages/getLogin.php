<?php
/**
 * This script is used by the ktesaPanel to determine whether or not
 * a user is logged in, and if so, to get the user id. The user id is
 * utilized to enable menu settings (via javascript/getLogins.js), and
 * to direct the user to the correct pages for his/her hikes for editing.
 * If there are cookies on the client's browser for this site (ktesa),
 * then they are used. If there are no cookies for this site, or if the
 * client has cookies turned off, the script will look for login via a
 * php session ($_SESSION). If not logged in via the session, the $uname
 * is set to 'none' to advise the javascript.
 * PHP Version 7.1
 * 
 * @package Display
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$master = isset($_COOKIE['nmh_mstr']) ? true : false;
$regusr = isset($_COOKIE['nmh_id'])   ? true : false;
$uname = 'none';
if (!$master && $regusr) {
    $uname = $_COOKIE['nmh_id'];
} elseif ($master) {
    $uname = 'mstr';
} else {
    if (array_key_exists('loggedin', $_SESSION)) {
        $uname = $_SESSION['loggedin'];
    }
}
