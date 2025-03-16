<?php
/**
 * This script allows a user to add location data to a photo 
 * that was uploaded without it.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$tsv_picid = filter_input(INPUT_POST, 'id');
$photo_lat 
    = filter_input(
        INPUT_POST, 'photolat', FILTER_VALIDATE_FLOAT
    );
$photo_lng 
    = filter_input(
        INPUT_POST, 'photolng', FILTER_VALIDATE_FLOAT
    );

$setlat = round($photo_lat, 7) * LOC_SCALE;
$setlng = round($photo_lng, 7) * LOC_SCALE;

$updateReq = "UPDATE `ETSV` Set `lat`=?,`lng`=? WHERE `picIdx`=?;";
$update = $pdo->prepare($updateReq);
$update->execute([$setlat, $setlng, $tsv_picid]);
echo "OK";
