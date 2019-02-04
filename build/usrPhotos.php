<?php
/**
 * This script is called via ajax from the ktesaUploader.js module.
 * One photo file will be POSTed from the form, accompanied by a photo
 * name and description, and the corresponding EHIKES hike number.
 * This data will be used to construct the images required for storage on
 * the site and corresponding data will be entered into the database.
 * PHP Version 7.1
 * 
 * @package Uploading
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../vendor/autoload.php";
require "../php/global_boot.php";

// POSTED DATA
$filedat = $_FILES['file'];
$photo = $filedat['tmp_name'];
$fname = $filedat['name'];
$indxNo = filter_input(INPUT_POST, 'indx');
$namedat = filter_input(INPUT_POST, 'namestr');
$descdat = filter_input(INPUT_POST, 'descstr');
$picnames = json_decode($namedat);
$picdescs = json_decode($descdat);
// Size width definitions:
$n_size = 320;
$z_size = 640;

set_error_handler(
    function () {
        throw new Exception();
    }, E_ALL
); 

// container for exif data results
$upld_results = '';

// Process files
if (exif_imagetype($photo) !== IMAGETYPE_JPEG) {
    $upld_results .= "Image " . $fname . 
        " is not JPEG: No upload will occur." . PHP_EOL;
    $process = false;
} else {
    $process = true;
}
try {
    $exifData = exif_read_data($photo);
} catch (\Exception $e) {
    $exifData = false;
    $upld_results .= "File " . $fname . " has no exif data" . PHP_EOL;
}
$dot = strrpos($fname, ".");
$imgName = substr($fname, 0, $dot);
$orient = false;
list($orgWd, $orgHt) = getimagesize($photo);
// translate ht/wd into new n-size/z-size dimensions:
$aspect = $orgHt/$orgWd;
$imgWd_n = $n_size;
$imgHt_n = intval($n_size * $aspect);
$imgWd_z = $z_size;
$imgHt_z = intval($z_size * $aspect);
// Remaining values dependent on presence of exif data
if ($exifData) {
    try {
        $orient = $exifData['Orientation'];
    }
    catch (\Exception $e) {
        $orient = false;
    }
    if ($orient == '6') {
        $tmpval = $imgHt_n;
        $imgHt_n = $imgWd_n;
        $imgWd_n = $tmpval;
        $tmpval = $imgHt_z;
        $imgHt_z = $imgWd_z;
        $imgWd_z = $tmpval;
    }
    if (!isset($exifData['DateTimeOriginal'])) {
        $timestamp = 0; // used to determine NULL entry in db
        $upld_results .= "File " . $fname . " has no date/time data" . PHP_EOL;
    } else {
        $timestamp = $exifData["DateTimeOriginal"];
        if ($timestamp == '') {
            $timestamp = 0; // used to determine NULL entry in db
            $upld_results .= "File " . $fname . 
                " has no date/time data" . PHP_EOL;
        }
    }
    if (!isset($exifData["GPSLatitudeRef"])
        || !isset($exifData["GPSLatitude"])
    ) {
        // zero values will be used to determine if db NULLs are req'd
        $lats = 0;
        $lngs = 0;
        $upld_results .= "No lat/lng data found for " . $fname . PHP_EOL;
    } else {
        if ($exifData["GPSLatitudeRef"] == 'N') {
            $lats = mantissa($exifData["GPSLatitude"]);
        } else {
            $lats = -1 * mantissa($exifData["GPSLatitude"]);
        }
        if ($exifData["GPSLongitudeRef"] == 'E') {
            $lngs = mantissa($exifData["GPSLongitude"]);
        } else {
            $lngs = -1 * mantissa($exifData["GPSLongitude"]);
        }
    }
} else {
    // Photos without exif data
    $timestamp = 0;
    $lats = 0;
    $lngs = 0;
}

restore_error_handler();

// check the GD support in this version of php:
$GDsupport = gd_info();
if ($GDsupport['JPEG Support']) {
    if ($process) {
        $rotate = false;
        if ($orient == '6') {
            $rotate = true;
        }
        $nfileName = $imgName . "_n.jpg";
        $zfileName = $imgName . "_z.jpg";
        $size = "n";
        try {
            storeUploadedImage(
                $nfileName, $photo, $imgWd_n, $imgHt_n, $rotate, $size
            );
        }
        catch (Exception $e) {
            $msg = "No n-size image write: " . $e->getMessage();
            die(json_encode($msg));
        }
        $size = "z";
        try {
            storeUploadedImage(
                $zfileName, $photo, $imgWd_z, $imgHt_z, $rotate, $size
            );
        }
        catch (Exception $e) {
            $msg = "No z-size image write: " . $e->getMessage();
            die(json_encode($msg));
        }
    }
} else {
    $upld_results .= "There is no support for image resizing;";
    die(json_encode($upld_results));
}

/*
 *  THIS IS THE CODE THAT MAY CHANGE WHEN TSV GETS REDEFINED 
 *  ALSO: WHEN 'PDO_complete' is merged, the PDO connection will already 
 *  be in place, hence eliminate the first line of this code
 */

/**
 * Create VALUES list, adding NULLs where needed:
 * Always present: indxNo, title, mid, imgHt, imgWd
 */
$valstr = "VALUES (?,?,"; // indxNo, title fields
if ($picnames == "") {
    $vals = [$indxNo, $imgName];
} else {
    $vals = [$indxNo, $picnames];
}
$vindx = 1; // index of last element in $vals array
if ($picdescs == "") {
    $valstr .= "NULL,"; // desc field
} else {
    $valstr .= "?,";
    $vindx++;
    $vals[$vindx] = $picdescs;
}
if ($lats === 0 || $lngs === 0) {
    $valstr .= "NULL,NULL,";
} else {
    $valstr .= "?,?,";
    $vindx++;
    $vals[$vindx] = $lats;
    $vindx++;
    $vals[$vindx] = $lngs;

}
if ($timestamp === 0) {
    $valstr .= "NULL,";
} else {
    $valstr .= "?,";
    $vindx++;
    $vals[$vindx] = $timestamp;
}
$valstr .= "?,?,?);";
$vals[$vindx+1] = $imgName;
$vals[$vindx+2] = $imgHt_n;
$vals[$vindx+3] = $imgWd_n;
$tsvReq
    = "INSERT INTO ETSV (indxNo,title,`desc`,lat,lng,`date`,mid,imgHt,imgWd) "
        . $valstr;
$tsv = $pdo->prepare($tsvReq);
try {
    $tsv->execute($vals);
}
catch (Exception $e) {
    $msg = "TSV fail: " . $e->getMessage();
    die(json_encode($msg));
}

// return json to ajax caller
if (isset($filedat)) {
    if ($upld_results !== '') {
        $msg = $upld_results;
    } else {
        $msg = "Uploaded {$fname}" . PHP_EOL;
    }
} else {
    $msg = "Failed to upload: Contact site master" . PHP_EOL;
}
echo json_encode($msg);
