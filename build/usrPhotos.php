<?php
/**
 * This script is called via ajax from the ktesaUploader.js module.
 * One or more files will be POSTed from the form. This data will be used
 * to construct the images required for storage on the site and
 * corresponding data will be entered into the database. The files will
 * be checked for exif metadata.
 * PHP Version 7.1
 * 
 * @package Uploading
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../vendor/autoload.php";
/** 
 * THE FOLLOWING REQUIRES WILL BE REPLACED WITH A SINGLE LINE WHEN
 * 'PDO_complete' is merged:
 * require "../php/global_boot.php";
 */
require "../php/global_boot.php";

// POSTED DATA
$filedat = $_FILES['files'];
$noOfFiles = count($filedat['name']);
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

// Arrays for storing data to be placed in to TSV table
/**
 * Until adjustments are made for 'title' and 'desc', 'title' will be the 
 * same as 'mid'
 */
$imgName = []; // This corresponds to the 'mid' field photo id
$imgHt_n = []; // NOTE: nsize is stored in TSV to supply <src> tag attributes
$imgWd_n = [];
$imgHt_z = []; // Only needed for z_size file creation
$imgWd_z = [];
$orient = [];
$timestamp = [];
$lats = [];
$lngs = [];
$procs = []; // Boolean array of photos to be processed (some may not)
// container for exif data results
$upld_results = '';

// Process files
for ($i=0; $i<$noOfFiles; $i++) {
    $photo = $filedat['tmp_name'][$i];
    $fname = $filedat['name'][$i];
    if (exif_imagetype($photo) !== IMAGETYPE_JPEG) {
        $upld_results .= "Image " . $filedat['name'] . 
            " is not JPEG: No upload will occur." . PHP_EOL;
        $procs[$i] = false;
        continue;
    } else {
        $procs[$i] = true;
    }
    try {
        $exifData = exif_read_data($photo);
    } catch (\Exception $e) {
        $exifData = false;
        $upld_results .= "File " . $fname . " has no exif data" . PHP_EOL;
    }
    // All photos:
    $dot = strrpos($fname, ".");
    $imgName[$i] = substr($fname, 0, $dot);
    $orient[$i] = false;
    list($orgWd, $orgHt) = getimagesize($photo);
    // translate ht/wd into new n-size/z-size dimensions:
    $aspect = $orgHt/$orgWd;
    $imgWd_n[$i] = $n_size;
    $imgHt_n[$i] = intval($n_size * $aspect);
    $imgWd_z[$i] = $z_size;
    $imgHt_z[$i] = intval($z_size * $aspect);
    // Remaining values dependent on presence of exif data
    if ($exifData) {
        try {
            $orient[$i] = $exifData['Orientation'];
        }
        catch (\Exception $e) {
            $orient[$i] = false;
        }
        if ($orient[$i] == '6') {
            $tmpval = $imgHt_n[$i];
            $imgHt_n[$i] = $imgWd_n[$i];
            $imgWd_n[$i] = $tmpval;
            $tmpval = $imgHt_z[$i];
            $imgHt_z[$i] = $imgWd_z[$i];
            $imgWd_z[$i] = $tmpval;
        }
        if (!isset($exifData['DateTimeOriginal'])) {
            $timestamp[$i] = 0; // used to determine NULL entry in db
            $upld_results .= "File " . $fname . " has no date/time data" . PHP_EOL;
        } else {
            $timestamp[$i] = $exifData["DateTimeOriginal"];
            if ($timestamp[$i] == '') {
                $timestamp[$i] = 0; // used to determine NULL entry in db
                $upld_results .= "File " . $fname . 
                    " has no date/time data" . PHP_EOL;
            }
        }
        if (!isset($exifData["GPSLatitudeRef"])
            || !isset($exifData["GPSLatitude"])
        ) {
            // zero values will be used to determine if db NULLs are req'd
            $lats[$i] = 0;
            $lngs[$i] = 0;
            $upld_results .= "No lat/lng data found for " . $fname . PHP_EOL;
        } else {
            if ($exifData["GPSLatitudeRef"] == 'N') {
                $lats[$i] = mantissa($exifData["GPSLatitude"]);
            } else {
                $lats[$i] = -1 * mantissa($exifData["GPSLatitude"]);
            }
            if ($exifData["GPSLongitudeRef"] == 'E') {
                $lngs[$i] = mantissa($exifData["GPSLongitude"]);
            } else {
                $lngs[$i] = -1 * mantissa($exifData["GPSLongitude"]);
            }
        }
    } else {
        // Photos without exif data
        $timestamp[$i] = 0;
        $lats[$i] = 0;
        $lngs[$i] = 0;
    }
}
restore_error_handler();

// check the GD support in this version of php:
$GDsupport = gd_info();
if ($GDsupport['JPEG Support']) {
    for ($j=0; $j<$noOfFiles; $j++) {
        if ($procs[$j]) {
            $rotate = false;
            if ($orient[$j] == '6') {
                $rotate = true;
            }
            $nfileName = $imgName[$j] . "_n.jpg";
            $zfileName = $imgName[$j] . "_z.jpg";
            storeUploadedImage(
                $nfileName, $filedat['tmp_name'][$j],
                $imgWd_n[$j], $imgHt_n[$j], $rotate
            );
            storeUploadedImage(
                $zfileName, $filedat['tmp_name'][$j],
                $imgWd_z[$j], $imgHt_z[$j], $rotate
            );
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
// TSV data is stored in arrays, now enter the values in db:
for ($k=0; $k<count($imgName); $k++) {
    /**
     * Create VALUES list, adding NULLs where needed:
     * Always present: indxNo, title, mid, imgHt, imgWd
     */
    $valstr = "VALUES (?,?,"; // indxNo, title fields
    if ($picnames[$k] == "") {
        $vals = [$indxNo, $imgName[$k]];
    } else {
        $vals = [$indxNo, $picnames[$k]];
    }
    $vindx = 1; // index of last element in $vals array
    if ($picdescs[$k] == "") {
        $valstr .= "NULL,"; // desc field
    } else {
        $valstr .= "?,";
        $vindx++;
        $vals[$vindx] = $picdescs[$k];
    }
    if ($lats[$k] === 0 || $lngs[$k] === 0) {
        $valstr .= "NULL,NULL,";
    } else {
        $valstr .= "?,?,";
        $vindx++;
        $vals[$vindx] = $lats[$k];
        $vindx++;
        $vals[$vindx] = $lngs[$k];

    }
    if ($timestamp[$k] === 0) {
        $valstr .= "NULL,";
    } else {
        $valstr .= "?,";
        $vindx++;
        $vals[$vindx] = $timestamp[$k];
    }
    $valstr .= "?,?,?);";
    $vals[$vindx+1] = $imgName[$k];
    $vals[$vindx+2] = $imgHt_n[$k];
    $vals[$vindx+3] = $imgWd_n[$k];
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
}

// return json to ajax caller
if (isset($filedat)) {
    if ($upld_results !== '') {
        $msg = $upld_results;
    } else {
        $msg = "Uploaded " . $noOfFiles . " files";
    }
} else {
    $msg = "Failed to upload: Contact site master";
}
echo json_encode($msg);
