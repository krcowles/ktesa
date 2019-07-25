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
$page = filter_input(INPUT_GET, 'page');
$user = filter_input(INPUT_GET, 'user');
if ($page > 9) {
    $user = 'mstr';
}
$script = "<script type='text/javascript'>window.open('";
$close  = "', target='_self');</script>";
if ($page == '0' || $page == '10') {
    $script .= "build/startNewPg.php?usr=" . $user . $close;
} elseif ($page == '1' || $page == '11') {
    $script .= "build/hikeEditor.php?age=new&usr=" . $user . "&show=usr" . $close;
} elseif ($page == '2' || $page == '12') {
    $script .= "build/hikeEditor.php?age=old&usr=" . $user . "&show=usr" . $close;
} elseif ($page == '3' || $page == '13') {
    $script .= "build/editDisplay.php?usr=" . $user . $close;
} elseif ($page == '14') {
    $script .= "admin/admintools.php" . $close;
}
echo $script;
