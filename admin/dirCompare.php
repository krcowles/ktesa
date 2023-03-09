<?php
/**
 * This script currently runs as a stand-alone diagnostic for 
 * the case where the two sites gpx/json files differ. Executing
 * the 'Install main' code indicates only the count differences, 
 * if any. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$site = filter_input(INPUT_GET, 'site');
$site_gpx  = '../' . $site . '/gpx';
$site_json = '../' . $site . '/json';

// resident site gpx/json files
$res_gpx  = scandir('../gpx');
$res_json = scandir('../json');
// test site gpx/json files
$new_gpx  = scandir($site_gpx);
$new_json = scandir($site_json);

echo "Assumes test site has fewer files:<br />";
print_r(arrdiff($new_gpx, $res_gpx));
echo "<br>";
print_r(arrdiff($new_json, $res_json));
echo "<br /><br />";
echo "Case where the main site has fewer files:<br />";
print_r(arrdiff($res_gpx, $new_gpx));
echo "<br />";
print_r(arrdiff($res_json, $new_json));
echo "<br /><br />DONE"; 
