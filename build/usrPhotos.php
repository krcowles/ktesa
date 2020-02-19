<?php
/**
 * This script is called via ajax from the ktesaUploader.js module.
 * One photo file will be POSTed from the form. It will be resized and stored
 * in the pictures directory (nsize, zsize).
 * PHP Version 7.1
 * 
 * @package Uploading
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
// POSTED DATA
$filedat = $_FILES['file'];
$fname = $filedat['name'];
$photo = $filedat['tmp_name'];
$fstat = $filedat['error'];
if ($fstat !== UPLOAD_ERR_OK) {
    echo "Error: " . uploadErr($fstat) . ": File " . $fname;
}
$dot = strrpos($fname, ".");
$imgName = substr($fname, 0, $dot);
$orient    = filter_input(INPUT_POST, 'orient', FILTER_VALIDATE_INT);
$orgHeight = filter_input(INPUT_POST, 'origHt', FILTER_VALIDATE_INT);
$orgWidth  = filter_input(INPUT_POST, 'origWd', FILTER_VALIDATE_INT);
$n_size = 320;
$z_size = 640;
// translate ht/wd into new n-size/z-size dimensions:
$aspect = $orgHeight/$orgWidth;
$imgWd_n = $n_size;
$imgHt_n = intval($n_size * $aspect);
$imgWd_z = $z_size;
$imgHt_z = intval($z_size * $aspect);
// Size width definitions:
$n_size = 320;
$z_size = 640;
// translate ht/wd into new n-size/z-size dimensions:
$aspect = $orgHeight/$orgWidth;
$imgWd_n = $n_size;
$imgHt_n = intval($n_size * $aspect);
$imgWd_z = $z_size;
$imgHt_z = intval($z_size * $aspect);
// determine next 'thumb' value for new entry
$tval = "SELECT `thumb` FROM `TSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$tresult = $pdo->query($tval);
$tmax = $tresult->fetch(PDO::FETCH_NUM);
$eval = "SELECT `thumb` FROM `ETSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$eresult = $pdo->query($eval);
$emax = $eresult->fetch(PDO::FETCH_NUM);
$max = $emax[0] > $tmax[0] ? $emax[0] : $tmax[0];
$newthumb = (int)$max + 1;

$rotate = false;
if ($orient == '6') {
    $rotate = true;
}
$nfileName = $imgName . "_" . $newthumb . "_n.jpg";
$zfileName = $imgName . "_" . $newthumb . "_z.jpg";
$size = "n";

// see buildFunctions.php: note the class SimpleImage checks for GD support
storeUploadedImage(
    $nfileName, $photo, $imgWd_n, $imgHt_n, $rotate, $size
);
$size = "z";
storeUploadedImage(
    $zfileName, $photo, $imgWd_z, $imgHt_z, $rotate, $size
);
echo $newthumb;
