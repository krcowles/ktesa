<?php
/**
 * This script will alter sql_mode settings.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandeberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$mode_str = 'SET sql_mode = "';
$callpath = getcwd();
$subdir = strrpos($callpath, "/") + 1;
$baseaddr = substr($callpath, 0, $subdir) . 'admin/';
$modes = file($baseaddr . 'sql_modes.ini', FILE_IGNORE_NEW_LINES);
foreach ($modes as $setting) {
    if (substr($setting, 0, 1) == 'Y') {
        $mode_str .= substr($setting, 2, strlen($setting)-2) . ",";
    }
}
$mode_str = substr($mode_str, 0, strlen($mode_str)-1) . '"';
