<?php
/**
 * This script was created to prevent non-registered users from 
 * viewing edit page links, and to prevent all but admins from
 * seeing the link to the admin tools.
 * PHP Version 7.1
 * 
 * @package Main
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../admin/mode_settings.php";
$page = filter_input(INPUT_GET, 'page');
$user = filter_input(INPUT_GET, 'user');
$script = "<script type='text/javascript'>window.open('";
$close  = "', target='_self');</script>";
$noedit = "<script type='text/javascript'>alert('No editing allowed');</script>";

if ($page === 'admin' && $user = 'mstr') {
    $script .= "../admin/admintools.php" . $close;
} elseif ($page === 'viewPubs') {
    $script .= "../build/viewPubs.php?usr=" . $user . $close;
} elseif ($page === 'new') {
    if ($editing === 'yes') {
        $script .= "../build/startNewPg.php?usr=" . $user . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'existing') {
    if ($editing === 'yes') {
        $script .= "../build/hikeEditor.php?age=new&usr=" . $user . "&show=usr" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'published') {
    if ($editing === 'yes') {
        $script .= "../build/hikeEditor.php?age=old&usr=" . $user . "&show=usr" . $close;
    } else {
        $script = $noedit;
    }
} elseif ($page === 'viewEdits') {
    $script .= "../build/editDisplay.php?usr=" . $user . $close;
} elseif ($page === 'register') {
    $script .= "../admin/registration.php" . $close;
}
echo $script;
