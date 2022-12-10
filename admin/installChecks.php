<?php
/**
 * This module will check for file differences (other than database) in
 * terms of number and type of files in both the resident site and the
 * new test site to be loaded into the main site. This will allow the admin to
 * determine, for instance, that a new gpx or json file has been uploaded since
 * the last site update, and help prevent losing a user file by over-writing the
 * site files with the specified test site files. NOTE: This first version only
 * checks for gpx and json files (no other user files can be uploaded and/or
 * overwritten [pictures are not overwritten]).
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
verifyAccess('post');

$site = filter_input(INPUT_POST, 'site');
$site_gpx  = '../' . $site . '/gpx';
$site_json = '../' . $site . '/json';

// resident site files
$res_gpx  = scandir('../gpx');
$res_json = scandir('../json');
// test site loc
$new_gpx  = scandir($site_gpx);
$new_json = scandir($site_json);
$return = [];
if (count($res_gpx) === count($new_gpx) && count($res_json) === count($new_json)) {
    $return[0] = 'none';
} else {
    if (count($res_gpx) === count($new_gpx)) {
        $return[0] = 'gpx';
    }
    if (count($res_json) !== count($site_json)) {
        array_push($return, 'json');
    }
}
// later on, identify the files that are different, if any?
$result = json_encode($return);
echo $result;
