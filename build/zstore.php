<?php
/**
 * Receive posted data from ktesaUploader.js containing a 
 * resized, z_size image and save it for display on the page.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$serverData = $_FILES['file']['tmp_name'];
$fname = $_POST['fname'];

$photoInfo = [];  // to be returned to caller
list($width, $height) = getimagesize($serverData);
$photoInfo[0] = $width;
$photoInfo[1] = $height;
$dot = strrpos($fname, ".");
$basename = substr($fname, 0, $dot);

if (($data = file_get_contents($serverData)) === false) {
    throw new Exception(
        "Uploaded server data could not be retrieved for {$fanme}\n" .
        "Error occurred in zstore.php"
    );
}

/**
 * Find the path to the pictures directory
 */ 
$picdir = "";
$current = getcwd();
$prev = $current;
while (!in_array('pictures', scandir($current))) {
    $picdir .= "../";
    chdir('..');
    $current = getcwd();
}
chdir($prev);
$picdir .= 'pictures/zsize/';

/**
 * Determine next 'thumb' value for new entry and create $newname
 */
$tval = "SELECT `thumb` FROM `TSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$tresult = $pdo->query($tval);
$tmax = $tresult->fetch(PDO::FETCH_NUM);
$eval = "SELECT `thumb` FROM `ETSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$eresult = $pdo->query($eval);
$emax = $eresult->fetch(PDO::FETCH_NUM);
$max = $emax[0] > $tmax[0] ? $emax[0] : $tmax[0];
$newthumb = (int)$max + 1;
$zimg = $basename . '_' . $newthumb . '_z.jpg';
$photoInfo[2] = $zimg;
$photoInfo[3] = $newthumb;
$photoInfo[4] = $picdir;
$filename = $picdir . $zimg;

if (file_put_contents($filename, $data) === false) {
    throw new Exception("Could not store image data from upload");
}
echo json_encode($photoInfo);
