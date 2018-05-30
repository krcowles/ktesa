<?php
/**
 * This script accepts data from a javascript ajax POST. The data contains
 * objects defining the photos selected to be added to the editor, and a final
 * object carrying miscellaneous info needed to save the photo data to ETSV.
 * PHP Version 7.0
 * 
 * @package Editing
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../mysql/dbFunctions.php";
$items = filter_input(INPUT_POST, 'info');
$saved = json_decode($items, true);
if (count($saved) === 1) {
    echo "No photos marked";
    exit;
}
$pageInfo = array_pop($saved);
if ($pageInfo['folder'] !== '999') {
    echo "Table Delimiters Not Received; " . count($saved) . "items";
    exit;
}
$hikeNo = $pageInfo['pic'];
$usr = $pageInfo['desc'];
$link = connectToDb(__FILE__, __LINE__);
foreach ($saved as $photo) {
    $folder = mysqli_real_escape_string($link, $photo['folder']);
    $title = mysqli_real_escape_string($link, $photo['pic']);
    $desc = mysqli_real_escape_string($link, $photo['desc']);
    $lat = floatval($photo['lat']);
    $lng = floatval($photo['lng']);
    $thumb = mysqli_real_escape_string($link, $photo['thumb']);
    $alblnk = mysqli_real_escape_string($link, $photo['alb']);
    $time = mysqli_real_escape_string($link, $photo['taken']);
    $mid = mysqli_real_escape_string($link, $photo['nsize']);
    $imgHt = mysqli_real_escape_string($link, $photo['pHt']);
    $imgWd = mysqli_real_escape_string($link, $photo['pWd']);
    $org = mysqli_real_escape_string($link, $photo['org']);
    $addReq = "INSERT INTO ETSV (indxNo,folder,title,hpg,mpg,`desc`,lat,lng,"
        . "thumb,alblnk,date,mid,imgHt,imgWd,org) VALUES ('{$hikeNo}','{$folder}',"
        . "'{$title}','N','N','{$desc}',{$lat},{$lng},'{$thumb}','{$alblnk}',"
        . "'{$time}','{$mid}','{$imgHt}','{$imgWd}','{$org}');";
    $add = mysqli_query($link, $addReq);
    if (!$add) {
        echo "Failed to insert photo {$title}: " . mysqli_error($link) . PHP_EOL;
        exit;
    }
}
echo "Success";
