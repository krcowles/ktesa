<?php
/**
 * Mobile offline version is held in the member_landing.html
 * file, and forces a mobile user's software update when 
 * modified. The current version is retrieved, and a new
 * version can optionally be set by the admin.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$version_page = "../pages/member_landing.html";
$action      = filter_input(INPUT_POST, 'action');
$new_version = filter_input(INPUT_POST, 'version'); // can be null

$curr_code = file($version_page);
$version_code = $curr_code[20];
$needle = 'id="version"';
$ver_start = strpos($version_code, $needle) + 13;
$ver_end   = strpos($version_code, "</");
$ver_lgth  = $ver_end - $ver_start;
$curr_vers = substr($version_code, $ver_start, $ver_lgth);
if ($action === 'get') {
    echo $curr_vers;
    exit;
} elseif ($action === 'set') {
    $modified = str_replace($curr_vers, $new_version, $version_code);
    $curr_code[20] = $modified;
    file_put_contents($version_page, $curr_code);
    echo "OK";
}
