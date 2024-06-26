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

$site = filter_input(INPUT_POST, 'site'); // the test site to install
$site_json = '../' . $site . '/json';

// resident site files
$res_json = scandir('../json');
// test site loc
$new_json = scandir($site_json);
$return = [];

// 'nim' => not in main'; deleted or missing compared to '$new_json'
$return['nim_json'] = arrdiff($new_json, $res_json);
// 'nit' => new in test site; a new file exists in the test site
$return['nit_json'] = arrdiff($res_json, $new_json);

$result = json_encode($return);
echo $result;
