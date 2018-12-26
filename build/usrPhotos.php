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
$filedat = $_FILES['files'];
if (isset($filedat)) {
    $msg = $_FILES['files']['name'][0];
} else {
    $msg = "Failed to upload: Contact site master";
}
echo json_encode($msg);
