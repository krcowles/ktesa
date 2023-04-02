<?php
/**
 * TSV data is passed in via ajax from ktesaUploader.js or from heic_convert.ts;
 * A new thumb value (unique photo id) is calculated and stored - along with its
 * corresponding input data - in the ETSV table. A new pictures directory filename
 * is formed, based on the new `thumb` value, and the tmp picture is then moved to
 * the pictures directory. Return data depends on which source invoked this routine.
 * PHP Version 7.4
 * 
 * @package Ktesa
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
$conv  = isset($_POST['page']) ? true : false;

// prepare database entries
if ($lat == 0 || $lng == 0) {
    $dblat = null;
    $dblng = null;
} else {
    $dblat = $lat * LOC_SCALE;
    $dblng = $lng * LOC_SCALE;
}
$dbdate =  strlen($date) > 1 ? $date : null;

if (($image = file_get_contents($blob)) === false) {
    throw new Exception(
        "Uploaded server data could not be retrieved for {$fname}\n" .
        "Error occurred in zstore.php"
    );
}

// get file name without extension:
$dot = strrpos($fname, ".");
$basename = substr($fname, 0, $dot); // this is the 'title' field in ETSV

$pictures_directory = getPicturesDirectory();
// Determine next 'thumb' value for new entry and create filepath for image
$tval = "SELECT `thumb` FROM `TSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";
$eval = "SELECT `thumb` FROM `ETSV` ORDER BY CAST(thumb AS UNSIGNED) DESC LIMIT 1;";

$tresult = $pdo->query($tval);
$tmax = $tresult->fetch(PDO::FETCH_NUM);
$eresult = $pdo->query($eval);
$emax = $eresult->fetch(PDO::FETCH_NUM);
// ETSV may be empty
if ($emax === false) {
    $max = $tmax[0];
} else {
    $max = $emax[0] > $tmax[0] ? $emax[0] : $tmax[0];
}
$newthumb = (int)$max + 1;
// Note: 'org' field sequences are established by makeThumbs.js
$tsv_req = "INSERT INTO `ETSV` (`indxNo`,`title`,`hpg`,`mpg`,`lat`," .
    "`lng`,`thumb`,`date`,`mid`,`imgHt`,`imgWd`) VALUES " .
    "(?,?,'N','N',?,?,?,?,?,?,?);";
$tsv = $pdo->prepare($tsv_req);
$tsv->execute(
    [$ehike, $basename, $dblat, $dblng, $newthumb, $dbdate, $basename, 
    $imght, $imgwd]
);

$zimg = $basename . '_' . $newthumb . '_z.jpg';
$filename = $pictures_directory . $zimg;
if (file_put_contents($filename, $image) === false) {
    throw new Exception("Could not store image data from upload");
}

if ($conv) {
    if ($map) {
        echo "YES.{$newthumb}";
    } else {
        echo "NO.{$newthumb}";
    }
} else {
    if ($map) {
        echo "YES";
    } else {
        echo "NO";
    }
}
