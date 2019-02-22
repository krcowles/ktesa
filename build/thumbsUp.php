<?php
/**
 * This is a one-use script to update the 'thumb' fields in both TSV
 * and ETSV by providing incrementing (unique) integers.
 * PHP Version 7.1
 * 
 * @package Test
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

// NOTE: You must do both tables at one time to properly utilize $indx
$tsv = "SELECT picIdx,thumb FROM TSV;";
$thumbs = $pdo->query($tsv);
$indx = 1;
foreach ($thumbs as $row) {
    $req = "UPDATE TSV Set thumb = ? WHERE picIdx = ?;";
    $pdo->prepare($req)->execute([$indx++, $row['picIdx']]);
}
$etsv = "SELECT picIdx,thumb FROM ETSV;";
$ethumbs = $pdo->query($etsv);
foreach ($ethumbs as $row) {
    $req = "UPDATE ETSV Set thumb = ? WHERE picIdx = ?;";
    $pdo->prepare($req)->execute([$indx++, $row['picIdx']]);
}
echo "TSV & ETSV 'thumb' fields are now incrementing (unique) integers";
