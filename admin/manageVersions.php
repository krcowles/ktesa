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
$version_page = "../pages/landing.php";
$action      = filter_input(INPUT_POST, 'action');
$new_version = filter_input(INPUT_POST, 'version'); // can be null

$curr_code = file($version_page);
$ver_start = 0;
$ver_end   = 0;
$curr_version = '';
$line_index = 0;
for ($j=0; $j<count($curr_code); $j++) {
    if (strpos($curr_code[$j], "current_version") !== false) {
        $line_index = $j;
        $line = $curr_code[$j];
        $ver_start = strpos($line, '"') + 1;
        $ver_end   = strrpos($line, '"');
        $curr_version = substr($line, $ver_start, $ver_end - $ver_start);
        break;
    }
}
if ($action === 'get') {
    echo $curr_version;
    exit;
} elseif ($action === 'set') {
    $curr_code[$line_index] = str_replace(
        $curr_version, $new_version, $curr_code[$line_index]
    );
    file_put_contents($version_page, $curr_code);
    echo "OK";
}
