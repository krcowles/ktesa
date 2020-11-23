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
verifyAccess('ajax');

$blob  = $_FILES['file']['tmp_name'];
$ehike = filter_input(INPUT_POST, 'ehike');
$fname = filter_input(INPUT_POST, 'fname');
$imght = filter_input(INPUT_POST, 'imght');
$imgwd = filter_input(INPUT_POST, 'imgwd');
$lat   = filter_input(INPUT_POST, 'lat', FILTER_VALIDATE_FLOAT);
$lng   = filter_input(INPUT_POST, 'lng', FILTER_VALIDATE_FLOAT);
$date  = filter_input(INPUT_POST, 'date');
$map   = filter_input(INPUT_POST, 'mappable', FILTER_VALIDATE_BOOLEAN);

if (($image = file_get_contents($blob)) === false) {
    throw new Exception(
        "Uploaded server data could not be retrieved for {$fanme}\n" .
        "Error occurred in zstore.php"
    );
}

// get file name without extension:
$dot = strrpos($fname, ".");
$basename = substr($fname, 0, $dot); // this is the 'title' field in ETSV

 // Determine next 'thumb' value for new entry and create filepath for image
$pictures_directory = getPicturesDirectory();
$tval = "SELECT `thumb` FROM `TSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$tresult = $pdo->query($tval);
$tmax = $tresult->fetch(PDO::FETCH_NUM);
$eval = "SELECT `thumb` FROM `ETSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$eresult = $pdo->query($eval);
$emax = $eresult->fetch(PDO::FETCH_NUM);
$max = $emax[0] > $tmax[0] ? $emax[0] : $tmax[0];
$newthumb = (int)$max + 1;
$zimg = $basename . '_' . $newthumb . '_z.jpg';
$filename = $pictures_directory . $zimg;

// prepare database entries
if ($lat == 0 || $lng == 0) {
    $dblat = null;
    $dblng = null;
} else {
    $dblat = $lat * LOC_SCALE;
    $dblng = $lng * LOC_SCALE;
}
$dbdate =  strlen($date) > 1 ? $date : null;

$tsv_req = "INSERT INTO `ETSV` (`indxNo`,`title`,`hpg`,`mpg`,`lat`," .
    "`lng`,`thumb`,`date`,`mid`,`imgHt`,`imgWd`) VALUES " .
    "(?,?,'N','N',?,?,?,?,?,?,?);";
$tsv = $pdo->prepare($tsv_req);
$tsv->execute(
    [$ehike, $basename, $dblat, $dblng, $newthumb, $dbdate, $basename, 
    $imght, $imgwd]
);

if (file_put_contents($filename, $image) === false) {
    throw new Exception("Could not store image data from upload");
}

if ($map) {
    echo "YES";
} else {
    echo "NO";
}
