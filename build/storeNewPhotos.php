<?php
/**
 * This script accepts data from a javascript ajax POST. The data contains
 * objects defining the photos selected to be added to the editor, and a final
 * object carrying miscellaneous info needed to save the photo data to ETSV.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../php/global_boot.php";

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
foreach ($saved as $photo) {
    $pix = [$hikeNo, $photo['folder'],$photo['pic'],$photo['desc'],
    floatval($photo['lat']),floatval($photo['lng']),$photo['thumb'],
    $photo['alb'],$photo['taken'],$photo['nsize'],$photo['pHt'],
    $photo['pWd'],$photo['org']];
    $addReq = "INSERT INTO ETSV
        (indxNo,folder,title,hpg,mpg,`desc`,
            lat,lng,thumb,alblnk,`date`,mid,imgHt,imgWd,org) 
        VALUES (?,?,?,'N','N',?,?,?,?,?,?,?,?,?,?);";
    $addpix = $pdo->prepare($addReq);
    $addpix->execute($pix);
}
echo "Success";
