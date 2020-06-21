<?php
/**
 * TSV data is passed in via ajax from ktesaUploader.js; A new pictures
 * directory filename is formed based on TSV/ETSV `thumb` value and the
 * tmp picture is then moved to the pictures directory. Then the ETSV
 * data is updated for the image.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$ehike = filter_input(INPUT_POST, 'ehike');
$fname = filter_input(INPUT_POST, 'fname');
$thumb = filter_input(INPUT_POST, 'thumb');
$imght = filter_input(INPUT_POST, 'imght');
$imgwd = filter_input(INPUT_POST, 'imgwd');
$lat   = filter_input(INPUT_POST, 'lat');   // can be null
$lng   = filter_input(INPUT_POST, 'lng');   // can be null
$date  = filter_input(INPUT_POST, 'date');  // can be null

// save ETSV data
$dot = strrpos($fname, ".");
$title = substr($fname, 0, $dot);
if (empty($lat) || empty($lng)) {
    $dblat = null;
    $dblng = null;
} else {
    $dblat = $lat * LOC_SCALE;
    $dblng = $lng * LOC_SCALE;
}
$dbdate =  empty($date) ? null : $date;

$tsv_req = "INSERT INTO `ETSV` (`indxNo`,`title`,`hpg`,`mpg`,`lat`," .
    "`lng`,`thumb`,`date`,`mid`,`imgHt`,`imgWd`) VALUES " .
    "(?,?,'N','N',?,?,?,?,?,?,?);";
$tsv = $pdo->prepare($tsv_req);
$tsv->execute(
    [$ehike, $title, $dblat, $dblng, $thumb, $dbdate, $title, $imght, $imgwd]
);
