<?php
/**
 * Extract latest date from photos and write to db
 * * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$getPixReq
    = "SELECT `date` FROM `ETSV` WHERE `indxNo`=? ORDER BY `date` DESC LIMIT 1;";
$photoDates = $pdo->prepare($getPixReq);
$photoDates->execute([$hikeNo]);
$mostRecent = $photoDates->fetch(PDO::FETCH_ASSOC);
$lastHiked = substr($mostRecent['date'], 0, 10); // dispose of 'time'
$writeDateReq = "UPDATE `EHIKES` SET `last_hiked` = '{$lastHiked}' WHERE " .
    "`indxNo` = {$hikeNo};";
$writeDate = $pdo->query($writeDateReq);
