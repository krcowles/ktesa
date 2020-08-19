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
require "../admin/mode_settings.php";
$page = filter_input(INPUT_GET, 'page');
$user = $_SESSION['username'];
$script = "<script type='text/javascript'>window.open('";
$close  = "', target='_self');</script>";
$noedit = "<script type='text/javascript'>alert('No editing allowed');</script>";

if ($page === 'admin' && $user = 'mstr') {
    $script .= "../admin/admintools.php" . $close;
} elseif ($page === 'viewPubs') { // not implemented yet
    $script .= "../build/viewPubs.php" . $close;
} elseif ($page === 'new') {
    if ($editing === 'yes') {
        $script .= "../build/startNewPg.php" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'existing') {
    if ($editing === 'yes') {
        $script .= "../build/hikeEditor.php?age=new&show=usr" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'published') {
    if ($editing === 'yes') {
        $script .= "../build/hikeEditor.php?age=old&show=usr" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'register') {
    $script .= "../accounts/registration.php" . $close;
}
echo $script;
