<?php
/**
 * This script is called via ajax from the ktesaUploader.js module
 * AFTER a successful photo upload. If the photo did not upload, this
 * script will not be executed for the corresponding photo. The script
 * merely writes the provided photo data into the ETSV table.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
// POSTED DATA
$fname     = filter_input(INPUT_POST, 'fname');
$indxNo    = filter_input(INPUT_POST, 'indxNo');
$descstr   = filter_input(INPUT_POST, 'descstr');
// note: lat/lng produce 'false' when fed null by ktesaUploader.js and validated
$photoLat  = filter_input(INPUT_POST, 'lat', FILTER_VALIDATE_FLOAT);
$photoLng  = filter_input(INPUT_POST, 'lng', FILTER_VALIDATE_FLOAT);
$photoDate = filter_input(INPUT_POST, 'date');
$orgHeight = filter_input(INPUT_POST, 'origHt', FILTER_VALIDATE_INT);
$orgWidth  = filter_input(INPUT_POST, 'origWd', FILTER_VALIDATE_INT);
$orient    = filter_input(INPUT_POST, 'orient');
$newThumb  = filter_input(INPUT_POST, 'thumb');
if ($orient == '6' || $orient == '8') {
    $tmp = $orgHeight;
    $orgHeight = $orgWidth;
    $orgWidth  = $tmp;
}
// prep data:
$picDesc   = json_decode($descstr);
$dot = strrpos($fname, ".");
$imgName = substr($fname, 0, $dot);
if ($photoLng) {
    $photoLng = $photoLng > 0 ? -$photoLng : $photoLng;
}

/**
 * Create VALUES list, adding NULLs where needed:
 * Always present (or explicitly set): indxNo, title, hpg, mpg, mid, imgHt, imgWd
 */
$valstr = "VALUES (?,?,'N','N',"; // indxNo, title fields
$vals = [$indxNo, $imgName];

$vindx = 1; // index of last element in $vals array
if ($picDesc == "") {
    $valstr .= "NULL,"; // desc field
} else {
    $valstr .= "?,";
    $vindx++;
    $vals[$vindx] = $picDesc;
}
if (!$photoLat || !$photoLng) {
    $valstr .= "NULL,NULL,";
} else {
    $valstr .= "?,?,";
    $vindx++;
    $vals[$vindx] = (int) ((float) $photoLat * LOC_SCALE);
    $vindx++;
    $vals[$vindx] = (int) ((float) $photoLng * LOC_SCALE);
}
$valstr .= "?,";
$vindx++;
$vals[$vindx] = $newThumb;
if (empty($photoDate)) {
    $valstr .= "NULL,";
} else {
    $valstr .= "?,";
    $vindx++;
    $vals[$vindx] = $photoDate;
}
$valstr .= "?,?,?);";
$vals[$vindx+1] = $imgName;
$vals[$vindx+2] = $orgHeight;
$vals[$vindx+3] = $orgWidth;
$tsvReq
    = "INSERT INTO ETSV (`indxNo`,`title`,`hpg`,`mpg`,`desc`,`lat`,`lng`," .
        "`thumb`,`date`,`mid`,`imgHt`,`imgWd`) " . $valstr;
$tsv = $pdo->prepare($tsvReq);
$tsv->execute($vals);
