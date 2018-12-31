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

$filedat = $_FILES['files'];
$noOfFiles = count($filedat['name']);

// Look for exif data; report if none
set_error_handler(function() {
    throw new Exception();
}, E_ALL); 
$exif_results = '';
$orient = [];
for ($i=0; $i<$noOfFiles; $i++) {
    $photo = $filedat['tmp_name'][$i];
    $orient[$i] = false;
    try {
        $exifData = exif_read_data($photo);
    } catch (\Exception $e) {
        $exifData = false;
        $exif_results .= "File " . $filedat['name'][$i] . " has no exif data" . PHP_EOL;
    }
    if ($exifData) {
        try {
            $orient[$i] = $exifData['Orientation'];
        }
        catch (\Exception $e) {
            $orient[$i] = false;
        }
    }
}
restore_error_handler();
// check the GD support in this version of php:
$GDsupport = gd_info();
if ($GDsupport['JPEG Support']) {
    $zwidth = 640;
    for ($j=0; $j<$noOfFiles; $j++) {
        list($width, $height) = getimagesize($filedat['tmp_name'][$j]);
        $rotate = false;
        if ($orient[$j] == '6') {
            $rotate = true;
        }
        /* Don't believe this to be necessary:
        else {
            if ($height > $width) {
                $rotate = true;
            }
        }
        */
        $aspect = $height / $width;
        $zheight = intval($zwidth * $aspect);
        store_uploaded_image($filedat['name'][$j], $filedat['tmp_name'][$j],
            $zwidth, $zheight, $rotate);
    }
}
if (isset($filedat)) {
    if ($exif_results !== '') {
        $msg = $exif_results;
    } else {
        $msg = "Retrieved " . $noOfFiles . " files";
    }
} else {
    $msg = "Failed to upload: Contact site master";
}
echo json_encode($msg);
// TEMPORARY FUNCTION STORAGE FOR TESTING ONLY!!
function store_uploaded_image($old_fname, $old_file, $new_img_width,
        $new_img_height, $rotated) {
    $target_dir = "../tmp/";
    $target_file = $target_dir . $old_fname;
    $image = new \claviska\SimpleImage();
    $image->fromFile($old_file);
    $image->autoOrient();
    if ($rotated) {
        $tmp = $new_img_height;
        $new_img_height = $new_img_width;
        $new_img_width = $tmp;
    }
    $image->resize($new_img_width, $new_img_height);
    $image->toFile($target_file);
    //return name of saved file in case you want to store it in you database or show confirmation message to user
    return $target_file;
}