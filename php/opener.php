<?php
/**
 * This script was created to prevent non-registered users from 
 * viewing edit page links, and to prevent all but admins from
 * seeing the link to the admin tools.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');

$page   = filter_input(INPUT_GET, 'page');
$login  = isset($_SESSION['userid']) ? $_SESSION['userid'] : false;
$script = "<script type='text/javascript'>window.open('";
$close  = "', target='_self');</script>";
$noedit = "<script type='text/javascript'>alert('No editing allowed');</script>";

if ($page === 'admin' && $login) {
    if ($login == '1' || $login == '2') {
        $script .= "../admin/admintools.php" . $close;
    } else {
        $script = '';
    }
} elseif ($page === 'viewPubs') { // not implemented yet
    $script .= "../edit/viewPubs.php" . $close;
} elseif ($page === 'new') {
    if ($editing === 'yes') {
        $script .= "../edit/startNewPg.php" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'existing') {
    if ($editing === 'yes') {
        $script .= "../edit/hikeEditor.php?age=new&show=usr" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'published') {
    if ($editing === 'yes') {
        $script .= "../edit/hikeEditor.php?age=old" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'ready') {
    $script .= "../edit/hikeEditor.php?age=new&pub=usr" . $close;
} elseif ($page === 'register') {
    $script .= "../accounts/registration.php" . $close;
}
echo $script;
